<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

$nb_y = 0;
$year_p1 = date('Y');
$year_p2 = date('Y');


$sql_an = "SELECT DISTINCT YEAR(date_first) as year FROM ".TABLE_IMPORT." UNION SELECT YEAR(date_end) FROM ".TABLE_IMPORT." ORDER BY year ASC";

$annee_query = tep_db_query($sql_link,$sql_an);
while ($annee_t = tep_db_fetch_array($annee_query))
{
	$tab_annee[] = $annee_t['year'];
	if($nb_y == 1){$year_p1 = $annee_t['year'];}
	$year_p2 = $annee_t['year'];
	
	$nb_y++;
}

$date_p1 = $year_p1.'-01-01';
$date_p2 = $year_p2.'-12-31';


$min_limni = 0;
$max_limni = 0;
$nb_data = 0;
$year_encours = 0;

$data_limni = getDatesBetween($date_p1, $date_p2,4);
$groupby = 'date_mesure';


$sql_data = "SELECT DISTINCT date_mesure, HOUR(heure_mesure) as heure_m, date_heure_mesure, AVG(qte) as hauteur, AVG(qte_lacune) as hauteur_lac, SUM(lacune) as lacune_day FROM ".TABLE_DATA_LIMNI. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_p1." 00:00:00' AND date_heure_mesure<='".$date_p2." 23:59:00' GROUP BY ".$groupby;
$data_query = tep_db_query($sql_link,$sql_data);	
while($data = tep_db_fetch_array($data_query))
{
	$date_mesure = $data['date_mesure'];
	$heure_m = $data['heure_m'];
	if($heure_m<10){$heure_m='0'.$heure_m;}
	$date_heure_mesure = $data['date_mesure'].' '.$heure_m.':00:00';
	
	$year_temp = explode('-',$date_mesure); 
	$year_mesure = $year_temp[0];
	
	//if($data['hauteur']>0){}
	
	$data_limni[$date_mesure] = array('year_m' => $year_mesure,'heure_m' => '00:00:00','hauteur' => $data['hauteur'],'hauteur_lac' => $data['hauteur_lac'],'lacune' => $data['lacune_day']);
	
	if($data['hauteur'] > $max_limni){$max_limni = $data['hauteur_lac'];}
	if($nb_data==0){$min_limni=$max_limni;}
	
	if($data['hauteur'] < $min_limni){$min_limni = $data['hauteur_lac'];}
	
	$nb_data++;
}


$string_graph_all_a = '';
$hauteur = 0;
$hauteur_lac = 0;
$last_hauteur = 0;
$last_tot_data = 0;
$nb_days = 0;
$means_data = 0;

if($nb_data>0)
{
	$i=0;
	
	foreach($data_limni as $cle => $element)
	{
		
		$indice = ( ($i+1)*100 ) / sizeof($data_limni);
		progression($indice);	
		$i++;
		
		if($year_encours == 0)
		{
			$year_encours = $element['year_m'];
		
			${'string_graph_'.$year_encours.'_a'} = '';
			
			$nb_days = 1;
		}
		
		if($year_encours!=$element['year_m'])
		{
			$means_data = round($last_tot_data/$nb_days,0);
			$year_tab[$year_encours] = array('means' => $means_data,'nb_days' => $nb_days); 
			
			if($nb_days!=0 && $last_tot_data!=0)
			{
				$year_time = strtotime($year_encours.'-01-01')*1000;
				
				$string_graph_all_a .= "[".$year_time.",".$means_data."],";
			}
			
			$year_encours = $element['year_m'];
			
			${'string_graph_'.$year_encours.'_a'} = '';
			
			$last_tot_data = 0;
			$nb_days = 0;
			
		}
		
		$hauteur = round($element['hauteur_lac'],1);
		//if($element['hauteur_lac']>0){$hauteur = round($element['hauteur_lac'],1);}
		if($element['lacune']>0){$hauteur = 0;}
		
		$last_tot_data += $hauteur;
		
		
		$cle_time = strtotime($cle)*1000;
		
		${'string_graph_'.$year_encours.'_a'} .= "[".$cle_time.",".$hauteur."],";
		
		
		$nb_days++;
	}
	
	$means_data = round($last_tot_data/$nb_days,0);
	$year_tab[$year_encours] = array('means' => $means_data,'nb_days' => $nb_days);
	
	$year_time = strtotime($year_encours.'-01-01')*1000;			
	$string_graph_all_a .= "[".$year_time."-01-01,".$means_data."]";
	
	/* A METTRE APRES
	${'string_graph_'.$year_encours.'_a'} = substr(${'string_graph_'.$year_encours.'_a'}, 0, -1);
	${'string_graph_'.$year_encours.'_b'} = substr(${'string_graph_'.$year_encours.'_b'}, 0, -1);
	${'string_graph_'.$year_encours.'_c_a'} = substr(${'string_graph_'.$year_encours.'_c_a'}, 0, -1);
	${'string_graph_'.$year_encours.'_c_b'} = substr(${'string_graph_'.$year_encours.'_c_b'}, 0, -1);
	*/
	
}

