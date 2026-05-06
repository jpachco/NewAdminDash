<?php
/**
 * Dashboard inventario multi-almacen basado en Mov. producto
 * Almacenes permitidos:
 * - H500 (Highlife)
 * - F500 (Mensfashion)
 * - R500 (Roberts)
 */
class InventoryWarehouses
{
    private const WAREHOUSES = [
        'H500' => ['db' => 'HL', 'empresa' => 'Highlife'],
        'F500' => ['db' => 'MF', 'empresa' => 'Mensfashion'],
        'R500' => ['db' => 'RB', 'empresa' => 'Roberts']
    ];

    private const COL_CANDIDATES = [
        'fecha' => ['Fecha registro', 'Fecha Registro'],
        'documento' => ['Nº documento', 'Nº Documento'],
        'almacen' => ['Cód. almacén', 'Cód. Almacén'],
        'tipo' => ['Tipo movimiento', 'Tipo Movimiento'],
        'concepto' => ['Concepto Movimiento'],
        'descripcion' => ['Descripción', 'Descripcion'],
        'familia' => ['Familia'],
        'marca' => ['Colección'],
        'Colección' => ['Colección'],
        'subfamilia' => ['Subfamilia', 'Temporada'],
        'proveedor_logistica' => ['Nº proveedor'],
        'proveedor' => ['Compra a-Nº proveedor', 'Proveedor', 'Nº proveedor', 'No. proveedor'],
        'modelo' => ['Cód. Producto Proveedor', 'Cod Producto Proveedor', 'Cod productoproveedor', 'Producto Proveedor'],
        'cantidad' => ['Cantidad'],
        'importe' => ['Importe'],
    ];

    private static function quoteIdentifier(string $name): string
    {
        return '[' . str_replace(']', ']]', $name) . ']';
    }

    private static function getColumnMap(\PDO $db): array
    {
        $stmt = $db->prepare("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME in('PRODUCTO_LOGISTICA','Mov. producto')
        ");
        $stmt->execute();
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $col = (string)$row['COLUMN_NAME'];
            $map[mb_strtolower($col)] = $col;
        }
        return $map;
    }

    private static function pickExpr(array $map, string $key, string $alias, bool $required = false): string
    {
        foreach (self::COL_CANDIDATES[$key] ?? [] as $candidate) {
            $found = $map[mb_strtolower($candidate)] ?? null;
            if ($found !== null) {
                return 'm.' . self::quoteIdentifier($found) . " AS {$alias}";
            }
        }

        if ($required) {
            throw new \RuntimeException("Columna requerida no encontrada para {$key}");
        }

        return "NULL AS {$alias}";
    }

    private static function pickWhereColumn(array $map, string $key, bool $required = false): ?string
    {
        foreach (self::COL_CANDIDATES[$key] ?? [] as $candidate) {
            $found = $map[mb_strtolower($candidate)] ?? null;
            if ($found !== null) {
                return 'm.' . self::quoteIdentifier($found);
            }
        }
        if ($required) {
            throw new \RuntimeException("Columna requerida no encontrada para {$key}");
        }
        return null;
    }

    private static function buildSelectParts(array $map): array
    {
        return [
            self::pickExpr($map, 'fecha', 'fecha', true),
            self::pickExpr($map, 'documento', 'documento', true),
            self::pickExpr($map, 'almacen', 'almacen', true),
            self::pickExpr($map, 'tipo', 'tipo_movimiento'),
            self::pickExpr($map, 'concepto', 'concepto'),
            self::pickExpr($map, 'descripcion', 'descripcion'),
            self::pickExpr($map, 'familia', 'familia'),
            self::pickExpr($map, 'marca', 'marca'),
            self::pickExpr($map, 'subfamilia', 'subfamilia'),
            self::pickExpr($map, 'proveedor', 'Nº proveedor'),
            self::pickExpr($map, 'modelo', 'modelo'),
            self::pickExpr($map, 'cantidad', 'cantidad', true),
            self::pickExpr($map, 'importe', 'importe', true),
        ];
    }

