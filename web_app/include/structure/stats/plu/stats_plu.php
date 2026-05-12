<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

$f_y = 0; 
$l_y = 0;
$y = 0;

// Pour fichier stats_plu_tab.php	
$edit_tab_html = "";
$edit_tab_resume_html = "";
$style_resume = "";
$tab_mois = array('janv.','f&eacute;v.','mars','avr.','mai','juin','juil.','ao&ucirc;t','sept.','oct.','nov.','d&eacute;c.');


$sql_an = "SELECT DISTINCT YEAR(date_first) as year FROM ".TABLE_IMPORT." UNION SELECT YEAR(date_end) FROM ".TABLE_IMPORT." WHERE id_station='".$select_station."' ORDER BY year DESC";
$annee_query = tep_db_query($sql_link,$sql_an);
while ($annee_t = tep_db_fetch_array($annee_query))
{
	if($f_y == 0){$f_y = $annee_t['year'];}
	$l_y = $annee_t['year'];
}
for($i=$f_y;$i>=$l_y;$i--){$tab_annee[]=$i;}

$intervalle_type = 'd';
$print=false;

if(isset($_GET['date_p1']) && isset($_GET['date_p2']))
{
	$date_p1 = $_GET['date_p1'];
	$date_p2 = $_GET['date_p2'];
	
	$print=true;
}

	
	
switch($periode)
{
  case 1 :
	  $titre = htmlaccent('Journalière');
	  
	  if(!$print)
	  {
		  $day_p1 = $_POST['day_stats'];
		  if($day_p1<10){$day_p1='0'.$day_p1;}
		  $month_p1 = $_POST['month_day'];
		  $year_p1 = $_POST['annee_d'];
		  $date_p1 = $year_p1.'-'.$month_p1.'-'.$day_p1;
		  $date_p2 = $date_p1;
	  }
	  $intervalle_type = 'h';
	  break;
  case 2 :
	  $titre = htmlaccent('Mensuelle');
	  
	  if(!$print)
	  {
		  $month_p1 = $_POST['month_stats'];
		  $year_p1 = $_POST['annee_m'];
		  $date_p1 = $year_p1.'-'.$month_p1.'-01';
		  $date_p2 = $year_p1.'-'.$month_p1.'-31';
	  }
	  break;
  case 3 :
	  $titre = htmlaccent('Annuelle');
	  
	  if(!$print)
	  {
		  $year_p1 = $_POST['select_year'];
		 	  
		  $date_p1 = $year_p1.'-01-01';
		  $date_p2 = $year_p1.'-12-31';
	  }
	  else
	  {
		  $year_tab = explode('-',$date_p1);
		  $year_p1 = $year_tab[0]; 
	  }
	   $y = $year_p1;
	  break;
  case 4 :
	  $titre = htmlaccent('Personnalisée');
	  
	  if(!$print)
	  {
		  $day_p1 = $_POST['day_p1'];
		  if($day_p1<10){$day_p1='0'.$day_p1;}
		  $month_p1 = $_POST['month_p1'];
		  $year_p1 = $_POST['year_p1'];
		  $date_p1 = $year_p1.'-'.$month_p1.'-'.$day_p1;
		  
		  $day_p2 = $_POST['day_p2'];
		  if($day_p2<10){$day_p2='0'.$day_p2;}
		  $month_p2 = $_POST['month_p2'];
		  $year_p2 = $_POST['year_p2'];
		  $date_p2 = $year_p2.'-'.$month_p2.'-'.$day_p2;
	  }
	  break;
}


// vérification des dates
$date_verif_p1 = false;
while(!$date_verif_p1)
{
	$tab_verif_p1 = explode('-',$date_p1);
	if(checkdate($tab_verif_p1[1],$tab_verif_p1[2],$tab_verif_p1[0])){$date_verif_p1 = true;}
	else{$date_p1=$tab_verif_p1[0].'-'.$tab_verif_p1[1].'-'.($tab_verif_p1[2]-1);}
}

$date_verif_p2 = false;
while(!$date_verif_p2)
{
	$tab_verif_p2 = explode('-',$date_p2);
	if(checkdate($tab_verif_p2[1],$tab_verif_p2[2],$tab_verif_p2[0])){$date_verif_p2 = true;}
	else{$date_p2=$tab_verif_p2[0].'-'.$tab_verif_p2[1].'-'.($tab_verif_p2[2]-1);}
}
// -----------------

