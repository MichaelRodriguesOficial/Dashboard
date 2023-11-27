<?php

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
include (GLPI_ROOT . "/inc/config.php");

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


?>

<html> 
<head>
<title> <?php echo $entidade_user; ?> - <?php echo __('Tickets Map','dashboard'); ?></title>

<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="content-language" content="en-us" />
<meta http-equiv="refresh" content= "180"/> 

<link rel="icon" href="../../../../pics/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="../../../../pics/favicon.ico" type="image/x-icon" />
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
<link href="../css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
<link href="../css/font-awesome.css" type="text/css" rel="stylesheet" />
<script src="../js/jquery.js" type="text/javascript" ></script>

<!--<script src="./js/markerclusterer.js" type="text/javascript" ></script>
<link href="css/google_api.css" rel="stylesheet" type="text/css" />  -->   

<script src="../js/bootstrap.min.js" type="text/javascript" ></script>  

<?php echo '<link rel="stylesheet" type="text/css" href="../css/style-'.$_SESSION['style'].'">';  ?> 

  <link rel="stylesheet" href="css/leaflet.css" />
  <script src="js/leaflet.js"></script>

	<link rel="stylesheet" href="css/MarkerCluster.css" />
   <link rel="stylesheet" href="css/MarkerCluster.Default.css" />
	<script src="js/leaflet.markercluster-src.js"></script>

	<link rel="stylesheet" href="css/leaflet-beautify-marker-icon.css">
	<script src="js/leaflet-beautify-marker-icon.js"></script>	

	<style type="text/css">
		html { margin-top: 3px;}
		a, a:visited, a:focus, a:hover { color: #0776cc;}	
		#map_canvas {
			margin-left: auto;
			margin-right: auto;
			float: none;
			margin-top: 25px;
			width: 93%;
			height: 100%;
		}
		.mycluster-green {
			width: 32px;
			height: 32px;
			line-height: 32px;
			background-image: url('images/0-32.png');
			text-align: center;		
		}
		
		.mycluster-red {
			width: 32px;
			height: 32px;
			line-height: 32px;
			background-image: url('images/3-32.png');
			text-align: center;		
		}
	</style>

</head>

<?php


//entities
/*if(isset($_SESSION['glpiID'])) {
	$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$_SESSION['glpiID']."";
	$result_e = $DB->query($sql_e);
	$sel_ent = $DB->result($result_e,0,'value');

	$entidade = "AND entities_id IN (".$sel_ent.")";
}
else {
	$entidade = '';
}*/

if(isset($_SESSION['glpiID'])) {
	
	$entities = $_SESSION['glpiactiveentities'];
	$ent = implode(",",$entities);
	
	if($ent != '') {
		$entidade = "AND entities_id IN (".$ent.")";
	}
	else {
		$entidade = "";
	}
}	

$status = "";
$status_open = "('1','2','3','4','13','14')";
$status_close = "('5','6')";	
$status_all = "('1','2','3','4','5','6','13','14')";

if(isset($_GET['stat_option'])) {
	
	if($_GET['stat_option'] == "open") {		
		$status = $status_open;
		$stat = "open";
		$state = __('Opened','dashboard');
	}
	if($_GET['stat_option'] == "closed") {
		$status = $status_close;
		$stat = "closed";
		$state = __('Closed','dashboard');
	}
	if($_GET['stat_option'] == "all") {
		$status = $status_all;
		$stat = "all";	
		$state = __('Overall','dashboard');
	}
	if($_GET['stat_option'] == "") {		
		$status = $status_open;
		$stat = "open";
		$state = __('Opened','dashboard');
	}
}

else {
		$status = $status_open;
		$stat = "open";
		$state = __('Opened','dashboard');
	}

