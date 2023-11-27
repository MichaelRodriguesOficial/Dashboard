<?php

if($data_ini == $data_fin) {
    $datas = "LIKE '".$data_ini."%'";	
} else {
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

$slaid = "slas_id_ttr";

$query2 = "
SELECT COUNT(glpi_tickets.id) AS tick, glpi_itilsolutions.solutiontypes_id AS tipo, glpi_tickets.$slaid AS id, glpi_slas.name
FROM glpi_tickets
JOIN glpi_itilsolutions ON glpi_tickets.id = glpi_itilsolutions.items_id
JOIN glpi_groups_tickets ON glpi_tickets.id = glpi_groups_tickets.tickets_id
JOIN glpi_slas ON glpi_tickets.$slaid = glpi_slas.id
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.date ".$datas."
".$entidade."
AND glpi_slas.type = 0
AND (glpi_tickets.status = 5 OR glpi_tickets.status = 6)
GROUP BY glpi_itilsolutions.solutiontypes_id, glpi_tickets.$slaid
ORDER BY tipo ASC, tick DESC
";


$result2 = $DB->query($query2) or die('erro');

$arr_grft2 = array();
while ($row_result = $DB->fetchAssoc($result2)) {
    $v_row_result = $row_result['tipo'];
    $arr_grft2[$v_row_result] = $row_result['tick'];			
} 
	
$grft2 = array_keys($arr_grft2);
$quantt2 = array_values($arr_grft2);
$conta = count($arr_grft2);

if ($conta == 1) {
    if (in_array(1, $grft2)) {		
        $grft2[array_search(1, $grft2)] = __('Presencial'); 
    }
	
    if (in_array(2, $grft2)) {		
        $grft2[array_search(2, $grft2)] = __('Remoto'); 
    }
	
    if (in_array(3, $grft2)) {		
        $grft2[array_search(3, $grft2)] = __('Terceiros'); 
    }
	
    if (in_array(0, $grft2)) {		
        $grft2[array_search(0, $grft2)] = __('Não selecionado'); 
    }
}

if ($conta > 1) {
    if (in_array(0, $grft2)) {
        $grft2[array_search(0, $grft2)] = __('Não selecionado'); 
    }
    
    if (in_array(1, $grft2)) {
        $grft2[array_search(1, $grft2)] = __('Presencial'); 
    }
    
    if (in_array(2, $grft2)) {
        $grft2[array_search(2, $grft2)] = __('Remoto'); 
    }
    
    if (in_array(3, $grft2)) {
        $grft2[array_search(3, $grft2)] = __('Terceiros'); 
    }
}

// ...

$fechados_remoto = 0;
$fechados_presencial = 0;
$fechados_terceiros = 0;
$fechados_nao_selecionado = 0;

foreach ($arr_grft2 as $categoria => $count) {
    if ($categoria == 2) {
        $fechados_remoto += $count;
    } elseif ($categoria == 1) {
        $fechados_presencial += $count;
    } elseif ($categoria == 3) {
        $fechados_terceiros += $count;
    } elseif ($categoria == 0) {
        $fechados_nao_selecionado += $count;
    }
}




echo "
<script type='text/javascript'>
    $(function () {		
        // Build the chart
        $('#graf_tipo_grupo_sltsr').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '".__('Tickets','dashboard')." ".__('by Type','dashboard')."'
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
    if ($quantt2[$i] != 0) {
        echo "{
            name: '".$grft2[$i]."',
            y: ".$quantt2[$i].",
            sliced: true,
            selected: true
        },";
    }
}

// Verificação para adicionar "Não selecionado" para quantidade zero
if (in_array(0, $quantt2)) {
    echo "{
        name: 'Não selecionado',
        y: 0,
        sliced: true,
        selected: true
    },";
}

echo "                ]
            }]
        });
    });
</script>";