<?php
/**
 * Clase de Inventario
 * Base de datos: dbhighlife (conexión 'HL')
 * Tablas: InventarioNV, Ventas, Pedidos,
 *         Recepciones_de_Compra, Mov_producto,
 *         st_semanal, Tienda, Proveedor
 *
 * NOTA: Columnas con caracteres especiales siempre
 *       entre corchetes: [Nº_Documento], [Cód._Almacén], etc.
 */
class Inventory {

    // ─────────────────────────────────────────
    // EXISTENCIA
    // ─────────────────────────────────────────

    /**
     * KPI: Totales globales de existencia
     */
    public static function getTotalExistencia(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                SELECT
                    SUM(Existencia)                        AS total_piezas,
                    COUNT(DISTINCT TIENDA)                 AS total_tiendas,
                    COUNT(DISTINCT Sku)                    AS total_skus,
                    SUM(Existencia * [Precio venta])         AS valor_inventario
                FROM InventarioNV 
                WHERE Existencia > 0
            ");
            return $stmt->fetch() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getTotalExistencia error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Existencia agrupada por tienda
     */
    public static function getExistenciaPorTienda(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                SELECT
                    i.TIENDA,
                    t.Nombre,
                    SUM(i.Existencia)                    AS total_piezas,
                    COUNT(DISTINCT i.Sku)                AS total_skus,
                    SUM(i.Existencia * i.[Precio venta])   AS valor_inventario
                FROM InventarioNV i
                LEFT JOIN Tienda t ON i.TIENDA = t.Tienda
                WHERE i.Existencia > 0
                GROUP BY i.TIENDA, t.Nombre
                ORDER BY total_piezas DESC
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getExistenciaPorTienda error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Existencia agrupada por familia (TOP 10)
     */
    public static function getExistenciaPorFamilia(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                SELECT TOP 10
                    Familia,
                    SUM(Existencia)                    AS total_piezas,
                    COUNT(DISTINCT Sku)                AS total_skus,
                    SUM(Existencia * [Precio venta])     AS valor_inventario
                FROM InventarioNV
                WHERE Existencia > 0
                  AND Familia IS NOT NULL
                  AND Familia <> ''
                GROUP BY Familia
                ORDER BY total_piezas DESC
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getExistenciaPorFamilia error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Existencia agrupada por marca
     */
    public static function getExistenciaPorMarca(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                SELECT
                    Marca,
                    SUM(Existencia)      AS total_piezas,
                    COUNT(DISTINCT Sku)  AS total_skus
                FROM InventarioNV
                WHERE Existencia > 0
                  AND Marca IS NOT NULL
                  AND Marca <> ''
                GROUP BY Marca
                ORDER BY total_piezas DESC
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getExistenciaPorMarca error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // VENTAS DEL MES
    // ─────────────────────────────────────────

    /**
     * KPI: Resumen de ventas del mes actual
     * Columnas especiales entre corchetes
     */
    public static function getVentasMes(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
               SELECT
                    COUNT(DISTINCT [Nº Documento] )       AS total_transacciones,
                    SUM(Cantidad)                        AS total_piezas,
                    SUM(Importe)                         AS importe_total,
                    SUM([Importe Iva Incl.] )              AS importe_iva,
                    COUNT(DISTINCT [Cód. Almacén])       AS tiendas_con_venta
                FROM Ventas
                WHERE MONTH([Fecha Registro] ) = MONTH(GETDATE())
                  AND YEAR([Fecha Registro] )  = YEAR(GETDATE())
            ");
            return $stmt->fetch() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getVentasMes error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ventas por día del mes actual (para gráfica de línea)
     */
    public static function getVentasPorDia(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                SELECT
                    DAY([Fecha Registro])   AS dia,
                    SUM(Cantidad)         AS piezas,
                    SUM(Importe)          AS importe
                FROM Ventas
                WHERE MONTH([Fecha Registro]) = MONTH(GETDATE())
                  AND YEAR([Fecha Registro])  = YEAR(GETDATE())
                GROUP BY DAY([Fecha Registro])
                ORDER BY dia ASC
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getVentasPorDia error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Top 10 productos más vendidos del mes
     */
    public static function getTopProductos(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                   SELECT TOP 10
                    v.Sku,
                    v.Descripción ,
                    v.Familia,
                    SUM(v.Cantidad)   AS total_vendido,
                    SUM(v.Importe)    AS importe_total
                FROM Ventas v
                WHERE MONTH(v.[Fecha Registro] ) = MONTH(GETDATE())
                  AND YEAR(v.[Fecha Registro])  = YEAR(GETDATE())
                  AND v.Sku IS NOT NULL
                GROUP BY v.Sku, v.Descripción, v.Familia
                ORDER BY total_vendido DESC
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getTopProductos error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // PEDIDOS PENDIENTES
    // ─────────────────────────────────────────

    /**
     * KPI: Resumen de pedidos pendientes
     * Columnas especiales entre corchetes
     */
    public static function getPedidosPendientes(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                 SELECT
                    COUNT(DISTINCT [Nº documento] )           AS total_pedidos,
                    SUM([Cantidad pendiente] )                  AS piezas_pendientes,
                    SUM([Importe pendiente] )                   AS importe_pendiente,
                    COUNT(DISTINCT [Compra a-Nº proveedor] )     AS proveedores
                FROM Pedidos
                WHERE [Cantidad pendiente]  > 0
            ");
            return $stmt->fetch() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getPedidosPendientes error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Pedidos pendientes agrupados por proveedor (TOP 8)
     */
    public static function getPedidosPorProveedor(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                 SELECT TOP 8
                    p.[Compra a-Nº proveedor]               AS proveedor,
                    pr.Name                             AS nombre_proveedor,
                    COUNT(DISTINCT [p].[Nº documento] )  AS total_pedidos,
                    SUM(p.[Cantidad pendiente] )            AS piezas_pendientes
                FROM Pedidos p
                LEFT JOIN Proveedor pr ON p.[Compra a-Nº proveedor]  = pr.[No.]
                WHERE p.[Cantidad pendiente]  > 0
                GROUP BY p.[Compra a-Nº proveedor] , pr.Name
                ORDER BY piezas_pendientes DESC
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getPedidosPorProveedor error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // RECEPCIONES RECIENTES
    // ─────────────────────────────────────────

    /**
     * Últimas 10 recepciones de compra (30 días)
     * Columnas especiales entre corchetes
     */
    public static function getRecepcionesRecientes(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                SELECT TOP 10
                    r.[Nº documento] ,
                    r.[Fecha registro] ,
                    r.[Cód. almacén]                             AS almacen,
                    r.[Compra a-Nº proveedor]                       AS proveedor,
                    p.Name                                      AS nombre_proveedor,
                    r.Marca,
                    SUM(r.[Cantidad recibida no facturada] )       AS piezas_recibidas,
                    SUM(r.[Costo unitario] 
                        * r.[Cantidad recibida no facturada] )     AS costo_total
                FROM [Recepciones de Compra]   r
                LEFT JOIN Proveedor p ON r.[Compra a-Nº proveedor]  = p.[No.] collate Latin1_General_100_CI_AS
                WHERE r.[Fecha registro]  >= DATEADD(DAY, -30, GETDATE())
                GROUP BY
                    r.[Nº documento] , r.[Fecha registro] , r.[Cód. almacén] ,
                    r.[Compra a-Nº proveedor] , p.Name, r.Marca
                ORDER BY r.[Fecha registro] DESC
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getRecepcionesRecientes error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // MOVIMIENTOS DE PRODUCTO
    // ─────────────────────────────────────────

    /**
     * Últimos 15 movimientos de producto
     * Columnas especiales entre corchetes
     */
    public static function getMovimientosRecientes(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
               SELECT TOP 15
                    mp.[Nº documento]           AS documento,
                    mp.[Fecha registro] ,
                    mp.[Tipo movimiento] ,
                    mp.Descripción ,
                    mp.[Cód. almacén]           AS almacen,
                    Familia,
                    Cantidad,
                    Importe,
                    mp.[Concepto Movimiento] 
                FROM [Mov. producto] mp 
                ORDER BY [Fecha registro]  DESC
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getMovimientosRecientes error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Movimientos agrupados por tipo — mes actual
     */
    public static function getMovimientosPorTipo(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                 SELECT
                    mp.[Tipo movimiento] ,
                    COUNT(*)         AS total_movimientos,
                    SUM(Cantidad)    AS total_piezas,
                    SUM(Importe)     AS importe_total
                FROM [Mov. producto] mp 
                WHERE MONTH(mp.[Fecha registro] ) = MONTH(GETDATE())
                  AND YEAR(mp.[Fecha registro] )  = YEAR(GETDATE())
                GROUP BY mp.[Tipo movimiento] 
                ORDER BY total_movimientos DESC
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getMovimientosPorTipo error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // ST% SEMANAL
    // ─────────────────────────────────────────

    /**
     * ST% promedio por familia — últimos 90 días
     * [ST_%] entre corchetes por el caracter especial
     */
    public static function getStSemanal(): array {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
                SELECT TOP 10
                    Familia,
                    AVG(CAST([ST%]  AS DECIMAL(10,2)))  AS st_promedio,
                    SUM(Compra)                          AS compra_total,
                    SUM(Actual)                          AS actual_total
                FROM st_semanal
                WHERE FechaCompra >= DATEADD(DAY, -90, GETDATE())
                  AND Familia IS NOT NULL
                  AND Familia <> ''
                GROUP BY Familia
                ORDER BY st_promedio DESC
            ");
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Inventory::getStSemanal error: " . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────
    // PROVEEDORES
    // ─────────────────────────────────────────

    /**
     * Cuenta proveedores con pedidos activos pendientes
     */
    public static function getProveedoresActivos(): int {
        try {
            $db   = Database::getConnection('HL');
            $stmt = $db->query("
               SELECT COUNT(DISTINCT [Compra a-Nº proveedor] ) AS total
                FROM Pedidos
                WHERE [Cantidad pendiente]  > 0
            ");
            return (int)($stmt->fetch()['total'] ?? 0);
        } catch (Exception $e) {
            error_log("Inventory::getProveedoresActivos error: " . $e->getMessage());
            return 0;
        }
    }
}
