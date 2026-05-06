-- ─────────────────────────────────────────
-- NOTAS DE COMPATIBILIDAD — dbhighlife
-- ─────────────────────────────────────────

-- 1. La columna ST_% en st_semanal tiene un caracter especial.
--    Si el nombre exacto es [ST_%] usar corchetes en la query:
--    AVG(CAST([ST_%] AS DECIMAL(10,2)))
--    Si es diferente, ajustar en Inventory::getStSemanal()

-- 2. La columna [No.] en Proveedor tiene punto.
--    Siempre referenciarla con corchetes: Proveedor.[No.]

-- 3. La columna Cód._Almacén en Ventas y Mov_producto
--    tiene caracteres especiales. Usar corchetes:
--    [Cód._Almacén]

-- 4. Verificar nombres exactos de columnas con:
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'st_semanal';

SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'Ventas';

SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'Mov_producto';
