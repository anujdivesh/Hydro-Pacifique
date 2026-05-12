<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

$nb_data=0;
$nb_day=0;
$nb_y=0;

$cumul_pluvio=0;
$cumul_pluvio_lac=0;

$data_raw=array();

$year_p1 = date('Y');
$year_p2 = date('Y');

$interval=86400; // 86400 secondes = 1 jour

$sql_an = "SELECT DISTINCT YEAR(date_first) as year FROM ".TABLE_IMPORT." WHERE id_station=".$select_station."  UNION SELECT YEAR(date_end) FROM ".TABLE_IMPORT." WHERE id_station=".$select_station." ORDER BY year ASC";
$annee_query = tep_db_query($sql_link,$sql_an);
while ($annee_t = tep_db_fetch_array($annee_query))
{
	$tab_annee[] = $annee_t['year'];
	if($nb_y == 0){$year_p1 = $annee_t['year'];}
	$year_p2 = $annee_t['year'];
	
	$nb_y++;
}

$date_p1 = $year_p1.'-01-01';
$date_p2 = $year_p2.'-12-31';

//-----------------------------------------------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------------------------------

// Variables de date
$date_heure_p1 = $date_p1." 00:00:00";
$date_heure_p2 = $date_p2." 23:59:00";

$datetime_p1 = strtotime($date_heure_p1);
$datetime_p2 = strtotime($date_heure_p2);

$nb_data_raw=0;

// Nombre de données
$nb_data = ($datetime_p2 - $datetime_p1) / $interval;
/*
$sql_data_count = "SELECT DISTINCT count(*) as count_data FROM ".TABLE_DATA_PLUVIO. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_heure_p1."' AND date_heure_mesure<='".$date_heure_p2."'";
$data_count_query = tep_db_query($sql_link,$sql_data_count);	
$data_count = tep_db_fetch_array($data_count_query);
$nb_data = $data_count['count_data'];
*/

$groupby = 'date_mesure';
// Données Brutes
//$sql_data = "SELECT DISTINCT date_mesure, heure_mesure, date_heure_mesure, qte, qte_lacune, lacune FROM ".TABLE_DATA_PLUVIO." WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_heure_p1."' AND date_heure_mesure<='".$date_heure_p2."' ORDER BY date_heure_mesure";
$sql_data = "SELECT DISTINCT date_mesure, heure_mesure, date_heure_mesure, SUM(qte) as data_plu, SUM(qte_lacune) as data_plu_lac, SUM(lacune) as plu_lac FROM ".TABLE_DATA_PLUVIO." WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_heure_p1."' AND date_heure_mesure<='".$date_heure_p2."' GROUP BY ".$groupby;
$data_query = tep_db_query($sql_link,$sql_data);	

while($data = tep_db_fetch_array($data_query))
{		
	$indice = ( ($nb_data_raw+1)*100 ) / $nb_data;
	progression($indice);	
	$nb_data_raw++;
	
	$date_mesure = $data['date_mesure'];
	$heure_mesure = $data['heure_mesure'];
	$date_index = $data['date_heure_mesure'];
		
	$qte = $data['data_plu'];
	$qte_lacune = $data['data_plu_lac'];
	$lacune = $data['plu_lac'];
	
	$data_raw[] = array('date_index' => $date_index,'qte' => $qte,'qte_lacune' => $qte_lacune,'lacune'=>$lacune);		
}



//-----------------------------------------------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------------------------------
// Algorithme de traitement de données

// Contrôle données dans $data_raw