if($intervalle_type=='d')
{
	$data_pluvio = getDatesBetween ($date_p1, $date_p2);
	$groupby = 'date_mesure';
	$titre_by = 'quotidiennes';
}
if($intervalle_type=='h')
{
	for($h=0;$h<=24;$h++)
	{
		if($h<10){$h='0'.$h;}
		$date_day = $date_p1." ".$h.":00:00";
		$data_pluvio[$date_day] = array('heure_m' => $h.':00:00','qte_plu' => 0,'qte_plu_lac' => 0,'cumul_pluvio' => '0','cumul_pluvio_lac' => '0','lacune' => '-');	
		$groupby = 'HOUR(heure_mesure)';
		$titre_by = 'horaires';
		//$groupby = 'SUBSTRING(date_heure_mesure , 15, 2 )';
	}
}

// initialisation des variables
$cumul_pluvio = 0;
$cumul_pluvio_lac = 0;
$min_pluvio = 0;
$max_pluvio = 0;
$date_max = '';
$date_min = '';
$max_cumul_pluvio = 0;
$nb_dayofrain = 0;

$string_graph_1 = '';
$string_graph_1b = '';
$string_graph_2 = '';
$string_graph_2b = '';

$string_html_plu = '';
$string_html_plu_lac = '';


$sql_data = "SELECT DISTINCT date_mesure, HOUR(heure_mesure) as heure_m, date_heure_mesure, SUM(qte) as qte_plu, SUM(qte_lacune) as qte_plu_lac, SUM(lacune) as lacune_info FROM ".TABLE_DATA_PLUVIO. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_p1." 00:00:00' AND date_heure_mesure<='".$date_p2." 23:59:00' GROUP BY ".$groupby;

$data_query = tep_db_query($sql_link,$sql_data);	
while($data = tep_db_fetch_array($data_query))
{
	$date_mesure = $data['date_mesure'];
	$heure_m = $data['heure_m'];
	if($heure_m<10){$heure_m='0'.$heure_m;}
	$date_heure_mesure = $data['date_mesure'].' '.$heure_m.':00:00';
	
	//tab pluvio
	$cumul_pluvio += $data['qte_plu'];	
	$cumul_pluvio_lac += $data['qte_plu_lac'];	
	
	if($intervalle_type=='d'){$data_pluvio[$date_mesure] = array('heure_m' => '00:00:00','qte_plu' => $data['qte_plu'],'qte_plu_lac' => $data['qte_plu_lac'],'cumul_pluvio' => $cumul_pluvio,'cumul_pluvio_lac' => $cumul_pluvio_lac,'lacune' => $data['lacune_info']);}
	if($intervalle_type=='h'){$data_pluvio[$date_heure_mesure] = array('heure_m' => $heure_m.':00:00','qte_plu' => $data['qte_plu'],'qte_plu_lac' => $data['qte_plu_lac'],'cumul_pluvio' => $cumul_pluvio,'cumul_pluvio_lac' => $cumul_pluvio_lac,'lacune' => $data['lacune_info']);}
	
	if($data['qte_plu_lac'] > $max_pluvio){$max_pluvio = $data['qte_plu_lac'];$date_max = dateus_fr($date_mesure);}
	if($nb_dayofrain==0){$min_pluvio=$max_pluvio;$date_min = dateus_fr($date_mesure);}
	
	if($data['qte_plu_lac'] < $min_pluvio){$min_pluvio = $data['qte_plu_lac'];$date_min = dateus_fr($date_mesure);}
	
	$nb_dayofrain++;
}
//$date_p2 = $date_mesure;

$last_cumul_data = 0;
$last_cumul_lac_data = 0;

