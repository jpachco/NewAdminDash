<?php
include_once('consulta_monitorLogs.php');
include_once('consulta_monitorMF.php');
include_once('consulta_monitorRB.php');
include_once('consulta_monitorHL.php');
include_once('consulta_monitorBO.php');
include_once('consulta_monitorBOM.php');
include_once('consulta_monitorCRM.php');


echo'

<div class="row fade-in">
    <!-- Cards de Estadísticas -->
    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="stat-title">Venta PH BOGGI al día</div>
                        <div class="stat-value">'.$resultadoLB1[0].'</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="stat-title">Ultimá Actualización </div>
                        <div class="stat-value">'. $resultadoLB1[1].'</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-shield stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
</div> 
           <div class="row fade-in">
    <!-- Últimos Usuarios Registrados -->
    <div class="col-lg-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Log Actividades Database</h6>
                <button onclick="exportTableToExcel(\'jobs\', \'Reporte_'.date('Y-m-d').'.xlsx\')" class="btn btn-outline-success">
                 Descargar Reporte del Día
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
              '.$resultadoMSDB[0].'
            </div>
            </div>
        </div>
    </div>
</div>
            
            ';



 ?>


<script type="text/javascript">
    var today = new Date(),
        day = 1000 * 60 * 60 * 24;

    // Set to 00:00:00:000 today
    today.setUTCHours(0);
    today.setUTCMinutes(0);
    today.setUTCSeconds(0);
    today.setUTCMilliseconds(0);

    // THE CHART
 Highcharts.ganttChart('monitor_mensfashion', {
     chart: {

         styledMode: true,
         height: 1080,
         width: null
     },
     title: {
   text: 'Mensfashion'
  },
  series: [{
   name: 'Actualización',
   data: [
       {
           name: 'BI',
           id: 'plan',
           start: today.getTime(),
           end: today.getTime(),
           milestone: true
       },

       <?php  print_r($estructuraMF);    ?>]
  }]
 });

</script>


<script type="text/javascript">
    var today = new Date(),
        day = 1000 * 60 * 60 * 24;

    // Set to 00:00:00:000 today
    today.setUTCHours(0);
    today.setUTCMinutes(0);
    today.setUTCSeconds(0);
    today.setUTCMilliseconds(0);

    // THE CHART
 Highcharts.ganttChart('monitor_roberts', {
     chart: {

         styledMode: true,
         height: 1080,
         width: null
     },
     title: {
   text: 'Roberts'
  },
  series: [{
   name: 'Actualización',
   data: [
       {
           name: 'BI',
           id: 'plan',
           start: today.getTime(),
           end: today.getTime(),
           milestone: true
       },

       <?php  print_r($estructuraRB);    ?>]
  }]
 });

</script>
<script type="text/javascript">
    var today = new Date(),
        day = 1000 * 60 * 60 * 24;

    // Set to 00:00:00:000 today
    today.setUTCHours(0);
    today.setUTCMinutes(0);
    today.setUTCSeconds(0);
    today.setUTCMilliseconds(0);

    // THE CHART
 Highcharts.ganttChart('monitor_highlife', {
     chart: {

         styledMode: true,
         height: 1080,
         width: null
     },
     title: {
   text: 'Highlife'
  },
  series: [{
   name: 'Actualización',
   data: [
       {
           name: 'BI',
           id: 'plan',
           start: today.getTime(),
           end: today.getTime(),
           milestone: true
       },

       <?php  print_r($estructuraHL);    ?>]
  }]
 });

</script>
<script type="text/javascript">
    var today = new Date(),
        day = 1000 * 60 * 60 * 24;

    // Set to 00:00:00:000 today
    today.setUTCHours(0);
    today.setUTCMinutes(0);
    today.setUTCSeconds(0);
    today.setUTCMilliseconds(0);

    // THE CHART
 Highcharts.ganttChart('monitor_lamberti', {
     chart: {

         styledMode: true,
         height: 1080,
         width: null
     },
     title: {
   text: 'Boggi'
  },
  series: [{
   name: 'Actualización',
   data: [
       {
           name: 'BI',
           id: 'plan',
           start: today.getTime(),
           end: today.getTime(),
           milestone: true
       },

       <?php  print_r($estructuraLB);    ?>]
  }]
 });

</script>
<script type="text/javascript">
    var today = new Date(),
        day = 1000 * 60 * 60 * 24;

    // Set to 00:00:00:000 today
    today.setUTCHours(0);
    today.setUTCMinutes(0);
    today.setUTCSeconds(0);
    today.setUTCMilliseconds(0);

    // THE CHART
 Highcharts.ganttChart('monitor_crm', {
     chart: {

         styledMode: true,
         height: 1080,
         width: null
     },
     title: {
   text: 'CRM'
  },
  series: [{
   name: 'Actualización',
   data: [
       {
           name: 'BI',
           id: 'plan',
           start: today.getTime(),
           end: today.getTime(),
           milestone: true
       },

       <?php  print_r($estructuraCRM);    ?>]
  }]
 });

</script>