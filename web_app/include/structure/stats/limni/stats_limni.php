<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/
$f_y = 0; 
$l_y = 0;



$sql_an = "SELECT DISTINCT YEAR(date_first) as year FROM ".TABLE_IMPORT." UNION SELECT YEAR(date_end) FROM ".TABLE_IMPORT." WHERE id_station='".$select_station."' ORDER BY year DESC";
$annee_query = tep_db_query($sql_link,$sql_an);
while ($annee_t = tep_db_fetch_array($annee_query))
{
	if($f_y == 0){$f_y = $annee_t['year'];}
	$l_y = $annee_t['year'];
}
for($i=$f_y;$i>=$l_y;$i--){$tab_annee[]=$i;}


$intervalle_type = 'd';


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

// initialisation des variables
$min_limni = 0;
$max_limni = 0;
$date_max = '';
$date_min = '';
$nb_data = 0;
$tot_limni_all = 0;
$moy_limni_all = 0;
$last_qte_lim = 0;
$last_qte_lim_lac = 0;
$string_graph_1 = '';
$string_html_limni = '';
$edition = '';

//$sql_data = "SELECT DISTINCT date_mesure, heure_mesure, HOUR(heure_mesure) as heure_m, date_heure_mesure, AVG(qte) as hauteur, AVG(qte_lacune) as hauteur_lac, lacune FROM ".TABLE_DATA_LIMNI. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_p1." 00:00:00' AND date_heure_mesure<='".$date_p2." 23:59:00' GROUP BY ".$groupby;

$sql_data = "SELECT DISTINCT date_mesure, heure_mesure, qte as hauteur, qte_lacune as hauteur_lac, lacune FROM ".TABLE_DATA_LIMNI. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_p1." 00:00:00' AND date_heure_mesure<='".$date_p2." 23:59:00' ORDER BY date_heure_mesure";
$data_query = tep_db_query($sql_link,$sql_data);	
while($data = tep_db_fetch_array($data_query))
{
	$date_mesure = $data['date_mesure'];
	$heure_mesure = $data['heure_mesure'];
	$data_index = $date_mesure." ".$heure_mesure;
	
	if($data['hauteur_lac'] > $max_limni){$max_limni = $data['hauteur_lac'];$date_max = dateus_fr($date_mesure).' '.$heure_mesure;}
	if($nb_data==0)
	{
		$min_limni=$max_limni;
		$date_min = dateus_fr($date_mesure).' '.$heure_mesure;
		
		if($data_index <> $date_p1." 00:00:00")
		{
			$data_limni[$date_p1." 00:00:00"] = array('data_index' => $date_p1." 00:00:00",'qte_lim' => 0,'qte_lim_lac' => 0,'lacune' => 0);
			$data_limni[date('Y-m-d h:i:s', strtotime($data_index)-60)] = array('data_index' => date('Y-m-d h:i:s', strtotime($data_index)-60),'qte_lim' => 0,'qte_lim_lac' => 0,'lacune' => 0);
		}
	}
	
	if($data['hauteur_lac'] < $min_limni && $data['hauteur_lac'] != 0){$min_limni = $data['hauteur_lac'];$date_min = dateus_fr($date_mesure).' '.$heure_mesure;}
		
	//tab limni cumul
	$data_limni[$data_index] = array('data_index' => $data_index,
								  'qte_lim' => $data['hauteur'],
								  'qte_lim_lac' => $data['hauteur_lac'],
								  'lacune' => $data['lacune']);
	
	$tot_limni_all += $data['hauteur'];
	$last_qte_lim = $data['hauteur'];
	$last_qte_lim_lac = $data['hauteur_lac'];
	$last_lacune = $data['lacune'];
	$nb_data++;
}

								  
if($nb_data > 0)
{
	// Préparation des données limnimétrique
	//$data_limni[date('Y-m-d h:i:s', strtotime($data_index)+60)] = array('data_index' => date('Y-m-d h:i:s', strtotime($data_index)+60),'qte_lim' => 0,'qte_lim_lac' => 0,'lacune' => 0);	
	$data_limni[$date_p2.' 23:59:00'] = array('data_index' => $date_p2.' 23:59:00','qte_lim' => 0,'qte_lim_lac' => 0,'lacune' => 0);
	
	$moy_limni_all = round($tot_limni_all / $nb_data,0);
	
	$debut_lac_0=0;
	$old_cle_time=strtotime($date_p1." 00:01:00")*1000;
	$i=0;
	
	foreach($data_limni as $cle => $element)
	{
		$indice = ( ($i+1)*100 ) / sizeof($data_limni);
		progression($indice);	
		$i++;
		
		$cle_time = strtotime($cle)*1000;
		
		
		$round_limni_lac = round($element['qte_lim_lac'],1);
		
		//--------------------------------------------------
		// Engendrer visuellement une lacune automatique si il n'y a pas de données sur une période supérieur à 24h (86400 sec)
		if($cle_time > (($old_cle_time+86400000)))
		{
			// Début lacune auto
			$string_html_limni .= "<tr>";
		
				$string_html_limni .= "<td>".date('Y-m-d h:i:s', $old_cle_time+60000)."</td>";
				
				$string_graph_1 .= "[".($old_cle_time+60000).",0],";
				$string_html_limni .= "<td>0</td>";
				$string_html_limni .= "<td>1</td>";
			
			$string_html_limni .= "</tr>";
			
			// Fin lacune auto
			$string_html_limni .= "<tr>";
		
				$string_html_limni .= "<td>".date('Y-m-d h:i:s', $cle_time-60000)."</td>";
				
				$string_graph_1 .= "[".($cle_time-60000).",0],";
				$string_html_limni .= "<td>0</td>";
				$string_html_limni .= "<td>1</td>";
			
			$string_html_limni .= "</tr>";
		}
		//--------------------------------------------------
		
		if($round_limni_lac==0 && $debut_lac_0==1){$debut_lac_0=0;}
		if($round_limni_lac==0 && $debut_lac_0==0){$debut_lac_0=1;}
		if($round_limni_lac!=0 && $debut_lac_0==1)
		{
			//$cle_time_0 = $cle_time-1;
			//$cle_0 = date('Y-m-d H:i:s',$cle_time_0);
			
			$string_html_limni .= "<tr>";
		
				$string_html_limni .= "<td>".$cle."</td>";
				
				$string_graph_1 .= "[".$cle_time.",0],";
				$string_html_limni .= "<td>0</td>";
				$string_html_limni .= "<td>1</td>";
			
			$string_html_limni .= "</tr>";
			
			$debut_lac_0=0;
		}
		
		
		
		$string_html_limni .= "<tr>";
		
			$string_html_limni .= "<td>".$cle."</td>";
			
			//$cle_time = strtotime($cle)*1000;
			$string_graph_1 .= "[".$cle_time.",".$round_limni_lac."],";
			$string_html_limni .= "<td>".$element['qte_lim']."</td>";
			$string_html_limni .= "<td>".$element['lacune']."</td>";
		
		$string_html_limni .= "</tr>";
		
		
		$old_cle_time = $cle_time;
	}
	$string_graph_1 = substr($string_graph_1, 0, -1);
}