if($nb_dayofrain>0)
{
	$lacune_encours = false;
	
	$i=0;
	
	foreach($data_pluvio as $cle => $element)
	{
		$indice = ( ($i+1)*100 ) / sizeof($data_pluvio);
		progression($indice);	
		$i++;
		
		$round_plu = round($element['qte_plu'],1);
		$round_cumul = round($element['cumul_pluvio'],1);
		
		$round_plu_lac = round($element['qte_plu_lac'],1);
		$round_cumul_lac = round($element['cumul_pluvio_lac'],1);
		
		$lacune = $element['lacune'];
		if($lacune_encours)
		{
			if($lacune>0){$lacune_encours=false;$lacune=1;}
			if($lacune==0){$lacune=1;}
		}
		else{if($lacune>0){$lacune_encours=true;$lacune=1;}}
		
		
		
		
		$cle_time = strtotime($cle)*1000;
		$string_graph_1b .= "[".$cle_time.",".$round_plu."],";
		$string_graph_1 .= "[".$cle_time.",".$round_plu_lac."],";
		
		// data plu
		$string_html_plu .= "<tr>";
			if($intervalle_type=='d'){$string_html_plu .= "<td>".dateus_fr($cle)."</td>";}
			if($intervalle_type=='h'){$string_html_plu .= "<td>".$element['heure_m']."</td>";}
			$string_html_plu .= "<td>".$round_plu."</td>";
			$string_html_plu .= "<td>".$round_plu_lac."</td>";
			$string_html_plu .= "<td>".$lacune."</td>";
		$string_html_plu .= "</tr>";
		
		
		
		$string_html_plu_lac .= "<tr>";
			if($intervalle_type=='d'){$string_html_plu_lac .= "<td>".dateus_fr($cle)."</td>";}
			if($intervalle_type=='h'){$string_html_plu_lac .= "<td>".$element['heure_m']."</td>";}
			
			if($element['cumul_pluvio_lac']!=0)
			{
				$string_graph_2 .= "[".$cle_time.",".$round_cumul_lac."],";
				$string_graph_2b .= "[".$cle_time.",".$round_cumul."],";
				$last_cumul_data = $round_cumul;
				$last_cumul_lac_data = $round_cumul_lac;
				
				// data cumul plu
				$string_html_plu_lac .= "<td>".$round_cumul."</td>";
				$string_html_plu_lac .= "<td>".$round_cumul_lac."</td>";
				$string_html_plu_lac .= "<td>".$lacune."</td>";
			}
			else
			{
				$string_graph_2 .= "[".$cle_time.",".$last_cumul_lac_data."],";
				$string_graph_2b .= "[".$cle_time.",".$last_cumul_data."],";
				
				// data cumul plu
				$string_html_plu_lac .= "<td>".$last_cumul_data."</td>";
				$string_html_plu_lac .= "<td>".$last_cumul_lac_data."</td>";
				$string_html_plu_lac .= "<td>".$lacune."</td>";
			}
		$string_html_plu_lac .= "</tr>";
		
	}
	$string_graph_1 = substr($string_graph_1, 0, -1);
	$string_graph_1b = substr($string_graph_1b, 0, -1);
	$string_graph_2 = substr($string_graph_2, 0, -1);
	$string_graph_2b = substr($string_graph_2b, 0, -1);
	
}	

$max_cumul_pluvio = $cumul_pluvio_lac;
$min_date = $date_p1.' 00:00:00';
$max_date = $date_p2.' 23:59:00';



// Load liste lacunes
$lacune_load=false;

$sql_listlac = "SELECT DISTINCT * FROM ".TABLE_DATA_LACUNE. " WHERE station_lacune='".$select_station."' AND ((date_deb_lacune>='".$min_date."' AND date_deb_lacune<='".$max_date."') OR (date_fin_lacune>='".$min_date."' AND date_fin_lacune<='".$max_date."')) ORDER BY date_deb_lacune, heure_deb_lacune";
$listlac_query = tep_db_query($sql_link,$sql_listlac);	
while($listlac = tep_db_fetch_array($listlac_query))
{
	$date_deb_lacune = dateus_fr($listlac['date_deb_lacune']);
	$heure_deb_lacune = $listlac['heure_deb_lacune'];
	$time_deb = $date_deb_lacune." ".$heure_deb_lacune;
	
	$date_fin_lacune = dateus_fr($listlac['date_fin_lacune']);
	$heure_fin_lacune = $listlac['heure_fin_lacune'];
	$time_fin = $date_fin_lacune." ".$heure_fin_lacune;
	
	$cumul_lacune = htmlaccent(html_entity_decode($listlac['cumul_lacune']));
	$observation_lacune = htmlaccent(html_entity_decode($listlac['observation_lacune']));
	
	//tab pluvio cumul
	$data_list_lacunes[] = array('id' => $listlac['id'],
							'time_deb' => $time_deb,
							'date_deb' => $date_deb_lacune,
							'time_fin' => $time_fin,
							'date_fin' => $date_fin_lacune,
							'cumul_lacune' => $cumul_lacune,
							'observation_lacune' => $observation_lacune);
							
	$lacune_load=true;						
}

/*
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

*/
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

