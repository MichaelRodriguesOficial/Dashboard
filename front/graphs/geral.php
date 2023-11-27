<?php

include ("../../../../inc/includes.php");
include ("../../../../inc/config.php");

Session::checkLoginUser();
Session::checkRight("profile", READ);

$userID = $_SESSION['glpiID'];

# entity in index
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$userID."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');

if ($sel_ent == 0) {
    $entities = $_SESSION['glpiactiveentities'];
    $sel_ent = implode(",", $entities);
    $query = "SELECT name FROM glpi_entities WHERE id IN (".$sel_ent.")";
    $result = $DB->query($query);
    $entidade_user = $DB->result($result, 0, 'name');
} 

else {
    $entidade_user = "GLPi";
}


global $DB;
   
    switch (date("m")) {
    case "01": $mes = __('January','dashboard'); break;
    case "02": $mes = __('February','dashboard'); break;
    case "03": $mes = __('March','dashboard'); break;
    case "04": $mes = __('April','dashboard'); break;
    case "05": $mes = __('May','dashboard'); break;
    case "06": $mes = __('June','dashboard'); break;
    case "07": $mes = __('July','dashboard'); break;
    case "08": $mes = __('August','dashboard'); break;
    case "09": $mes = __('September','dashboard'); break;
    case "10": $mes = __('October','dashboard'); break;
    case "11": $mes = __('November','dashboard'); break;
    case "12": $mes = __('December','dashboard'); break;
    }
?>

<html> 
<head>
<title> <?php echo $entidade_user; ?> - <?php echo __('Charts','dashboard')." - ".__('Overall','dashboard'); ?></title>
<!-- <base href= "<?php $_SERVER['SERVER_NAME'] ?>" > -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="content-language" content="en-us" />

<link rel="icon" href="../../../../pics/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="../../../../pics/favicon.ico" type="image/x-icon" />
<link href="../css/styles.css" rel="stylesheet" type="text/css" />
<link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
<link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
<link href="../css/font-awesome.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="../js/jquery.min.js"></script> 

<script src="../js/highcharts.js"></script>
<script src="../js/highcharts-3d.js"></script>
<script src="../js/modules/boost.js"></script>
<script src="../js/modules/exporting.js"></script>
<script src="../js/modules/no-data-to-display.js"></script>

<?php echo '<link rel="stylesheet" type="text/css" href="../css/style-'.$_SESSION['style'].'">';  ?>
<?php echo '<script src="../js/themes/'.$_SESSION['charts_colors'].'"></script>'; ?>

</head>

<body style="background-color:#e5e5e5; margin-left:0%;">
<?php

$ano = date("Y");
$month = date("Y-m");
$datahoje = date("Y-m-d");

# entity
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$_SESSION['glpiID']."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');


if($sel_ent != '') {			
	$entidade = "AND glpi_tickets.entities_id IN (".$sel_ent.")";
	$problem =  "AND glpi_problems.entities_id IN (".$sel_ent.")";
}

if($sel_ent == '') {
	
	$entities = $_SESSION['glpiactiveentities'];
	$ent = implode(",",$entities);
	$entidade = "AND glpi_tickets.entities_id IN (".$ent.")";
	$problem =  "AND glpi_problems.entities_id IN (".$ent.")";
}

//total de chamados
$sql =	"SELECT COUNT(glpi_tickets.id) as total        
      FROM glpi_tickets
      LEFT JOIN glpi_entities ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.is_deleted = '0'
      ".$entidade." ";

$result = $DB->query($sql) or die ("erro");
$total_mes = $DB->fetchAssoc($result);
				
				
				
				//count by status
				$query_stat = "
				SELECT
				SUM(case when glpi_tickets.status = 1 then 1 else 0 end) AS new,
				SUM(case when glpi_tickets.status = 2 then 1 else 0 end) AS assig,
				SUM(case when glpi_tickets.status = 3 then 1 else 0 end) AS plan,
				SUM(case when glpi_tickets.status = 4 then 1 else 0 end) AS pend,
				SUM(case when glpi_tickets.status = 5 then 1 else 0 end) AS solve,
				SUM(case when glpi_tickets.status = 6 then 1 else 0 end) AS close
				FROM glpi_tickets
				WHERE glpi_tickets.is_deleted = 0";

				
				$result_stat = $DB->query($query_stat);
				
				$new = $DB->result($result_stat,0,'new') + 0;
				$assig = $DB->result($result_stat,0,'assig') + 0;
				$plan = $DB->result($result_stat,0,'plan') + 0;
				$pend = $DB->result($result_stat,0,'pend') + 0;
				$solve = $DB->result($result_stat,0,'solve') + 0;
				$close = $DB->result($result_stat,0,'close') + 0;
				


?>
<div id='content' >
	<div id='container-fluid' style="margin: 0px 5% 0px 5%;"> 

	<div id="pad-wrapper" >
		<div id="charts" class="fluid chart"> 
			<div id="head" class="fluid" style="min-height:100px !important;">						
				<div id="titulo_graf" style="text-align: center;">				
					<?php echo __('Tickets Total','dashboard'); ?>: <?php //echo $ano .":" ; ?> 
					<span class="quant"> <?php echo " ".$total_mes['total'] ; ?> </span> 
				</div>
			</div>
			
