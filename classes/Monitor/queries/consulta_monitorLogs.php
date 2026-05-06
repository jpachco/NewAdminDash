<?php
require_once __DIR__ . '/../../../config/config.php';
//error_reporting(0);

global $conexionMSDB;
$ds=Database::getConnection();
$rows_lab=array();
$i=0;
$resultadoMSDB=array();

$cons_mon_lab="
-- 1. Capturamos la sesión actual para evitar subconsultas repetitivas
DECLARE @CurrentSessionID INT, @RemoteSessionID INT;
SELECT TOP 1 @CurrentSessionID = session_id FROM msdb.dbo.syssessions ORDER BY agent_start_date DESC;

-- Usamos EXEC para traer el ID de sesión remoto de forma eficiente
-- (O puedes dejarlo como subconsulta si el rendimiento es aceptable)

WITH RemoteData AS (
    -- Esta parte trae los datos del servidor vinculado
    -- Intenta filtrar por fecha reciente si el historial es muy grande
    SELECT 
        sj.job_id,
        sj.name COLLATE Latin1_General_100_CI_AS AS Nombre,
        jh.run_date, jh.run_time, jh.run_status, jh.run_duration,
        jh.message COLLATE Latin1_General_100_CI_AS AS message,
        sch.NextRunDate, sch.NextRunTime
    FROM [172.25.12.26\BI].msdb.dbo.sysjobs sj
    LEFT JOIN (
        SELECT job_id, MIN(next_run_date) NextRunDate, MIN(next_run_time) NextRunTime
        FROM [172.25.12.26\BI].msdb.dbo.sysjobschedules GROUP BY job_id
    ) sch ON sj.job_id = sch.job_id
    LEFT JOIN (
        SELECT *, ROW_NUMBER() OVER (PARTITION BY job_id ORDER BY run_date DESC, run_time DESC) as rn
        FROM [172.25.12.26\BI].msdb.dbo.sysjobhistory
        WHERE step_id = 0 -- Step 0 es el resumen del Job, es más rápido que filtrar step_id = 1
    ) jh ON sj.job_id = jh.job_id AND jh.rn = 1
    WHERE sj.enabled = 1 
      AND sj.name NOT IN ('syspolicy_purge_history','Trunca Logs de Bases de Datos')
),
LocalData AS (
    -- Misma lógica para el servidor local
    SELECT 
        sj.job_id,
        sj.name AS Nombre,
        jh.run_date, jh.run_time, jh.run_status, jh.run_duration,
        jh.message,
        sch.NextRunDate, sch.NextRunTime
    FROM msdb.dbo.sysjobs sj
    LEFT JOIN (
        SELECT job_id, MIN(next_run_date) NextRunDate, MIN(next_run_time) NextRunTime
        FROM msdb.dbo.sysjobschedules GROUP BY job_id
    ) sch ON sj.job_id = sch.job_id
    LEFT JOIN (
        SELECT *, ROW_NUMBER() OVER (PARTITION BY job_id ORDER BY run_date DESC, run_time DESC) as rn
        FROM msdb.dbo.sysjobhistory
        WHERE step_id = 0
    ) jh ON sj.job_id = jh.job_id AND jh.rn = 1
    WHERE sj.enabled = 1 
      AND sj.name NOT IN ('syspolicy_purge_history','Trunca Logs de Bases de Datos')
),
JobsUnion AS (
    SELECT * FROM LocalData
    UNION ALL
    SELECT * FROM RemoteData
),
RunningJobs AS (
    -- Jobs ejecutándose localmente
    SELECT ja.job_id, 'Ejecutando' as Stat
    FROM msdb.dbo.sysjobactivity ja 
    WHERE ja.session_id = @CurrentSessionID 
      AND start_execution_date IS NOT NULL AND stop_execution_date IS NULL
    UNION ALL
    -- Jobs ejecutándose remotamente
    SELECT ja.job_id, 'Ejecutando' COLLATE Latin1_General_100_CI_AS
    FROM [172.25.12.26\BI].msdb.dbo.sysjobactivity ja 
    WHERE ja.start_execution_date IS NOT NULL AND ja.stop_execution_date IS NULL
    -- Nota: Aquí podrías filtrar por sesión remota si fuera necesario
)
SELECT 
    U.Nombre,
    -- Conversión de fecha más limpia
    TRY_CAST(CAST(U.run_date AS CHAR(8)) + ' ' + STUFF(STUFF(RIGHT('000000' + CAST(U.run_time AS VARCHAR(6)), 6), 3, 0, ':'), 6, 0, ':') AS DATETIME) AS UltimaFechaEjecucion,
    ISNULL(R.Stat, CASE U.run_status 
        WHEN 0 THEN 'Fallido' 
        WHEN 1 THEN 'Completado' 
        WHEN 2 THEN 'Reintento' 
        WHEN 3 THEN 'Cancelado' 
        ELSE 'Desconocido' END) AS EstadoUltimaEjecucion,
    U.message,
    STUFF(STUFF(RIGHT('000000' + CAST(U.run_duration AS VARCHAR(6)), 6), 3, 0, ':'), 6, 0, ':') AS [DuracionUltimaEjecucion(HH:MM:SS)],
    TRY_CAST(CAST(U.NextRunDate AS CHAR(8)) + ' ' + STUFF(STUFF(RIGHT('000000' + CAST(U.NextRunTime AS VARCHAR(6)), 6), 3, 0, ':'), 6, 0, ':') AS DATETIME) AS FechaProximaEjecucion