    // Dentro de InventoryWarehouses.php -> buildWhere
private static function buildWhere(array $map, string $warehouse, array $filters): array
{
    $clauses = [];
    $params = [];
    $fechaCol = self::pickWhereColumn($map, 'fecha', true);

    // LÓGICA DE TIEMPO ARMONIZADA
    if (!empty($filters['sem_num']) && $filters['sem_num'] > 0) {
        // Prioridad: Semana seleccionada
        $rango = Sales::getRangoSemana((int)$filters['sem_anio'], (int)$filters['sem_num']);
        if ($rango) {
            $clauses[] = "CAST({$fechaCol} AS DATE) BETWEEN ? AND ?";
            $params[]  = $rango['fei'];
            $params[]  = $rango['fecf'];
        }
    } elseif (!empty($filters['fecha_ini']) || !empty($filters['fecha_fin'])) {
        // Alternativa: Rango manual si no hay semana seleccionada
        if (!empty($filters['fecha_ini'])) {
            $clauses[] = "{$fechaCol} >= ?";
            $params[]  = $filters['fecha_ini'] . ' 00:00:00';
        }
        if (!empty($filters['fecha_fin'])) {
            $clauses[] = "{$fechaCol} <= ?";
            $params[]  = $filters['fecha_fin'] . ' 23:59:59';
        }
    }
    
        // Filtro de Almacén (Obligatorio)
        $almacenCol = self::pickWhereColumn($map, 'almacen', true);
        $clauses[] = "{$almacenCol} = ?";
        $params[] = $warehouse;

        $familyCol = self::pickWhereColumn($map, 'familia');
        if ($familyCol && !empty($filters['familia'])) {
            $clauses[] = "{$familyCol} = ?";
            $params[] = $filters['familia'];
        }

        $brandCol = self::pickWhereColumn($map, 'marca');
        if ($brandCol && !empty($filters['marca'])) {
            $clauses[] = "{$brandCol} = ?";
            $params[] = $filters['marca'];
        }

        $seasonCol = self::pickWhereColumn($map, 'subfamilia');
        if ($seasonCol && !empty($filters['subfamilia'])) {
            $clauses[] = "{$seasonCol} = ?";
            $params[] = $filters['subfamilia'];
        }

        $providerCol = self::pickWhereColumn($map, 'proveedor');
        if ($providerCol && !empty($filters['proveedor'])) {
            $clauses[] = "{$providerCol} = ?";
            $params[] = $filters['proveedor'];
        }

        $modelCol = self::pickWhereColumn($map, 'modelo');
        if ($modelCol && !empty($filters['modelo'])) {
            $clauses[] = "{$modelCol} = ?";
            $params[] = $filters['modelo'];
        }

        return ['sql' => implode(' AND ', $clauses), 'params' => $params];
    }

