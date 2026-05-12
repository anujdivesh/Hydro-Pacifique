<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

$nb_data=0;
$nb_day=0;
$nb_y=0;

$data_raw=array();

$interval=86400; // 86400 secondes = 1 jour

// Variables de date

$year_p1 = $_POST['year_p1'];  
$year_p2 = $_POST['year_p2'];

$date_p1 = $year_p1.'-01-01';
$date_p2 = $year_p2.'-12-31';

$date_heure_p1 = $date_p1." 00:00:00";
$date_heure_p2 = $date_p2." 23:59:00";

$datetime_p1 = strtotime($date_heure_p1);
$datetime_p2 = strtotime($date_heure_p2);




//-----------------------------------------------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------------------------------

$n=0;

// Nombre de données
$nb_data = ($datetime_p2 - $datetime_p1) / $interval;
/*$sql_data_count = "SELECT DISTINCT count(*) as count_data FROM ".TABLE_DATA_DEBIT. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_heure_p1."' AND date_heure_mesure<='".$date_heure_p2."'";
$data_count_query = tep_db_query($sql_link,$sql_data_count);	
$data_count = tep_db_fetch_array($data_count_query);
$nb_data = $data_count['count_data'];*/

$groupby = 'date_mesure';

// Données Brutes
//$sql_data = "SELECT DISTINCT date_mesure, heure_mesure, date_heure_mesure, qte, lacune FROM ".TABLE_DATA_DEBIT." WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_heure_p1."' AND date_heure_mesure<='".$date_heure_p2."' ORDER BY date_heure_mesure";
$sql_data = "SELECT DISTINCT date_mesure, heure_mesure, date_heure_mesure, AVG(qte) as debit, AVG(qte_lacune) as debit_lac, SUM(lacune) as lacune_day FROM ".TABLE_DATA_DEBIT. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_p1." 00:00:00' AND date_heure_mesure<='".$date_p2." 23:59:00' GROUP BY ".$groupby;
$data_query = tep_db_query($sql_link,$sql_data);	
	
while($data = tep_db_fetch_array($data_query))
{		
	$indice = ( ($n+1)*100 ) / $nb_data;
	progression($indice);	
	$n++;
	
	$date_mesure = $data['date_mesure'];
	$heure_mesure = $data['heure_mesure'];
	$date_index = $data['date_heure_mesure'];
		
	$debit_data = $data['debit'];
	$lacune = $data['lacune_day'];
	
	//$debit_data=0;
	//if($qte_limni>0){$debit_data = $math->evaluate("y($qte_limni)");}	
	
	$data_raw[] = array('date_index' => $date_index,'debit_data' => $debit_data,'lacune'=>$lacune);		
}


//-----------------------------------------------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------------------------------
// Algorithme de traitement de données

$datetime_inf = $datetime_p1;
$datetime_sup = $datetime_inf + $interval;
	
$i=0;
$max_debit=0;
$year_encours = $year_p1;

while($datetime_inf < $datetime_p2)
{		
	$debit_data=0;
	$mean_debit=0;
	$lacune=0;
	$nb_data_bytime=0;
			
			
	if(isset($data_raw) && $n>0)
	{
		while((strtotime($data_raw[$i]['date_index']) >= $datetime_inf) && (strtotime($data_raw[$i]['date_index']) < $datetime_sup))
		{
			$debit_data += $data_raw[$i]['debit_data'];	
			$lacune += $data_raw[$i]['lacune'];
																
			if($i==(sizeof($data_raw)-1)){break;}
			$nb_data_bytime++;
			$i++;	
		}
		if($nb_data_bytime>0){$mean_debit =  round($debit_data/$nb_data_bytime,3);}
		//if($lacune>0){$mean_debit = 0;}
	
	}
	
	
	// Save data
	$date_inf = date("Y-m-d", $datetime_inf);	
	$year_inf = date("Y", $datetime_inf);
	
	// Date de la mesure maximum de l'année
	if($year_inf != $year_encours)
	{
		$max_debit=$mean_debit;
	}
	else
	{
		if($max_debit < $mean_debit)
		{
			$year_tab_max[$year_inf]=$date_inf;
			$max_debit = $mean_debit;
		}
	}
	$year_encours = $year_inf;
		
	$data_debit[$date_inf] = array('year_m' => $year_inf,'debit' => $mean_debit,'lacune' => $lacune);
			
	// Réaffectation des dates
	$datetime_inf = $datetime_sup;
	$datetime_sup += $interval;
}

ksort($data_debit);

//-----------------------------------------------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------------------------------



// Load liste lacunes

$lacune_load=false;

$sql_listlac = "SELECT DISTINCT * FROM ".TABLE_DATA_LACUNE_LIMNI. " WHERE station_lacune='".$select_station."' AND ((date_deb_lacune>='".$date_p1."' AND date_deb_lacune<='".$date_p2."') OR (date_fin_lacune>='".$date_p1."' AND date_fin_lacune<='".$date_p2."')) ORDER BY date_deb_lacune, heure_deb_lacune";
$listlac_query = tep_db_query($sql_link,$sql_listlac);	
while($listlac = tep_db_fetch_array($listlac_query))
{
	$date_deb_lacune = dateus_fr($listlac['date_deb_lacune']);
	$heure_deb_lacune = $listlac['heure_deb_lacune'];
	$time_deb = $date_deb_lacune." ".$heure_deb_lacune;
	
	$date_fin_lacune = dateus_fr($listlac['date_fin_lacune']);
	$heure_fin_lacune = $listlac['heure_fin_lacune'];
	$time_fin = $date_fin_lacune." ".$heure_fin_lacune;
	
	//$cumul_lacune = htmlaccent(html_entity_decode($listlac['cumul_lacune']));
	$observation_lacune = htmlaccent(html_entity_decode($listlac['observation_lacune']));
	
	//tab pluvio cumul
	$data_list_lacunes[] = array('id' => $listlac['id'],
							'date_deb' => $date_deb_lacune,
							'time_deb' => $time_deb,							
							'date_fin' => $date_fin_lacune,
							'time_fin' => $time_fin,
							//'cumul_lacune' => $cumul_lacune,
							'observation_lacune' => $observation_lacune);
							
	$lacune_load=true;						
}

