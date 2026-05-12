<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/
$f_y = 0; 
$l_y = 0;
$y = 0;

// Pour fichier stats_debit_tab.php	
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

// initialisation des variables
$min_debit = 0;
$max_debit = 0;
$date_max = '';
$date_min = '';
$nb_data = 0;
$tot_debit_all = 0;
$moy_debit_all = 0;
$last_qte_debit = 0;
$last_qte_debit_lac = 0;
$last_hauteur_lac = 0;
$string_graph_1 = '';
$string_html_debit = '';
$edition = '';

// requête sql pour récupérer le domaine de validité de la courbe de tarage
$sql_jaugeage = "SELECT DISTINCT hauteur_mean FROM ".TABLE_DATA_JAUGEAGE." WHERE id_station=".$select_station. " ORDER BY hauteur_mean DESC LIMIT 1";
$jaugeage_query = tep_db_query($sql_link,$sql_jaugeage);
$jaugeage = tep_db_fetch_array($jaugeage_query);
if(isset($jaugeage)){$max_hauteur_jaugeage=$jaugeage['hauteur_mean'];}
if($math->evaluate('y(x) = '.$equation))
{
	$x = $max_hauteur_jaugeage;
	$max_debit_jaugeage = $math->evaluate("y($x)");		
}


$sql_data = "SELECT DISTINCT date_mesure, heure_mesure, qte as debit, qte_lacune as debit_lac, lacune FROM ".TABLE_DATA_DEBIT. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_p1." 00:00:00' AND date_heure_mesure<='".$date_p2." 23:59:00' GROUP BY date_heure_mesure";
$data_query = tep_db_query($sql_link,$sql_data);	
while($data = tep_db_fetch_array($data_query))
{
	$date_mesure = $data['date_mesure'];
	$heure_mesure = $data['heure_mesure'];
	$data_index = $date_mesure." ".$heure_mesure;
		
	$debit_data=$data['debit'];
	$debit_data_lacune=$data['debit_lac'];
	
	if($debit_data_lacune > $max_debit){$max_debit = $debit_data_lacune;$date_max = dateus_fr($date_mesure).' '.$heure_mesure;}
	if($nb_data==0)
	{
		$min_debit=$max_debit;
		$date_min = dateus_fr($date_mesure).' '.$heure_mesure;
		
		if($data_index <> $date_p1." 00:00:00")
		{
			$data_debit[$date_p1." 00:00:00"] = array('data_index' => $date_p1." 00:00:00",'qte_debit' => 0,'qte_debit_lac' => 0,'lacune' => 0);
			$data_debit[date('Y-m-d h:i:s', strtotime($data_index)-60)] = array('data_index' => date('Y-m-d h:i:s', strtotime($data_index)-60),'qte_debit' => 0,'qte_debit_lac' => 0,'lacune' => 0);
		}
	}
	
	if($debit_data_lacune < $min_debit && $debit_data_lacune != 0){$min_debit = $debit_data_lacune;$date_min = dateus_fr($date_mesure).' '.$heure_mesure;}
	
	//tab debit cumul
	$data_debit[$data_index] = array('data_index' => $data_index,
								  'qte_debit' => $debit_data,
								  'qte_debit_lac' => $debit_data_lacune,
								  'lacune' => $data['lacune']);
	
	$last_qte_debit = $debit_data;
	$last_qte_debit_lac = $debit_data_lacune;
	$last_lacune = $data['lacune'];
	$nb_data++;
}


//echo date('Y-m-d h:i:s', strtotime($data_index)+5);

