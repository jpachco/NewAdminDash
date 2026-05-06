<?php
/**
 * Clase de Ventas
 * Compatible con: dbhighlife (HL), dbdistribucion (MF), dbRoberts (RB)
 * Calendario retail: dbLamberti (BG) → tabla calendario
 * Semana retail: Lunes a Domingo — columnas fei (inicio) y fecf (fin)
 */
class Sales {

    private const DB_MAP = [
        'HL' => 'HL',
        'MF' => 'MF',
        'RB' => 'RB',
    ];

    private static function db(string $base): \PDO {
        $key = self::DB_MAP[$base] ?? 'HL';
        return Database::getConnection($key);
    }

    // ─────────────────────────────────────────
    // CALENDARIO RETAIL
    // Siempre desde dbLamberti (BG)
    // ─────────────────────────────────────────

    /**
     * Lista de semanas retail disponibles para el selector
     * Ordenadas de más reciente a más antigua
     */
    public static function getSemanasRetail(int $anio = 0): array {
        try {
            $db   = Database::getConnection('BG');
            $anio = $anio ?: (int)date('Y');
            $stmt = $db->prepare("
                SELECT Anio, Sem, fei, fecf
                FROM dbLamberti.dbo.calendario
                WHERE Anio = ?
                ORDER BY Sem ASC
            ");
            $stmt->bindValue(1, $anio, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getSemanasRetail error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene fei/fecf de una semana retail específica
     * @return array ['fei' => string, 'fecf' => string] | []
     */
    public static function getRangoSemana(int $anio, int $sem): array {
        try {
            $db   = Database::getConnection('BG');
            $stmt = $db->prepare("
                SELECT fei, fecf
                FROM dbLamberti.dbo.calendario
                WHERE Anio = ? AND Sem = ?
            ");
            $stmt->bindValue(1, $anio, \PDO::PARAM_INT);
            $stmt->bindValue(2, $sem,  \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getRangoSemana error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la semana retail actual (la que contiene hoy)
     * @return array ['Anio' => int, 'Sem' => int, 'fei' => string, 'fecf' => string]
     */
    public static function getSemanaActual(): array {
        try {
            $db   = Database::getConnection('BG');
            $stmt = $db->query("
                SELECT Anio, Sem, fei, fecf
                FROM dbLamberti.dbo.calendario
                WHERE CAST(GETDATE() AS DATE) BETWEEN fei AND fecf
            ");
            return $stmt->fetch() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getSemanaActual error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Años disponibles en el calendario retail
     */
    public static function getAniosRetail(): array {
        try {
            $db   = Database::getConnection('BG');
            $stmt = $db->query("
                SELECT DISTINCT Anio
                FROM dbLamberti.dbo.calendario
                ORDER BY Anio DESC
            ");
            return array_column($stmt->fetchAll() ?: [], 'Anio');
        } catch (Exception $e) {
            error_log("Sales::getAniosRetail error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────

    /**
     * Construye WHERE + params desde filtros
     * Si se pasa semana retail, usa fei/fecf como rango de fechas
     */
    private static function buildWhere(array $filters): array {
        $clauses = [];
        $params  = [];

        // ── Semana retail tiene prioridad sobre fechas manuales ──
        if (!empty($filters['sem_anio']) && !empty($filters['sem_num'])) {
            $rango = self::getRangoSemana((int)$filters['sem_anio'], (int)$filters['sem_num']);
            if ($rango) {
                $clauses[] = "CAST([Fecha Registro] AS DATE) BETWEEN ? AND ?";
                $params[]  = $rango['fei'];
                $params[]  = $rango['fecf'];
            }
        } elseif (!empty($filters['fecha_ini']) || !empty($filters['fecha_fin'])) {
            if (!empty($filters['fecha_ini'])) {
                $clauses[] = "[Fecha Registro] >= ?";
                $params[]  = $filters['fecha_ini'] . ' 00:00:00';
            }
            if (!empty($filters['fecha_fin'])) {
                $clauses[] = "[Fecha Registro] <= ?";
                $params[]  = $filters['fecha_fin'] . ' 23:59:59';
            }
        } else {
            // Sin filtro de fecha → mes actual por defecto
            $clauses[] = "MONTH([Fecha Registro]) = MONTH(GETDATE())";
            $clauses[] = "YEAR([Fecha Registro])  = YEAR(GETDATE())";
        }

        if (!empty($filters['tienda'])) {
            $clauses[] = "[Cód. Almacén] = ?";
            $params[]  = $filters['tienda'];
        }
        if (!empty($filters['familia'])) {
            $clauses[] = "Familia = ?";
            $params[]  = $filters['familia'];
        }
        if (!empty($filters['vendedor'])) {
            $clauses[] = "[Cód. Vendedor] = ?";
            $params[]  = $filters['vendedor'];
        }

        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
        return ['where' => $where, 'params' => $params];
    }

    private static function execute(\PDO $db, string $sql, array $params, array $intIndexes = []): \PDOStatement {
        $stmt = $db->prepare($sql);
        foreach ($params as $i => $value) {
            $pos  = $i + 1;
            $type = in_array($i, $intIndexes) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue($pos, $value, $type);
        }
        $stmt->execute();
        return $stmt;
    }

    // ─────────────────────────────────────────
    // KPI: RESUMEN PERÍODO
    // ─────────────────────────────────────────

    public static function getResumen(string $base, array $filters = []): array {
        try {
            $db  = self::db($base);
            $w   = self::buildWhere($filters);
            $sql = "
                SELECT
                    COUNT(DISTINCT [Nº Documento])   AS total_transacciones,
                    COUNT(DISTINCT [Cód. Almacén])   AS total_tiendas,
                    COUNT(DISTINCT [Cód. Vendedor])  AS total_vendedores,
                    SUM(Cantidad)                    AS total_piezas,
                    SUM(Importe)                     AS importe_total,
                    SUM([Importe Iva Incl.])         AS importe_iva,
                    AVG(Importe)                     AS ticket_promedio
                FROM Ventas {$w['where']}
            ";
            $stmt = self::execute($db, $sql, $w['params']);
            return $stmt->fetch() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getResumen error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * KPI: Misma semana retail del año anterior
     * Busca en el calendario la semana con mismo Sem pero Anio-1
     */
    public static function getResumenSemanaAnioAnterior(string $base, int $anio, int $sem): array {
        try {
            // Obtener rango de la misma semana del año anterior
            $rangoAnt = self::getRangoSemana($anio - 1, $sem);
            if (!$rangoAnt) return [];

            $db   = self::db($base);
            $stmt = $db->prepare("
                SELECT
                    SUM(Cantidad)                    AS total_piezas,
                    SUM(Importe)                     AS importe_total,
                    COUNT(DISTINCT [Nº Documento])   AS total_transacciones,
                    AVG(Importe)                     AS ticket_promedio
                FROM Ventas
                WHERE CAST([Fecha Registro] AS DATE) BETWEEN ? AND ?
            ");
            $stmt->execute([$rangoAnt['fei'], $rangoAnt['fecf']]);
            return $stmt->fetch() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getResumenSemanaAnioAnterior error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // KPI: MES ANTERIOR (comparativo original)
    // ─────────────────────────────────────────

    public static function getResumenMesAnterior(string $base): array {
        try {
            $db   = self::db($base);
            $stmt = $db->query("
                SELECT
                    SUM(Cantidad)                    AS total_piezas,
                    SUM(Importe)                     AS importe_total,
                    COUNT(DISTINCT [Nº Documento])   AS total_transacciones
                FROM Ventas
                WHERE MONTH([Fecha Registro]) = MONTH(DATEADD(MONTH, -1, GETDATE()))
                  AND YEAR([Fecha Registro])  = YEAR(DATEADD(MONTH, -1, GETDATE()))
            ");
            return $stmt->fetch() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getResumenMesAnterior error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // GRÁFICA TENDENCIA DIARIA
    // ─────────────────────────────────────────

    public static function getTendenciaDiaria(string $base, array $filters = []): array {
        try {
            $db  = self::db($base);
            $w   = self::buildWhere($filters);
            $sql = "
                SELECT
                    CAST([Fecha Registro] AS DATE)  AS fecha,
                    SUM(Cantidad)                   AS piezas,
                    SUM(Importe)                    AS importe,
                    COUNT(DISTINCT [Nº Documento])  AS transacciones
                FROM Ventas {$w['where']}
                GROUP BY CAST([Fecha Registro] AS DATE)
                ORDER BY fecha ASC
            ";
            $stmt = self::execute($db, $sql, $w['params']);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getTendenciaDiaria error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // VENTAS POR TIENDA
    // ─────────────────────────────────────────

    public static function getPorTienda(string $base, array $filters = []): array {
        try {
            $db  = self::db($base);
            $w   = self::buildWhere($filters);
            $sql = "
                SELECT
                    v.[Cód. Almacén]                 AS tienda,
                    t.Nombre                         AS nombre_tienda,
                    SUM(v.Cantidad)                  AS total_piezas,
                    SUM(v.Importe)                   AS importe_total,
                    COUNT(DISTINCT v.[Nº Documento]) AS transacciones
                FROM Ventas v
                LEFT JOIN Tienda t ON v.[Cód. Almacén] = t.Tienda
                {$w['where']}
                GROUP BY v.[Cód. Almacén], t.Nombre
                ORDER BY importe_total DESC
            ";
            $stmt = self::execute($db, $sql, $w['params']);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getPorTienda error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // VENTAS POR VENDEDOR
    // ─────────────────────────────────────────

    public static function getPorVendedor(string $base, array $filters = []): array {
        try {
            $db  = self::db($base);
            $w   = self::buildWhere($filters);
            $sql = "
                SELECT TOP 10
                    v.[Cód. Vendedor]                AS cod_vendedor,
                    vd.Nombre                        AS nombre_vendedor,
                    SUM(v.Cantidad)                  AS total_piezas,
                    SUM(v.Importe)                   AS importe_total,
                    COUNT(DISTINCT v.[Nº Documento]) AS transacciones
                FROM Ventas v
                LEFT JOIN Vendedores_62 vd ON v.[Cód. Vendedor] = vd.Codigo collate Latin1_General_100_CI_AS
                {$w['where']}
                GROUP BY v.[Cód. Vendedor], vd.Nombre
                ORDER BY importe_total DESC
            ";
            $stmt = self::execute($db, $sql, $w['params']);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getPorVendedor error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // VENTAS POR FAMILIA
    // ─────────────────────────────────────────

    public static function getPorFamilia(string $base, array $filters = []): array {
        try {
            $db  = self::db($base);
            $w   = self::buildWhere($filters);
            $and = $w['where'] ? 'AND' : 'WHERE';
            $sql = "
                SELECT
                    Familia,
                    SUM(Cantidad)                   AS total_piezas,
                    SUM(Importe)                    AS importe_total,
                    SUM([Importe Iva Incl.])        AS importe_iva,
                    COUNT(DISTINCT [Nº Documento])  AS transacciones
                FROM Ventas
                {$w['where']}
                {$and} Familia IS NOT NULL AND Familia <> ''
                GROUP BY Familia
                ORDER BY importe_total DESC
            ";
            $stmt = self::execute($db, $sql, $w['params']);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getPorFamilia error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // TOP PRODUCTOS
    // ─────────────────────────────────────────

    public static function getTopProductos(string $base, array $filters = [], int $top = 10): array {
        try {
            $db  = self::db($base);
            $w   = self::buildWhere($filters);
            $and = $w['where'] ? 'AND' : 'WHERE';
            $sql = "
                SELECT TOP {$top}
                    Sku,
                    [Descripción]                   AS descripcion,
                    Familia,
                    Subfamilia,
                    SUM(Cantidad)                   AS total_piezas,
                    SUM(Importe)                    AS importe_total,
                    AVG([Precio Venta])             AS precio_promedio
                FROM Ventas
                {$w['where']}
                {$and} Sku IS NOT NULL
                GROUP BY Sku, [Descripción], Familia, Subfamilia
                ORDER BY importe_total DESC
            ";
            $stmt = self::execute($db, $sql, $w['params']);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getTopProductos error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // DETALLE PAGINADO
    // ─────────────────────────────────────────

    public static function getDetalle(string $base, array $filters = [], int $page = 1, int $limit = ITEMS_PER_PAGE): array {
        try {
            $db     = self::db($base);
            $w      = self::buildWhere($filters);
            $offset = ($page - 1) * $limit;

            $sqlCount  = "SELECT COUNT(*) AS total FROM Ventas {$w['where']}";
            $stmtCount = self::execute($db, $sqlCount, $w['params']);
            $total     = (int)($stmtCount->fetch()['total'] ?? 0);

            $sql = "
                SELECT
                    [Fecha Registro]                AS fecha,
                    [Nº Documento]                  AS documento,
                    [Cód. Almacén]                  AS tienda,
                    [Cód. Vendedor]                 AS vendedor,
                    Sku,
                    [Descripción]                   AS descripcion,
                    Familia,
                    Color,
                    Talla,
                    Cantidad,
                    [Precio Venta]                  AS precio_venta,
                    Importe,
                    [Por Desc.]                     AS descuento,
                    [Importe Iva Incl.]             AS importe_iva
                FROM Ventas
                {$w['where']}
                ORDER BY [Fecha Registro] DESC
                OFFSET ? ROWS FETCH NEXT ? ROWS ONLY
            ";
            $params  = array_merge($w['params'], [$offset, $limit]);
            $intIdx  = [count($w['params']), count($w['params']) + 1];
            $stmt    = self::execute($db, $sql, $params, $intIdx);

            return [
                'data'  => $stmt->fetchAll() ?: [],
                'total' => $total,
                'pages' => (int)ceil($total / $limit),
            ];
        } catch (Exception $e) {
            error_log("Sales::getDetalle error: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'pages' => 0];
        }
    }

    // ─────────────────────────────────────────
    // FILTROS DINÁMICOS
    // ─────────────────────────────────────────

    public static function getTiendas(string $base): array {
        try {
            $db   = self::db($base);
            $stmt = $db->query("
                SELECT DISTINCT v.[Cód. Almacén] AS tienda, t.Nombre AS nombre
                FROM Ventas v
                LEFT JOIN Tienda t ON v.[Cód. Almacén] = t.Tienda
                ORDER BY v.[Cód. Almacén]
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getTiendas error: " . $e->getMessage());
            return [];
        }
    }

    public static function getFamilias(string $base): array {
        try {
            $db   = self::db($base);
            $stmt = $db->query("
                SELECT DISTINCT Familia
                FROM Ventas
                WHERE Familia IS NOT NULL AND Familia <> ''
                ORDER BY Familia
            ");
            return array_column($stmt->fetchAll() ?: [], 'Familia');
        } catch (Exception $e) {
            error_log("Sales::getFamilias error: " . $e->getMessage());
            return [];
        }
    }

    public static function getVendedores(string $base): array {
        try {
            $db   = self::db($base);
            $stmt = $db->query("
                SELECT DISTINCT v.[Cód. Vendedor] AS codigo, vd.Nombre AS nombre
                FROM Ventas v
                LEFT JOIN Vendedores_62 vd ON v.[Cód. Vendedor] = vd.Codigo
                WHERE v.[Cód. Vendedor] IS NOT NULL
                ORDER BY v.[Cód. Vendedor]
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Sales::getVendedores error: " . $e->getMessage());
            return [];
        }
    }
}