    public static function getFiltersOptions(): array
    {
        $options = [
            'familias' => [],
            'marcas' => [],
            'subfamilias' => [],
            'proveedores' => [],
            'modelos' => [],
        ];

        foreach (self::WAREHOUSES as $warehouse => $cfg) {
            try {
                $db = Database::getConnection($cfg['db']);
                $map = self::getColumnMap($db);
                $almacenCol ="";// self::pickWhereColumn($map, 'almacen', true);

                $queries = [
                    'familias' => self::pickWhereColumn($map, 'familia'),
                    'marcas' => self::pickWhereColumn($map, 'Colección'),
                    'subfamilias' => self::pickWhereColumn($map, 'subfamilia'),
                    'proveedores' => self::pickWhereColumn($map, 'proveedor_logistica'),
                    'modelos' => self::pickWhereColumn($map, 'modelo'),
                ];

                foreach ($queries as $target => $colExpr) {
                    if ($colExpr === null) {
                        continue;
                    }
                    ////{$almacenCol} = ? //SE ELIMINA ESTA LINEA DE CODIGO DEL SQL YA QUE SE SACA DE PRODUCTO Y NO DE MOVIMIENTO ANTES DE AND {$colExpr} IS NOT NULL
                    $sql = "
                        SELECT DISTINCT {$colExpr} AS val
                        FROM [PRODUCTO_LOGISTICA] m
                        WHERE 
                              {$colExpr} IS NOT NULL
                          AND LTRIM(RTRIM(CAST({$colExpr} AS NVARCHAR(255)))) <> ''
                        ORDER BY val
                    ";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$warehouse]);
                    foreach ($stmt->fetchAll() as $row) {
                        $value = trim((string)$row['val']);
                        if ($value !== '') {
                            $options[$target][$value] = $value;
                        }
                    }
                }
            } catch (\Throwable $e) {
                error_log('InventoryWarehouses::getFiltersOptions error: ' . $e->getMessage());
            }
        }

        foreach ($options as $key => $vals) {
            $arr = array_values($vals);
            sort($arr, SORT_NATURAL | SORT_FLAG_CASE);
            $options[$key] = $arr;
        }

        return $options;
    }

    public static function getKpis(array $filters = []): array
    {
        $totals = [
            'movimientos' => 0,
            'piezas_netas' => 0.0,
            'piezas_movidas' => 0.0,
            'importe_total' => 0.0,
            'modelos' => [],
        ];

        foreach (self::WAREHOUSES as $warehouse => $cfg) {
            try {
                $db = Database::getConnection($cfg['db']);
                $map = self::getColumnMap($db);
                $w = self::buildWhere($map, $warehouse, $filters);
                $cantidadCol = self::pickWhereColumn($map, 'cantidad', true);
                $importeCol = self::pickWhereColumn($map, 'importe', true);
                $modeloCol = self::pickWhereColumn($map, 'modelo');
                $modelSelect = $modeloCol ? "COUNT(DISTINCT {$modeloCol})" : "0";

                $sql = "
                    SELECT
                        COUNT(*) AS total_movimientos,
                        SUM(CAST({$cantidadCol} AS DECIMAL(18,2))) AS piezas_netas,
                        SUM(ABS(CAST({$cantidadCol} AS DECIMAL(18,2)))) AS piezas_movidas,
                        SUM(CAST({$importeCol} AS DECIMAL(18,2))) AS importe_total,
                        {$modelSelect} AS total_modelos
                    FROM [Mov. producto] m
                    WHERE {$w['sql']}
                ";
                $stmt = $db->prepare($sql);
                $stmt->execute($w['params']);
                $row = $stmt->fetch() ?: [];

                $totals['movimientos'] += (int)($row['total_movimientos'] ?? 0);
                $totals['piezas_netas'] += (float)($row['piezas_netas'] ?? 0);
                $totals['piezas_movidas'] += (float)($row['piezas_movidas'] ?? 0);
                $totals['importe_total'] += (float)($row['importe_total'] ?? 0);
                $totals['modelos'][] = (int)($row['total_modelos'] ?? 0);
            } catch (\Throwable $e) {
                error_log('InventoryWarehouses::getKpis error: ' . $e->getMessage());
            }
        }

        return [
            'movimientos' => $totals['movimientos'],
            'piezas_netas' => $totals['piezas_netas'],
            'piezas_movidas' => $totals['piezas_movidas'],
            'importe_total' => $totals['importe_total'],
            'modelos' => array_sum($totals['modelos']),
        ];
    }

