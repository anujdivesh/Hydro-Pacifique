<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Affichage de la Liste des stations avec options de sélection
Sur la colonne de gauche on retrouve les filtres et le tri
----------------------------------------
*/

//---------------------------------------------------------------
// Appel du fichier contenant les infos de connexions et de configuration chargée à chaque page
require('include/application_top.php');

// -----------------------------
// Initialisation des Var.

$message_suprr_station = '';
$message_modifEtat_station = '';
$row = 0;



// Lien vers le fichier permettant de supprimer une station
if(isset($_GET['del']) && tep_not_null($_GET['del'])){require(DIR_WS_SUPPRIMER . 'suppr_station.php');}



//---------------------------------------------------------------
// Récupération des champs de formulaires pour sélection


// SELECT POUR LE TRI
// TRI (Nom station, Code Station, Commune, Type_DATA)
$tri_encours = 1;
$tri = "s.nom_station";
if(isset($_POST['select_tri']))
{
	$tri_encours = $_POST['select_tri'];
	if($_POST['select_tri'] == 1){$tri = "s.nom_station";} // tri nom station
	if($_POST['select_tri'] == 2){$tri = "s.code_station";} // tri code station
	if($_POST['select_tri'] == 3){$tri = "c.nom_commune";} // tri commune station
	if($_POST['select_tri'] == 4){$tri = "s.station_type";} // tri type data (Pluie, Hydro, Piezo)
}

$tri_order_encours = 1;
$tri_order = " ASC,";
if(isset($_POST['order_tri']))
{
	$tri_order_encours = $_POST['order_tri'];
	if($_POST['order_tri'] == 1){$tri_order = " ASC,";} // tri croissant
	if($_POST['order_tri'] == 2){$tri_order = " DESC,";} // tri décroissant
}


// Initialisation des var. pour les filtres les plus commun
$affiche_select_type = true;
$affiche_select_tournee = true;
$affiche_search = true;
$affiche_select_riviere = false;
$affiche_select_station = false;
$affiche_select_statut_station = true;
require(DIR_WS_FILTRE . 'filtre_stations_var.php');


// Mise à jour des données Statut / Suivi / Etat des stations
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    // Parcourir toutes les clés dans $_POST
    foreach ($_POST as $key => $value) 
	{
        // Vérifier si la clé commence par 'check_active_' et si la case est cochée
        if (strpos($key, 'check_active_') === 0) 
		{            
            $check_id = substr($key, strlen('check_active_')); // Extraire l'ID de la station à partir de la clé
			// Vérifier si cette case est cochée
            $active_check = $_POST['check_active_'.$check_id];
			$suivi_check = isset($_POST['check_suivi_'.$check_id]) ? 1 : 0;
			$armee_check = isset($_POST['check_armee_'.$check_id]) ? 1 : 0;

			$query = "UPDATE ".TABLE_STATION." SET active_station=".$active_check.", 
												suivi=".$suivi_check.", 					
												armee=".$armee_check."
											WHERE id_station=".$check_id;
			tep_db_query($sql_link, $query);	

        }
    }
}

//---------------------------------------------------------------
// TABLE SQL - Recupération de la listes des Stations pour affichage

$station_array = [];
$nb_station = 0;
$nb_station_active = 0;
$nb_station_suivi = 0;
$nb_station_armee = 0;

$sql_station = "SELECT DISTINCT s.id_station, s.id_station_old, s.id_region, s.id_commune, s.nom_station, s.code_station, s.vallee_station, 
								s.date_installation_station, s.date_fermeture_station, s.active_station, s.suivi, s.armee, s.station_type,
								s.id_tournee, s.id_regionhydro, s.id_riviere
				FROM ".TABLE_STATION." s
				LEFT JOIN ".TABLE_STATION_TO_TOURNEE." st ON st.id_station = s.id_station		
				WHERE s.id_territoire=".$territoire_id.
					$where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_type.$where_and_tournee.
					$where_and_active.$where_and_suivi.$where_and_armee." 
				ORDER BY ".$tri.$tri_order." s.active_station DESC, s.suivi DESC, s.armee ASC";

