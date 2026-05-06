<?php
require_once __DIR__ . '/../../../config/config.php';

// Usamos la conexión HL según tu script original, pero con PDO
$ds = Database::getConnection('HL');
$resultadoCRM = [];
$diferencia = [];
$estructuraCRM = "";

$cons_mon = "SELECT proceso, fecha FROM Hist_procesos 
             WHERE CONVERT(date, Fecha, 103) = CONVERT(date, GETDATE(), 103) 
             AND (proceso LIKE '%SF%' OR proceso LIKE '%Spot%' OR proceso LIKE '%HS%' OR proceso LIKE '%MAIL%')
             ORDER BY fecha";

$stmt = $ds->query($cons_mon);
$rows = $stmt->fetchAll(PDO::FETCH_NUM);

$filas = count($rows);

if ($filas > 0) {
    // --- PROCESAMIENTO DE DATOS ---
    for ($i = 0; $i < $filas; $i++) {
        $proceso = $rows[$i][0];
        $fechaActual = date_create($rows[$i][1]);

        // 1. Lógica para $resultadoCRM (Asignación directa)
        $resultadoCRM[$i][0] = $proceso;
        $resultadoCRM[$i][1] = date_format($fechaActual, 'j');
        $resultadoCRM[$i][2] = intval(date_format($fechaActual, 'n')) - 1; // Mes 0-11
        $resultadoCRM[$i][3] = date_format($fechaActual, 'Y');
        $resultadoCRM[$i][4] = date_format($fechaActual, 'G');
        $resultadoCRM[$i][5] = intval(date_format($fechaActual, 'i'));
        $resultadoCRM[$i][6] = intval(date_format($fechaActual, 's'));

        // 2. Lógica para $diferencia
        $diferencia[$i][0] = $proceso;
        $diferencia[$i][2] = date_format($fechaActual, 'Y/m/d H:i:s');

        if ($i == 0) {
            $diferencia[$i][1] = date_format(date_create('00:00:00'), 'Y/m/d H:i:s');
        } else {
            // Fecha del registro anterior
            $diferencia[$i][1] = date_format(date_create($rows[$i - 1][1]), 'Y/m/d H:i:s');
        }
    }

    // --- CONSTRUCCIÓN DE ESTRUCTURA CRM PARA GANTT ---
    $estructuraCRM = "{";
    $estructuraCRM .= "name:'SalesForce',id:'crm',parent:'plan',";
    $estructuraCRM .= "start:Date.UTC(" . $resultadoCRM[0][3] . "," . $resultadoCRM[0][2] . "," . $resultadoCRM[0][1] . ",0,0,0),";
    $estructuraCRM .= "end:Date.UTC(" . $resultadoCRM[0][3] . "," . $resultadoCRM[0][2] . "," . $resultadoCRM[0][1] . "," . $resultadoCRM[$filas - 1][4] . "," . $resultadoCRM[$filas - 1][5] . "," . $resultadoCRM[$filas - 1][6] . ")";
    $estructuraCRM .= "},";

    for ($i = 0; $i < $filas; $i++) {
        $estructuraCRM .= "{";
        $estructuraCRM .= "name:'" . $resultadoCRM[$i][0] . "',";
        $estructuraCRM .= "id:'" . $resultadoCRM[$i][0] . "crm',";
        $estructuraCRM .= "parent:'crm',";

        if ($i == 0) {
            $inicioH = 0; $inicioM = 0; $inicioS = 0;
        } else {
            $estructuraCRM .= "dependency:'" . $resultadoCRM[$i - 1][0] . "crm',";
            $inicioH = $resultadoCRM[$i - 1][4];
            $inicioM = $resultadoRB[$i - 1][5] ?? $resultadoCRM[$i - 1][5]; // Fix preventivo
            $inicioH = $resultadoCRM[$i - 1][4];
            $inicioM = $resultadoCRM[$i - 1][5];
            $inicioS = $resultadoCRM[$i - 1][6];
        }

        $estructuraCRM .= "start:Date.UTC(" . $resultadoCRM[$i][3] . "," . $resultadoCRM[$i][2] . "," . $resultadoCRM[$i][1] . "," . $inicioH . "," . $inicioM . "," . $inicioS . "),";
        $estructuraCRM .= "end:Date.UTC(" . $resultadoCRM[$i][3] . "," . $resultadoCRM[$i][2] . "," . $resultadoCRM[$i][1] . "," . $resultadoCRM[$i][4] . "," . $resultadoCRM[$i][5] . "," . $resultadoCRM[$i][6] . ")";
        $estructuraCRM .= "},";
    }

    $estructuraCRM = rtrim($estructuraCRM, ",");
}
?>