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
<title> <?php echo $entidade_user; ?> - <?php echo __('Charts','dashboard'). " " . __('by Category','dashboard'); ?></title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="content-language" content="en-us" />
<!--  <meta http-equiv="refresh" content= "120"/> -->

<link rel="icon" href="../../../../pics/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="../../../../pics/favicon.ico" type="image/x-icon" />
<link href="../css/styles.css" rel="stylesheet" type="text/css" />
<link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
<link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
<link href="../css/font-awesome.css" type="text/css" rel="stylesheet" />

<script type="text/javascript" src="../js/jquery.min.js"></script>
<link href="../inc/select2/select2.css" rel="stylesheet" type="text/css">
<script src="../inc/select2/select2.js" type="text/javascript" language="javascript"></script>

<script src="../js/highcharts.js"></script>
<script src="../js/modules/exporting.js"></script>
<script src="../js/modules/no-data-to-display.js"></script>

<script src="../js/bootstrap-datepicker.js"></script>
<link href="../css/datepicker.css" rel="stylesheet" type="text/css">

<?php echo '<link rel="stylesheet" type="text/css" href="../css/style-'.$_SESSION['style'].'">';  ?>
<?php echo '<script src="../js/themes/'.$_SESSION['charts_colors'].'"></script>'; ?>

</head>
<body style="background-color: #e5e5e5; margin-left:0%;">

<?php

global $DB;

if(!empty($_POST['submit']))
{
	$data_ini =  $_POST['date1'];
	$data_fin = $_POST['date2'];
}

else {
	$data_ini = date("Y-m-01");
	$data_fin = date("Y-m-d");
}

$ano = date("Y");
$month = date("Y-m");
$datahoje = date("Y-m-d");

// cat
$id_cat = isset($_POST["sel_cat"]) ? $_POST["sel_cat"] : (isset($_GET["sel_cat"]) ? $_GET["sel_cat"] : null);


# sons categories
if(!isset($_POST["sons"])) {
	//$sons_cat = $_GET["sons"];
	$sons_cat = 0;
}

else {
	$sons_cat = $_POST["sons"];
}


# entity
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$_SESSION['glpiID']."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');

//select entity
if($sel_ent == '' || $sel_ent == -1) {

	$query_ent1 = "
	SELECT entities_id
	FROM glpi_users
	WHERE id = ".$_SESSION['glpiID']." ";

	$res_ent1 = $DB->query($query_ent1);
	$user_ent = $DB->result($res_ent1,0,'entities_id');

	//get all user entities
	$entities = $_SESSION['glpiactiveentities'];
	$ent = implode(",",$entities);

	$entidade = "AND glpi_tickets.entities_id IN (".$ent.")";
	$entidade_cw = "WHERE (entities_id IN (".$ent.") OR is_recursive = 1)";	
}
else {
	$entidade = "AND glpi_tickets.entities_id IN (".$sel_ent.")";
	$entidade_cw = "WHERE (entities_id IN (".$sel_ent.") OR is_recursive = 1)";	
}

// lista
function dropdown( $name, array $options, $selected=null )
{
    /*** begin the select ***/
    $dropdown = '<select style="width: 300px; height: 27px;" autofocus onChange="javascript: document.form1.submit.focus()" name="'.$name.'" id="'.$name.'">'."\n";

    $selected = $selected;
    /*** loop over the options ***/
    foreach( $options as $key=>$option )
    {
        /*** assign a selected value ***/
        $select = $selected==$key ? ' selected' : null;

        /*** add each option to the dropdown ***/
        $dropdown .= '<option value="'.$key.'"'.$select.'>'.$option.'</option>'."\n";
    }

    /*** close the select ***/
    $dropdown .= '</select>'."\n";

    /*** and return the completed dropdown ***/
    return $dropdown;
}

// lista de categorias
$sql_cat = "
SELECT id, completename AS name
FROM `glpi_itilcategories`
". $entidade_cw ."
ORDER BY `name` ASC ";

$result_cat = $DB->query($sql_cat);

$arr_cat = array();
$arr_cat[0] = "-- ". __('Select a category', 'dashboard') . " --" ;

while ($row_result = $DB->fetchAssoc($result_cat))
{
	$v_row_result = $row_result['id'];
	$arr_cat[$v_row_result] = $row_result['name'] ;
}

$name = 'sel_cat';
$options = $arr_cat;
$selected = $id_cat;

?>
<div id='content' >
<div id='container-fluid' style="margin: 0px 5% 0px 5%;">
<div id="pad-wrapper" >

<div id="charts" class="fluid chart">
	<div id="head" class="fluid">

		<div id="titulo_graf" >
		   <?php echo __('Tickets','dashboard') ." ". __('by Category','dashboard'); ?>
			<span style="color:#8b1a1a; font-size:35pt; font-weight:bold;"> </span>
		</div>
		<div id="datas-tec" class="col-md-12 col-sm-12 fluid" >
		
			<form id="form1" name="form1" class="form2" method="post" action="?date1=<?php echo $data_ini ?>&date2=<?php echo $data_fin ?>&con=1">
				<table border="0" cellspacing="0" cellpadding="1" bgcolor="#efefef" width="850" style="margin-bottom: 20px;">
					<tr>
					<td style="width: 360px;">
					<?php
						echo'
							<table>
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
					<?php
						echo dropdown( $name, $options, $selected );
					?>
					</td>
					<td class="sons"> 
						<input type="checkbox" name="sons" value="1"> <?php echo __('Sons','dashboard'); ?>
					</td>
					</tr>
					<tr><td height="15px"></td></tr>
					<tr>
						<td colspan="2" align="center" style="">
							<button class="btn btn-primary btn-sm" type="submit" name="submit" value="Atualizar" ><i class="fa fa-search"></i>&nbsp; <?php echo __('Consult','dashboard'); ?></button>
							<button class="btn btn-primary btn-sm" type="button" name="Limpar" value="Limpar" onclick="location.href='graf_categoria.php'" > <i class="fa fa-trash-o"></i>&nbsp; <?php echo __('Clean','dashboard'); ?> </button></td>
						</td>
					</tr>
				</table>
			<?php Html::closeForm(); ?>
			<!-- </form> -->
		</div>
	</div>
