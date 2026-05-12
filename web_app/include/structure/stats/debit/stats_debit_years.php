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

$min_debit = 0;
$max_debit = 0;
$max_debit_years = 0;
$nb_data = 0;
$year_encours = 0;

$data_debit = getDatesBetween($date_p1, $date_p2,5);
$groupby = 'date_mesure';


$sql_data = "SELECT DISTINCT date_mesure, HOUR(heure_mesure) as heure_m, date_heure_mesure, AVG(qte) as debit, AVG(qte_lacune) as debit_lac, SUM(lacune) as lacune_day FROM ".TABLE_DATA_DEBIT. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_p1." 00:00:00' AND date_heure_mesure<='".$date_p2." 23:59:00' GROUP BY ".$groupby;
$data_query = tep_db_query($sql_link,$sql_data);	
while($data = tep_db_fetch_array($data_query))
{
	$date_mesure = $data['date_mesure'];
	$heure_m = $data['heure_m'];
	if($heure_m<10){$heure_m='0'.$heure_m;}
	$date_heure_mesure = $data['date_mesure'].' '.$heure_m.':00:00';
	
	$year_temp = explode('-',$date_mesure); 
	$year_mesure = $year_temp[0];
	
	$data_debit[$date_mesure] = array('year_m' => $year_mesure,'heure_m' => '00:00:00','debit' => $data['debit'],'debit_lac' => $data['debit_lac'],'lacune' => $data['lacune_day']);
	
	if($data['debit'] > $max_debit){$max_debit = $data['debit'];}
	if($nb_data==0){$min_debit=$max_debit;}
	
	if($data['debit'] < $min_debit){$min_debit = $data['debit'];}
	
	$nb_data++;
}


$string_graph_all_a = '';
$debit = 0;
$debit_lac = 0;
$last_debit = 0;
$last_tot_data = 0;
$nb_days = 0;
$means_data = 0;
$max_data = 0;