    /**
     * KPIs logísticos calculables con Mov. producto.
     * Referencia conceptual: tiempo/costo/rotación/devoluciones/proveedores.
     */
    public static function getLogisticsKpis(array $filters = []): array
    {
        $agg = [
            'movimientos' => 0,
            'documentos' => 0,
            'importe_abs' => 0.0,
            'entradas' => 0.0,
            'salidas' => 0.0,
            'devoluciones' => 0,
            'proveedores' => [],
            'almacenes_activos' => 0,
        ];

        foreach (self::WAREHOUSES as $warehouse => $cfg) {
            try {
                $db = Database::getConnection($cfg['db']);
                $map = self::getColumnMap($db);
                $w = self::buildWhere($map, $warehouse, $filters);

                $cantidadCol = self::pickWhereColumn($map, 'cantidad', true);
                $importeCol = self::pickWhereColumn($map, 'importe', true);
                $docCol = self::pickWhereColumn($map, 'documento', true);
                $tipoCol = self::pickWhereColumn($map, 'tipo');
                $conceptoCol = self::pickWhereColumn($map, 'concepto');
                $provCol = self::pickWhereColumn($map, 'proveedor');

                $devExprs = [];
                if ($tipoCol) {
                    $devExprs[] = "UPPER(CAST({$tipoCol} AS NVARCHAR(255))) LIKE '%DEV%'";
                }
                if ($conceptoCol) {
                    $devExprs[] = "UPPER(CAST({$conceptoCol} AS NVARCHAR(255))) LIKE '%DEV%'";
                }
                $devCondition = $devExprs ? implode(' OR ', $devExprs) : '1=0';
                $provDistinct = $provCol ? "COUNT(DISTINCT {$provCol})" : "0";

                $sql = "
                    SELECT
                        COUNT(*) AS total_movimientos,
                        COUNT(DISTINCT {$docCol}) AS total_documentos,
                        SUM(ABS(CAST({$importeCol} AS DECIMAL(18,2)))) AS importe_abs,
                        SUM(CASE WHEN CAST({$cantidadCol} AS DECIMAL(18,2)) > 0 THEN CAST({$cantidadCol} AS DECIMAL(18,2)) ELSE 0 END) AS entradas,
                        SUM(CASE WHEN CAST({$cantidadCol} AS DECIMAL(18,2)) < 0 THEN ABS(CAST({$cantidadCol} AS DECIMAL(18,2))) ELSE 0 END) AS salidas,
                        SUM(CASE WHEN ({$devCondition}) THEN 1 ELSE 0 END) AS devoluciones,
                        {$provDistinct} AS proveedores_activos
                    FROM [Mov. producto] m
                    WHERE {$w['sql']}
                ";
                $stmt = $db->prepare($sql);
                $stmt->execute($w['params']);
                $row = $stmt->fetch() ?: [];

                $movs = (int)($row['total_movimientos'] ?? 0);
                if ($movs > 0) {
                    $agg['almacenes_activos']++;
                }
                $agg['movimientos'] += $movs;
                $agg['documentos'] += (int)($row['total_documentos'] ?? 0);
                $agg['importe_abs'] += (float)($row['importe_abs'] ?? 0);
                $agg['entradas'] += (float)($row['entradas'] ?? 0);
                $agg['salidas'] += (float)($row['salidas'] ?? 0);
                $agg['devoluciones'] += (int)($row['devoluciones'] ?? 0);
                $agg['proveedores'][] = (int)($row['proveedores_activos'] ?? 0);
            } catch (\Throwable $e) {
                error_log('InventoryWarehouses::getLogisticsKpis error: ' . $e->getMessage());
            }
        }

        $mov = max(1, $agg['movimientos']);
        $docs = max(1, $agg['documentos']);

        return [
            'costo_promedio_movimiento' => $agg['importe_abs'] / $mov,
            'movimientos_por_documento' => $agg['movimientos'] / $docs,
            'rotacion_salidas_entradas' => $agg['entradas'] > 0 ? ($agg['salidas'] / $agg['entradas']) * 100 : 0.0,
            'indice_devoluciones' => ($agg['devoluciones'] / $mov) * 100,
            'cobertura_almacenes' => ($agg['almacenes_activos'] / count(self::WAREHOUSES)) * 100,
            'proveedores_activos' => array_sum($agg['proveedores']),
        ];
    }

