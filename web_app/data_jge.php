<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Affichage de la Liste des jaugeages à partir du dernier, avec tri pour aider à la sélection
----------------------------------------
*/

require('include/application_top.php');

// -----------------------------
// Initialisation des Var.
$modif_jge = false;
$id_jge_modif = 0;

$message_info = '';
$message_suprr_jge = '';
$row = 0;
$date_format = 'd-m-Y';
$heure_format = 'H:i:s';



$where_and_type = " AND s.station_type=11"; // Uniquement des stations hydrométriques ce sont des Jaugeages

$select_periode_encours = 60;
$where_and_periode = " AND jge.datetime >= CURDATE() - INTERVAL ".$select_periode_encours." MONTH";


// SELECT POUR LE TRI
// TRI (Nom station, Code Station, Commune, Type_DATA)
$tri_encours = 3;
$tri = "jge.datetime";
if(isset($_POST['select_tri']))
{
	$tri_encours = $_POST['select_tri'];
	if($_POST['select_tri'] == 1){$tri = "s.nom_station";} // tri nom station
	if($_POST['select_tri'] == 2){$tri = "s.code_station";} // tri code station
	if($_POST['select_tri'] == 3){$tri = "jge.datetime";} // tri date
}

$tri_order_encours = 2;
$tri_order = " DESC,";
if(isset($_POST['order_tri']))
{
	$tri_order_encours = $_POST['order_tri'];
	if($_POST['order_tri'] == 1){$tri_order = " ASC,";} // tri croissant
	if($_POST['order_tri'] == 2){$tri_order = " DESC,";} // tri décroissant
}


// Initialisation des var. pour les filtres les plus communs
$affiche_select_type = false;
$affiche_select_tournee = true;
$affiche_search = true;
$affiche_select_riviere = false;
$affiche_select_station = true;
$affiche_select_statut_station = true;
require(DIR_WS_FILTRE . 'filtre_stations_var.php');

//---------------------------------------------------------------
// Récupération des champs de formulaires pour sélection

// recherche des stations
if(isset($_POST['search_station']) || isset($_GET['search_station']))
{
	if(isset($_POST['search_station'])){$search_station = post_secure($sql_link,$_POST['search_station']);}
	if(isset($_GET['search_station'])){$search_station = post_secure($sql_link,$_GET['search_station']);}
	
	$where_search = search_station($search_station,'');
}

// Période de données
if(isset($_POST['select_periode']))
{
	$select_periode_encours = $_POST['select_periode'];
	$where_and_periode = " AND datetime >= CURDATE() - INTERVAL ".$select_periode_encours." MONTH";		
	if($select_periode_encours==0){$where_and_periode = '';}
}

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA


// TABLE JGE TYPE
$sql_jge_type = "SELECT DISTINCT id, titre, obs 
				FROM ".TABLE_DATA_JGE_TYPE;
$jge_type_query = tep_db_query($sql_link,$sql_jge_type);
while ($jge_type = tep_db_fetch_array($jge_type_query))
{
	$jge_type_array[$jge_type['id']] = htmlaccent(html_entity_decode($jge_type['titre'] ?? $default_string));
}

// TABLE JGE METHODE
$sql_jge_methode = "SELECT DISTINCT id, titre, obs 
				FROM ".TABLE_DATA_JGE_METHODE;
$jge_methode_query = tep_db_query($sql_link,$sql_jge_methode);
while ($jge_methode = tep_db_fetch_array($jge_methode_query))
{
	$jge_methode_array[$jge_methode['id']] = htmlaccent(html_entity_decode($jge_methode['titre'] ?? $default_string));
}

// DATA CODE QUALITE
$sql_code_qual = "SELECT DISTINCT id_data_qualite, init_qualite_data, nom_qualite_data 
                FROM ".TABLE_DATA_QUALITE." 
                WHERE (id_eq_type=0 OR id_eq_type=11) 
                ORDER BY id_eq_type DESC, init_qualite_data";
$code_qual_query = tep_db_query($sql_link,$sql_code_qual);
while ($code_qual = tep_db_fetch_array($code_qual_query))
{				
	$code_qual_array[$code_qual['id_data_qualite']] = array('init_qualite_data' => htmlaccent(html_entity_decode($code_qual['init_qualite_data'] ?? $default_string)),
                                                            'nom_qualite_data' => htmlaccent(html_entity_decode($code_qual['nom_qualite_data'] ?? $default_string))
                                                            );
} 