$station_query = tep_db_query($sql_link,$sql_station);
while ($station = tep_db_fetch_array($station_query))
{	
	$id_region =  $station['id_region'];
	$id_eq_type =  $station['station_type'];
	$nom_station =  htmlaccent(html_entity_decode($station['nom_station'] ?? $default_string));
	$code_station =  htmlaccent(html_entity_decode($station['code_station'] ?? $default_string));

	$id_commune = $station['id_commune'];
	$nom_commune = '';
	if(isset($commune_array[$id_commune])){$nom_commune = $commune_array[$id_commune];}

	$id_region = $station['id_region'];
	$nom_region = '';
	if(isset($region_array[$id_region])){$nom_region = $region_array[$id_region];}


	//$vallee_station =  htmlaccent(html_entity_decode($station['vallee_station'] ?? $default_string));
	$id_regionhydro = $station['id_regionhydro'];
	$nom_regionhydro = '';
	if(isset($regionhydro_array[$id_regionhydro])){$nom_regionhydro = $regionhydro_array[$id_regionhydro];}

	$id_riviere = $station['id_riviere'];
	$nom_riviere = '';
	if(isset($riviere_array[$id_riviere])){$nom_riviere = $riviere_array[$id_riviere];}

	$date_installation_station =  dateus_fr($station['date_installation_station']);
	$date_fermeture_station =  dateus_fr($station['date_fermeture_station']);
		
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
	
	// Incrémentation d'un tableau avec les données de chaque station à aficher dans la page
		
	$station_array[$station['id_station']] = array('id_old' => $station['id_station_old'],
													'active_station' => $active_station,							 
													'suivi_station' => $suivi_station,                             						 
													'armee_station' => $armee_station,
													'nom_station' => $nom_station,
													'code_station' => $code_station,
													'id_eq_type' => $id_eq_type,
													'nom_region' => $nom_region,
													'nom_regionhydro' => $nom_regionhydro,
													'nom_riviere' => $nom_riviere,
													'id_commune' => $id_commune,
													'nom_commune' => $nom_commune,
													'id_region' => $id_region,
													'date_installation_station' => $date_installation_station,
													'date_fermeture_station' => $date_fermeture_station
													);
	
}
$nb_station = sizeof($station_array);	


// Nbre Fichier de données / TABLE Meta
$sql_meta_data = "SELECT COUNT(DISTINCT m.id_typedata) AS nb_diff_typedata, s.id_station 
				FROM ".TABLE_DATA_META." m
				JOIN ".TABLE_STATION." s ON m.id_station=s.id_station 
				JOIN ".TABLE_REGION." r ON s.id_region=r.id_region 							
				JOIN ".TABLE_COMMUNE." c ON s.id_commune=c.id_commune 
				WHERE r.id_territoire=".$territoire_id.$where_search.$where_and_type.$where_and_region.$where_and_commune.$where_and_active." 
				GROUP BY m.id_station";

$meta_query = tep_db_query($sql_link,$sql_meta_data);
while ($meta_tab = tep_db_fetch_array($meta_query))
{	
	$nb_meta_array[$meta_tab['id_station']] = array('nb_diff_typedata' => $meta_tab['nb_diff_typedata']);
}


// Nombre de RA 
$sql_ra = "SELECT COUNT(*) as nb_ra, s.id_station, MAX(ra.date_heure_ra) AS date_heure_ra_recente
			FROM ".TABLE_DATA_RA." ra			
			JOIN ".TABLE_STATION." s ON ra.id_station=s.id_station
			JOIN ".TABLE_REGION." r ON s.id_region=r.id_region 					
			JOIN ".TABLE_COMMUNE." c ON s.id_commune=c.id_commune 
			WHERE r.id_territoire=".$territoire_id.$where_search.$where_and_type.$where_and_region.$where_and_commune.$where_and_active." 
			GROUP BY ra.id_station";

$ra_query = tep_db_query($sql_link,$sql_ra);
while ($nb_ra_tab = tep_db_fetch_array($ra_query))
{	
	$nb_ra_array[$nb_ra_tab['id_station']] = $nb_ra_tab['nb_ra'];

	$date_heure_ra_recente_tab =  explode(' ',$nb_ra_tab['date_heure_ra_recente']);
	$date_heure_ra_recente =  dateus_fr($date_heure_ra_recente_tab[0]);
	$last_ra_array[$nb_ra_tab['id_station']] = $date_heure_ra_recente;
}


// ---------------------------------------------------

// EDITION HTML

