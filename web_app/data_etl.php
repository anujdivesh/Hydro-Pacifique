<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
ETL - List (ETL : relation d'Etalonnage pour convertir les hauteurs d'eau en débit)
Cette page permet d'afficher la liste des stations Hydrométriques 
et de proposer un accès aux courbes d'ETL.

----------------------------------------
*/

require('include/application_top.php');

//$message_suprr_station = '';
$row = 0;

// -----------------------------
// Initialisation des Var.

// SELECT permettant de n'avoir que les stations qui ont au moins un ETL
$etl_exist = 2;
$having_nbetl = " HAVING nb_etl>0";
if(isset($_POST['etl_exist']))
{
	$etl_exist = $_POST['etl_exist'];
	if($etl_exist==1){$having_nbetl = "";}
}


// SELECT POUR LE TRI
// TRI (Nom station, Code Station, Commune, Type_DATA)
$tri_encours = 1;
$tri = "s.nom_station";
if(isset($_POST['select_tri']))
{
	$tri_encours = $_POST['select_tri'];
	if($_POST['select_tri'] == 2){$tri = "s.code_station";} // tri code station
}

$tri_order_encours = 1;
$tri_order = " ASC,";
if(isset($_POST['order_tri']))
{
	$tri_order_encours = $_POST['order_tri'];
	if($_POST['order_tri'] == 2){$tri_order = " DESC,";} // tri décroissant
}


// Initialisation des var. pour les filtres les plus commun
$affiche_select_type = false;
$affiche_select_tournee = true;
$affiche_search = true;
$affiche_select_riviere = false;
$affiche_select_station = false;
$affiche_select_statut_station = false;
require(DIR_WS_FILTRE . 'filtre_stations_var.php');

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

/* REQUETE SQL - DATA STATION */
$station_array = [];
$nb_station = 0;
$nb_station_active = 0;
$nb_station_suivi = 0;
$nb_station_armee = 0;

$where_and_type = " AND s.station_type=11"; // Uniquement des stations hydrométriques

$sql_station = "SELECT s.id_station, s.id_commune, s.nom_station, s.code_station,
								s.active_station, s.suivi, s.armee,
        						COUNT(etl.id) AS nb_etl 
				FROM ".TABLE_STATION." s		
				LEFT JOIN ".TABLE_COMMUNE." c ON s.id_commune=c.id_commune
				LEFT JOIN ".TABLE_DATA_ETL." etl ON etl.id_station = s.id_station 
				WHERE s.id_territoire=".$territoire_id.
					$where_and_type.
					$where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_tournee.
					$where_and_active.$where_and_suivi.$where_and_armee." 
				GROUP BY s.id_station
				".$having_nbetl."
				ORDER BY ".$tri.$tri_order." s.active_station ASC, s.suivi DESC, s.armee ASC";

$station_query = tep_db_query($sql_link,$sql_station);
while ($station = tep_db_fetch_array($station_query))
{	
	$id_region =  html_entity_decode($station['id_region'] ?? $default_string);
	$id_commune =  html_entity_decode($station['id_commune'] ?? $default_string);	
	$nom_station =  htmlaccent(html_entity_decode($station['nom_station'] ?? $default_string));
	$code_station =  htmlaccent(html_entity_decode($station['code_station'] ?? $default_string));
	$nb_etl = $station['nb_etl'];

	$active_station = 0;
	if($station['active_station'] == 1)
	{
		$active_station = 1;
		$nb_station_active++;
	}

	$suivi_station = 0;
	if($station['suivi'] == 1)
	{
		$suivi_station = 1;
		$nb_station_suivi++;
	}
	
	$armee_station = 0;
	if($station['armee'] == 1)
	{
		$armee_station = 1;
		$nb_station_armee++;
	}
		
	$station_array[$station['id_station']] = array(
													'active_station' => $active_station,							 
													'suivi_station' => $suivi_station,                             						 
													'armee_station' => $armee_station,
													'nom_station' => $nom_station,
													'code_station' => $code_station,
													'nb_etl' => $nb_etl
													);
}
$nb_stations = sizeof($station_array);	


// Nombre de JGE 
// On ne s'intéresse qu'aux stations hydrométriques  
$sql_jge = "SELECT COUNT(*) as nb_jge, s.id_station 
			FROM ".TABLE_DATA_JGE." jge			
			JOIN ".TABLE_STATION." s ON jge.id_station=s.id_station
			WHERE 1=1 
			AND jge.depouil_hmoy REGEXP '^-?[0-9]+(\.[0-9]+)?$'  -- Vérifie si c'est un nombre
			AND jge.depouil_hmoy < 9999
            AND jge.depouil_q REGEXP '^-?[0-9]+(\.[0-9]+)?$'       -- Vérifie si c'est un nombre
			".
			$where_and_type.
			$where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_tournee.
			$where_and_active.$where_and_suivi.$where_and_armee." 
			GROUP BY jge.id_station";

