<?php

if($data_ini == $data_fin) {
    $datas = "LIKE '".$data_ini."%'";	
} else {
    $datas = "BETWEEN '".$data_ini." 00:00:00' AND '".$data_fin." 23:59:59'";	
}

//tickets by type
$query2 = "
SELECT COUNT(glpi_tickets.id) AS tick, glpi_itilsolutions.solutiontypes_id AS tipo
FROM glpi_tickets
JOIN glpi_itilsolutions ON glpi_tickets.id = glpi_itilsolutions.items_id
WHERE glpi_tickets.is_deleted = '0'
AND glpi_tickets.date ".$datas."
AND glpi_tickets.type = ".$id_tip."
GROUP BY glpi_itilsolutions.solutiontypes_id
ORDER BY tipo ASC    
";


$result2 = $DB->query($query2) or die('erro');

$arr_grft2 = array();
while ($row_result = $DB->fetchAssoc($result2)) {
    $v_row_result = $row_result['tipo'];
    $arr_grft2[$v_row_result] = $row_result['tick'];			
} 

$grft2 = array();
$quantt2 = array();

if (isset($arr_grft2[1])) {
    $grft2[] = __('Presencial'); 
    $quantt2[] = $arr_grft2[1];
}

if (isset($arr_grft2[2])) {
    $grft2[] = __('Remoto'); 
    $quantt2[] = $arr_grft2[2];
}

if (isset($arr_grft2[3])) {
    $grft2[] = __('Terceiros'); 
    $quantt2[] = $arr_grft2[3];
}

$conta = count($grft2);

// ...

echo "
<script type='text/javascript'>
    $(function () {		
        // Build the chart
        $('#graf_tipo_').highcharts({
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

for ($i = 0; $i < $conta; $i++) {
    echo "{
        name: '".$grft2[$i]."',
        y: ".$quantt2[$i].",
        sliced: true,
        selected: true
    },";
}

echo "                ]
            }]
        });
    });
</script>";