// --------------------------------------------------
// Pour l'enregitrement ou la suppression d'un JGE 


// Enregistrer les RA
if(isset($_POST['save_jge'])){require(DIR_WS_FORMULAIRE . 'ctrl_jge_simple.php');}

// Lien vers le fichier permettant de supprimer un jaugaeage
// Lien vers le fichier permettant de supprimer une station
if(isset($_GET['del']) && tep_not_null($_GET['del'])){require(DIR_WS_SUPPRIMER . 'suppr_jge.php');}



//---------------------------------------------------------------
// TABLE SQL - Recupération des la listes des Jaugeages
$nb_jge = 0;

// Pour récupérer le tri station pour affiner la liste des stations dans le bloc RA. 
//$where_jge = $where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_type.$where_and_tournee.$where_and_station.
//				$where_and_active.$where_and_suivi.$where_and_armee.$where_and_station;

// jge.nb_bras,  (SELECT COUNT(*) FROM ".TABLE_DATA_JGE_BRAS." WHERE id_jge = jge.id) AS nb_bras_tab,  TROP LENT CETTE REQUETE
$sql_jge = "SELECT DISTINCT jge.id, jge.id_station, s.code_station, s.nom_station, jge.datetime, 
							jge.nb_bras, 
							jge.id_methode, jge.id_typejge, jge.depouil_hmoy, jge.depouil_q, jge.obs,
							jge.code_qualite,
							s.active_station, s.suivi, s.armee
				FROM ".TABLE_DATA_JGE." jge
				JOIN ".TABLE_STATION." s ON jge.id_station=s.id_station
				JOIN ".TABLE_REGION." r ON s.id_region=r.id_region	
				WHERE r.id_territoire=".$territoire_id.
				$where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_type.$where_and_tournee.$where_and_station.
				$where_and_active.$where_and_suivi.$where_and_armee.
				$where_and_station.				
				$where_and_periode." 
				ORDER BY ".$tri.$tri_order." s.active_station DESC, s.suivi DESC, s.armee ASC";

$jge_query = tep_db_query($sql_link,$sql_jge);
while ($jge = tep_db_fetch_array($jge_query))
{	
	$id =  html_entity_decode($jge['id'] ?? $default_string);
	
	$id_station =  html_entity_decode($jge['id_station'] ?? $default_string);
	$code_station = htmlaccent(html_entity_decode($jge['code_station'] ?? $default_string));
	$nom_station = htmlaccent(html_entity_decode($jge['nom_station'] ?? $default_string));
	$obs = htmlaccent(html_entity_decode($jge['obs'] ?? $default_string));

	$code_qualite =  html_entity_decode($jge['code_qualite'] ?? $default_string);

	$active_station = 0;
	if($jge['active_station'] == 1)
	{
		$active_station = 1;
		//$nb_station_active++;
	}

	$suivi_station = 0;
	if($jge['suivi'] == 1)
	{
		$suivi_station = 1;
		//$nb_station_suivi++;
	}
	
	$armee_station = 0;
	if($jge['armee'] == 1)
	{
		$armee_station = 1;
		//$nb_station_armee++;
	}

	
	$tab_date_heure_jge =  explode(" ",$jge['datetime']);
	$date_jge =  dateus_fr($tab_date_heure_jge[0]);
	$heure_jge =  $tab_date_heure_jge[1];
	$date_heure_jge =  $date_jge.' '.$heure_jge;
	
	$nb_bras =  html_entity_decode($jge['nb_bras'] ?? $default_string); // valeur contenu dans la table JGE
	
	$id_methode =  html_entity_decode($jge['id_methode'] ?? $default_string);
	$id_typejge =  html_entity_decode($jge['id_typejge'] ?? $default_string);

	$jge_methode_titre = '';
	$jge_type_titre = '';

	if(isset($jge_methode_array[$id_methode])){$jge_methode_titre = $jge_methode_array[$id_methode];}
	if(isset($jge_type_array[$id_typejge])){$jge_type_titre = $jge_type_array[$id_typejge];}
	

	// On cherche à savoir si le Jaugeage est lié à au moins un bras dans la table bras
	$sql_jge_bras = "SELECT DISTINCT count(*) as nb_bras_bytab
					FROM ".TABLE_DATA_JGE_BRAS."
					WHERE id_jge=".$id;
	$jge_bras_query = tep_db_query($sql_link,$sql_jge_bras);
	$jge_bras = tep_db_fetch_array($jge_bras_query);
	
	$nb_bras_bytab = 0; 
	if(isset($jge_bras['nb_bras_bytab'] ))
	{
		$nb_bras_bytab =  html_entity_decode($jge_bras['nb_bras_bytab'] ?? $default_string); // valeur contenu dans la table JGE				
	}

	$depouil_hmoy =  round(floatval($jge['depouil_hmoy']),3);
	$depouil_q =  round(floatval($jge['depouil_q']),3);

	// Lacune
	if($depouil_hmoy == '9999'){$depouil_hmoy = 'Err';}
		
	$jge_array[$id] = array('id_station' => $id_station,
							'code_station' => $code_station,
							'nom_station' => $nom_station,
							'active_station' => $active_station,
							'suivi_station' => $suivi_station,
							'armee_station' => $armee_station,
							'jge_methode_titre' => $jge_methode_titre,
							'jge_type_titre' => $jge_type_titre,
							'date_heure' => $date_heure_jge,						
							'date' => $date_jge,						
							'heure' => $heure_jge,
							'nb_bras' => $nb_bras,						
							'nb_bras_bytab' => $nb_bras_bytab,
							'depouil_hmoy' => $depouil_hmoy,
							'depouil_q' => $depouil_q,
							'obs' => $obs,
							'code_qualite' => $code_qualite
							);
	
}
if(isset($jge_array)){$nb_jge = sizeof($jge_array);}	