$jge_query = tep_db_query($sql_link,$sql_jge);
while ($nb_jge_tab = tep_db_fetch_array($jge_query))
{	
	$nb_jge_array[$nb_jge_tab['id_station']] = array('nb_jge' => $nb_jge_tab['nb_jge']);
}




//---------------------------------------------------------------
// EDITION HTML

require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";	

	echo "<div id='contenu_centre'>";
		
		echo "<div id='contenu_box2'>";
		
			echo "<h1>";
				
				echo "<span>".htmlaccent('Relation d\'Etalonnage des Stations Hydrométriques')."</span>";
				//echo button_pdf('export_pdf.php?type=list_st');

			echo "</h1>";

				$lien_form = tep_href_link('data_etl.php');
				$name_form = 'form_select_etl';			
				echo "<form name='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data'>";
					  
					echo "<div id='cadre_graph' style='float:left;width:250px;margin-right:5px;height:70vh;overflow-y: auto;'>\n"; 
					
						echo "<div id='boxpopup' class='select-top' style='width:210px;padding: 0 10px;'>\n";

							echo "<p style='float:left;padding-top:5px;color:#186F65;margin-top:15px;'>".htmlaccent('Courbes ETL')."</p>";

							echo "<select name='etl_exist' id='etl_exist' onchange='".$name_form.".submit();' style='float:right;width:120px;margin-top:15px;'>";
								
								$selected = ($etl_exist == 1) ? "selected" : "";
								echo "<option value='1' ".$selected.">"."Toutes les stations"."</option>";
								$selected = ($etl_exist == 2) ? "selected" : "";
								echo "<option value='2' ".$selected.">"."Stations ETL"."</option>";
									
							echo "</select>";
			
							echo "<hr>";

							require(DIR_WS_FILTRE . 'filtre_stations_html.php');

							echo "<hr>";

							// TRI DE LA TABLE		
							echo "<div style='width:100%;border-bottom:2px solid #176B87;margin-top:15px;'></div>";
											
							echo "<p style='float:left;width:60px;padding-top:5px;color:#186F65;margin-top:15px;'>".htmlaccent('TRIER PAR')."</p>";

							echo "<select name='select_tri' id='select_tri' onchange='".$name_form.".submit();' style='float:right;width:140px;margin-top:15px;'>";
								
								$selected = ($tri_encours == 1) ? "selected" : "";
								echo "<option value='1' ".$selected.">".htmlaccent('Nom de la station')."</option>";
								$selected = ($tri_encours == 2) ? "selected" : "";
								echo "<option value='2' ".$selected.">".htmlaccent('Code de la station')."</option>";
									
							echo "</select>";
							
							echo "<hr>";

							echo "<div style='float:right;'>";

								// Déterminer la valeur de l'attribut "checked" en fonction de $tri_order_encours
								$asc_checked = ($tri_order_encours == 1) ? "checked" : "";
								$desc_checked = ($tri_order_encours == 2) ? "checked" : "";

								echo "<p style='float:left;width:55px;padding-top:3px;'>".htmlaccent('Croissant')."</p>";
								echo "<input type='radio' id='asc' name='order_tri' value='1' style='float:left;' ".$asc_checked." onchange='".$name_form.".submit();' >";

								echo "<p style='float:left;width:65px;margin-left:10px;padding-top:3px;'>".htmlaccent('Décroissant')."</p>";
								echo "<input type='radio' id='desc' name='order_tri' value='2' style='float:left;' ".$desc_checked." onchange='".$name_form.".submit();' >";

							echo "</div>";

		
							echo "<div id='contenu_infos' style='width:auto;'>";
										
								echo "<p>";
									echo "<span>";
										echo "Nombre de stations : ".number_format($nb_stations,0,'.',' ');
									echo "</span>";
								echo "</p>";
							
							echo "</div>";

							echo "<hr>";
				
						echo "</div>";	
					
					echo "</div>";
				
				echo "</form>"; // Fin du formulaire
			
			
			
			
			
			// ----------------------------------------------------------------------------------------		
			// TABLEAU GENERAL STATIONS - Permet d'afficher la liste des Stations
			if(isset($station_array) && ($nb_stations>0))
			{
				echo "<div class='table-container' style='float:none;width:auto;height:80vh;'>";

					echo "<div style='width:auto;height:78vh;overflow-y: auto;'>";
						echo "<table id='table_tri' cellspacing='0' style=''>";
					
							echo "<thead>";
								echo "<tr class='header-row'>";
																	
									echo "<th style='width:60px;text-align:center;' title=\""."Active ou Historique (Fermée)"."\">"."Statut"."</th>";
									echo "<th style='width:60px;text-align:center;' title=\""."Mesures en continue ou Mesures Ponctuelles"."\">"."Suivi"."</th>";	
									//echo "<th style='width:60px;text-align:center;padding-left:5px;' title=".htmlaccent('En fonctionnement ou en panne').">".htmlaccent('Etat')."</th>";	
									echo "<th style='width:400px;padding-left:15px;'>"."Station (Code - Nom)"."</th>";							
									echo "<th style='width:80px;text-align:center;' title=\""."Nombre de Jaugeages Valides"."\">"."Nb JGE"."</th>";
									echo "<th style='width:80px;text-align:center;' title=\""."Nombre de Relation d''Etalonnage"."\">"."Nb ETL"."</th>";
									echo "<th style='width:80px;text-align:center;' title=\""."Editer la Relation d'Etalonnage (ETL)"."\">";
										echo "Courbe ETL";
									echo "</th>";
									echo "<th style='width:80px;text-align:center;' title=\""."Convertir des Hauteurs d'eau en Débits"."\">";
										echo "H -> Q";
									echo "</th>";

								echo "</tr>";
							echo "</thead>";
					
							//ligne vide dans le tableau		
								
							echo "<tr>";
								echo "<td colspan='6' style='height:10px;'>&nbsp;</td>";
							echo "</tr>";	
								
							foreach($station_array as $key => $value)
							{								
								$nb_jge=0;
								if(isset($nb_jge_array[$key])){$nb_jge=$nb_jge_array[$key]['nb_jge'];}

								$nb_etl=$value['nb_etl'];
							
								if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
								else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
															
								echo "<tr ".$row_l." >";
								
									//Statut Station
									echo "<td style='text-align:center;'>";
											
										if($value['active_station'] === 1){
											echo "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title=\""."En activité"."\">";
										}
										else{
											echo "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' title=\""."Historique (Fermée)"."\">";
										}

									echo "</td>\n";
									
									// Suivi Station
									echo "<td style='text-align:center;' >";

										if($value['suivi_station'] === 1){
											echo "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title=\""."Mesures en continu"."\">";									
										}
										else{
											echo "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' title=\""."Mesures ponctuelles"."\">";	
										}

									echo "</td>\n";
									
									$lien_modif = "modif_etl.php?st=".$key;								
									echo "<td style='padding-left:15px;'>";
										echo "<a href=".$lien_modif ."  target='_blank' title=\"".$value['nom_station']."\">";
											echo $value['code_station']." - ".affichelettres($value['nom_station'],50);
										echo "</a>\n";				
									echo "</td>\n";			
																	
									
									echo "<td style='text-align:center;' >".$nb_jge."</td>\n";
									echo "<td style='text-align:center;' >".$nb_etl."</td>\n";

									

									$lien_modif = "modif_etl.php?st=".$key;		
									echo "<td style='text-align:center;cursor:pointer;' >";
										if($nb_jge>0 || $nb_etl>0)
										{
											echo "<a href='".$lien_modif."' target='_blank' 
												title=\""."Editer la Relation d'Etalonnage (ETL)"."\">"; 
												echo "<img src='".DIR_WS_IMG_ICO."reg.png' style='width:30px;'>";							
											echo "</a>";
										}
									echo "</td>\n";


									$lien_modif = "convert_hq.php?st=".$key;		
									echo "<td style='text-align:center;cursor:pointer;' >";
										if($nb_etl>0)
										{
											echo "<a href='".$lien_modif."' target='_blank' 
												title=\""."Convertir Hauteurs d'eau en Débits"."\">"; 
												echo "<img src='".DIR_WS_IMG_ICO."hq.png' style='width:30px;'>";							
											echo "</a>";
										}
										else{echo '-';}
									echo "</td>\n";

								echo "</tr>\n";

								$row++;
							}

						echo "</table>";
					echo "</div>";
					
				echo "</div>";
			}
			else
			{
				echo "<div id='boxpopup' >\n";
						echo "<p class='alert'>".htmlaccent('Aucune Station n\'a été trouvée')."</p>";
				echo "</div>";
			}
			
		
			
		
		echo "<hr>";
		echo "</div>";
		
		
	echo "<hr>";
	echo "</div>";
	
	
	echo "<hr>";
echo "</div>";
	require('include/application_bottom.php'); 
echo "</body>";

echo "</html>";

?>	
