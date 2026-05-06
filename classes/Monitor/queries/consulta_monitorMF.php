<?php
require_once __DIR__ . '/../../../config/config.php';

$ds = Database::getConnection('MF');
$resultadoMF = [];
$diferencia = [];

$cons_mon = "select proceso, fecha from Hist_procesos 
             where convert(date, Fecha, 103) = convert(date, GETDATE(), 103) 
             order by fecha";

$stmt = $ds->query($cons_mon);
$rows = $stmt->fetchAll(PDO::FETCH_NUM);

$filasMF = count($rows);
if ($filasMF == 0) {
    die("No hay datos para hoy.");
}

for ($i = 0; $i < $filasMF; $i++) {
    $proceso = $rows[$i][0];
    $fechaActual = date_create($rows[$i][1]);

    // 1. Lógica para $resultadoMF (Estructura fija de columnas)
    $resultadoMF[$i][0] = $proceso;
    $resultadoMF[$i][1] = date_format($fechaActual, 'j');     // Día
    $resultadoMF[$i][2] = intval(date_format($fechaActual, 'n')) - 1; // Mes (0-11 para JS)
    $resultadoMF[$i][3] = date_format($fechaActual, 'Y');     // Año
    $resultadoMF[$i][4] = date_format($fechaActual, 'G');     // Hora
    $resultadoMF[$i][5] = intval(date_format($fechaActual, 'i')); // Minuto
    $resultadoMF[$i][6] = intval(date_format($fechaActual, 's')); // Segundo

    // 2. Lógica para $diferencia (Evitando errores de índice -1)
    $diferencia[$i][0] = $proceso;
    $diferencia[$i][2] = date_format($fechaActual, 'Y/m/d H:i:s');

    if ($i == 0) {
        // Para el primero, no hay anterior, usamos medianoche o un valor base
        $diferencia[$i][1] = date_format(date_create('00:00:00'), 'Y/m/d H:i:s');
    } else {
        // Usamos la fecha de la fila anterior
        $diferencia[$i][1] = date_format(date_create($rows[$i - 1][1]), 'Y/m/d H:i:s');
    }
}

// Generación de estructura JSON/Gantt
$estructuraMF = "{";
$estructuraMF .= "name:'Mens Fashion',id:'mf',parent:'plan',";
// Usamos los índices ya limpios que definimos arriba
$estructuraMF .= "start:Date.UTC(" . $resultadoMF[0][3] . "," . $resultadoMF[0][2] . "," . $resultadoMF[0][1] . ",0,0,0),";
$estructuraMF .= "end:Date.UTC(" . $resultadoMF[0][3] . "," . $resultadoMF[0][2] . "," . $resultadoMF[0][1] . "," . $resultadoMF[$filasMF - 1][4] . "," . $resultadoMF[$filasMF - 1][5] . "," . $resultadoMF[$filasMF - 1][6] . ")";
$estructuraMF .= "},";

for ($i = 0; $i < count($resultadoMF); $i++) {
    $estructuraMF .= "{";
    $estructuraMF .= "name:'" . $resultadoMF[$i][0] . "',";
    $estructuraMF .= "id:'" . $resultadoMF[$i][0] . "mf',";
    $estructuraMF .= "parent:'mf',";

    if ($i > 0) {
        $estructuraMF .= "dependency:'" . $resultadoMF[$i - 1][0] . "mf',";
        $inicioH = $resultadoMF[$i - 1][4];
        $inicioM = $resultadoMF[$i - 1][5];
        $inicioS = $resultadoMF[$i - 1][6];
    } else {
        $inicioH = 0; $inicioM = 0; $inicioS = 0;
    }

    $estructuraMF .= "start:Date.UTC(" . $resultadoMF[$i][3] . "," . $resultadoMF[$i][2] . "," . $resultadoMF[$i][1] . "," . $inicioH . "," . $inicioM . "," . $inicioS . "),";
    $estructuraMF .= "end:Date.UTC(" . $resultadoMF[$i][3] . "," . $resultadoMF[$i][2] . "," . $resultadoMF[$i][1] . "," . $resultadoMF[$i][4] . "," . $resultadoMF[$i][5] . "," . $resultadoMF[$i][6] . ")";
    $estructuraMF .= "},";
}

$estructuraMF = rtrim($estructuraMF, ",");
?>