if($nb_data_raw > 0)
{
	$datetime_inf = $datetime_p1;
	$datetime_sup = $datetime_inf + $interval;
	$year_encours=$datetime_inf;
		
	$cumul_pluvio=0;
	$cumul_pluvio_lac=0;
	
	$i=0;
	$max_pluvio=0;
	$max_cumul_pluvio=0;
	
	while($datetime_inf < $datetime_p2)
	{
		
		$qte_plu = 0;
		$qte_plu_lac = 0;
		$lacune = 0;
		
		
		if(isset($data_raw))
		{
			while((strtotime($data_raw[$i]['date_index']) >= $datetime_inf) && (strtotime($data_raw[$i]['date_index']) < $datetime_sup))
			{
				$qte_plu += $data_raw[$i]['qte'];
				$qte_plu_lac += $data_raw[$i]['qte_lacune'];	
									
				$lacune += $data_raw[$i]['lacune'];	
													
				if($i==(sizeof($data_raw)-1)){break;}
				$i++;	
			}
		}
			
		$cumul_pluvio += $qte_plu;
		$cumul_pluvio_lac += $qte_plu_lac;	
		
		
		// Save data
		$date_inf = date("Y-m-d", $datetime_inf);	
		$year_inf = date("Y", $datetime_inf);
		
		// Date de la mesuyre maximum de l'année
		if($year_inf != $year_encours){$cumul_pluvio=0;$cumul_pluvio_lac=0;}
		$year_encours = $year_inf;
			
		$data_pluvio[$date_inf] = array('year_m' => $year_inf,'heure_m' => '00:00:00','qte_plu' => $qte_plu,'qte_plu_lac' => $qte_plu_lac,'cumul_pluvio' => $cumul_pluvio,'cumul_pluvio_lac' => $cumul_pluvio_lac,'lacune' => $lacune);
				
		// Réaffectation des dates
		$datetime_inf = $datetime_sup;
		$datetime_sup += $interval;
	}
	
	ksort($data_pluvio);
	
	
	//-----------------------------------------------------------------------------------------------------------------------------
	//-----------------------------------------------------------------------------------------------------------------------------
	// Affichage dans graphiques
	
	
	$string_graph_all_a='';
	$string_graph_all_b='';
	$last_cumul_data = 0;
	$last_cumul_lac_data=0;
	$year_encours=0;
	$n=0;
	
	if($nb_data>0)
	{
		foreach($data_pluvio as $cle => $element)
		{
			$indice = ( ($n+1)*100 ) / sizeof($data_pluvio);
			progression($indice);	
			$n++;
			
			
			if($year_encours == 0)
			{
				$year_encours = $element['year_m'];
			
				${'string_graph_'.$year_encours.'_a'} = '';
				${'string_graph_'.$year_encours.'_b'} = '';
				${'string_graph_'.$year_encours.'_c_a'} = '';
				${'string_graph_'.$year_encours.'_c_b'} = '';
				
				$last_cumul_data = 0;
				$last_cumul_lac_data = 0;
				$nb_days = 0;
			}
			
			if($year_encours!=$element['year_m'])
			{
				$year_tab[$year_encours] = array('cumul' => $last_cumul_lac_data,'nb_days' => $nb_days); 
				if($last_cumul_lac_data!=0)
				{
					$year_time = strtotime($year_encours.'-01-01')*1000;
					
					$string_graph_all_a .= "[".$year_time.",".$last_cumul_data."],";
					$string_graph_all_b .= "[".$year_time.",".$last_cumul_lac_data."],";
				}
				
				$year_encours = $element['year_m'];
				
				${'string_graph_'.$year_encours.'_a'} = '';
				${'string_graph_'.$year_encours.'_b'} = '';
				${'string_graph_'.$year_encours.'_c_a'} = '';
				${'string_graph_'.$year_encours.'_c_b'} = '';
				
				$last_cumul_data = 0;
				$last_cumul_lac_data = 0;
				$nb_days = 0;
				
			}
			
			$round_plu = round($element['qte_plu'],1);
			$round_cumul = round($element['cumul_pluvio'],1);
			
			$round_plu_lac = round($element['qte_plu_lac'],1);
			$round_cumul_lac = round($element['cumul_pluvio_lac'],1);
			
			
			// max
			//if($max_pluvio<$round_plu_lac){$max_pluvio=$round_plu_lac;}
			if($max_pluvio<$round_plu){$max_pluvio=$round_plu;}
			if($max_cumul_pluvio<$round_cumul_lac){$max_cumul_pluvio=$round_cumul_lac;}
			
			
			$cle_time = strtotime($cle)*1000;
			
			${'string_graph_'.$year_encours.'_a'} .= "[".$cle_time.",".$round_plu."],";
			${'string_graph_'.$year_encours.'_b'} .= "[".$cle_time.",".$round_plu_lac."],";
			
				
			if($element['cumul_pluvio_lac']!=0)
			{
				${'string_graph_'.$year_encours.'_c_a'} .= "[".$cle_time.",".$round_cumul."],";
				${'string_graph_'.$year_encours.'_c_b'} .= "[".$cle_time.",".$round_cumul_lac."],";
				$last_cumul_data = $round_cumul;
				$last_cumul_lac_data = $round_cumul_lac;
				$nb_days++;
			
			}
			else
			{
				${'string_graph_'.$year_encours.'_c_a'} .= "[".$cle_time.",".$last_cumul_data."],";
				${'string_graph_'.$year_encours.'_c_b'} .= "[".$cle_time.",".$last_cumul_lac_data."],";
			}
			
			
		}
		
		$year_tab[$year_encours] = array('cumul' => round($last_cumul_lac_data,0),'nb_days' => $nb_days);
		
		$year_time = strtotime($year_encours.'-01-01')*1000;			
		$string_graph_all_a .= "[".$year_time."-01-01,".$last_cumul_data."]";
		$string_graph_all_b .= "[".$year_time."-01-01,".$last_cumul_lac_data."]";
		
		/* A METTRE APRES
		${'string_graph_'.$year_encours.'_a'} = substr(${'string_graph_'.$year_encours.'_a'}, 0, -1);
		${'string_graph_'.$year_encours.'_b'} = substr(${'string_graph_'.$year_encours.'_b'}, 0, -1);
		${'string_graph_'.$year_encours.'_c_a'} = substr(${'string_graph_'.$year_encours.'_c_a'}, 0, -1);
		${'string_graph_'.$year_encours.'_c_b'} = substr(${'string_graph_'.$year_encours.'_c_b'}, 0, -1);
		*/
	}
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
				
				
				
echo "<form name='stats_years_select' action='stats_result_years.php' method='post' enctype='multipart/form-data'>";

	//echo "<input type='hidden' name='button_stats' id='button_stats' value='1'>";
	//echo "<input type='hidden' name='type_affiche' id='type_affiche' value='1'>";
	
	//echo "<input type='hidden' name='select_region' id='select_region' value='".$_POST['select_region']."'>";
	//echo "<input type='hidden' name='select_station' id='select_station' value='".$_POST['select_station']."'>";
	//echo "<input type='hidden' name='select_type_eq' id='select_station' value='".$_POST['select_type_eq']."'>";
	

 
echo "<h1>";
				
	echo "<span>".htmlaccent('Chroniques des précipitations journalières')."</span>";
	if($nb_data > 0 && $nb_data_raw > 0 && !$print)
	{	
		echo button_print('print_stats_years.php?ty=plu&print=ok&bs=1&il='.$select_region.'&st='.$select_station.'&eq=1','Tableau');
	}
echo "</h1>"; 	
	

echo "<div id='box_graph_all'>";

	echo "<div id='box_graph' class='lgt'>";
		require(DIR_WS_STATS . 'stats_box_info.php');
	echo "</div>";
	
	echo "<div id='box_graph' class='lgt_r'>";
	
		echo "<h8>".htmlaccent('Synthèse pluriannuelle')."</h8>";
		echo "<div id='container_all' style='float:left;width:98%;height:200px;margin:10px 1%;'></div>";
	
	echo "</div>";
	
	//echo "<img src='".DIR_WS_IMG."header_logo.png' style='margin-top:90px;' >";	
	
	
		
echo "<hr>";		
echo "</div>";



echo "<div id='box_graph_all'>";

	if($cumul_pluvio > 0)
	{
		echo "<div id='box_graph' class='gd' >";
			
			//echo "<h8>".htmlaccent('Précipitations '.$titre_by.' (hauteur de pluie en mm)')."</h8>";
							
			
			
				echo "<div id='graph_onglet' style='margin-right:1%;'>";
						
					echo "<div id='contenu-0' class='contenu'>";
						
						$year_tab_inverse = array_reverse($year_tab,true);
						foreach($year_tab_inverse as $cle => $element)
						{
							if($element['cumul']>0)
							{
								echo "<h7>";
								
									echo htmlaccent('Année '.$cle)." - ";
									//echo "<span>";
										echo htmlaccent('Cumul des précipitations : '.$element['cumul'].' mm')." - ".htmlaccent($element['nb_days'].' jours de pluie');
									//echo "</span>";
								
								echo "</h7>";
								
								echo "<div id='container_".$cle."' style='float:left;width:48%;height:300px;margin:15px 0;'></div>";
								echo "<div id='container_c_".$cle."' style='float:right;width:48%;height:300px;margin:15px 0;'></div>";
							}
						}
					
					echo "</div>";
				
				echo "</div>";	
			
		echo "</div>";	
		
		
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
					 'legend': {'show': true, 'position': 'ne'}, 
					 'xaxis': {'showLabels': true, 'mode': 'time', 'timeFormat': '%y','noTicks': 10},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0, 'max': <?php echo $max_cumul_pluvio*1.1; ?>}, 
					 'bars': {'show': true, 'barWidth': 2, 'vertical': 'vertical'}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'tickColor': '#e7e7e7', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 1}, 					 
					 <?php
					 if(!$print)
					 {
						 echo "
						 'crosshair': {'mode': 'x', 'color': '#dddddd', 'hideCursor': true, 'lineWidth': 1}, 
						 'mouse': {'track': true, 'trackAll': false, 'position': 'nw', 'relative': false, 'margin': 3, 'trackDecimals': 0, 'trackFormatter':function dateTracker(obj){
																																										  var dateToDisplay = new Date(parseInt(obj.x)); 
																																										  var fullYear = dateToDisplay.getFullYear();
																																										  return 'Précipitation : ' + obj.y + ' mm<br>Année: '+fullYear; 
																																									  } 
					 			}
					 	";
					 }
					 ?>
				  
					}
	
	
	var options1 = {
					 'shadowSize': 0, 
					 'HtmlText': false, 
					 'fontSize': 12,
					 'legend': {'show': true, 'position': 'ne'}, 
					 'xaxis': {'showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','noTicks': 5},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0, 'max': <?php echo $max_pluvio*1.1; ?>},
					 'bars': {'show': true, 'barWidth': 2, 'vertical': 'vertical'}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'tickColor': '#e7e7e7', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 1}, 
					 <?php
					 if(!$print)
					 {
						 echo "
						 'crosshair': {'mode': 'x', 'color': '#dddddd', 'hideCursor': false}, 
						 'mouse': {'track': true, 'trackAll': false, 'position': 'nw', 'relative': false, 'margin': 3, 'trackDecimals': 0, 'trackFormatter':function dateTracker(obj){
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
																																										  return 'Précipitation : ' + obj.y + ' mm<br>Date: '+date+'-'+month+'-'+fullYear;   
																																									  }
						 
									}
					 	";
					 }
					 ?> 
					
				  
					}
					
	var options2 = {
					 'shadowSize': 0, 
					 'HtmlText': false, 
					 'fontSize': 12,
					 'legend': {'show': true, 'position': 'ne'}, 
					 'xaxis': {'showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','noTicks': 5},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0, 'max': <?php echo $max_cumul_pluvio*1.1; ?>}, 
					 'lines': {'show': true, 'lineWidth': 1.75, 'fill': true, 'fillOpacity': 0.4}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'tickColor': '#e7e7e7', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 1}, 
					 <?php
					 if(!$print)
					 {
						 echo "
						 'crosshair': {'mode': 'x', 'color': '#dddddd', 'hideCursor': false}, 
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
																																										  return 'Précipitation : ' + obj.y + ' mm<br>Date: '+date+'-'+month+'-'+fullYear;   
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
		if($element['cumul']>0)
		{
			echo "var d".$cle."a = [".${'string_graph_'.$cle.'_a'}."];";
			echo "var d".$cle."b = [".${'string_graph_'.$cle.'_b'}."];";
			
			echo "var d".$cle."ca = [".${'string_graph_'.$cle.'_c_a'}."];";
			echo "var d".$cle."cb = [".${'string_graph_'.$cle.'_c_b'}."];";
		
			echo "f = Flotr.draw($('container_".$cle."'),[{data:d".$cle."b, color:'#336699',label: 'données corrigées'}],Object.extend(Object.clone(options1)));";
			echo "fc = Flotr.draw($('container_c_".$cle."'),[{data:d".$cle."cb, color:'#336699',label: 'données corrigées'},{data:d".$cle."ca, color:'#C70039',label: 'données brutes', 'lines':{'fill': false}}],Object.extend(Object.clone(options2)));";
		}
	}
	
	
	echo "var dall_a = [".$string_graph_all_a."];";
	echo "var dall_b = [".$string_graph_all_b."];";
	echo "fall = Flotr.draw($('container_all'),[{data:dall_b, color:'#336699',label: 'données corrigées'},{data:dall_a, color:'#00232c',label: 'données brutes'}],Object.extend(Object.clone(options0)));";
	//echo "fall = Flotr.draw($('container_all'),[{data:dall_b, color:'#336699',label: 'donnees corrigees'},{data:dall_a, color:'#C70039',label: 'données brutes', 'lines':{'fill': false,'lineWidth': 2}}],Object.extend(Object.clone(options0)));";		
	
	
	?>	
	
	
	
});



  
</script>