//---------------------------------------------------------------
// EDITION HTML

require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

	// ----------------------------------------------------------------------------------------
	// FORMULAIRE DE SELECTION - Cadre en-tête de la page
	// Ce bloc contient les champs formulaire en liste qui permettent de sélectionner les RA en fonction de différents critères
	// On met les formulaire ici pour enblober les boutons de recherche et les champs des JGE dans le popup

	$lien_form = tep_href_link('data_jge.php');
	$name_form = 'form_select_jge';			
	echo "<form name='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data'>";

		require(DIR_WS_JAUGEAGE . 'block_jge_simple.php'); // Block pour affichage d'une fiche RA en premier plan		
		require(DIR_WS_JAUGEAGE . 'block_verifdel_jge.php'); // Block pour permettre une confirmation de la suppresion d'un jaugeage
		require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
		include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

		echo "<div id='contour_general'>";	

			if(tep_not_null($message_info)){echo "<div id='contenu_info'>".$message_info."</div>";}

			echo "<div id='contenu_centre'>";
				
				echo "<div id='contenu_box2'>";
				
					echo "<h1>";
						echo "<span>".htmlaccent('Liste des Jaugeages')."</span>";
					echo "</h1>";	

					echo "<div id='cadre_graph' style='float:left;width:250px;margin-right:1%;height:70vh;overflow-y: auto;'>\n"; 
					
						echo "<div id='boxpopup' class='select-top' style='width:92%;padding:10px 3%;margin-bottom:10px;'>\n";
						
							// Boutton pour Saisir un nouveau JGE
							echo "<div id='button_titre' style='margin-left:20%;' onclick=\"window.open('modif_jge.php', '_blank');\" >\n";	
								echo htmlaccent('Nouveau JGE'); 
							echo "</div>\n";

						echo "</div>";
					
						echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 0 3%;'>\n";
					
							// PERIODE
							echo "<p style='float:left;width:60px;margin-top:15px;padding-top:5px;color:#609966;'>".htmlaccent('Période')."</p>";

							echo "<select name='select_periode' id='select_periode' onchange='".$name_form.".submit();' style='float:right;width:140px;margin-top:15px;'>";
								
								if($select_periode_encours==1){$selected="selected";}	
								else{$selected = '';}									
								echo "<option value='1' ".$selected.">".htmlaccent('1 mois')."</option>";
								
								if($select_periode_encours==3){$selected="selected";}	
								else{$selected = '';}									
								echo "<option value='3' ".$selected.">".htmlaccent('3 mois')."</option>";
								
								if($select_periode_encours==6){$selected="selected";}	
								else{$selected = '';}									
								echo "<option value='6' ".$selected.">".htmlaccent('6 mois')."</option>";
								
								if($select_periode_encours==12){$selected="selected";}	
								else{$selected = '';}									
								echo "<option value='12' ".$selected.">".htmlaccent('1 ans')."</option>";									
								
								if($select_periode_encours==24){$selected="selected";}	
								else{$selected = '';}	
								echo "<option value='24' ".$selected.">".htmlaccent('2 ans')."</option>";									
								
								if($select_periode_encours==60){$selected="selected";}	
								else{$selected = '';}									
								echo "<option value='60' ".$selected.">".htmlaccent('5 ans')."</option>";

								if($select_periode_encours==120){$selected="selected";}	
								else{$selected = '';}									
								echo "<option value='120' ".$selected.">".htmlaccent('10 ans')."</option>";

								if($select_periode_encours==0){$selected="selected";}	
								else{$selected = '';}									
								echo "<option value='0' ".$selected.">".htmlaccent('Toutes les données')."</option>";
																	
							echo "</select>";

							echo "<hr>\n";

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
								$selected = ($tri_encours == 3) ? "selected" : "";
								echo "<option value='3' ".$selected.">".htmlaccent('Date')."</option>";
									
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
										echo "<span style='margin:0px;'>".htmlaccent('Nombre de Jaugeages : ').number_format($nb_jge,0,'.',' ')."</span>";
									echo "</p>";

							echo "</div>";

							echo "<hr>";
						
						echo "</div>";	
					
					echo "</div>";
					
					// Affichage Table
					if(isset($jge_array) && ($nb_jge>0))
					{
						echo "<div class='table-container' style='float:none;width:auto;height:80vh;'>";

							echo "<div style='width:95%;height:78vh;overflow-y: auto;'>";

								echo "<table id='table_tri' cellspacing='0' >";
							
									echo "<thead>";
										echo "<tr class='header-row'>";		
											echo "<th style='width:40px;text-align:center;' title=".htmlaccent('Active ou Historique (Fermée)').">".htmlaccent('Statut')."</th>";
											echo "<th style='width:40px;text-align:center;' title=".htmlaccent('Mesures en continue ou Mesures Ponctuelles').">".htmlaccent('Suivi')."</th>";	
											echo "<th style='width:40px;text-align:center;' title=".htmlaccent('En fonctionnement ou en panne').">".htmlaccent('Etat')."</th>";														
											echo "<th style='width:80px;padding-left:5px;'>".htmlaccent('Type')."</th>";
											echo "<th style='width:90px;'>".htmlaccent('Code station')."</th>";						
											echo "<th style='width:250px;'>".htmlaccent('Nom de la station')."</th>";			
											echo "<th style='width:70px;'>".htmlaccent('Date')."</th>";											
											echo "<th style='width:70px;'>".htmlaccent('Heure')."</th>";
											echo "<th style='width:70px;text-align:center;'>".htmlaccent('Nb Bras')."</th>";				
											echo "<th style='width:90px;text-align:center;'>".htmlaccent('Débit [m<sup>3</sup>/s]')."</th>";	
											echo "<th style='width:90px;text-align:center;'>".htmlaccent('Hauteur [cm]')."</th>";																					
											echo "<th style='width:40px;'>&nbsp;</th>";
											echo "<th style='width:50px;'>&nbsp;</th>";
										echo "</tr>";
									echo "</thead>";
							
									//ligne vide dans le tableau
									echo "<tr>";
										echo "<td colspan='11' style='height:10px;'>&nbsp;</td>";
									echo "</tr>";	
									
									// Parcours du tableau Stations
									foreach($jge_array as $key => $value)
									{	
										echo "<input type='hidden' id='id_jge_".$key."' value='".$key."' />\n";
										echo "<input type='hidden' id='id_station_".$key."' value='".$value['id_station']."' />\n";
										echo "<input type='hidden' id='code_station_".$key."' value='".$value['code_station']."' />\n";
										echo "<input type='hidden' id='nom_station_".$key."' value='".$value['nom_station']."' />\n";
										echo "<input type='hidden' id='date_".$key."' value='".$value['date']."' />\n";								
										echo "<input type='hidden' id='heure_".$key."' value='".$value['heure']."' />\n";							
										echo "<input type='hidden' id='debit_".$key."' value='".$value['depouil_q']."' />\n";					
										echo "<input type='hidden' id='hauteur_".$key."' value='".$value['depouil_hmoy']."' />\n";				
										echo "<input type='hidden' id='code_qualite_".$key."' value='".$value['code_qualite']."' />\n";											
										echo "<input type='hidden' id='obs_".$key."' value='".$value['obs']."' />\n";


										
										if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
										else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
																
										echo "<tr ".$row_l." >";
										
											$lien_modif = "modif_jge.php?ref=".$key;

											//Statut Station
											echo "<td style='text-align:center;'>";
												
												if($value['active_station'] === 1){
													echo "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title='".htmlaccent('En activité')."'>";
												}
												else{
													echo "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' title='".htmlaccent('Historique (Fermée)')."'>";
												}
												
											echo "</td>\n";
											
											// Suivi Station
											echo "<td style='text-align:center;' >";

												if($value['suivi_station'] === 1){
													echo "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title='".htmlaccent('Mesures en continu')."'>";									
												}
												else{
													echo "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' title='".htmlaccent('Mesures ponctuelles')."'>";	
												}

											echo "</td>\n";

											// Etat Station
											echo "<td style='text-align:center;' >";

												if($value['armee_station'] === 1){
													echo "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title='".htmlaccent('En fonctionnement')."'>";									
												}
												else{
													echo "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' title='".htmlaccent('En panne')."'>";	
												}

											echo "</td>\n";
										
											echo "<td style='padding-left:10px;cursor:pointer;' onClick=\"affiche_JGE(".$key.");\">";
													echo $value['jge_type_titre'];
											echo "</td>\n";	

											// Code de la station
											echo "<td style='cursor:pointer;' onClick=\"affiche_JGE(".$key.");\">";
													echo $value['code_station'];
											echo "</td>\n";	
											
											// Nom de la station
											echo "<td style='width:250px;cursor:pointer;' onClick=\"affiche_JGE(".$key.")\";' 
														title='".$value['nom_station']."'>";
													echo affichelettres($value['nom_station'],50);
											echo "</td>\n";	
											
											echo "<td>";
													echo $value['date'];
											echo "</td>\n";

											echo "<td>";
													echo $value['heure'];
											echo "</td>\n";

											echo "<td style='text-align:center;'>";
													echo $value['nb_bras'];
											echo "</td>\n";
											
											echo "<td style='text-align:center;' >";
													echo $value['depouil_q'];
											echo "</td>\n";
											
											echo "<td style='text-align:center;' >";
													echo $value['depouil_hmoy'];
											echo "</td>\n";
											
											echo "<td style='text-align:center;'>";				
												echo "<a href='".$lien_modif."' target='_blank' title='".htmlaccent('Saisir un Jaugeage par point')."'>"; 
													echo "<img src='".DIR_WS_IMG_ICO."detail.png' style='width:20px;'>";							
												echo "</a>";
											echo "</td>\n";
											
											
											
											// supprimer
											
											echo "<td style='text-align:center;'>";			
												//$lien_suppr = "data_jge.php?del=".$key;
												//echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:20px;cursor:pointer;' title='".htmlaccent('Supprimer le Jaugeage')."' onClick=\"confirm_suppr('".$lien_suppr."','".htmlaccent('le Jaugeage de la station ')."','".$value['nom_station']."');\">";					
												echo "<a style='font-size:12px;font-weight:bold;' id='del_".$key."' onClick='verifDelJGE(".$key.");' 
															title='".htmlaccent('Supprimer le Jaugeage')."'>
															X
														</a>";
												
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
						echo "<div id='boxpopup' style='margin-left:1%;'>\n";
							echo "<p class='alert'>".htmlaccent('Aucune - Jaugeage - n\'a été trouvé')."</p>";
						echo "</div>";
					}
				
				echo "<hr>";
				echo "</div>";
				
			echo "<hr>";
			echo "</div>";
			
		echo "<hr>";
		echo "</div>";


	echo "</form>";	

	require('include/application_bottom.php'); 
echo "</body>";

echo "</html>";

?>	

<script id="source" type="text/javascript">

	var blockValidDel = document.getElementById('box_del_jge'); // cadre de validation des corrections
	var buttonValidDel = document.getElementById('ok_valid_del'); // bouton de validation des corrections

	function verifDelJGE(id_jge)
    {
		blockValidDel.style.display = 'block';

		// Ajoute un écouteur d'événement au bouton "Valider"
		buttonValidDel.addEventListener('click', function() 
		{
			// Redirection vers modif_jge.php avec le paramètre ID
			window.location.href = 'data_jge.php?del=' + id_jge;
    	});
	}



	<?php 
		// $modif_jge est passé à true dans ctrl_jge_simple.php
		
		if($modif_jge)
		{
			// Affiche cadre JGE en premier plan
			// $id_jge_modif a été incémenté dans ctrl_jge_simple.php
			echo "affiche_JGE(".$id_jge_modif.")";	
		}
		
		//if(isset($_GET['id_ra'])){echo "affiche_RA(".$_GET['id_ra'].")";}
	?>

</script>