echo "<form name='stats_select' action='stats_result.php' method='post' enctype='multipart/form-data' onSubmit='return verif_dates_stats();'>";

	echo "<input type='hidden' name='button_stats' id='button_stats' value='1'>";
	
	
	echo "<input type='hidden' name='select_region' id='select_region' value='".$select_region."'>";
	echo "<input type='hidden' name='select_station' id='select_station' value='".$select_station."'>";
	echo "<input type='hidden' name='select_periode' id='select_periode' value='".$periode."'>";
	echo "<input type='hidden' name='select_type_eq' id='select_type_eq' value='1'>";
	//echo "<input type='hidden' name='select_year' id='select_year' value='".$year_stats."'>";
	

 
echo "<h1>";
				
	$titre = htmlaccent('Chronique des précipitations');
	
	//$titre_p = htmlaccent($titre)." - Station : ".$nom_station;
	
	if($print){echo "<span class='print'>".$titre."</span>";}
	else{echo "<span>".$titre."</span>";}
	
	
	if($cumul_pluvio > 0  && !$print)
	{
		//echo button_pdf('export_excel.php?imp=2&ty=plu&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&date_p1='.$date_p1.'&date_p2='.$date_p2);
		//echo button_xls('export_excel.php?imp=2&ty=plu&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&date_p1='.$date_p1.'&date_p2='.$date_p2);
		echo button_print('print_stats.php?imp=2&ty=plu&print=ok&bs=1&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&eq=1&date_p1='.$date_p1.'&date_p2='.$date_p2,'Tableau');
	}
	
	
echo "</h1>"; 	
	

echo "<div id='box_graph_all'>";

	echo "<div id='box_graph' class='lgt'>";
		
		require(DIR_WS_STATS . 'stats_box_info.php');
	
	echo "</div>";
	
	echo "<div id='box_graph' class='lgt_r'>";
	
		echo "<h8>".htmlaccent('Résumé')."</h8>";
		
		echo "<table id='resume' cellspacing='0'>";
			
			echo "<tr class='grey'>";
				echo "<td class='bold'>".htmlaccent('Période')."</td>";
				echo "<td style='text-align:right;'>du ".dateus_fr($date_p1)." au ".dateus_fr($date_p2)."</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td class='bold'>".htmlaccent('Cumul des précipitations (mm)')."</td>";
				echo "<td style='text-align:right;'>".round($max_cumul_pluvio,1)."</td>";
			echo "</tr>";
			
			echo "<tr class='grey'>";
				if($periode==1){echo "<td class='bold'>".htmlaccent('Nombre d\'heures de pluie')."</td>";}
				else{echo "<td class='bold'>".htmlaccent('Nombre de jours de pluie')."</td>";}
				echo "<td style='text-align:right;'>".$nb_dayofrain."</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td class='bold'>".htmlaccent('Précipitation max (mm)')." - ".$date_max."</td>";
				echo "<td style='text-align:right;'>".round($max_pluvio,1)."</td>";
			echo "</tr>";
			
			echo "<tr class='grey'>";
				echo "<td class='bold'>".htmlaccent('Précipitation min (mm)')." - ".$date_min."</td>";
				echo "<td style='text-align:right;'>".round($min_pluvio,1)."</td>";
			echo "</tr>";

				
		echo "</table>";		
		
	echo "</div>";
	
	if(!$print){require(DIR_WS_STATS . 'stats_box_form.php');}
	
		
echo "</div>";