$min_date = $date_p1;
$max_date = $date_p2;


// Load liste lacunes
$lacune_load=false;

$sql_listlac = "SELECT DISTINCT * FROM ".TABLE_DATA_LACUNE_LIMNI. " WHERE station_lacune='".$select_station."' AND ((date_deb_lacune>='".$min_date."' AND date_deb_lacune<='".$max_date."') OR (date_fin_lacune>='".$min_date."' AND date_fin_lacune<='".$max_date."')) ORDER BY date_deb_lacune, heure_deb_lacune";
$listlac_query = tep_db_query($sql_link,$sql_listlac);	
while($listlac = tep_db_fetch_array($listlac_query))
{
	$date_deb_lacune = dateus_fr($listlac['date_deb_lacune']);
	$heure_deb_lacune = $listlac['heure_deb_lacune'];
	$time_deb = $date_deb_lacune." ".$heure_deb_lacune;
	
	$date_fin_lacune = dateus_fr($listlac['date_fin_lacune']);
	$heure_fin_lacune = $listlac['heure_fin_lacune'];
	$time_fin = $date_fin_lacune." ".$heure_fin_lacune;
	
	$observation_lacune = htmlaccent(html_entity_decode($listlac['observation_lacune']));
	
	//tab limni cumul
	$data_list_lacunes_limni[] = array('id' => $listlac['id'],
									'time_deb' => $time_deb,
									'date_deb' => $date_deb_lacune,
									'time_fin' => $time_fin,
									'date_fin' => $date_fin_lacune,
									'observation_lacune' => $observation_lacune);
							
	$lacune_load=true;						
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

	if(!$debit_tarage){echo "<input type='hidden' name='button_stats' id='button_stats' value='1'>";}
	else
	{
		if($type_data==3){echo "<input type='hidden' name='button_stats' id='button_stats' value='3'>";}
		if($type_data==2){echo "<input type='hidden' name='button_stats' id='button_stats' value='2'>";}
	}
	
	echo "<input type='hidden' name='select_region' id='select_region' value='".$select_region."'>";
	echo "<input type='hidden' name='select_station' id='select_station' value='".$select_station."'>";
	echo "<input type='hidden' name='select_periode' id='select_periode' value='".$periode."'>";
	echo "<input type='hidden' name='select_type_eq' id='select_type_eq' value='2'>";
	
	
	//echo "<input type='hidden' name='select_year' id='select_year' value='".$year_stats."'>";
	

 
//echo "<h1 style='background-color:#eaf7ea;'>";
echo "<h1>";
				
	$titre = htmlaccent('Chronique des hauteurs d\'eau');
	//$titre_p = htmlaccent($titre)." - Station : ".$nom_station;
	
	if($print){echo "<span class='print'>".$titre."</span>";}
	else{echo "<span>".$titre."</span>";}
	
	if($nb_data > 0  && !$print)
	{
		//echo button_pdf('export_pdf.php?type=stats&ty=limni&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&date_p1='.$date_p1.'&date_p2='.$date_p2);
		//echo button_xls('export_excel.php?imp=2&ty=limni&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&date_p1='.$date_p1.'&date_p2='.$date_p2);
		echo button_print('print_stats.php?imp=2&ty=limni&print=ok&bs=1&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&eq=2&date_p1='.$date_p1.'&date_p2='.$date_p2,'Tableau');
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
				echo "<td class='bold'>".htmlaccent('Hauteur d\'eau moyenne (en cm)')."</td>";
				echo "<td style='text-align:right;'>".round($moy_limni_all,1)."</td>";
			echo "</tr>";
			
			echo "<tr class='grey'>";
				if($periode==1){echo "<td class='bold'>".htmlaccent('Nombre d\'enregistrements')."</td>";}
				else{echo "<td class='bold'>".htmlaccent('Nombre d\'enregistrements')."</td>";}
				echo "<td style='text-align:right;'>".$nb_data."</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td class='bold'>".htmlaccent('Hauteur d\'eau max (en cm)')." - ".$date_max."</td>";
				echo "<td style='text-align:right;'>".round($max_limni,1)."</td>";
			echo "</tr>";
			
			echo "<tr class='grey'>";
				echo "<td class='bold'>".htmlaccent('Hauteur d\'eau min (en cm)')." - ".$date_min."</td>";
				echo "<td style='text-align:right;'>".round($min_limni,1)."</td>";
			echo "</tr>";
				
		echo "</table>";
		
	echo "</div>";
	
	if(!$print){require(DIR_WS_STATS . 'stats_box_form.php');}
	
		
echo "</div>";



echo "<div id='box_graph_all'>";

	if($nb_data > 0)
	{
		echo "<div id='box_graph' class='gd'>";
			
			echo "<h8>".htmlaccent('Côtes limnimètriques (cm)')."</h8>";
							
			echo "<hr><hr><hr>";
			
				echo "<div id='graph_onglet'>";
						
					echo "<div id='contenu-1' class='contenu'>";
						//echo "<div  class='jqPlot' id='chart1' style='height:100%;width:95%;margin-left:3%;'></div>";
						echo "<div id='container' style='width:95%;height:400px;margin-left:3%;'></div>";
					echo "</div>";
				
					if(!$print)
					{
						echo "<div id='contenu-2' class='contenu'  style='display:none;'>";
							echo affiche_plu_data($string_html_limni,'lm');
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
									echo "<button name='download' onclick='f1.saveImage(getV(this.form.format))'>".htmlaccent('Enregistrer le graphique')."</button>";
								echo "</p>";
							echo "</form>";	
							*/
						//echo "</div>";	
					}
					
				echo "</div>";		
		
		echo "</div>";
		
		
		//require(DIR_WS_STRUCTURE . 'stats_limni_year_tab.php');
		//if($periode == 3){require(DIR_WS_STATS_LIMNI . 'stats_limni_tab.php');echo $edition;}
		
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
	<?php echo "var d1 = [".$string_graph_1."];";?>
				
	// OPTIONS			
	var options = {
					 'shadowSize': 0,
					 'HtmlText': false, 
					 'fontSize':12, 
					 'legend': {'show': false}, 
					 //'xaxis': {'title': 'Date','showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','minTickSize': [1, 'month']},
					 'xaxis': {'showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','minTickSize': [1, 'month']},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0,'min': <?php echo $min_limni*0.9; ?>, 'max': <?php echo $max_limni*1.1; ?>}, 
					 'lines': {'show': true, 'lineWidth': 2, 'fill': true, 'fillOpacity': 0.4}, 
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
																																  										  return 'Hauteur d\'eau : ' + obj.y + ' cm<br>Date: '+date+'-'+month+'-'+fullYear+' '+heures+':'+minutes+':'+sec; 
																																									  }
						 
									}
					 	";
					 }
					 ?>
					 
					
				  
					}
  
	
	f = Flotr.draw($('container'),[{data:d1, color:'#265a31'}],Object.extend(Object.clone(options)));
	 
	 
	function drawGraph(opts){
		
		var o = Object.extend(Object.clone(options), opts || {});
		
		return f = Flotr.draw(
			$('container'), 
			[{data:d1, color:'#265a31'}],
			o
		);
	}	
	
	
	$('container').observe('flotr:select', function(evt){
	
		var area = evt.memo[0];
		
		f = drawGraph({
			xaxis: {min:area.x1, max:area.x2,'mode': 'time', 'timeFormat': '%d-%m-%y'},
			yaxis: {min:area.y1, max:area.y2*1.1,'trackDecimals': 0}
		});
	});
	
	$('container').observe('dblclick', function(){drawGraph()});
	
	/*
	if (Prototype.Browser.IE) 
	{
		var form = $(document.forms['image-download']);
		form.disable().insert({top: "Your browser doesn't allow you to use this feature, sorry :(<br />"});
	}
	*/
	
});
 


  
</script>