FROM JobsUnion U
LEFT JOIN RunningJobs R ON U.job_id = R.job_id
ORDER BY U.Nombre;

  
  ";


$stmt =$ds->query($cons_mon_lab);
$rows_lab =$stmt->fetchAll(PDO::FETCH_ASSOC);


$columnas_lab=count($rows_lab[0]);
$filas_lab=count($rows_lab);

$data_lab=array();
for($i=0;$i<$filas_lab;$i++){



 $data_lab[$i][0]=$rows_lab[$i]['Nombre'];
 $data_lab[$i][1]=date_format(date_create($rows_lab[$i]['UltimaFechaEjecucion']!=null ? $rows_lab[$i]['UltimaFechaEjecucion']:'now'  ), 'Y/m/d H:i:s');
 $data_lab[$i][2]=$rows_lab[$i]['EstadoUltimaEjecucion'];
 $data_lab[$i][3]=$rows_lab[$i]['message'];
 $data_lab[$i][4]=$rows_lab[$i]['DuracionUltimaEjecucion(HH:MM:SS)'];
 $data_lab[$i][5]=date_format(date_create($rows_lab[$i]['FechaProximaEjecucion']), 'Y/m/d H:i:s');




}

$filas_data=count($data_lab);
$columnas_data=count($data_lab[0]);
$estructura_log="";
$estructura_log.='<table class="table" id="jobs">';
$estructura_log.='  <thead><tr> 
<th>ACTIVIDAD</th>
<th>ULTIMA EJECUCION</th>
<th>ESTADO</th>
<th>DURACION</th>
<th>NOTIFICACIÓN</th>
</tr></thead><tbody>';


for ($i=0;$i<$filas_data;$i++){
 $estructura_log.='<tr>';
 $estructura_log.= "<td>".$data_lab[$i][0]."</td>\n";
  $estructura_log.='<td> '. $data_lab[$i][1]."</td> \n";
  if($data_lab[$i][2]=='Fallido' ){
   $background=" color:red;  ";
  }
 elseif($data_lab[$i][2]=='Cancelado' ){
  $background=" color:orange;  ";
 }
  elseif($data_lab[$i][2]=='Ejecutando' ){
   $background=" color:blue;  ";
  }
  else{$background=" color:green; ";}

  $estructura_log.='<td  data-original-title="Toggle Navigation"  style="'.$background.'">'. $data_lab[$i][2]."</td> \n";
  $estructura_log.='<td>'.$data_lab[$i][4]."</td> \n";
   $estructura_log.='<td  class="text-truncate"  >'.$data_lab[$i][3]."</td> \n"; 
 $estructura_log.='</tr>';

}


array_push($resultadoMSDB,$estructura_log.'<tbody></table>');

 ?>
