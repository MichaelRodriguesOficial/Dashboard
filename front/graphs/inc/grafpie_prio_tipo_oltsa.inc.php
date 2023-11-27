
<?php

if($data_ini == $data_fin) {
$datas = "LIKE '".$data_ini."%'";	
}	

else {
$datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";	
}

#entity
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$_SESSION['glpiID']."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');

if($sel_ent == '' || $sel_ent == -1) {
	//get user entities
	$entities = $_SESSION['glpiactiveentities'];
	$ent = implode(",",$entities);

	$entidade = "AND glpi_tickets.entities_id IN (".$ent.")";
}
else {
	$entidade = "AND glpi_tickets.entities_id IN (".$sel_ent.")";
}

$olaid = "olas_id_tto";	

$query2 = "
SELECT COUNT(glpi_tickets.id) as tick, (glpi_tickets.olas_id_tto) AS total, glpi_tickets.".$olaid." AS id, glpi_olas.name, glpi_tickets.priority AS prio
FROM glpi_tickets, glpi_olas
WHERE glpi_tickets.".$olaid." = glpi_olas.id
AND glpi_tickets.is_deleted = '0'
AND glpi_olas.type = 1
AND glpi_tickets.date ".$datas."
".$entidade."
GROUP BY prio
ORDER BY tick DESC ";

		
$result2 = $DB->query($query2) or die('erro');

$arr_grf2 = array();
while ($row_result = $DB->fetchAssoc($result2))		
	{ 
	
		$priority = $row_result['prio'];
		
		if($priority == 1) {
			$prio_name = _x('priority', 'Very low'); }
		
		if($priority == 2) {
			$prio_name = _x('priority', 'Low'); }
			
		if($priority == 3) {
			$prio_name = _x('priority', 'Medium'); } 		
			
		if($priority == 4) {	
			$prio_name = _x('priority', 'High'); }
			
		if($priority == 5) {
			$prio_name = _x('priority', 'Very high'); } 	
			
		if($priority == 6) {
			$prio_name = _x('priority', 'Major'); } 	
	
		$v_row_result = $prio_name;
		$arr_grf2[$v_row_result] = $row_result['tick'];			
	} 
	
$grf2 = array_keys($arr_grf2);
$quant2 = array_values($arr_grf2);

$conta = count($arr_grf2);
	

echo "
<script type='text/javascript'>

$(function () {		
	// Build the chart
	$('#graf_prio_oltsa').highcharts({
		chart: {
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false
		},
		title: {
			text: '".__('Tickets by Priority','dashboard')."'
		},
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				size: '85%',
				dataLabels: {
					format: '{point.y} - ( {point.percentage:.1f}% )',
					style: {
						color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
					},
					connectorColor: 'black'
				},
				showInLegend: true
			}
		},
		series: [{
			type: 'pie',
			name: '".__('Tickets','dashboard')."',
			data: [";

for($i = 0; $i < $conta; $i++) {
	echo "{ name: '" . $grf2[$i] . "', y: $quant2[$i], sliced: true, selected: true },";
}

echo "]
		}]
	});
});

</script>";
?>