    public static function getByWarehouse(array $filters = []): array
    {
        $rows = [];
        foreach (self::WAREHOUSES as $warehouse => $cfg) {
            $rows[$warehouse] = [
                'almacen' => $warehouse,
                'empresa' => $cfg['empresa'],
                'movimientos' => 0,
                'piezas_netas' => 0.0,
                'importe_total' => 0.0,
            ];
        }

        foreach (self::WAREHOUSES as $warehouse => $cfg) {
            try {
                $db = Database::getConnection($cfg['db']);
                $map = self::getColumnMap($db);
                $w = self::buildWhere($map, $warehouse, $filters);
                $cantidadCol = self::pickWhereColumn($map, 'cantidad', true);
                $importeCol = self::pickWhereColumn($map, 'importe', true);

                $sql = "
                    SELECT
                        COUNT(*) AS total_movimientos,
                        SUM(CAST({$cantidadCol} AS DECIMAL(18,2))) AS piezas_netas,
                        SUM(CAST({$importeCol} AS DECIMAL(18,2))) AS importe_total
                    FROM [Mov. producto] m
                    WHERE {$w['sql']}
                ";
                $stmt = $db->prepare($sql);
                $stmt->execute($w['params']);
                $r = $stmt->fetch() ?: [];
                $rows[$warehouse]['movimientos'] = (int)($r['total_movimientos'] ?? 0);
                $rows[$warehouse]['piezas_netas'] = (float)($r['piezas_netas'] ?? 0);
                $rows[$warehouse]['importe_total'] = (float)($r['importe_total'] ?? 0);
            } catch (\Throwable $e) {
                error_log('InventoryWarehouses::getByWarehouse error: ' . $e->getMessage());
            }
        }

        return array_values($rows);
    }

    public static function getByFamily(array $filters = [], int $top = 10): array
    {
        $acc = [];
        foreach (self::WAREHOUSES as $warehouse => $cfg) {
            try {
                $db = Database::getConnection($cfg['db']);
                $map = self::getColumnMap($db);
                $familiaCol = self::pickWhereColumn($map, 'familia');
                if ($familiaCol === null) {
                    continue;
                }
                $cantidadCol = self::pickWhereColumn($map, 'cantidad', true);
                $importeCol = self::pickWhereColumn($map, 'importe', true);
                $w = self::buildWhere($map, $warehouse, $filters);

                $sql = "
                    SELECT
                        {$familiaCol} AS familia,
                        SUM(CAST({$cantidadCol} AS DECIMAL(18,2))) AS piezas_netas,
                        SUM(CAST({$importeCol} AS DECIMAL(18,2))) AS importe_total
                    FROM [Mov. producto] m
                    WHERE {$w['sql']}
                      AND {$familiaCol} IS NOT NULL
                      AND LTRIM(RTRIM(CAST({$familiaCol} AS NVARCHAR(255)))) <> ''
                    GROUP BY {$familiaCol}
                ";
                $stmt = $db->prepare($sql);
                $stmt->execute($w['params']);
                foreach ($stmt->fetchAll() as $row) {
                    $fam = (string)$row['familia'];
                    if (!isset($acc[$fam])) {
                        $acc[$fam] = ['familia' => $fam, 'piezas_netas' => 0.0, 'importe_total' => 0.0];
                    }
                    $acc[$fam]['piezas_netas'] += (float)($row['piezas_netas'] ?? 0);
                    $acc[$fam]['importe_total'] += (float)($row['importe_total'] ?? 0);
                }
            } catch (\Throwable $e) {
                error_log('InventoryWarehouses::getByFamily error: ' . $e->getMessage());
            }
        }

        $rows = array_values($acc);
        usort($rows, fn($a, $b) => $b['piezas_netas'] <=> $a['piezas_netas']);
        return array_slice($rows, 0, max(1, $top));
    }