if(isset($_GET['period_option'])) {

	$post_date = $_GET['period_option'];
	$period = $_GET['period_option'];
	
	switch($post_date) {
	
		case ("today") :
		   $data_ini2 = date('Y-m-d');
		   $data_fin2 = date('Y-m-d');														   
		   $sel_date = "AND gt.date LIKE '".$data_ini2."%'";	
		break;
		case ("week") :
		   $data_ini2 = date('Y-m-d', strtotime('-1 week'));
		   $data_fin2 = date('Y-m-d');
			$sel_date = "AND gt.date BETWEEN '" . $data_ini2 ." 00:00:00' AND '".$data_fin2." 23:59:59'";
		break;
		case ("month") :
		   $data_ini2 = date('Y-m-d', strtotime('-1 month'));
		   $data_fin2 = date('Y-m-d');					
			$sel_date = "AND gt.date BETWEEN '" . $data_ini2 ." 00:00:00' AND '".$data_fin2." 23:59:59'";
		break;				
		case ("all") :
		   $data_ini2 = date('Y-m-d', strtotime('-1 year'));
		   $data_fin2 = date('Y-m-d');								
			$sel_date = "";
		break;	
		default:
			$sel_date = "";
	} 
}

else {
	$period = "all";
	$data_ini2 = date('Y-m-d', strtotime('-1 year'));
   $data_fin2 = date('Y-m-d');								
	$sel_date = "";
}

$query_cloc = "SELECT COUNT(id) AS conta FROM `glpi_locations` WHERE latitude IS NOT NULL";

$res_cloc = $DB->query($query_cloc);
$cloc = $DB->result($res_cloc,0,'conta');

$conta_loc = count($cloc);

$query_coo = "	SELECT id
	FROM glpi_locations
	WHERE latitude IS NOT NULL	
	".$entidade."
	ORDER BY id ASC ";
	
$res_coo = $DB->query($query_coo);

?>

<!-- maps - by Stevenes Donato -->
<script type="text/javascript">	 
                
var locations = 
<?php

$locations = [];

while ($row_id = $DB->fetchAssoc($res_coo)) {
	
	// get location info
	$query_loc = "
	SELECT id, entities_id, name AS location, latitude AS lat, longitude AS lng
	FROM glpi_locations
	WHERE id = ".$row_id['id']."		
	GROUP BY id
	ORDER BY id DESC";	
	
	$result = $DB->query($query_loc) or die ("error query_loc");
	$row = $DB->fetchAssoc($result);
	
	// get location tickets
	$query_cham = "
	SELECT locations_id, COUNT(id) AS conta
	FROM glpi_tickets
	WHERE locations_id = ".$row_id['id']."
	AND status IN ".$status."
	".$sel_date."	
	AND is_deleted = 0
	GROUP BY locations_id 
	ORDER BY locations_id DESC";	
	
	$result_cham = $DB->query($query_cham) or die ("error query_cham");
	$row_cham = $DB->fetchAssoc($result_cham); 
 
  $id = $row['entities_id'];
  $title = $row['location'];        	
  $url = $CFG_GLPI['root_doc']."/front/ticket.php?is_deleted=0&criteria[0][field]=12&criteria[0][searchtype]=equals&criteria[0][value]=notold&criteria[1][link]=AND&criteria[1][field]=83&criteria[1][searchtype]=equals&criteria[1][value]=".$row_id['id']."&itemtype=Ticket&start=0";      	
  $host = "<a href=". $url ." target=_blank >" . $title . "</a>";  
  //$status = $row['conta'];  
  $local = $row['location']; 
  $lat = $row['lat']; 
  $lng = $row['lng']; 
  $quant = $row_cham['conta'];  

if ($quant == 0) {
	$quant = 0;
	//$color = $icon_green.$quant."";
	$color = "";
	$num_up = 1;	
	$num_down = 0;
	
}

else {
	//$color = $icon_red.$quant."";
	$color = "";
	$num_up = 0;	
	$num_down = 1;
}

$locations[] = [
        $title,
        $lat,
        $lng,
        $local,
        $color,
        $host,
        $id,
        $quant,
        $num_up,
        $num_down,
        $url
    ];
}

echo json_encode($locations);
?>
;
    
