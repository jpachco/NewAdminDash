<?php
require_once __DIR__ . '/../../../config/config.php';

// Conexión mediante PDO
$ds = Database::getConnection('BG');
$resultadoLB1 = [];

// Consulta SQL (eliminamos utf8_encode si el archivo ya es UTF-8)
$cons_mon = "SELECT MAX([Fecha registro]), MAX([Log Hour]) FROM VentaPH";

try {
    $stmt = $ds->query($cons_mon);
    // Usamos FETCH_NUM para obtener índices [0] y [1]
    $row = $stmt->fetch(PDO::FETCH_NUM);

    if ($row) {
        // En SQL Server a través de PDO, estas fechas suelen llegar como strings o objetos
        // Las convertimos a objeto DateTime para formatearlas con seguridad
        $fechaRegistro = date_create($row[0]);
        $logHour = date_create($row[1]);

        // Llenamos el array resultadoLB1 con el formato solicitado
        $resultadoLB1[] = date_format($fechaRegistro, 'Y/m/d');
        $resultadoLB1[] = date_format($logHour, 'Y/m/d H:i:s');
    }
} catch (Exception $e) {
    // Manejo de error silencioso o log
    error_log($e->getMessage());
}

// Ahora $resultadoLB1[0] tiene la fecha y $resultadoLB1[1] tiene el log hour completo
?>