if($nb_data>0)
{
	$i=0;
	
	foreach($data_debit as $cle => $element)
	{
		
		$indice = ( ($i+1)*100 ) / sizeof($data_debit);
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
			$means_data = round($last_tot_data/$nb_days,3);
			$year_tab[$year_encours] = array('means' => $means_data,'max' => $max_data,'nb_days' => $nb_days); 
			
			if($means_data > $max_debit_years){$max_debit_years = $means_data;}
			
			if($nb_days!=0 && $last_tot_data!=0)
			{
				$year_time = strtotime($year_encours.'-01-01')*1000;
				
				$string_graph_all_a .= "[".$year_time.",".$means_data."],";
			}
			
			$year_encours = $element['year_m'];
			
			${'string_graph_'.$year_encours.'_a'} = '';
			
			$last_tot_data = 0;
			$max_data = 0;
			$nb_days = 0;
			
		}
		
		//$debit = round($element['debit_lac'],1);
		$debit = round($element['debit'],3);
		if($element['lacune']>0){$debit = 0;}
		
		if($element['debit_lac']>$max_data){$max_data = $debit;}		
		$last_tot_data += $debit;
		
		$cle_time = strtotime($cle)*1000;
		
		${'string_graph_'.$year_encours.'_a'} .= "[".$cle_time.",".$debit."],";
		
		
		$nb_days++;
	}
	
	$means_data = round($last_tot_data/$nb_days,3);
	$year_tab[$year_encours] = array('means' => $means_data,'max' => $max_data,'nb_days' => $nb_days);
	
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
				
	echo "<span>".htmlaccent('Chroniques des débits journaliers')."</span>";
	if($nb_data > 0 && !$print)
	{	
		echo button_print('print_stats_years.php?ty=debit&print=ok&bs=1&il='.$select_region.'&st='.$select_station.'&eq=2','Tableau');
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
	
	// VALIDITER DE La COURBE DE TARAGE
	/*
	echo "<div id='box_graph' class='lgt'>";
		  
		  echo "<h8>".htmlaccent('Courbe de tarage')."</h8>";
		  
		  echo "<table id='resume' cellspacing='0'>";
			
			echo "<tr>";
			  echo "<td class='bold'>".htmlaccent('Equation')."</td>";
			  echo "<td class='bold' style='text-align:right;'>".htmlaccent('Débit Min')."</td>";
			  echo "<td class='bold' style='text-align:right;'>".htmlaccent('Débit Max')."</td>";
			echo "</tr>";
			
			// requête sql pour récupérer le domaine de validité de la courbe de tarage
	
			$sql_tarage = "SELECT DISTINCT * FROM ".TABLE_DATA_TARAGE." WHERE id_station=".$select_station;
			$tarage_query = tep_db_query($sql_link,$sql_tarage);
			
			while($tarage = tep_db_fetch_array($tarage_query))
			{	
				$id_eq = htmlaccent(html_entity_decode($tarage['id']));
				
				$equation =  $tarage['equation'];
				
				$hauteur_min_eq =  $tarage['debit_min'];
				$hauteur_max_eq =  $tarage['debit_max'];
				$hauteur_mean_eq =  $tarage['debit_mean'];
				
				echo "<tr>";
				  echo "<td>".$equation."</td>";
				  echo "<td style='text-align:right;'>".$hauteur_min_eq."</td>";
				  echo "<td style='text-align:right;'>".$hauteur_max_eq."</td>";
				echo "</tr>";
				
			}
			
		  echo "</table>";
	  
	  echo "</div>";
		*/
	
		
echo "<hr>";		
echo "</div>";	


	

echo "<div id='box_graph_all'>";

	if($nb_data > 0)
	{
		//echo "<div id='box_graph'  class='gd'>";
			
			//echo "<h8>".htmlaccent('Précipitations '.$titre_by.' (debit de pluie en mm)')."</h8>";
							
			
			
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
										echo htmlaccent('Débit moyen : '.$element['means'].' m<sup>3</sup>/s');//." - ".htmlaccent($element['nb_days'].' jours d\'enregistrement');
										echo htmlaccent(' - Débit max. : '.$element['max'].' m<sup>3</sup>/s');
										
									echo "</h7>";
									
									echo "<div id='container_".$cle."' style='float:left;width:98%;height:300px;margin:15px 1%;'></div>";
			
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
					 'xaxis': {'showLabels': true, 'mode': 'time', 'timeFormat': '%y','noTicks': 10},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0, 'min':0, 'max': <?php echo $max_debit_years*1.1; ?>}, 
					 'lines': {'show': true, 'lineWidth': 2, 'fill': true, 'fillOpacity': 0.4}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'tickColor': '#e7e7e7', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 1}, 
					  <?php
					 if(!$print)
					 {
						 echo "
						 'crosshair': {'mode': 'x', 'color': '#dddddd', 'hideCursor': true, 'lineWidth': 1}, 
						 'mouse': {'track': true, 'trackAll': true, 'position': 'nw', 'relative': false, 'margin': 3, 'trackDecimals': 3, 'trackFormatter':function dateTracker(obj){
																																									  var dateToDisplay = new Date(parseInt(obj.x)); 
																																									  var fullYear = dateToDisplay.getFullYear();
																																									  var month = dateToDisplay.getMonth()+1;
																																									  return 'Débit : ' + obj.y + ' m<sup>3</sup>/s<br>Année : '+fullYear; 
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
					 'legend': {'show': false}, 
					 'xaxis': {'showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','noTicks': 5},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0, 'min':0, 'max': <?php echo $max_debit*1.1; ?>}, 
					 'lines': {'show': true, 'lineWidth': 2, 'fill': true, 'fillOpacity': 0.4}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'tickColor': '#e7e7e7', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 1}, 
					 <?php
					 if(!$print)
					 {
						 echo "
						 'crosshair': {'mode': 'x', 'color': '#dddddd', 'hideCursor': true, 'lineWidth': 1}, 
						 'mouse': {'track': true, 'trackAll': true, 'position': 'nw', 'relative': false, 'margin': 3, 'trackDecimals': 3, 'trackFormatter':function dateTracker(obj){
																																										  var dateToDisplay = new Date(parseInt(obj.x)); 
																																										  var fullYear = dateToDisplay.getFullYear();
																																										  var month = dateToDisplay.getMonth()+1;
																																										  if(month<10){month='0'+month;}
																																										  var date = dateToDisplay.getDate();
																																										  if(date<10){date='0'+date;};
																																										  return 'Débit : ' + obj.y + ' m<sup>3</sup>/s<br>Date: '+date+'-'+month+'-'+fullYear; 
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
		
			echo "f = Flotr.draw($('container_".$cle."'),[{data:d".$cle."a, color:'#d19c36'}],Object.extend(Object.clone(options1)));";
		}
	}
	
	echo "var dall_a = [".$string_graph_all_a."];";
	echo "fall = Flotr.draw($('container_all'),[{data:dall_a, color:'#d19c36'}],Object.extend(Object.clone(options0)));";
			
	
	
	?>	
	
	
	
});



  
</script>