?>		

<script type="text/javascript" src="include/javascript/flotr/lib/prototype.js"></script>
<!--[if IE]>
	<script type="text/javascript" src="include/javascript/flotr/lib/excanvas.js"></script>
	<script type="text/javascript" src="include/javascript/flotr/lib/base64.js"></script>
<![endif]-->
<script type="text/javascript" src="include/javascript/flotr/lib/canvas2image.js"></script>
<script type="text/javascript" src="include/javascript/flotr/lib/canvastext.js"></script>
<script type="text/javascript" src="include/javascript/flotr/flotr.js"></script>
		
				

<?php

echo "<form name='stats_select' action='stats_result.php' method='post' enctype='multipart/form-data'>";


echo "<h1><span>";
				
	echo "<span>".htmlaccent('Chroniques des hauteurs d\'eau journalières')."</span>";
	if($nb_data > 0 && !$print)
	{	
		echo button_print('print_stats_years.php?ty=limni&print=ok&bs=1&il='.$select_region.'&st='.$select_station.'&eq=2','Tableau');
	}
echo "</h1>"; 
 
echo "<div id='box_graph_all'>";

	echo "<div id='box_graph' class='lgt'>";
		require(DIR_WS_STATS . 'stats_box_info.php');
	echo "</div>";
	
	echo "<div id='box_graph' class='lgt_r' >";
	
		echo "<h8>".htmlaccent('Synthèse pluriannuelle')."</h8>";
		echo "<div id='container_all' style='float:left;width:98%;height:200px;margin:10px 1%;'></div>";
	
	echo "</div>";
	
	//echo "<img src='".DIR_WS_IMG."header_logo.png' style='margin-top:90px;' >";	
	
	
		
echo "<hr>";		
echo "</div>";	
	

echo "<div id='box_graph_all'>";

	if($nb_data > 0)
	{
		//echo "<div id='box_graph'  class='gd'>";
			
			//echo "<h8>".htmlaccent('Précipitations '.$titre_by.' (hauteur de pluie en mm)')."</h8>";
							
			
			
				//echo "<div id='graph_onglet'>";
						$col=0;
						
						$year_tab_inverse = array_reverse($year_tab,true);
						foreach($year_tab_inverse as $cle => $element)
						{
							if($element['means']>0)
							{
								$col_style='lgt_r';
								if(fmod($col,2)==0){$col_style='lgt';}
								
								echo "<div id='box_graph' class='".$col_style."' style='margin-right:1%;'>";
								
									echo "<h7>";
									
										echo htmlaccent('Année '.$cle)." - ";
										echo "<span>";
											echo htmlaccent('Hauteur d\'eau moyenne : '.$element['means'].' cm');//." - ".htmlaccent($element['nb_days'].' jours d\'enregistrement');
										echo "</span>";
									
									echo "</h7>";
									
									echo "<div id='container_".$cle."' style='float:left;width:95%;height:300px;margin:15px 1%;'></div>";
			
								echo "</div>";
								
								$col++;
							}
						}
					
					//echo "</div>";
				
			//	echo "</div>";	
			
		//echo "</div>";	
		
		
	}
	else
	{
		echo "<div id='box_graph'>";
			
			echo "<h6>".htmlaccent('Aucune donnée n\'a été trouvée pour cette station, pour cette période')."</h6>";
							
			
		echo "</div>";
	}
	
echo "</div>";

echo "</form >\n";



?>


<script id="source" type="text/javascript">