    public static function getRecentMovements(array $filters = [], int $limit = 250): array
    {
        $rows = [];
        foreach (self::WAREHOUSES as $warehouse => $cfg) {
            try {
                $db = Database::getConnection($cfg['db']);
                $map = self::getColumnMap($db);
                $select = self::buildSelectParts($map);
                $w = self::buildWhere($map, $warehouse, $filters);
                $fechaCol = self::pickWhereColumn($map, 'fecha', true);

                $sql = "
                    SELECT TOP {$limit}
                        '" . addslashes($cfg['empresa']) . "' AS empresa,
                        " . implode(",\n                        ", $select) . "
                    FROM [Mov. producto] m
                    WHERE {$w['sql']}
                    ORDER BY {$fechaCol} DESC
                ";
                $stmt = $db->prepare($sql);
                $stmt->execute($w['params']);
                foreach ($stmt->fetchAll() as $row) {
                    $rows[] = $row;
                }
            } catch (\Throwable $e) {
                error_log('InventoryWarehouses::getRecentMovements error: ' . $e->getMessage());
            }
        }

        usort($rows, function ($a, $b) {
            $ta = strtotime((string)($a['fecha'] ?? '1970-01-01'));
            $tb = strtotime((string)($b['fecha'] ?? '1970-01-01'));
            return $tb <=> $ta;
        });

        return array_slice($rows, 0, $limit);
    }
            // --- TODAS LAS FAMILIAS POR ALMACÉN ---
        public static function getFamiliesByWarehouse(array $filters = []): array
        {
            $results = [];
            foreach (self::WAREHOUSES as $warehouse => $cfg) {
                try {
                    $db = Database::getConnection($cfg['db']);
                    $map = self::getColumnMap($db);
                    $w = self::buildWhere($map, $warehouse, $filters);
                    
                    $famCol = self::pickWhereColumn($map, 'familia', true);
                    $impCol = self::pickWhereColumn($map, 'importe', true);
                    $qtyCol = self::pickWhereColumn($map, 'cantidad', true);

                    $sql = "
                        SELECT 
                            ISNULL({$famCol}, 'SIN FAMILIA') as familia, 
                            SUM({$qtyCol}) as piezas,
                            SUM({$impCol}) as importe
                        FROM [Mov. producto] m
                        WHERE {$w['sql']}
                        GROUP BY {$famCol}
                        having SUM({$qtyCol})<>0
                        ORDER BY SUM({$qtyCol}),ISNULL({$famCol}, 'SIN FAMILIA')  DESC
                    ";

                    $stmt = $db->prepare($sql);
                    $stmt->execute($w['params']);
                    $results[$cfg['empresa']] = $stmt->fetchAll();
                } catch (\Throwable $e) { $results[$cfg['empresa']] = []; }
            }
            return $results;
        }

        // --- TODAS LAS TEMPORADAS POR ALMACÉN ---
        public static function getSubfamiliesByWarehouse(array $filters = []): array
        {
            $results = [];
            foreach (self::WAREHOUSES as $warehouse => $cfg) {
                try {
                    $db = Database::getConnection($cfg['db']);
                    $map = self::getColumnMap($db);
                    $w = self::buildWhere($map, $warehouse, $filters);
                    
                    $subCol = self::pickWhereColumn($map, 'subfamilia', true);
                    $impCol = self::pickWhereColumn($map, 'importe', true);
                    $qtyCol = self::pickWhereColumn($map, 'cantidad', true);

                    $sql = "
                        SELECT 
                            ISNULL({$subCol}, 'SIN TEMPORADA') as subfamilia, 
                            SUM({$qtyCol}) as piezas,
                            SUM({$impCol}) as importe
                        FROM [Mov. producto] m
                        WHERE {$w['sql']}
                        GROUP BY {$subCol}
                        having SUM({$qtyCol})<>0
                        ORDER BY  SUM({$qtyCol}), ISNULL({$subCol}, 'SIN TEMPORADA') DESC
                    ";

                    $stmt = $db->prepare($sql);
                    $stmt->execute($w['params']);
                    $results[$cfg['empresa']] = $stmt->fetchAll();
                } catch (\Throwable $e) { $results[$cfg['empresa']] = []; }
            }
            return $results;
        }