// Indication d'affichage de la page en HTML
require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

	echo "<div id='contenu_info' style='display:none;'></div>";

	require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
	include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

	echo "<div id='contour_general'>";	
		
		echo "<div id='contenu_centre'>";
			
			//FORMULAIRE DE RECHERCHE

			echo "<div id='contenu_box2'>";
			
				// Titre de la page
				echo "<h1>";
					
					echo "<span style=''>".htmlaccent('Liste des Stations de Mesures')."</span>";

					echo "<div style='float:right;margin-right:5%;'>";

						echo "<img src='".DIR_WS_IMG_ICO."xls.png' 
								id='img_file'
								style='width:30px;cursor:pointer;' 
								title='".htmlaccent('Télécharger les informations relatives aux Stations sélectionnées')."'
								onClick='downloadStation_xls()';>";

						echo "<div id='wait_file' style='text-align:center;display:none;'>";
							echo "<img src='".DIR_WS_IMG."wait.gif' style='width:15px;'>";
							echo "<span style='margin-left:10px;font-size:11px;font-weight:bold;color:#000;'>".htmlaccent('Création du fichier en cours ...')."</span>";
						echo "</div>\n";

					echo "</div>";

					

				echo "</h1>";	
			
				// Message d'action
				if(tep_not_null($message_suprr_station)){echo "<div id='contenu_info'>".$message_suprr_station."</div>";}			
				
				// Balise indiquant le début du formulaire
				$lien_form = tep_href_link('list_stations.php');
				$name_form = 'form_station';

				echo "<form name='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data'>";

					// ----------------------------------------------------------------------------------------
					// FORMULAIRE DE SELECTION - Cadre en-tête de la page
					// Ce bloc contient les champs formulaire en liste qui permettent de sélectionner les RA en fonction de différents critères

					echo "<div id='cadre_graph' style='float:left;width:250px;margin-right:1%;height:75vh;overflow-y: auto;'>\n"; 

						echo "<div id='boxpopup' class='select-top' style='width:92%;padding:10px 3%;margin-bottom:10px;'>\n";
					
							echo "<div id='button_titre' style='margin-left:17%;' onclick=\"window.open('modif_station.php?new=1', '_blank');\" >\n";	
								echo htmlaccent('Nouvelle Station'); 
							echo "</div>\n";
						
						echo "</div>";

						echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 0 3%;padding-top:10px;'>\n";

							require(DIR_WS_FILTRE . 'filtre_stations_html.php');

							echo "<hr>";

							// TRI DE LA TABLE
							echo "<div style='width:100%;border-bottom:2px solid #176B87;margin-top:15px;'></div>";

							echo "<p style='float:left;width:60px;padding-top:5px;margin-top:15px;'>".htmlaccent('TRIER PAR')."</p>";

							echo "<select name='select_tri' id='select_tri' onchange='".$name_form.".submit();' style='float:right;width:140px;margin-top:15px;'>";
								
								$selected = ($tri_encours == 1) ? "selected" : "";
								echo "<option value='1' ".$selected.">".htmlaccent('Nom de la station')."</option>";
								$selected = ($tri_encours == 2) ? "selected" : "";
								echo "<option value='2' ".$selected.">".htmlaccent('Code de la station')."</option>";
								/*
								$selected = ($tri_encours == 3) ? "selected" : "";
								echo "<option value='3' ".$selected.">".htmlaccent('Nom de la commune')."</option>";
								*/
								$selected = ($tri_encours == 4) ? "selected" : "";
								echo "<option value='4' ".$selected.">".htmlaccent('Type de données')."</option>";
									
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
							
							// Affichage nombre de stations ; nbre stations activse ; nbre stations suivies - Cadre jaune
							echo "<div id='contenu_infos'>";
											
									echo "<p>";
										echo "<span style='margin:0px;'>".htmlaccent('Nb stations : ').number_format($nb_station,0,'.',' ')."</span>";
										echo "<hr>";
										echo "<span style='margin:0px;'>".htmlaccent('Nb stations actives : ').number_format($nb_station_active,0,'.',' ')."</span>";		
										echo "<hr>";
										echo "<span style='margin:0px;'>".htmlaccent('Nb stations mesures continues : ').number_format($nb_station_suivi,0,'.',' ')."</span>";
										echo "<hr>";
										echo "<span style='margin:0px;'>".htmlaccent('Nb de stations en panne : ').number_format($nb_station_armee,0,'.',' ')."</span>";
									echo "</p>";

							echo "</div>";

							echo "<hr>";
						
						echo "</div>";	
					
					echo "</div>";
					
					// ----------------------------------------------------------------------------------------		
					// TABLEAU GENERAL STATIONS - Permet d'afficher la liste des Stations
					if(isset($station_array) && ($nb_station>0))
					{
						echo "<div class='table-container' style='float:none;width:auto;height:80vh;'>";

							echo "<div style='width:95%;height:78vh;overflow-y: auto;'>";
								echo "<table id='table_tri' cellspacing='0' >";
							
									// En-tête des colonnes
									echo "<thead>";
										echo "<tr class='header-row'>";	
																			
											echo "<th style='width:30px;text-align:center;' title='".htmlaccent('Active / Historique (Fermée)')."'>".htmlaccent('Statut')."</th>";
											echo "<th style='width:30px;text-align:center;' title='".htmlaccent('Mesures en continue / Mesures Ponctuelles')."'>".htmlaccent('Suivi')."</th>";	
											echo "<th style='width:30px;text-align:center;' title='".htmlaccent('En fonctionnement // En panne')."'>".htmlaccent('Etat')."</th>";	
											echo "<th style='width:70px;padding-left:5px;'>".htmlaccent('Type')."</th>";
											echo "<th style='width:80px;'>".htmlaccent('Code station')."</th>";						
											echo "<th style='width:280px;'>".htmlaccent('Nom de la station')."</th>";
											echo "<th style='width:100px;padding-left:5px;'>".htmlaccent('Commune')."</th>";
											echo "<th style='width:140px;padding-left:5px;' title='".htmlaccent('Région Hydrologique ou Bassin Versant')."'>".htmlaccent('Région Hydro / BV')."</th>";									
											//echo "<th style='width:120px;padding-left:5px;' title='".htmlaccent('Rivière')."'>".htmlaccent('Rivière')."</th>";
											echo "<th style='width:90px;padding-left:5px;'>".$territoire_region."</th>";
											echo "<th style='width:80px;' title='".htmlaccent('Date Installation')."'>".htmlaccent('Installation')."</th>";
											echo "<th style='width:80px;' title='".htmlaccent('Date de la dernière visite')."'>".htmlaccent('Visite')."</th>";
											echo "<th style='width:50px;text-align:center;' title='".htmlaccent('Nombre de Rapport Activité')."'>".htmlaccent('Nb RA')."</th>";
											echo "<th style='width:50px;text-align:center;' title='".htmlaccent('Sélectionné les stations pour l\'export au format XLS')."'>";
												echo htmlaccent('Export');
											echo "</th>";
											echo "<th style='width:50px;'>&nbsp;</th>";
										echo "</tr>";
									echo "</thead>";
							
									//ligne vide dans le tableau							
									echo "<tr>";
										echo "<td colspan='3' style='height:10px;'>";

											echo "<p onclick='".$name_form.".submit();' style='cursor:pointer;text-align:center;'>";
												echo "<span class='selectAll'>".htmlaccent('Changement d\'état')."</span>";
											echo "</p>";

										echo "</td>";
										echo "<td colspan='10' style='height:10px;'>&nbsp;</td>";
									echo "</tr>";	
									
									// Parcours du tableau Stations
									foreach($station_array as $key => $value)
									{	
										if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
										else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
										
										// On cherche les couleurs des liées aux types de données
										$color_type = '';
										if(tep_not_null($eq_type_array[$value['id_eq_type']]['type_color_border']))
										{
											$color_type = 'color:'.$eq_type_array[$value['id_eq_type']]['type_color_border'].';';
										}
										
										// Nbre de RA pour la station en cours
										$nb_ra=0;
										if(isset($nb_ra_array[$key])){$nb_ra=$nb_ra_array[$key];}
										// Nbre de Chonique différentes pour chaque station
										$nb_diff_typedata=0;
										if(isset($nb_meta_array[$key])){$nb_diff_typedata=$nb_meta_array[$key]['nb_diff_typedata'];}

										// Date de la dernière visite (dernier RA)
										$last_ra='';
										if(isset($last_ra_array[$key])){$last_ra=$last_ra_array[$key];}
										
										echo "<tr ".$row_l." >";
										
											//Statut Station (Active / Historique (fermée))
											echo "<td style='text-align:center;'>";

												echo "<input type='hidden' name='check_active_".$key."' value='0' >"; // ce champs caché est une astuce qui permet de s'assurer du retour de l'information
												$check = '';
												if($value['active_station'] == 1){$check = 'checked';}
												echo "<input type='checkbox' name='check_active_".$key."' value='1' ".$check." >";

											echo "</td>\n";
											
											// Suivi Station (Mesures continues / Mesures ponctuelles)
											echo "<td style='text-align:center;' >";

												$check = '';
												if($value['suivi_station'] == 1){$check = 'checked';}
												echo "<input type='checkbox' name='check_suivi_".$key."' ".$check." >";

											echo "</td>\n";

											// Etat Station (En fonctionnement / En panne)
											echo "<td style='text-align:center;' >";

												$check = '';
												if($value['armee_station'] == 1){$check = 'checked';}
												echo "<input type='checkbox' name='check_armee_".$key."' ".$check." >";

											echo "</td>\n";
											
											// Pour savoir sur quelle station on va en cliquant sur le lien
											$lien_modif = "modif_station.php?ref=".$key;
											
											// Type de données
											echo "<td style='padding-left:5px;".$color_type."cursor:pointer;' onClick=\"window.open('".$lien_modif."', '_blank')\">";
												echo $eq_type_array[$value['id_eq_type']]['nom_eq_type'];
											echo "</td>\n"; 
												
											// Code de la station
											echo "<td style='cursor:pointer;' onClick=\"window.open('".$lien_modif."', '_blank')\">".$value['code_station']."</td>\n";	

											// Nom de la station
											echo "<td style='cursor:pointer;' onClick=\"window.open('".$lien_modif."', '_blank')\" title='".$value['nom_station']."'>";
												echo affichelettres($value['nom_station'],50);
											echo "</td>\n";
											
											// Commune
											echo "<td style='padding-left:5px;cursor:pointer;' onClick=\"window.open('".$lien_modif."', '_blank')\" title='".$value['nom_commune']."'>";
												echo affichelettres($value['nom_commune'],30);
											echo"</td>\n";

											// Région Hydro / BV / Vallée 
											echo "<td style='padding-left:5px;cursor:pointer;' onClick=\"window.open('".$lien_modif."', '_blank')\">";
												echo $value['nom_regionhydro'];
											echo "</td>\n";

											// Riviere
											/*
											echo "<td style='padding-left:5px;cursor:pointer;' onClick=\"window.open('".$lien_modif."', '_blank')\">";
												echo $value['nom_riviere'];
											echo "</td>\n";
											*/

											// Région Géographique (Province, îles)
											echo "<td style='padding-left:5px;cursor:pointer;' onClick=\"window.open('".$lien_modif."', '_blank')\">";
												echo $value['nom_region'];
											echo "</td>\n";
											
											// Date d'installation de la station
											echo "<td style='padding-left:5px;cursor:pointer;' onClick=\"window.open('".$lien_modif."', '_blank')\">";
												echo $value['date_installation_station'];
											echo "</td>\n";

											// Date de la dernière visite										
											echo "<td style='padding-left:5px;'>" . $last_ra . "</td>\n";

											// NB RA
											echo "<td style='text-align:center;cursor:pointer;' onClick=\"window.open('".$lien_modif."', '_blank')\">".$nb_ra."</td>\n";

											// NB Chroniques
											//echo "<td style='width:60px;text-align:center;cursor:pointer;' onClick=\"window.open('".$lien_modif."', '_blank')\">".$nb_diff_typedata."</td>\n";
												
											// Sélection des stations pour export des informations en format XLS
											echo "<td style='text-align:center;'>";

												echo "<input type='checkbox' name='check_export_".$key."' value='".$key."' checked >";

											echo "</td>\n";

											// Supprimer
											echo "<td style='text-align:center;'>";
												//if($nb_ra<1 && $nb_diff_typedata<1 && !tep_not_null($value['id_old'])) // On ne pas supprimer les stations historiques
												if($nb_ra<1 && $nb_diff_typedata<1) 
												{
													$lien_suppr = "list_stations.php?del=".$key;
													echo "<span style='font-size:12px;font-weight:bold;color:#d9534f;cursor:pointer;' 
																title='".htmlaccent('Supprimer')."' 
																onClick=\"confirm_suppr('".$lien_suppr."','la station','".$value['nom_station']."')\";>";
													echo "X</span>";
												}
												else{echo "-";}
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
						echo "<div id='boxpopup' style='margin-left: 1%;'>\n";
							echo "<p class='alert'>".htmlaccent('Aucune - Station - n\'a été trouvée')."</p>";
						echo "</div>";
					}


				echo "</form>"; // Fin du formulaire
			
			echo "<hr>";
			echo "</div>";		
			
		echo "<hr>";
		echo "</div>";	
		
	echo "<hr>";
	echo "</div>";
						
	// Pied de page
	require('include/application_bottom.php'); 

echo "</body>";

echo "</html>";

?>	

<script>

	var idTerritoire = <?php echo $territoire_id; ?>;

	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info
	var waitFile = document.getElementById('wait_file'); // pour affichage de l'icone d'attente de création du fichier XLS
	var imgFile = document.getElementById('img_file');
	

	// ---------------------------------------
    // Function pour le téléchargement des Informations Stations
    function downloadStation_xls()
    {
		imgFile.style.display = 'none';
		waitFile.style.display = 'block';

		// ETAPE 1 : Récupérer tous les checkboxes
		var checkboxes = document.querySelectorAll("input[type='checkbox'][name^='check_export_']");
		var selectedStations = [];

		// Parcourir les checkboxes et ajouter les valeurs des checkboxes cochées à la liste
		checkboxes.forEach(function(checkbox) 
		{
			if (checkbox.checked) 
			{
				selectedStations.push(checkbox.value);
			}
		});

		if(selectedStations.length === 0)
		{
			contenuInfo.innerHTML  = 'Aucune Station n\'a été sélectionnée, le fichier ne peut pas être créé.';
			contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
			contenuInfo.style.display = 'block';

			waitFile.style.display = 'none';
			imgFile.style.display = 'block';

			return;
		}

		// Construire la liste pour la requête SQL
		var listStation = selectedStations.join(",");


		// ETAPE 2 : Préparer la création du fichier XLS en envoyant les infos coté Serveur

		cheminFolder = 'data/export/temp';

        // Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							idTerritoire: idTerritoire,
							listStation: listStation,
							cheminFolder: cheminFolder,
                        };

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/export/process_station_download_xls.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4)
            {
                if (xhr.status === 200) 
                {
                    // Analyser la réponse JSON
                    var jsonResponse = JSON.parse(xhr.responseText);  

                    if(jsonResponse['statut'])
                    {
                        // Créer un lien invisible pour déclencher le téléchargement
                        var downloadLink = document.createElement('a');
                        downloadLink.href = cheminFolder+'/'+jsonResponse['xlsFile']; // URL du fichier CSV
                        downloadLink.download = jsonResponse['xlsFile']; // Nom du fichier
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);						
                    }
                    else 
                    {
                        contenuInfo.innerHTML  = 'Erreur lors de la génération du fichier.';
                        contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
                        contenuInfo.style.display = 'block';
                    }

                } 
                else 
                {
                    contenuInfo.innerHTML  = 'Erreur lors de la requête au serveur.';
                    contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
                    contenuInfo.style.display = 'block';
                }
            }
			waitFile.style.display = 'none';
			imgFile.style.display = 'block';
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
    }