<!-- DIV's -->
<script type="text/javascript" >
	$(document).ready(function() { $("#sel_cat").select2({dropdownAutoWidth : true}); });
</script>

<?php
if(isset($_REQUEST['con'])) {
	$con = $_REQUEST['con'];
}
else { $con = ''; }

if($con == "1") {
		
		if (!isset($_POST['date1'])) {
			$data_ini = $_GET['date1'];
			$data_fin = $_GET['date2'];
		} else {
			$data_ini = $_POST['date1'];
			$data_fin = $_POST['date2'];
		}

		if (empty($id_cat)) {
			echo '<script language="javascript"> alert(" ' . __('Select a category','dashboard') . ' "); </script>';
			echo '<script language="javascript"> location.href="graf_categoria.php"; </script>';
			exit; // Encerra o script após exibir a mensagem de erro
		}

		if ($data_ini == $data_fin) {
			$datas = "LIKE '".$data_ini."%'";
		} else {
			$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";
		}

		
		# sons categories
		if (isset($_POST["sons"])) {
			$sons_cat = $_POST["sons"];
		} 
		else if (isset($_REQUEST["sons"])) {
			$sons_cat = $_REQUEST["sons"];
		} 
		else {
			$sons_cat = null; // Defina um valor padrão caso a chave 'sons' não esteja definida
		}


      ?>

		 
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
					
				// nome da categoria
				$sql_nm = "
				SELECT id, completename AS cname, name
				FROM glpi_itilcategories
				WHERE id = ".$id_cat." ";
				
				$result_nm = $DB->query($sql_nm);
				$ent_name = $DB->fetchAssoc($result_nm);

				if($sons_cat == 1) {
						
					$get_sons = getSonsOf('glpi_itilcategories',$id_cat);
					$id_cat = implode(',',$get_sons);	
					$and_sons = " (+ ".__('Sons','dashboard').")";						
				}
				else {
					$and_sons = "";	
				}
					
				 
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
				AND glpi_tickets.itilcategories_id IN (".$id_cat.")
				AND glpi_tickets.date ".$datas." ";

				
				$result_stat = $DB->query($query_stat);
				
				$new = $DB->result($result_stat,0,'new') + 0;
				$assig = $DB->result($result_stat,0,'assig') + 0;
				$plan = $DB->result($result_stat,0,'plan') + 0;
				$pend = $DB->result($result_stat,0,'pend') + 0;
				$solve = $DB->result($result_stat,0,'solve') + 0;
				$close = $DB->result($result_stat,0,'close') + 0;
				
					
										
				//quant chamados
				$query2 = "
				SELECT COUNT(glpi_tickets.id) as total
				FROM glpi_tickets
				WHERE glpi_tickets.date ".$datas."
				AND glpi_tickets.is_deleted = 0
				AND glpi_tickets.itilcategories_id IN (".$id_cat.")
				". $entidade ."  ";
				
				$result2 = $DB->query($query2) or die('erro1');
				
				$total = $DB->fetchAssoc($result2);
				
				//echo '<div id="entidade" class="col-md-12 col-sm-12 fluid" style="margin-top: -110px !important;">';
				//echo $ent_name['name']." ".$and_sons." - <span> ".$total['total']." ".__('Tickets','dashboard')."</span><br>";
				
				echo '<div id="entidade2" class="col-md-12 fluid" style="margin-bottom: 15px;">';
				echo '<div id="name"  style="margin-top: 15px;"><span class="total_tech"> '.$ent_name['name'].' '.$and_sons.' '.$total['total'].' '.__('Tickets','dashboard').'</span></div>
				
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
				
		<div id="graf_linhas" class="col-md-12 col-sm-12" style="height: 450px; margin-top: 20px; margin-bottom: 20px;">
			<?php include ("./inc/graflinhas_cat.inc.php"); ?>
		</div>

		
		<div id="graf2" class="col-md-6 col-sm-6" >
			<?php include ("./inc/grafpie_stat_cat.inc.php"); ?>
		</div>
		
		<div id="graf_tipo" class="col-md-6 col-sm-6" style="margin-left: 0%;">
			<?php include ("./inc/grafpie_tipo_cat.inc.php");  ?>
		</div>
		
		<div id="graf3" class="col-md-12 col-sm-12" >
			<?php  include ("./inc/grafbar_cat_user.inc.php");  ?>
		</div>
		
		<div id="grafcat_tec" class="col-md-12 col-sm-12" style="height: 450px; margin-top: 240px; margin-left: 0px;">
			<?php  include ("./inc/grafbar_cat_tec.inc.php");

		}
		
	?>
	</div>
	</div>
</div>
</div>
</div>


<!-- Highcharts export xls, csv -->
<script src="../js/export-csv.js"></script>
</body>
</html>