//-----------------------------------------------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------------------------------

$year_encours=0;

echo "<form name='stats_select' action='stats_result.php' method='post' enctype='multipart/form-data' onSubmit='return verif_dates_stats();'>";

	echo "<input type='hidden' name='button_stats' id='button_stats' value='1'>";
	
	
	echo "<input type='hidden' name='select_region' id='select_region' value='".$select_region."'>";
	echo "<input type='hidden' name='select_station' id='select_station' value='".$select_station."'>";
	echo "<input type='hidden' name='select_periode' id='select_periode' value='".$periode."'>";
	echo "<input type='hidden' name='select_type_eq' id='select_type_eq' value='1'>";
	//echo "<input type='hidden' name='select_year' id='select_year' value='".$year_stats."'>";
	

 
echo "<h1 style='margin-bottom:20px;'>";
				
	$titre = htmlaccent('Tableaux des débits moyens journaliers');
	$titre_p = htmlaccent($titre)." - Station : ".$nom_station;
	
	if($print){echo "<span class='print'>".$titre_p."</span>";}
	else{echo "<span>".$titre."</span>";}
	
	if($nb_data > 0  && !$print)
	{
		echo button_pdf('export_pdf.php?type=stats&ty=debittabday&periode=5&il='.$select_region.'&st='.$select_station.'&date_p1='.$year_p1.'&date_p2='.$year_p2);
	}
	
echo "<hr>"; 	
echo "</h1>"; 	
	

echo "<div id='box_graph_all' >";

	echo "<div id='box_graph' class='lgt'>";
		
		require(DIR_WS_STATS . 'stats_box_info.php');
	
	echo "</div>";
	
	echo "<div id='box_graph' class='lgt_r'>";
	
		echo "<h8>".htmlaccent('Résumé')."</h8>";
		
		echo "<table id='resume' cellspacing='0'>";
			
			echo "<tr class='grey'>";
				echo "<td class='bold'>".htmlaccent('Période')."</td>";
				echo "<td style='text-align:right;'>".$year_p1." à ".$year_p2."</td>";
			echo "</tr>";
			
				
		echo "</table>";		
		
	echo "</div>";
	
		
	// VALIDITER DE La COURBE DE TARAGE
	/*
	echo "<div id='box_graph' class='lgt_r'>";
		  
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
	
		
echo "</div>";


$tab_mois = array('janv.','f&eacute;v.','mars','avr.','mai','juin','juil.','ao&ucirc;t','sept.','oct.','nov.','d&eacute;c.');


$edit_tab_resume_html = '';
$edit_tab_html = '';

// Construction du tableau résumé pour toutes les années concernées
$edit_tab_resume_html .= "<hr><hr><hr>";

$edit_tab_resume_html .= "<div id='box_graph' class='gd'>";

	$edit_tab_resume_html .= "<h8>".htmlaccent($year_p1.' à '.$year_p2.' - Moyennes mensuelles (m<sup>3</sup>/s)')."</h8>";
	$edit_tab_resume_html .= "<hr><hr><hr>";
	
	$edit_tab_resume_html .= "<table id='stats_tab' cellspacing='0'>";
	
		$edit_tab_resume_html .= "<tr>";
			$edit_tab_resume_html .= "<td class='top' style='border-top:none;'>&nbsp;</td>";
			for($m=0;$m<sizeof($tab_mois);$m++)
			{
				$edit_tab_resume_html .= "<td class='top' style='border-top:none;'>".$tab_mois[$m]."</td>";
			}
			$edit_tab_resume_html .= "<td class='top' style='border-top:none;'>&nbsp;</td>";
		$edit_tab_resume_html .= "</tr>";
		
		$edit_tab_resume_html .= "<tr><td colspan='14'>&nbsp;</td></tr>";


// Construction de tous les tableaux

echo "<div id='box_graph_all'>";

	if($nb_data > 0)
	{
		
		for($y=$year_p2;$y>=$year_p1;$y--)
		{
			$date_max = 0;
			
			$style_resume='';
			if($y%2==0){$style_resume = "style='border-top:none;background-color:#E4E4E4;'";}
			
			if(isset($year_tab_max[$y])){$date_max = dateus_fr($year_tab_max[$y]);}
			require(DIR_WS_STATS_DEBIT . 'stats_debit_tab.php');
		}
		
				$edit_tab_resume_html .= "<tr><td colspan='14'>&nbsp;</td></tr>";
			$edit_tab_resume_html .= "</table>";
		$edit_tab_resume_html .= "<hr>";
		$edit_tab_resume_html .= "</div>";
		$edit_tab_resume_html .= "<hr><hr><hr>";
		
		echo $edit_tab_resume_html;
		echo $edit_tab_html;
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
