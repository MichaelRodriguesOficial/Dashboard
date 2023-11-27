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


$mydate = isset($_POST["date1"]) ? $_POST["date1"] : "";
?>

<html> 
<head>
<title> <?php echo $entidade_user; ?> - <?php echo __('Tickets','dashboard') .'  '. __('By location'); ?></title>
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
<link href="../css/datepicker.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="../js/jquery.min.js"></script> 
<script src="../js/jquery-ui.min.js"></script>  
<script src="../js/highcharts.js"></script>
<script src="../js/modules/exporting.js"></script>
<script src="../js/modules/no-data-to-display.js"></script>
<script src="../js/bootstrap-datepicker.js"></script>

<?php echo '<link rel="stylesheet" type="text/css" href="../css/style-'.$_SESSION['style'].'">';  ?>  
<?php echo '<script src="../js/themes/'.$_SESSION['charts_colors'].'"></script>'; ?>
  
</head>

<body style="background-color:#e5e5e5; margin-left:0%;">

<?php

if(!empty($_POST['submit']))
{	
	$data_ini =  $_POST['date1'];	
	$data_fin = $_POST['date2'];
}

else {
	$data_ini = date("Y-m-01");
	$data_fin = date("Y-m-d");
}    

$month = date("Y-m");
$datahoje = date("Y-m-d");  
	  
?>
<div id='content' >
<div id='container-fluid' style="margin: 0px 5% 0px 5%;"> 		 		
		<div id="charts" class="fluid chart"> 
		<div id="head" class="fluid">				
			<div id="titulo_graf" style="margin-bottom:45px;"> <?php echo __('Tickets','dashboard') .'  '. __('By location');  ?> 	
					<div id="datas" class="col-md-12" > 
					<form id="form1" name="form1" class="form1" method="post" action="?date1=<?php echo $data_ini ?>&date2=<?php echo $data_fin ?>"> 
						<table border="0" cellspacing="0" cellpadding="0">
								<tr>
									<td style="width: 300px;">			
									<?php	
									$url = $_SERVER['REQUEST_URI'];
									$arr_url = explode("?", $url);
									$url2 = $arr_url[0];									
									echo'
									<table style="margin-top:6px;" border=0>
										<tr>
											<td>
											   <div class="input-group date" id="dp1" data-date="'.$data_ini.'" data-date-format="yyyy-mm-dd">
											    	<input class="col-md-9 form-control" size="13" type="text" name="date1" value="'.$data_ini.'" >		    	
											    	<span class="input-group-addon add-on"><i class="fa fa-calendar"></i></span>	    	
										    	</div>
											</td>
											<td>&nbsp;</td>
											<td>
										   	<div class="input-group date" id="dp2" data-date="'.$data_fin.'" data-date-format="yyyy-mm-dd">
											    	<input class="col-md-9 form-control" size="13" type="text" name="date2" value="'.$data_fin.'" >		    	
											    	<span class="input-group-addon add-on"><i class="fa fa-calendar"></i></span>	    	
										    	</div>
											</td>
											<td>&nbsp;</td>
										</tr>
									</table> ';
									?>
									
									<script language="Javascript">			
										$('#dp1').datepicker('update');
										$('#dp2').datepicker('update');			
									</script>
									</td>
								
									<td style="margin-top:2px;">
								</tr>
								<tr height="12px" ><td></td></tr>
								<tr align="center">
									<td>
										<button class="btn btn-primary btn-sm" type="submit" name="submit" value="Atualizar" ><i class="fa fa-search"></i>&nbsp; <?php echo __('Consult','dashboard'); ?> </button>
										<button class="btn btn-primary btn-sm" type="button" name="Limpar" value="Limpar" onclick="location.href='<?php echo $url2 ?>'" ><i class="fa fa-trash-o"></i>&nbsp; <?php echo __('Clean','dashboard'); ?> </button>
									</td>
								</tr>
						</table>
					<p>
					</p>
					<?php Html::closeForm(); ?>
					<!-- </form> -->
					</div>
		
		</div>
		</div>
		</div>	
		
<?php
				
				if($data_ini == $data_fin) {
				$datas = "LIKE '".$data_ini."%'";	
				}	
				
				$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";
				
					$query_total = "
					SELECT COUNT(id) AS total
					FROM glpi_tickets
					WHERE glpi_tickets.is_deleted = '0'
					AND date ".$datas."";
					
					$result_total = $DB->query($query_total) or die('erro');
					$total = $DB->fetchAssoc($result_total);
					
				 
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
				WHERE glpi_tickets.is_deleted = 0
				AND glpi_tickets.date ".$datas." ";

				
				$result_stat = $DB->query($query_stat);
				
				$new = $DB->result($result_stat,0,'new') + 0;
				$assig = $DB->result($result_stat,0,'assig') + 0;
				$plan = $DB->result($result_stat,0,'plan') + 0;
				$pend = $DB->result($result_stat,0,'pend') + 0;
				$solve = $DB->result($result_stat,0,'solve') + 0;
				$close = $DB->result($result_stat,0,'close') + 0;
				
				echo '<div id="entidade2" class="col-md-12 fluid" style="margin-bottom: 15px;">';
				echo '<div id="name"  style="margin-top: 15px;"><span class="total_tech"> '.$total['total'].' '.__('Tickets','dashboard').'</span></div>
				
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
		
		
		
		<div id="graf1" class="fluid">
			<?php include ("./inc/grafbar_local.inc.php"); ?>
		</div>
</div>
</div>
</div>

<!-- Highcharts export xls, csv -->
<script src="../js/export-csv.js"></script>

</body>
</html>
