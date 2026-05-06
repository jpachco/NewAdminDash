/**
 * Exporta una tabla HTML a un archivo Excel (.xlsx)
 * @param {string} tableId - El ID de la tabla en el DOM.
 * @param {string} fileName - Nombre del archivo de salida.
 */
function exportTableToExcel(tableId, fileName = 'Reporte.xlsx') {
    const table = document.getElementById(tableId);
    
    if (!table) {
        console.error("Error: No se encontró la tabla con ID: " + tableId);
        return;
    }

    // Convertir la tabla a una hoja de trabajo
    // 'raw: false' intenta convertir fechas y números automáticamente
    const worksheet = XLSX.utils.table_to_sheet(table, { raw: false });

    // Crear el libro de trabajo (Workbook)
    const workbook = XLSX.utils.book_new();
    
    // Añadir la hoja al libro
    XLSX.utils.book_append_sheet(workbook, worksheet, "Datos");

    // Descargar el archivo
    XLSX.writeFile(workbook, fileName);
}