<?php
				echo '<div id="entidade2" class="col-md-12 fluid" style="margin-bottom: 15px;">
				
				<div class="row" style="margin: 10px 0px 0 0;" >
				<div style="margin-top: 20px; height: 45px;">
						<!-- COLUMN 1 -->
							  <div class="col-sm-3 col-md-3 stat" >
								 <div class="dashbox shad panel panel-default db-blue">
									<div class="panel-body_2">
									   <div class="panel-left red bluebg" style = "margin-top: -5px; margin-left: -5px;">
											<i class="fa fa-tags fa-3x fa2"></i>
									   </div>
									   <div class="panel-right">
										 <div id="odometer1" class="odometer" style="font-size: 20px; margin-top: 1px;">  </div><p></p>
										<span class="chamado">'. __('Tickets','dashboard').'</span><br>
										<span class="date" style="font-size: 16px;"><b>'. _x('status', 'New').' + '.__('Assigned').'</b></span>
									   </div>
									</div>
								 </div>
							  </div>
			
							  <div class="col-sm-3 col-md-3">
								 <div class="dashbox shad panel panel-default db-orange">
									<div class="panel-body_2">
									   <div class="panel-left orange orangebg" style = "margin-top: -5px; margin-left: -5px;">
											<i class="fa fa-clock-o fa-3x fa2"></i>
									   </div>
									   <div class="panel-right">
										<div id="odometer2" class="odometer" style="font-size: 20px; margin-top: 1px;">   </div><p></p>
										<span class="chamado">'. __('Tickets','dashboard').'</span><br>
										<span class="date"><b>'. __('Pending').'</b></span>
									   </div>
									</div>
								 </div>
							  </div>
			
							  <div class="col-sm-3 col-md-3">
								 <div class="dashbox shad panel panel-default db-red">
									<div class="panel-body_2">
									   <div class="panel-left yellow redbg" style = "margin-top: -5px; margin-left: -5px;">
											<i class="fa fa-check-square fa-3x fa2"></i>
									   </div>
									   <div class="panel-right">
											<div id="odometer3" class="odometer" style="font-size: 20px; margin-top: 1px;">   </div><p></p>
										<span class="chamado">'. __('Tickets','dashboard').'</span><br>
										<span class="date"><b>'. __('Solved','dashboard').'</b></span>
									   </div>
									</div>
								 </div>
							  </div>
							  
							  <div class="col-sm-3 col-md-3">
								 <div class="dashbox shad panel panel-default db-yellow">
									<div class="panel-body_2">
									   <div class="panel-left yellow yellowbg" style = "margin-top: -5px; margin-left: -5px;">
											<i class="fa fa-times-circle fa-3x fa2"></i>
									   </div>
									<div class="panel-right">
											<div id="odometer4" class="odometer" style="font-size: 20px; margin-top: 1px;">   </div><p></p>
										<span class="chamado">'. __('Tickets','dashboard').'</span><br>
										<span class="date"><b>'. __('Closed','dashboard').'</b></span>
									   </div>
									</div>
								 </div>
							  </div>
					</div>
				
				</div>
				</div>';
				
				?>
				
				<script type="text/javascript" >
					window.odometerOptions = {
					   format: '( ddd).dd'
					};
				
					setTimeout(function(){
						odometer1.innerHTML = <?php echo $new + $assig + $plan; ?>;
						odometer2.innerHTML = <?php echo $pend; ?>;
						odometer3.innerHTML = <?php echo $solve; ?>;
						odometer4.innerHTML = <?php echo $close; ?>;
					}, 1000);
				</script>				
			
			

			<!-- DIV's -->
			
			<div id="graf_linhas" class="col-md-12 col-sm-12 geral_linhas" style="margin-left: 0px;" >
				<?php include ("./inc/graflinhas_sat_geral.inc.php"); ?>
			</div>
			
			<div id="graf2" class="col-md-6 col-sm-6" style="margin-top:45px;" >
			 <?php include ("./inc/grafpie_stat_geral.inc.php"); ?>
			</div>
			
			<div id="graf4" class="col-md-6 col-sm-6" style="margin-top:45px;">
			 <?php include ("./inc/grafpie_origem.inc.php");  ?>
			</div>
			
			<div id="graf_tipo" class="col-md-12 col-sm-12 fluid" style="margin-top: 35px;">
			 <?php include ("./inc/grafcol_tipo_geral.inc.php");  ?>
			</div>
			
			<div>
			 <?php include ("./inc/grafent_geral.inc.php");  ?>
			</div>
			
			<div id="graftime" class="col-md-6 col-sm-6" style="height:450px; margin-top:35px; margin-left: 0px;">
			 <?php include ("./inc/grafpie_time_geral.inc.php");?>
			</div>
			
			<div id="grafgrp" class="col-md-6 col-sm-6 fluid" style="height: 450px; margin-top:35px; margin-left: 0px;">
			 <?php include ("./inc/grafbar_grupo_geral.inc.php");?>
			</div>			
						
			<div id="grafcat"  class="col-md-12 col-sm-12 fluid" style="margin-top:35px; margin-left: 0px;">
			 <?php include ("./inc/grafcat_geral.inc.php"); ?>
			</div>
			
			<div id="graf5" class="col-md-6 col-sm-6 fluid" style="height:450px; margin-top:35px; ">
				<?php include ("./inc/grafbar_tec.inc.php"); ?>
			</div>	
				
			<div id="graf3" class="col-md-6 col-sm-6 fluid" style="height:450px; margin-top:35px; ">
				<?php include ("./inc/grafbar_requerente.inc.php"); ?>
			</div>
			
			</div>
		</div>
</div>

<!-- Highcharts export xls, csv -->
<script src="../js/export-csv.js"></script>

</body>
</html>
