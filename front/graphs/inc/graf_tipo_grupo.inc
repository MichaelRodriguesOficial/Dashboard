<?php

if($data_ini == $data_fin) {
    $datas = "LIKE '".$data_ini."%'";	
} else {
    $datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";	
}

//tickets by type
$query2 = "
SELECT COUNT(glpi_tickets.id) AS tick, glpi_itilsolutions.solutiontypes_id AS tipo
FROM glpi_tickets_users, glpi_tickets, glpi_itilsolutions
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.date ".$datas."
AND glpi_tickets_users.users_id = ".$id_tec."
AND glpi_tickets_users.type = 2
AND glpi_itilsolutions.users_id = glpi_tickets_users.users_id
AND glpi_tickets_users.tickets_id = glpi_tickets.id
AND glpi_tickets.id = glpi_itilsolutions.items_id
GROUP BY glpi_itilsolutions.solutiontypes_id
ORDER BY tipo  ASC    
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

if($conta == 1) {
    if($grft2[0] == 1) {		
        $grft2[0] = __('Presencial'); 
    }
	
    if($grft2[0] == 2) {		
        $grft2[0] = __('Remoto'); 
    }
	
	if($grft2[0] == 3) {		
        $grft2[0] = __('Terceiros'); 
    }
	
	if($grft2[0] == 0) {		
        $grft2[0] = __('Não selecionado'); 
    }
}

if($conta > 1) {
    $grft2[0] = __('Não selecionado'); 
    $grft2[1] = __('Presencial'); 
    $grft2[2] = __('Remoto');
	$grft2[3] = __('Terceiros');
}

echo "
<script type='text/javascript'>
    $(function () {		
        // Build the chart
        $('#graf_tipo_grupo').highcharts({
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