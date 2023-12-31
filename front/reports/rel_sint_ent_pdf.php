<?php

include ("../../../../inc/includes.php");
include ("../../../../inc/config.php");
include "../inc/functions.php";

global $DB;

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


if(!empty($_REQUEST['submit']))
{
    $data_ini =  $_REQUEST['date1'];
    $data_fin = $_REQUEST['date2'];
}

else {
    $data_ini = date("Y-m-01");
    $data_fin = date("Y-m-d");
    }

if (!isset($_POST["sel_date"]) && isset($_GET["date"])) {
    $id_date = $_GET["date"];
} 

elseif (isset($_POST["sel_date"])) {
    $id_date = $_POST["sel_date"];
} 

else {
    $id_date = null; // ou outra ação para lidar com o caso de $id_date não estar definido
}

$content ='';

?>

<head>
<title> <?php echo $entidade_user; ?> - <?php echo __('Summary Report','dashboard')." - ". __('Entity') ?> </title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="content-language" content="en-us" />
<meta charset="utf-8">

<link rel="icon" href="../../../../pics/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="../../../../pics/favicon.ico" type="image/x-icon" />
<link href="../css/styles.css" rel="stylesheet" type="text/css" />
<link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
<link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
<link href="../css/font-awesome.css" type="text/css" rel="stylesheet" />

<script language="javascript" src="../js/jquery.js"></script>
<script src="../js/bootstrap.min.js"></script>

<style type="text/css">	
	select { width: 60px; }
	table.dataTable { empty-cells: show; }
   a:link, a:visited, a:active { text-decoration: none;}
</style>

<?php echo '<link rel="stylesheet" type="text/css" href="../css/style-'.$_SESSION['style'].'">';  ?> 
</head>

