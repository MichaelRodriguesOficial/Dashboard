<?php
include ("../../../../inc/includes.php");
include ("../../../../inc/config.php");
include "../inc/functions.php";

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

?>

<!DOCTYPE html>
<html>
<head>
    <title> <?php echo $entidade_user; ?> - Dashboard - Home</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	 <meta http-equiv="Pragma" content="public">
    <meta http-equiv="refresh" content="0; url=assets.php" />        
      	 
</head>
<body style='background-color: #FFF;'>
</body>
</html>