if($nb_data != 0)
{
	// Préparation des données debitmétrique
	//$data_debit[date('Y-m-d h:i:s', strtotime($date_p1)+60)] = array('data_index' => date('Y-m-d h:i:s', strtotime($date_p1)+60),'qte_debit' => 0,'qte_debit_lac' => 0,'lacune' => 0);	
	$data_debit[$date_p2.' 23:59:00'] = array('data_index' => $date_p2.' 23:59:00','qte_debit' => 0,'qte_debit_lac' => 0,'lacune' => 0);
	
	$debut_lac_0=0;
	$old_cle_time=strtotime($date_p1." 00:01:00")*1000;
	$i=0;
	foreach($data_debit as $cle => $element)
	{
		$indice = ( ($i+1)*100 ) / sizeof($data_debit);
		progression($indice);	
		$i++;
		
		$cle_time = strtotime($cle)*1000;
		
		$round_debit_lac = round($element['qte_debit_lac'],3);
		// En cas de résidu de calcul des débits
		if($round_debit_lac < 0){$round_debit_lac = 0;}
		
		//Visualisation : echo $cle_time.' '.$cle.' - '.$old_cle_time.' '.date('Y-m-d h:i:s', $old_cle_time/1000).'<br>';
				
		
		
		//--------------------------------------------------
		// Engendrer visuellement une lacune automatique si il n'y a pas de données sur une période supérieur à 24h (86400 sec)
		if($cle_time > (($old_cle_time+86400000)))
		{
			// Début lacune auto
			$string_html_debit .= "<tr>";
		
				$string_html_debit .= "<td>".date('Y-m-d h:i:s', $old_cle_time+60000)."</td>";
				
				$string_graph_1 .= "[".($old_cle_time+60000).",0],";
				$string_html_debit .= "<td>0</td>";
				$string_html_debit .= "<td>1</td>";
			
			$string_html_debit .= "</tr>";
			
			// Fin lacune auto
			$string_html_debit .= "<tr>";
		
				$string_html_debit .= "<td>".date('Y-m-d h:i:s', $cle_time-60000)."</td>";
				
				$string_graph_1 .= "[".($cle_time-60000).",0],";
				$string_html_debit .= "<td>0</td>";
				$string_html_debit .= "<td>1</td>";
			
			$string_html_debit .= "</tr>";
		}
		//--------------------------------------------------
		
		
		if($round_debit_lac==0 && $debut_lac_0==1){$debut_lac_0=0;}
		if($round_debit_lac==0 && $debut_lac_0==0){$debut_lac_0=1;}
		if($round_debit_lac!=0 && $debut_lac_0==1)
		{
			$string_html_debit .= "<tr>";
		
				$string_html_debit .= "<td>".$cle."</td>";
				
				$string_graph_1 .= "[".$cle_time.",0],";
				$string_html_debit .= "<td>0</td>";
				$string_html_debit .= "<td>1</td>";
			
			$string_html_debit .= "</tr>";
			
			$debut_lac_0=0;
		}
		
		
		$tot_debit_all += $round_debit_lac;
		
		
		$string_html_debit .= "<tr>";
		
			$string_html_debit .= "<td>".$cle."</td>";
			
			$string_graph_1 .= "[".$cle_time.",".$round_debit_lac."],";
			$string_html_debit .= "<td>".$element['qte_debit']."</td>";
			$string_html_debit .= "<td>".$element['lacune']."</td>";
		
		$string_html_debit .= "</tr>";
		
		
		$old_cle_time = $cle_time;
		
	}
	$string_graph_1 = substr($string_graph_1, 0, -1);
	
	$moy_debit_all = round($tot_debit_all / $nb_data,3);
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
	
	//tab debit cumul
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
	
	if($type_data==3){echo "<input type='hidden' name='button_stats' id='button_stats' value='3'>";}
	if($type_data==2){echo "<input type='hidden' name='button_stats' id='button_stats' value='2'>";}
	
	
	echo "<input type='hidden' name='select_region' id='select_region' value='".$select_region."'>";
	echo "<input type='hidden' name='select_station' id='select_station' value='".$select_station."'>";
	echo "<input type='hidden' name='select_periode' id='select_periode' value='".$periode."'>";
	echo "<input type='hidden' name='select_type_eq' id='select_type_eq' value='2'>";
	//echo "<input type='hidden' name='select_year' id='select_year' value='".$year_stats."'>";
	

 
//echo "<h1 style='background-color:#eaf7ea;'>";
echo "<h1>";
				
	$titre = htmlaccent('Chronique des débits');
	
	//$titre_p = htmlaccent($titre)." - Station : ".$nom_station;
	
	if($print){echo "<span class='print'>".$titre."</span>";}
	else{echo "<span>".$titre."</span>";}
	
	if($nb_data > 0  && !$print)
	{
		//echo button_pdf('export_pdf.php?type=stats&ty=debit&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&date_p1='.$date_p1.'&date_p2='.$date_p2);
		//echo button_xls('export_excel.php?imp=2&ty=debit&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&date_p1='.$date_p1.'&date_p2='.$date_p2);
		echo button_print('print_stats.php?imp=2&ty=debit&print=ok&bs=1&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&eq='.$type_data.'&date_p1='.$date_p1.'&date_p2='.$date_p2,'Tableau');
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
				echo "<td class='bold'>".htmlaccent('Débit moyen (m<sup>3</sup>/s)')."</td>";
				echo "<td style='text-align:right;'>".$moy_debit_all."</td>";
			echo "</tr>";
			
			echo "<tr class='grey'>";
				if($periode==1){echo "<td class='bold'>".htmlaccent('Nombre d\'enregistrements')."</td>";}
				else{echo "<td class='bold'>".htmlaccent('Nombre d\'enregistrements')."</td>";}
				echo "<td style='text-align:right;'>".$nb_data."</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td class='bold'>".htmlaccent('Débit max (m<sup>3</sup>/s)')." - ".$date_max."</td>";
				echo "<td style='text-align:right;'>".round($max_debit,3)."</td>";
			echo "</tr>";
			
			echo "<tr class='grey'>";
				echo "<td class='bold'>".htmlaccent('Débit min (m<sup>3</sup>/s)')." - ".$date_min."</td>";
				echo "<td style='text-align:right;'>".round($min_debit,3)."</td>";
			echo "</tr>";
				
		echo "</table>";
		
	echo "</div>";
	
	if(!$print){require(DIR_WS_STATS . 'stats_box_form.php');}
	
	
	if(!$print){echo "<div id='box_graph' class='lgt'>";}
	else{echo "<div id='box_graph' class='lgt_r'>";}
		  
		echo "<h8>".htmlaccent('Courbes de tarage')."</h8>";
		
		echo "<table id='resume' cellspacing='0'>";
		
		echo "<tr>";
		  echo "<td class='bold'>".htmlaccent('Equation')."</td>";
		  echo "<td class='bold' style='text-align:right;'>".htmlaccent('Débit min <br>(m<sup>3</sup>/s)')."</td>";
		  echo "<td class='bold' style='text-align:right;'>".htmlaccent('Débit max <br>(m<sup>3</sup>/s)')."</td>";
		  echo "<td class='bold' style='text-align:right;'>".htmlaccent('Période de validité')."</td>";
		echo "</tr>";
		
		$debit_min_eq = 0;
		$debit_max_eq = 0;
		
		// requête sql pour récupérer le domaine de validité de la courbe de tarage
		
		$sql_tarage = "SELECT DISTINCT * FROM ".TABLE_DATA_TARAGE." WHERE id_station=".$select_station;
		$tarage_query = tep_db_query($sql_link,$sql_tarage);
		
		while($tarage = tep_db_fetch_array($tarage_query))
		{	
			$id_eq = htmlaccent(html_entity_decode($tarage['id']));
			
			$equation =  $tarage['equation'];
			$equation = str_replace('exp','e^',$equation);
			
			$hauteur_min_eq =  $tarage['debit_min'];
			$hauteur_max_eq =  $tarage['debit_max'];
			$hauteur_mean_eq =  $tarage['debit_mean'];
			
			if($math->evaluate('y(x) = '.$equation))
			{
				$debit_min_eq = $math->evaluate("y($hauteur_min_eq)");
				$debit_max_eq = $math->evaluate("y($hauteur_max_eq)");
			}
						
			$periode_first =  dateus_fr($tarage['date_debut']);
			$periode_end =  dateus_fr($tarage['date_fin']);
			
			echo "<tr>";
			  echo "<td>".$equation."</td>";
			  echo "<td style='text-align:right;'>".round($debit_min_eq,3)."</td>";
			  echo "<td style='text-align:right;'>".round($debit_max_eq,3)."</td>";
			  echo "<td style='text-align:right;'>".$periode_first." / ".$periode_end."</td>";
			echo "</tr>";
			
		}
		
		echo "</table>";
		  
		  
		  
	  echo "</div>";
	
echo "<hr>";		
echo "</div>";



echo "<div id='box_graph_all' style='margin-top:30px;'>";

	if($nb_data > 0)
	{
		echo "<div id='box_graph' class='gd'>";
			
			echo "<h8>".htmlaccent('Chronique des débits (m<sup>3</sup>/s)')."</h8>";
							
			echo "<hr><hr><hr>";
			
				echo "<div id='graph_onglet'>";
						
					echo "<div id='contenu-1' class='contenu'>";
						//echo "<div  class='jqPlot' id='chart1' style='height:100%;width:95%;margin-left:3%;'></div>";
						echo "<div id='container' style='width:95%;height:400px;margin-left:3%;'></div>";
					echo "</div>";
				
					if(!$print)
					{
						echo "<div id='contenu-2' class='contenu'  style='display:none;'>";
							echo affiche_plu_data($string_html_debit,'lm');
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
		
		
		// ne sert à rien parceque les données sont brutes et pas quotidiennes
		/*
		if($periode == 3)
		{
			require(DIR_WS_STATS_DEBIT . 'stats_debit_tab.php');
			if(tep_not_null($edit_tab_html)){echo $edit_tab_html;}
		}
		*/
		
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
					 'xaxis': {'showLabels': true,'mode': 'time', 'timeFormat': '%d-%m-%y','minTickSize': [1, 'month']},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0,'min': <?php echo $min_debit*0.9; ?>, 'max': <?php echo $max_debit*1.1; ?>}, 
					 'lines': {'show': true, 'lineWidth': 2, 'fill': true, 'fillOpacity': 0.4}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 1, 'lineColor': '#dddddd'}, 
					 <?php
					 if(!$print)
					 {
						 echo "
						 'selection': {'mode': 'x', 'color': '#9bc0dd'}, 
						 'crosshair': {'mode': 'x', 'color': '#dddddd', 'hideCursor': true, 'lineWidth': 1}, 
						 'mouse': {'track': true, 'trackAll': true, 'position': 'nw', 'relative': true, 'margin': 40, 'trackDecimals': 3, 'trackFormatter':function dateTracker(obj){
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
																																  										  return 'Débit : ' + obj.y + ' m<sup>3</sup>/s<br>Date: '+date+'-'+month+'-'+fullYear+' '+heures+':'+minutes+':'+sec;  
																																									  }
						 
									}
					 	";
					 }
					 ?>
					 
					
				  
					}
  
	
	f = Flotr.draw($('container'),[{data:d1, color:'#d19c36'}],Object.extend(Object.clone(options)));
	 
	 
	function drawGraph(opts){
		
		var o = Object.extend(Object.clone(options), opts || {});
		
		return f = Flotr.draw(
			$('container'), 
			[{data:d1, color:'#d19c36'}],
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