        public static function getFillRateByCompany(array $filters = []): array
{
    $results = [];
    foreach (self::WAREHOUSES as $warehouse => $cfg) {
        try {
            $db = Database::getConnection($cfg['db']);
            
            // Filtros básicos de año (según tu query Año >= 2026)
            $sql = "SELECT 
                        COUNT(DISTINCT [N-PEDIDO]) as total_pedidos,
                        SUM(Cantidad) as piezas_pedidas,
                        SUM(Facturada) as piezas_facturadas,
                        SUM(Pendiente) as piezas_pendientes,
                        SUM(Cantidad * precio_costo) as importe_pedido,
                        SUM(Facturada * precio_costo) as importe_facturado
                    FROM dbo.ped_compr
                    WHERE Año >= 2026 AND Almacen = ?";

            $stmt = $db->prepare($sql);
            $stmt->execute([$warehouse]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($data) {
                // Cálculo de Porcentajes
                $totalPiezas = (float)$data['piezas_pedidas'] ?: 1;
                $totalImporte = (float)$data['importe_pedido'] ?: 1;
                
                $data['fill_rate_piezas'] = ($data['piezas_facturadas'] / $totalPiezas) * 100;
                $data['pend_piezas_pct'] = ($data['piezas_pendientes'] / $totalPiezas) * 100;
                $data['fill_rate_importe'] = ($data['importe_facturado'] / $totalImporte) * 100;
                
                $results[$cfg['empresa']] = $data;
            }
        } catch (\Throwable $e) {
            error_log("Error FillRate {$warehouse}: " . $e->getMessage());
            $results[$cfg['empresa']] = [];
        }
    }
    return $results;
}
public static function getMonthlyFillRate(array $filters = []): array
{
    $results = [];
    foreach (self::WAREHOUSES as $warehouse => $cfg) {
        try {
            $db = Database::getConnection($cfg['db']);
            
            // CAST a BIGINT y FLOAT para evitar el error 8115 de desbordamiento
            $sql = "SELECT 
                        Año, 
                        Mes,
                        COUNT(DISTINCT [N-PEDIDO]) as num_pedidos,
                        SUM(CAST(Cantidad AS BIGINT)) as pzas_pedidas,
                        SUM(CAST(Facturada AS BIGINT)) as pzas_facturadas,
                        SUM(CAST(Cantidad AS FLOAT) * precio_costo) as importe_pedido,
                        SUM(CAST(Facturada AS FLOAT) * precio_costo) as importe_facturado
                    FROM dbo.ped_compr
                    WHERE Año >= 2025 AND Almacen = ?
                    GROUP BY Año, Mes
                    ORDER BY Año ASC, Mes ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute([$warehouse]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as &$r) {
                // KPI: Fill Rate %
                $r['fill_rate_pzas'] = ($r['pzas_pedidas'] > 0) ? ($r['pzas_facturadas'] / $r['pzas_pedidas']) * 100 : 0;
                // KPI: Importe Pendiente
                $r['importe_pend'] = (float)$r['importe_pedido'] - (float)$r['importe_facturado'];
            }

            $results[$cfg['empresa']] = $rows;
        } catch (\Throwable $e) {
            error_log("Error FillRate {$warehouse}: " . $e->getMessage());
            $results[$cfg['empresa']] = [];
        }
    }
    return $results;
}
}