echo "<div id='box_graph_all'>";

	if($cumul_pluvio > 0)
	{
		echo "<div id='box_graph' class='gd'>";
			
			echo "<h8>".htmlaccent('Précipitations '.$titre_by.' (mm)')."</h8>";
							
			echo "<hr><hr><hr>";
			
				echo "<div id='graph_onglet'>";
						
					echo "<div id='contenu-1' class='contenu'>";
						//echo "<div  class='jqPlot' id='chart1' style='height:100%;width:95%;margin-left:3%;'></div>";
						echo "<div id='container' style='width:95%;height:400px;margin-left:2%;'></div>";
					echo "</div>";
				
					if(!$print)
					{
						echo "<div id='contenu-2' class='contenu' style='display:none;'>";
							echo affiche_plu_data($string_html_plu);
						echo "</div>";
						
						
						echo "<ul id='graph_menu_onglet'>";
						
							echo "<li onClick=\"javascript:ChangeOnglet_2(1, 2, 'onglet-', 'contenu-');\" id='onglet-1' class='actif'>".htmlaccent('Graph')."</li>\n";
							echo "<li onClick=\"javascript:ChangeOnglet_2(2, 2, 'onglet-', 'contenu-');\" id='onglet-2'>".htmlaccent('Data')."</li>\n";
											
						echo "</ul>";
					
					
						/*
						echo "<div class='zoom'>";
							echo "<p id='zoommoins'>";	
								echo "<img src='".DIR_WS_IMG_ICO."zoommoins.png' title='".htmlaccent('zoom -')."'>\n"; 
								echo "<span>".htmlaccent('Revenir au graphique complet')."</span>";
							echo "</p>";
								
							echo "</br>";
						*/	
							/*
							echo "<form name='image-download' action='' onsubmit='return false'>";
								echo "<p onclick='f.saveImage(getV(this.form.format))'>";	
									echo "<img src='".DIR_WS_IMG_ICO."download_png.png' title='".htmlaccent('Download PNG')."'>\n"; 
									echo "<button name='download' onclick='f.saveImage(getV(this.form.format))'>".htmlaccent('Enregistrer le graphique')."</button>";
								echo "</p>";
							echo "</form>";	
							*/
						//echo "</div>";
					}
					
					
				echo "</div>";		
			
		echo "</div>";
		
		
		
		echo "<div id='box_graph' class='gd'>";
			
			echo "<h8>".htmlaccent('Cumul des précipitations (mm)')."</h8>";
							
			echo "<hr><hr><hr>";
			
				echo "<div id='graph_onglet'>";
						
					echo "<div id='contenu2-1' class='contenu'>";
						//echo "<div  class='jqPlot' id='chart2' style='height:100%;width:95%;margin-left:3%;'></div>";
						echo "<div id='container1' style='width:95%;height:400px;margin-left:2%;'></div>";
					echo "</div>";
				
					if(!$print)
					{
						echo "<div id='contenu2-2' class='contenu' style='display:none;'>";
							echo affiche_plu_data($string_html_plu_lac,'cl');
						echo "</div>";
						
						echo "<ul id='graph_menu_onglet'>";
						
							echo "<li onClick=\"javascript:ChangeOnglet_2(1, 2, 'onglet2-', 'contenu2-');\" id='onglet2-1' class='actif'>".htmlaccent('Graph')."</li>\n";
							echo "<li onClick=\"javascript:ChangeOnglet_2(2, 2, 'onglet2-', 'contenu2-');\" id='onglet2-2'>".htmlaccent('Data')."</li>\n";
											
						echo "</ul>";
					
					/*
						echo "<div class='zoom'>";
							
							 echo "<p id='zoommoins1'>";	
								echo "<img src='".DIR_WS_IMG_ICO."zoommoins.png' title='".htmlaccent('zoom -')."'>\n"; 
								echo "<span>".htmlaccent('Revenir au graphique complet')."</span>";
							echo "</p>";
								
							echo "</br>";
					*/		
							/*
							echo "<form name='image-download1' action='' onsubmit='return false'>";
								echo "<p onclick='f1.saveImage(getV(this.form.format))'>";	
									echo "<img src='".DIR_WS_IMG_ICO."download_png.png' title='".htmlaccent('Download PNG')."'>\n"; 
									echo "<button name='download' onclick='f1.saveImage(getV(this.form.format))'>".htmlaccent('Enregistrer le graphique')."</button>";
									//echo "<button name='to-image' onclick='f1.saveImage(getV(this.form.format), null, null, true)'>To Image</button>";
								echo "</p>";
							echo "</form>";	
							*/
							
					//	echo "</div>";	
					
					}
					
				echo "</div>";	
			
		echo "</div>";
		
		
		
		if($periode == 3)
		{
			require(DIR_WS_STATS_PLU . 'stats_plu_tab.php');
			if(tep_not_null($edit_tab_html)){echo $edit_tab_html;}
		}
		
		
		$import = false;
		require(DIR_WS_STRUCTURE . 'box_list_lacunes.php');
		
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
	// DATA
	<?php echo "var d1 = [".$string_graph_1."];var d1b = [".$string_graph_1b."];";?>
	<?php echo "var d2 = [".$string_graph_2."];var d2b = [".$string_graph_2b."];";?>
				
	// OPTIONS			
	var options1 = {
					 'shadowSize': 0, 
					 'HtmlText': false, 
					 'fontSize': 13,
					 'legend': {'show': true,'backgroundColor': '#FFFFFF'}, 
					 //'xaxis': {'title': 'Date', 'showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','noTicks': 5},
					 'xaxis': {'showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','minTickSize': [1, 'month']},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0, 'max': <?php echo $max_pluvio*1.1; ?>}, 
					 'bars': {'show': true, 'barWidth': 2, 'vertical': 'vertical'}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 1, 'lineColor': '#dddddd'}, 
					 <?php
					 if(!$print)
					 {
						 echo "
						 'selection': {'mode': 'x', 'color': '#9bc0dd'}, 
						 'crosshair': {'mode': 'x', 'color': '#dddddd', 'hideCursor': true, 'lineWidth': 1}, 
						 'mouse': {'track': true, 'trackAll': false, 'position': 'nw', 'relative': true, 'margin': 20, 'trackDecimals': 0, 'trackFormatter':function dateTracker(obj){
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
					 'legend': {'show': true,'backgroundColor': '#FFFFFF'}, 
					 //'xaxis': {'title': 'Date', 'showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','noTicks': 5},
					 'xaxis': {'showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','minTickSize': [1, 'month']},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0, 'max': <?php echo $max_cumul_pluvio*1.1; ?>},
					 'lines': {'show': true, 'lineWidth': 1.5, 'fill': true, 'fillOpacity': 0.4}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 1, 'lineColor': '#dddddd'}, 
					 <?php
					 if(!$print)
					 {
						 echo "
						 'selection': {'mode': 'x', 'color': '#9bc0dd'}, 
						 'crosshair': {'mode': 'x', 'color': '#dddddd', 'hideCursor': true, 'lineWidth': 1}, 
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
																																										  return 'Précipitation : ' + obj.y + ' mm<br>Date: '+date+'-'+month+'-'+fullYear;   
																																									  }
						 
									}
					 	";
					 }
					 ?>
					
				  
					}
					
	
  	f = Flotr.draw($('container'),[{data:d1, color:'#336699',label: 'données corrigées'}],Object.extend(Object.clone(options1)));
	f1 = Flotr.draw($('container1'),[{data:d2, color:'#336699',label: 'données corrigées'},{data:d2b, color:'#C70039',label: 'données brutes', 'lines':{'fill': false}}],Object.extend(Object.clone(options2)));
  
	
	//f = Flotr.draw($('container'),[{data:d1b, color:'#008eb2',label: 'données brutes', 'lines':{'fill': false}},{data:d1, color:'#00232c',label: 'données corrigées'}],Object.extend(Object.clone(options1)));
	//f1 = Flotr.draw($('container1'),[{data:d2b, color:'#008eb2',label: 'données brutes', 'lines':{'fill': false}},{data:d2, color:'#00232c',label: 'données corrigées'}],Object.extend(Object.clone(options2)));
	 
	 
	function drawGraph(opts){
		
		var o = Object.extend(Object.clone(options1), opts || {});
		
		return f = Flotr.draw(
			$('container'), 
			[{data:d1, color:'#336699',label: 'données corrigées'}],
			o
		);
	}	
	function drawGraph1(opts){
		
		var o = Object.extend(Object.clone(options2), opts || {});
		
		return f1 = Flotr.draw(
			$('container1'), 
			[{data:d2, color:'#336699',label: 'données corrigées'},{data:d2b, color:'#C70039',label: 'données brutes', 'lines':{'fill': false}}],
			o
		);
	}	
	
	
	// ZOOM
	$('container').observe('flotr:select', function(evt){
	
		var area = evt.memo[0];
		
		f = drawGraph({
			xaxis: {min:area.x1, max:area.x2,'noTicks': 5,'mode': 'time', 'timeFormat': '%d-%m-%y'},
			yaxis: {min:area.y1, max:area.y2+10, 'trackDecimals': 0}
			
			
		});
	});
	
	$('container1').observe('flotr:select', function(evt){
	
		var area = evt.memo[0];
		
		f1 = drawGraph1({
			xaxis: {min:area.x1, max:area.x2,'mode': 'time', 'timeFormat': '%d-%m-%y'},
			yaxis: {min:area.y1, max:area.y2, 'trackDecimals': 0}
		});
	});
		
	$('container').observe('dblclick', function(evt){drawGraph()});
	$('container1').observe('dblclick', function(evt){drawGraph1()});
	
	
	
	
});



  
</script>