function initialize() {
   
	latlng = L.latLng(-9.95126,-63.9059);
	var map = L.map('map_canvas').setView([-9.95126,-63.9059], 13);
	    
		var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);
		
		for(var i = 0; i < locations.length; i++) {
			var markers = new L.MarkerClusterGroup({
        		iconCreateFunction: function(cl) {
            var layer = cl.getAllChildMarkers()[0].l;
            var cor = layer === 1 ? 'green' : 'red';            
            return L.divIcon({ html: '<b>' + cl.getChildCount() + '</b>', className: 'mycluster-' + cor, iconSize: L.point(32, 32) });
        	},        			
			maxClusterRadius: 50, spiderfyOnMaxZoom: false, showCoverageOnHover: true, zoomToBoundsOnClick: false 
			});
		}		
		
		//var markers = L.markerClusterGroup();

	  //marcadores individuais			
		var arr_markers = [];
		
		for (var i = 0; i < locations.length; i++) {
			
			var a = locations[i];

			var cor = a[8] === 1 ? '#43B53C' : '#FF0000';
			//var tipo = a[8] === 1 ? 'green' : 'red';
										 	 		 	 			
			var options = { isAlphaNumericIcon: true, text: a[7], iconShape: 'marker', borderColor: cor, textColor: cor};
		   var marker = L.marker([a[1], a[2]], {icon: L.BeautifyIcon.icon(options), draggable: true}, {title: a[3]});
		   marker.l = a[8];		     

			marker.bindPopup(a[5]);
			markers.addLayer(marker);
			
			//array to center
			arr_markers.push([a[1], a[2]]);
		}

		map.addLayer(markers);
		
		//center map		
		var bounds = L.latLngBounds(arr_markers);
		map.fitBounds(bounds);

}

</script> 

<script type="text/javascript" >
	$(document).ready(function(){
		var a = document.getElementById('stat_option').value;	
			
		if ( a === 'open')
		  { $( ".btn0" ).addClass( "active" ); }
		  
		else if ( a === 'closed')
		  {  $( ".btn1" ).addClass( "active" ); }
		
		else if ( a === 'all')
		  {  $( ".btn2" ).addClass( "active" ); }
		  
		else 
		  {  $( ".btn0" ).addClass( "active" ); }      
	
	});
	
	
	$(document).ready(function(){
		var b = document.getElementById('period_option').value;
			
		if ( b === 'today')
		  { $( ".btna" ).addClass( "active" ); }
		  
		else if ( b === 'week')
		  {  $( ".btnb" ).addClass( "active" ); }
		
		else if ( b === 'month')
		  {  $( ".btnc" ).addClass( "active" ); }
		  
		else if ( b === 'all')
		  {  $( ".btnd" ).addClass( "active" ); }
		  
		else 
		  {  $( ".btnd" ).addClass( "active" ); }      
		
	});
</script>

<script type="text/javascript">

		function ChecaEstado() {
		
		//localStorage.clear();	
		var estado = localStorage.getItem('status');	
		var head = document.getElementById('head-map').style.display;  		
		
		if (estado == 0 ) {
			document.getElementById('head-map').style.display = 'none';
			document.getElementById('head-map2').style.display = 'block';
	      document.getElementById('buttons').style.display = 'none';
	      document.getElementById('map_canvas').style.height = '100%';
	      //document.getElementById('charts').style.marginTop = '20px';
			document.getElementById('map_canvas').style.marginTop = '5px';

			localStorage.setItem('status',0);			
		}
		if (estado == 1 ) {
			document.getElementById('head-map').style.display = 'block';
			document.getElementById('head-map2').style.display = 'none';
	      document.getElementById('buttons').style.display = 'block';
	      document.getElementById('map_canvas').style.height = '90%';
	      //document.getElementById('charts').style.marginTop = '20px';
	      document.getElementById('map_canvas').style.marginTop = '15px';
	      
			localStorage.setItem('status',1);			
		}
	}


	function MudaEstado() {
		
		//localStorage.clear();		
	    var head = document.getElementById('head-map').style.display;
	    var buttons = document.getElementById('buttons').style.display;
	    var estado = localStorage.getItem('status');	    
	    	    	    	    	    	    
	    if(head == "block" && buttons == "block") {	       			    	
	        document.getElementById('head-map').style.display = 'none';
	        document.getElementById('head-map2').style.display = 'block';
	        document.getElementById('buttons').style.display = 'none';
	        document.getElementById('map_canvas').style.height = '100%';
	        //document.getElementById('charts').style.marginTop = '15px';	   
	        document.getElementById('map_canvas').style.marginTop = '5px';	
	              		      
	        localStorage.setItem('status',0);	        
	     }	    	    
	    	    	    			      		        	    		    
		 if(head == "none" && buttons == "none") {		 	
	        document.getElementById('head-map').style.display = 'block';
	        document.getElementById('head-map2').style.display = 'none';
	        document.getElementById('buttons').style.display = 'block';
	        document.getElementById('map_canvas').style.height = '90%';
	        document.getElementById('map_canvas').style.marginTop = '15px';	
	                       
	        localStorage.setItem('status',1);	        
	     }	     	    	     
	}
