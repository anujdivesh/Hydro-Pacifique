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


$groupby = 'date_mesure';
// Données Brutes
$sql_data = "SELECT DISTINCT date_mesure, heure_mesure, date_heure_mesure, SUM(qte) as data_plu, SUM(qte_lacune) as data_plu_lac, SUM(lacune) as plu_lac FROM ".TABLE_DATA_PLUVIO." WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_heure_p1."' AND date_heure_mesure<='".$date_heure_p2."' GROUP BY ".$groupby;
$data_query = tep_db_query($sql_link,$sql_data);	

while($data = tep_db_fetch_array($data_query))
{		
	$indice = ( ($n+1)*100 ) / $nb_data;
	progression($indice);	
	$n++;
	
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

$datetime_inf = $datetime_p1;
$datetime_sup = $datetime_inf + $interval;
	
$cumul_pluvio=0;
$cumul_pluvio_lac=0;

$i=0;
$max_pluvio=0;
$year_encours = $year_p1;

while($datetime_inf < $datetime_p2)
{
	
	$qte_plu = 0;
	$qte_plu_lac = 0;
	$lacune = 0;
	$lacune_encours = false;
	
	if(isset($data_raw) && sizeof($data_raw)>0)
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
	
	// Lacunes 
	if($lacune_encours)
	{
		if($lacune>0){$lacune_encours=false;$lacune=1;}
		else{$lacune=1;}
	}
	else{if($lacune>0){$lacune_encours=true;$lacune=1;}}
	
	
	// Save data
	$date_inf = date("Y-m-d", $datetime_inf);	
	$year_inf = date("Y", $datetime_inf);
	
	// Date de la mesure maximum de l'année
	if($year_inf != $year_encours)
	{
		$max_pluvio=$qte_plu_lac;
	}
	else
	{
		if($max_pluvio < $qte_plu_lac)
		{
			$year_tab_max[$year_inf]=$date_inf;
			$max_pluvio = $qte_plu_lac;
		}
	}
	$year_encours = $year_inf;
		
	$data_pluvio[$date_inf] = array('year_m' => $year_inf,'heure_m' => '00:00:00','qte_plu' => $qte_plu,'qte_plu_lac' => $qte_plu_lac,'cumul_pluvio' => $cumul_pluvio,'cumul_pluvio_lac' => $cumul_pluvio_lac,'lacune' => $lacune);
			
	// Réaffectation des dates
	$datetime_inf = $datetime_sup;
	$datetime_sup += $interval;
}

ksort($data_pluvio);

//-----------------------------------------------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------------------------------


// Load liste lacunes
$lacune_load=false;

$sql_listlac = "SELECT DISTINCT * FROM ".TABLE_DATA_LACUNE. " WHERE station_lacune='".$select_station."' AND ((date_deb_lacune>='".$date_p1."' AND date_deb_lacune<='".$date_p2."') OR (date_fin_lacune>='".$date_p1."' AND date_fin_lacune<='".$date_p2."')) ORDER BY date_deb_lacune, heure_deb_lacune";
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
							'date_deb' => $date_deb_lacune,
							'time_deb' => $time_deb,
							'date_fin' => $date_fin_lacune,
							'time_fin' => $time_fin,
							'cumul_lacune' => $cumul_lacune,
							'observation_lacune' => $observation_lacune);
							
	$lacune_load=true;						
}



echo "<form name='stats_select' action='stats_result.php' method='post' enctype='multipart/form-data' onSubmit='return verif_dates_stats();'>";

echo "<input type='hidden' name='button_stats' id='button_stats' value='1'>";


echo "<input type='hidden' name='select_region' id='select_region' value='".$select_region."'>";
echo "<input type='hidden' name='select_station' id='select_station' value='".$select_station."'>";
echo "<input type='hidden' name='select_periode' id='select_periode' value='".$periode."'>";
echo "<input type='hidden' name='select_type_eq' id='select_type_eq' value='1'>";
//echo "<input type='hidden' name='select_year' id='select_year' value='".$year_stats."'>";



echo "<h1 style='margin-bottom:20px;'>";
		
	$titre = htmlaccent('Tableaux des précipitations journalières');
	$titre_p = htmlaccent($titre)." - Station : ".$nom_station;
	
	if($print){echo "<span class='print'>".$titre."</span>";}
	else{echo "<span>".$titre."</span>";}
	
	if($cumul_pluvio > 0  && !$print)
	{
		echo button_pdf('export_pdf.php?type=stats&ty=plutabday&periode=5&il='.$select_region.'&st='.$select_station.'&date_p1='.$year_p1.'&date_p2='.$year_p2);
		//echo button_xls('export_excel.php?imp=2&ty=plu&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&date_p1='.$date_p1.'&date_p2='.$date_p2);
		
		//echo button_print('print_stats.php?imp=2&ty=plu&print=ok&bs=1&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&eq=1&date_p1='.$date_p1.'&date_p2='.$date_p2,'Tableau');
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
				echo "<td style='text-align:right;'>".$year_p1." à ".$year_p2."</td>";
			echo "</tr>";
				
		echo "</table>";		
		
	echo "</div>";
	
		
echo "</div>";


$tab_mois = array('janv.','f&eacute;v.','mars','avr.','mai','juin','juil.','ao&ucirc;t','sept.','oct.','nov.','d&eacute;c.');


$edit_tab_resume_html = '';
$edit_tab_html = '';

// Construction du tableau résumé pour toutes les années concernées
$edit_tab_resume_html .= "<hr><hr><hr>";

$edit_tab_resume_html .= "<div id='box_graph' class='gd'>";

	$edit_tab_resume_html .= "<h8>".htmlaccent($year_p1.' à '.$year_p2.' - Cumuls mensuels (mm)')."</h8>";
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

	if($cumul_pluvio > 0)
	{
		
		for($y=$year_p2;$y>=$year_p1;$y--)
		{
			$date_max = 0;
			
			$style_resume='';
			if($y%2==0){$style_resume = "style='border-top:none;background-color:#E4E4E4;'";}
			
			if(isset($year_tab_max[$y])){$date_max = dateus_fr($year_tab_max[$y]);}
			require(DIR_WS_STATS_PLU . 'stats_plu_tab.php');
			
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