/*
	// FONCTION POUR ACCEDER AU DONNEEES - METHODE CLIENT SERVEUR (AJAX)


	// Lancement de l'acquisition des données

	function load_data(list_station_txt,date_1,date_2)
	{
		// Mise au format JSON des données
		// Créer un objet contenant les données à envoyer
		var dataToSend = {
			list_station_txt: '<?php //echo $list_station_txt; ?>', // liste des stations sélectionnées
			date_1: '<?php //echo $date_1; ?>', 
			date_2: '<?php //echo $date_2; ?>'
		};

		// Convertir l'objet en JSON
		var jsonData = JSON.stringify(dataToSend);

		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/selectdata/process_select_chron.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				document.getElementById('wait').style.display = 'none';
				document.getElementById('result').style.display = 'block';
				document.getElementById('button_graph_edit').style.display = 'block';
				document.getElementById('button_export_edit').style.display = 'block';
				document.getElementById('button_delete_edit').style.display = 'block';

				// Accéder aux données individuelles
				
				result = xhr.responseText;
				document.getElementById('result').innerHTML = result;
				
			}
		};

		// Envoyer les données JSON au serveur
		xhr.send(jsonData);
	}


	load_data(<?php //echo $list_station_txt.','.$date_1.','.$date_2; ?>);

*/
</script>