</script>

<body onload="initialize(); ChecaEstado();" style="background:#e5e5e5;">

	<div id='container-fluid' style="margin: 0px 0px 0px 2%;" > 		

		<button id="hidetop" onclick="MudaEstado();" class="btn btn-primary btn-sm">Show/Hide</button>

		<div id="head-map" class="row-fluid" style="z-index:-999; display:block;">
			<div id="titulo_map"><?php echo __('Tickets','dashboard')." ". __('by Location','dashboard'); ?></div>	
		</div>	
		
		<div id="head-map2" class="col-md-12 col-sm-12 fluid" style="display: none; margin-top:15px;">
			<div id="titulo2"><h3><?php echo __('Tickets','dashboard')." ". __('by Location','dashboard'); ?></h3></div>				
		</div>
		
			<div id="charts" class="row-fluid chart" > 		      
				<div id="buttons" class="btn-toolbar" role="toolbar" class='center' style="margin-left:1%; margin-right:auto; display:block;" >				          
				    <div class="btn-group" data-toggle-name="radius_options" data-toggle="buttons-radio">		            
				        <button type="button" value="open" 	data-toggle="button" name="stat" class="btn btn-default btn0" onclick="document.getElementById('stat_option').value='open'; mapa();"><?php echo __('Opened','dashboard'); ?></button>
				        <button type="button" value="closed" data-toggle="button" name="stat" class="btn btn-default btn1" onclick="document.getElementById('stat_option').value='closed'; mapa();" ><?php echo __('Closed'); ?></button>
				        <button type="button" value="all" 	data-toggle="button" name="stat" class="btn btn-default btn2" onclick="document.getElementById('stat_option').value='all'; mapa();" ><?php echo __('All','dashboard'); ?></button>        
				    </div>
				    
				    <input type="hidden" id="stat_option" name="stat_option" value="<?php echo $stat; ?>">
				    
				    <div class="btn-group" data-toggle-name="sort_options" data-toggle="buttons-radio" style="margin-left: 25px;;">
				        <button type="button" value="today" 	data-toggle="button" name="period" class="btn btn-default btna" onclick="document.getElementById('period_option').value='today'; mapa();"><?php echo __('Today'); ?></button>
				        <button type="button" value="week" 	data-toggle="button" name="period" class="btn btn-default btnb" onclick="document.getElementById('period_option').value='week'; mapa();"><?php echo __('Last 7 days','dashboard'); ?></button>
				        <button type="button" value="month"  data-toggle="button" name="period" class="btn btn-default btnc" onclick="document.getElementById('period_option').value='month'; mapa();"><?php echo __('Last 30 days','dashboard'); ?></button>
				        <button type="button" value="all" 	data-toggle="button" name="period" class="btn btn-default btnd" onclick="document.getElementById('period_option').value='all'; mapa();"><?php echo __('All', 'dashboard'); ?></button>
				    </div>
				    
				    <input type="hidden" id="period_option" name="period_option" value="<?php echo $period; ?>">    
				</div>	 	       

				<script type="text/javascript">
				function mapa() {
					var stat = document.getElementById('stat_option').value;
					var period = document.getElementById('period_option').value;
					location.href='map_loc.php?period_option=' + period + '&stat_option=' + stat;
				}
				</script>

				<script type="text/javascript" >
				$(function () {
				    $('div.btn-group[data-toggle-name]').each(function () {
				        var group = $(this);
				        var form = group.parents('form').eq(0);
				        var name = group.attr('data-toggle-name');
				        var hidden = $('input[name="' + name + '"]', form);
				        $('button', group).each(function () {
				            var button = $(this);
				            button.on('click',  function () {
				                hidden.val($(this).val());
				               // alert(hidden.val());
				            });
				            if (button.val() == hidden.val()) {
				                button.addClass('active');                
				            }
				        });
				    });
				});				
				</script> 			
				<div id="map_canvas"></div>
			</div>
	</div>
</body>
</html>