<body style="background-color: #fff !important;">
<?php
		
		$con = $_REQUEST['con'];
		if($con == "1") {
		
		if(!isset($_REQUEST['date1']))
		{
		    $data_ini2 = $_REQUEST['date1'];
		    $data_fin2 = $_REQUEST['date2'];
		}
		
		else {
		    $data_ini2 = $_REQUEST['date1'];
		    $data_fin2 = $_REQUEST['date2'];
		}
		
		if($data_ini2 == $data_fin2) {
			$datas2 = "LIKE '".$data_ini2."%'";
		}
		
		else {
			$datas2 = "BETWEEN '".$data_ini2." 00:00:00' AND '".$data_fin2." 23:59:59'";
		}
		
		// do select
		//$post_date = $_REQUEST["sel_date"];
		$post_date = isset($_REQUEST["sel_date"]) ? $_REQUEST["sel_date"] : null;
		
		if(!isset($post_date) or $post_date == "0") {
		    $sel_date = $datas2;
		}
		
		else {
		    $sel_date = $_REQUEST["sel_date"];
		}
		
		switch($post_date) {
		
          case ("1") :
             $data_ini2 = date('Y-m-01');
             $data_fin2 = date('Y-m-d');
              $sel_date = "BETWEEN '" . $data_ini2 ." 00:00:00' AND '". $data_fin2 ." 23:59:59'";
          break;
          case ("2") :
             $data_ini2 = date('Y-m-d', strtotime('-1 week'));
              $sel_date = "BETWEEN '" . $data_ini2 ." 00:00:00' AND '".$data_fin2." 23:59:59'";
          break;
          case ("3") :
             $data_ini2 = date('Y-m-d', strtotime('-15 day'));
              $sel_date = "BETWEEN '" . $data_ini2 ." 00:00:00' AND '".$data_fin2." 23:59:59'";
          break;
          case ("4") :
              $data_ini2 = date('Y-m-d', strtotime('-1 month'));
              $sel_date = "BETWEEN '" . $data_ini2 ." 00:00:00' AND '".$data_fin2." 23:59:59'";
          break;
          case ("5") :
              $data_ini2 = date('Y-m-d', strtotime('-3 month'));
              $sel_date = "BETWEEN '" . $data_ini2 ." 00:00:00' AND '".$data_fin2." 23:59:59'";
          break;		
		}
		
		
		//selected entity	
		$id_ent = $_REQUEST["sel_ent"];
		$entidade = "AND glpi_tickets.entities_id = ".$id_ent."";
		
		//entity name
		$sql_entname = "
		SELECT id, name, completename AS cname
		FROM `glpi_entities`
		WHERE id = ".$id_ent."
		ORDER BY `cname` ASC ";

		$result_entname = $DB->query($sql_entname);
		$entname = $DB->result($result_entname,0,'cname');

		
		// Chamados
		$sql_cham = "SELECT glpi_tickets.id AS id, glpi_tickets.name AS descr, glpi_tickets.date AS date,
		 glpi_tickets.solvedate AS solvedate, glpi_tickets.status AS status
		FROM glpi_tickets
		WHERE glpi_tickets.date ".$sel_date."
		AND glpi_tickets.is_deleted = 0		
		".$entidade."
		ORDER BY id DESC ";
		
		$result_cham = $DB->query($sql_cham);
		$chamados = $DB->fetchAssoc($result_cham) ;
		
				
		//quant de chamados
		$sql_cham2 =
		"SELECT count(id) AS total, count(date) AS numdias, AVG(close_delay_stat) AS avgtime
		FROM glpi_tickets
		WHERE date ".$sel_date."		
		AND glpi_tickets.is_deleted = 0
		".$entidade." ";
		
		$result_cham2 = $DB->query($sql_cham2);		
		$conta_cham = $DB->fetchAssoc($result_cham2);
		
		$total_cham = $conta_cham['total'];	
		
		
		if($total_cham > 0) {
			
			//date diff
			$numdias = round(abs(strtotime($data_fin2) - strtotime($data_ini2)) / 86400,0);
						
			//tecnico
			$sql_tec = "SELECT count(glpi_tickets.id) AS conta, glpi_users.firstname AS name, glpi_users.realname AS sname
			FROM `glpi_tickets_users` , glpi_tickets, glpi_users
			WHERE glpi_tickets.id = glpi_tickets_users.`tickets_id`
			AND glpi_tickets.date ".$sel_date."
			AND glpi_tickets_users.`users_id` = glpi_users.id
			AND glpi_tickets_users.type = 2
			".$entidade." 
			GROUP BY name
			ORDER BY conta DESC
			LIMIT 5";
			
			$result_tec = $DB->query($sql_tec);	
			
			//requester
			$sql_req = "SELECT count(glpi_tickets.id) AS conta, glpi_users.firstname AS name, glpi_users.realname AS sname
			FROM `glpi_tickets_users` , glpi_tickets, glpi_users
			WHERE glpi_tickets.id = glpi_tickets_users.`tickets_id`
			AND glpi_tickets.date ".$sel_date."
			AND glpi_tickets_users.`users_id` = glpi_users.id
			AND glpi_tickets_users.type = 1
			".$entidade." 
			GROUP BY name
			ORDER BY conta DESC
			LIMIT 5";
			
			$result_req = $DB->query($sql_req);		
											
			//avg time
			$sql_time =
			"SELECT count(id) AS total, AVG(close_delay_stat) AS avgtime
			FROM glpi_tickets
			WHERE date ".$sel_date."			
			AND glpi_tickets.is_deleted = 0			
			".$entidade." ";
			
			$result_time = $DB->query($sql_time);		
			$time_cham = $DB->fetchAssoc($result_time);
			
			$avgtime = $time_cham['avgtime'];
			
			
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
			WHERE glpi_tickets.is_deleted = '0'
			AND glpi_tickets.date ".$sel_date."			
			".$entidade."";
		
			$result_stat = $DB->query($query_stat);
		
                        $new = $DB->result($result_stat,0,'new') + 0;
                        $assig = $DB->result($result_stat,0,'assig') + 0;
                        $plan = $DB->result($result_stat,0,'plan') + 0;
                        $pend = $DB->result($result_stat,0,'pend') + 0;
                        $solve = $DB->result($result_stat,0,'solve') + 0;
                        $close = $DB->result($result_stat,0,'close') + 0;
			
			
			//count by type
			$query_type = "
			SELECT
			SUM(case when glpi_tickets.type = 1 then 1 else 0 end) AS incident,
			SUM(case when glpi_tickets.type = 2 then 1 else 0 end) AS request
			FROM glpi_tickets
			WHERE glpi_tickets.is_deleted = '0'
			AND glpi_tickets.date ".$sel_date."			
			".$entidade."";
		
			$result_type = $DB->query($query_type);
		
			$incident = $DB->result($result_type,0,'incident');
			$request = $DB->result($result_type,0,'request');
			
			//select groups
			$sql_grp = 
			"SELECT count(glpi_tickets.id) AS conta, glpi_groups.name AS name
			FROM `glpi_groups_tickets`, glpi_tickets, glpi_groups
			WHERE glpi_groups_tickets.`groups_id` = glpi_groups.id
			AND glpi_groups_tickets.`tickets_id` = glpi_tickets.id
			AND glpi_tickets.is_deleted = 0
			AND glpi_tickets.date ".$sel_date."
			".$entidade."
			GROUP BY name
			ORDER BY conta DESC
			LIMIT 5 ";			
			
			$result_grp = $DB->query($sql_grp);	
			
			//logo		

			$imgsize= "";
			
			if (file_exists('../../../../pics/logo_big.png')) {
				$logo = "../../../../pics/logo_big.png";
				$imgsize = "width:100px; height:100px;";
			}
			else {					
				if ($CFG_GLPI['version'] >= 0.90){					
					$logo = "../../../../pics/logos/logo-GLPI-100-black.png";
					#$imgsize = "background-color:#000;";
				}	
				else {
					$logo = "../../../../pics/logos/logo-GLPI-100-black.png";
					$imgsize = "";
				}
			}


$content = "
<page backtop='5mm' backbottom='5mm' backleft='15mm' backright='10mm'> 
      <page_header> 
      </page_header>
      <page_footer align='center'>
    		[[page_cu]]/[[page_nb]]
  		</page_footer>
       
 		<!-- <div class='fluid col-md-12 report' style='margin-left: 0px; margin-top: -8px;'> -->		
			
			 <div id='logo' class='fluid'>
			 	<span class='col-md-2' ><img src='".$logo."' alt='GLPI' style='".$imgsize."'> </span>
			 	<span class='col-md-8' style='margin-top:-80px; height:60px; text-align:center; margin:auto;'><h3 style='vertical-align:top;'>". __('Summary Report','dashboard')." - " .__('Entity')." </h3></span>
			 </div>
									
			 <table id='data' class='table table-condensed table-striped' style='font-size: 16px; width:55%; margin:auto; margin-top:35px; margin-bottom:20px;'>			
			 <tbody>		
			 <tr>
			 <td>" .__('Entity')."</td>
			 <td align='right'>".$entname ."</td>
			 </tr>			
			 <tr>
			 <td width='300'>". __('Period','dashboard').": </td>";
			 
			if($data_ini2 == $data_fin2) {
				$content .= "<td width='200' align='right'>".conv_data($data_ini2)."</td>";		
			}
			else {
				 $content .= "<td width='200' align='right'>".conv_data($data_ini2)." to ".conv_data($data_fin2)."</td>";
			}	

$content .= "					
			 </tr>
			
			 <tr>
			 <td>". __('Date').": </td>
			 <td align='right'>".conv_data_hora(date("Y-m-d H:i"))."</td>			
			 </tr>
			 <tr><td>&nbsp;</td></tr>
			 </tbody>
			 </table>
			 

			 <table class='fluid table table-striped table-condensed'  style='font-size: 16px; width:85%; margin:auto;'>
			 <thead>
			 <tr>
			 <th colspan='2' style='text-align:center; background:#286090; color:#fff;' >". __('Tickets','dashboard')."</th>						
			 </tr>
			 </thead>	

			 <tbody>			
			 <tr>
			 <td width='300'>". __('Tickets Total','dashboard')."</td>
			 <td width='200' align='right'>".$total_cham."</td>			
			 </tr>			
			
			 <tr>
			 <td>". _n('Day','Days',2)."</td>
			 <td align='right'>".$numdias."</td>
			 </tr>	
			
			 <tr>
			 <td>". __('Tickets','dashboard')." ". __('By day')." - ". __('Average')."</td>
			 <td align='right'>".round($total_cham / $numdias,0)."</td>
			 </tr>
			
			 <tr>
			 <td>". __('Average time to closure')."</td>
			 <td align='right'>". time_hrs($avgtime )."</td>
			 </tr>	
			 <tr><td>&nbsp;</td></tr>				
		    </tbody> 
		    </table>
		   		    

			 <table class='fluid table table-striped table-condensed'  style='font-size: 16px; width:55%; margin:auto;'>
			 <thead>
			 <tr>
			 <th colspan='2' style='text-align:center; background:#286090; color:#fff;'>". __('Tickets by Status','dashboard')."</th>						
			 </tr>
			 </thead>	

			 <tbody>							
			 <tr>
			 <td width='300'>". _x('status','New')."</td>
			 <td width='200' align='right'>".$new."</td>			
			 </tr>	
			
			 <tr>
			 <td>". __('Assigned')."</td>
			 <td align='right'>".$assig."</td>			
			 </tr>	
			
			 <tr>
			 <td>". __('Planned')."</td>
			 <td align='right'>".$plan."</td>			
			 </tr>	
			
			 <tr>
			 <td>". __('Pending')."</td>
			 <td align='right'>".$pend."</td>			
			 </tr>
			
			 <tr>
			 <td>". __('Solved','dashboard')."</td>
			 <td align='right'>".$solve."</td>			
			 </tr>	
			
			 <tr>
			 <td>". __('Closed')."</td>
			 <td align='right'>".$close."</td>			
			 </tr>
			 <tr><td>&nbsp;</td></tr>								
													
		    </tbody> 
		    </table>		   		    
		   
			 <table class='fluid table table-striped table-condensed'  style='font-size: 16px;  margin:auto;'>
			 <thead>
			 <tr>
			 <th colspan='2' style='text-align:center; background:#286090; color:#fff;'>". __('Tickets','dashboard')." ". __('by Type','dashboard')."</th>						
			 </tr>
			 </thead>	

			 <tbody>							
			 <tr>
			 <td width='300'>". __('Incident')."</td>
			 <td width='200' align='right'>".$incident."</td>			
			 </tr>				
			 <tr>
			 <td>". __('Request')."</td>
			 <td align='right'>".$request."</td>			
			 </tr>	
			 <tr><td>&nbsp;</td></tr>
			 </tbody> </table>
			 		
		    			   
			 <table class='fluid table table-striped table-condensed'  style='font-size: 16px; width:55%; margin:auto;'>
			 <thead>
			 <tr>
			 <th colspan='2' style='text-align:center; background:#286090; color:#fff;'>Top 5 - ". __('Tickets','dashboard')." ". __('by Group','dashboard')."</th>						
			 </tr>
			 </thead>	

			 <tbody>";
			
			while($row = $DB->fetchAssoc($result_grp)) {
				$content .= "<tr>
				 <td width='300'>".$row['name']."</td>
				 <td width='200' align='right'>".$row['conta']."</td>			
				 </tr> ";	
			}				 

$content .= "	 	
			 <tr><td>&nbsp;</td></tr>				
 			 </tbody> </table> 			  			 		   		    
			
			 <table class='fluid table table-striped table-condensed'  style='font-size: 16px; width:55%; margin:auto;'>
			 <thead>
			 <tr>
			 <th colspan='2' style='text-align:center; background:#286090; color:#fff;'>Top 5 - ". __('Tickets','dashboard')." ". __('by Technician','dashboard')."</th>						
			 </tr>
			 </thead>	

			 <tbody>";		
			
			while($row_tec = $DB->fetchAssoc($result_tec)) {
				 $content .= "<tr>
				 <td width='300'>".$row_tec['name']." ".$row_tec['sname']."</td>
				 <td width='200' align='right'>".$row_tec['conta']."</td>			
				 </tr> ";	
			}		

$content .= "	
			 <tr><td>&nbsp;</td></tr>				
		    </tbody> </table>
		   		    	
		   
			 <table class='fluid table table-striped table-condensed'  style='font-size: 16px; width:55%; margin:auto;'>
			 <thead>
			 <tr>
			 <th colspan='2' style='text-align:center; background:#286090; color:#fff;'>Top 5 - ". __('Tickets','dashboard')." ". __('by Requester','dashboard')."</th>						
			 </tr>
			 </thead>	
			 
			 <tbody>";	
			
			while($row = $DB->fetchAssoc($result_req)) {
				$content .= "<tr>
				 <td width='300'>".$row['name']." ".$row['sname']."</td>
				 <td width='200' align='right'>".$row['conta']."</td>			
				 </tr> ";	
			}		
													
$content .= "</tbody> </table> </page> ";		   		   											
			
		}							
	}		

require_once('../inc/html2pdf/html2pdf.class.php');

$filename = "summary_report_entity.pdf";

$html2pdf = new HTML2PDF('P', 'A4', 'en');
$html2pdf->writeHTML($content);

ob_end_clean();
$html2pdf->Output($filename,'D');		

//header("Location:".$filename);

		
?>	
</body>
</html>

