<?php

include ("../../../inc/includes.php");
include ("../../../inc/config.php");
global $DB;

Session::checkLoginUser();

$userID = $_SESSION['glpiID'];

# entity in index
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$userID."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');

if ($sel_ent == '') {
    $entities = $_SESSION['glpiactiveentities'];
    $sel_ent = implode(",", $entities);
    $query = "SELECT name FROM glpi_entities WHERE id IN (".$sel_ent.")";
    $result = $DB->query($query);
    $entidade_user = $DB->result($result, 0, 'name');
} 

else {
    $entidade_user = "GLPi";
}


$query_lay = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'layout' AND users_id = ".$_SESSION['glpiID']." ";																
					$result_lay = $DB->query($query_lay);					
					$layout = $DB->result($result_lay,0,'value');
					
//redirect to index
if($layout == '0')
	{
		//top menu
		$redir = '<meta http-equiv="refresh" content="0; url=index2.php" />';
	}

if($layout == 1 || $layout == '' )
	{		
		// sidebar
		$redir = '<meta http-equiv="refresh" content="0; url=index1.php" />';
	}						
?>

<!DOCTYPE html>
<html>
<head>
    <title> <?php echo $entidade_user; ?> - Dashboard - Home</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	 <meta http-equiv="Pragma" content="public">
    <?php echo $redir; ?>        
      	 
</head>
<body style='background-color: #FFF;'>
</body>
</html>