function getV(nl) 
{
	var v = null;
	$A(nl).each(function(e) {
	  if (e.checked) {
	    v = e.value;
	      return;
	    }
	});
	return v;
}



var f;
			
document.observe('dom:loaded', 

function()
{
	// OPTIONS			
	var options0 = {
					 'shadowSize': 0, 
					 'HtmlText': false, 
					 'fontSize': 12,
					 'legend': {'show': false}, 
					 'xaxis': {'title': 'Année','showLabels': true, 'mode': 'time', 'timeFormat': '%y','noTicks': 10},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0, 'min':0, 'max': <?php echo $max_limni; ?>}, 
					 'lines': {'show': true, 'lineWidth': 0.5, 'fill': true, 'fillOpacity': 0.2}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'tickColor': '#cccccc', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 0}, 
					 'crosshair': {'mode': 'x', 'color': '#FF0000', 'hideCursor': true}, 
					 'mouse': {'track': true, 'trackAll': true, 'position': 'nw', 'relative': false, 'margin': 3, 'trackDecimals': 0, 'trackFormatter':function dateTracker(obj){
																																									  var dateToDisplay = new Date(parseInt(obj.x)); 
																																									  var fullYear = dateToDisplay.getFullYear();
																																									  var month = dateToDisplay.getMonth()+1;
																																									  if(month<10){month='0'+month;}
																																									  var date = dateToDisplay.getDate();
																																									  if(date<10){date='0'+date;}
																																									  var heures = dateToDisplay.getHours();
																																									  if(heures<10){heures='0'+heures;}
																																									  var minutes = dateToDisplay.getMinutes();
																																									  if(minutes<10){minutes='0'+minutes;}
																																									  var sec = dateToDisplay.getSeconds();
																																									  if(sec<10){sec='0'+sec;}
																																									  return 'Hauteur d\'eau : ' + obj.y + ' cm<br>Année : '+fullYear; 
																																								  } 
					 			}
				  
					}
	
	
	var options1 = {
					 'shadowSize': 0, 
					 'HtmlText': false, 
					 'fontSize': 12,
					 'legend': {'show': false}, 
					 'xaxis': {'showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','noTicks': 5},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0, 'min':0, 'max': <?php echo $max_limni; ?>, 'min': 0}, 
					 'lines': {'show': true, 'lineWidth': 0.5, 'fill': true, 'fillOpacity': 0.2}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'tickColor': '#cccccc', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 0}, 
					 <?php
					 if(!$print)
					 {
						 echo "
						 'crosshair': {'mode': 'x', 'color': '#FF0000', 'hideCursor': true}, 
						 'mouse': {'track': true, 'trackAll': true, 'position': 'nw', 'relative': true, 'margin': 20, 'trackDecimals': 0, 'trackFormatter':function dateTracker(obj){
																																										  var dateToDisplay = new Date(parseInt(obj.x)); 
																																										  var fullYear = dateToDisplay.getFullYear();
																																										  var month = dateToDisplay.getMonth()+1;
																																										  if(month<10){month='0'+month;}
																																										  var date = dateToDisplay.getDate();
																																										  if(date<10){date='0'+date;}
																																										  var heures = dateToDisplay.getHours();
																																										  if(heures<10){heures='0'+heures;}
																																										  var minutes = dateToDisplay.getMinutes();
																																										  if(minutes<10){minutes='0'+minutes;}
																																										  var sec = dateToDisplay.getSeconds();
																																										  if(sec<10){sec='0'+sec;}
																																										  return 'Hauteur d\'eau : ' + obj.y + ' cm<br>Date: '+date+'-'+month+'-'+fullYear; 
																																									  } 
					 			}
					 	";
					 }
					 ?>
				  
					}
					
	
	// DATA
	<?php 
	foreach($year_tab as $cle => $element)						
	{
		if($element['means']>0)
		{
			echo "var d".$cle."a = [".${'string_graph_'.$cle.'_a'}."];";
		
			echo "f = Flotr.draw($('container_".$cle."'),[{data:d".$cle."a, color:'#007300'}],Object.extend(Object.clone(options1)));";
		}
	}
	
	echo "var dall_a = [".$string_graph_all_a."];";
	echo "fall = Flotr.draw($('container_all'),[{data:dall_a, color:'#007300'}],Object.extend(Object.clone(options0)));";
			
	
	
	?>	
	
	
	
});



  
</script>
