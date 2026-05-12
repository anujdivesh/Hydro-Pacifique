<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Formulaire des fiches RA
----------------------------------------
*/
$today = date('d-m-Y'); 
$time = date('H:i');
$today_time = date('d-m-Y H:i');

$day = date('d');
$month = date('m');
$year = date('Y');

$display_box = '';

echo "<div id='box_ra' class='block_view'>\n";

	echo "<div id='cadre_view' class='cadre_view' style='width:1180px;' >\n";
	
		echo "<div id='cadre_limit' style='height:670px;'>";	
			
			//echo "<form name='form_ra' action='list_ra.php' method='post' enctype='multipart/form-data'>";
			
				// Identifiant du RA en cours - (ALL)	
				echo "<input type='hidden' name='id_ra' id='id_ra' value=''/>";
				echo "<input type='hidden' name='id_agent_user' id='id_agent_user' value=''/>";
				// TITRE - Cartouche du haut - (ALL)
				echo "<table id='tab_titre_popup' cellspacing='0'>";
						
					echo "<tr>";
						
						echo "<td class='titre'>";
							
							// Affichage Type de Mesure - Liste défilante
							echo "<p style='margin-right:20px;'>";

								echo "<select name='select_type_ra' id='select_type_ra' class='titre_ra' style='width:150px;height:40px;' onchange='select_boxRA_typeData(this.value);'>";
											
									//echo "<option value='0'>-</option>";
									
									$selected = '';		

									if(isset($eq_type_array))
									{
										foreach($eq_type_array as $key => $value)
										{
											if($key == $select_type_encours){$selected="selected";}	
											else{$selected = '';}											
											echo "<option value='".$key."' ".$selected.">".$value['nom_eq_type']."</option>";
										}
									}
								echo "</select>";
								
							echo "</p> \n";
							
							// Affichage Station
							echo "<p style='width:400px;'>";
								
								echo "<select name='select_station_ra' id='select_station_ra' class='titre_ra' style='width:100%;height:40px;' >";
										
								$selected = '';			

								if(isset($station_array))
								{
									foreach($station_array as $key => $value)
									{
										if($key == $select_station_encours){$selected="selected";}	
										else{$selected = '';}											
										echo "<option value='".$key."' ".$selected.">".$value['code_station']." - ".$value['nom_station']."</option>";
									}
								}
								
								/*
								$sql_station = "SELECT DISTINCT * FROM ".TABLE_STATION." WHERE id_territoire=".$territoire_id." ORDER BY code_station";
								$station_query = tep_db_query($sql_link,$sql_station);
								while ($station = tep_db_fetch_array($station_query))
								{				
									$nom_station = htmlaccent(html_entity_decode($station['nom_station']));
									$code_station = htmlaccent(html_entity_decode($station['code_station']));
									
									if($station['id_station'] == $select_station_encours){$selected="selected";}	
									else{$selected = '';}											
									echo "<option value='".$station['id_station']."' ".$selected.">".$code_station." - ".affichelettres($nom_station,40)."</option>";
								}
								*/
								
								
								echo "</select>";	
								
							echo "</p> \n";

							echo "<img src='".DIR_WS_IMG_ICO."puce_verte.png' id='valid_puce_ok' title='".htmlaccent('RA validé')."' style='display:none;'>";
							echo "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' id='valid_puce_no' title='".htmlaccent('RA non validé')."'>";
							
							// Affichage de la date de Saisie de de l'agent qui s'est connectée sur la plateforme pour saisir qui a saisie
							echo "<p style='float:right;margin-top:0px;'>";
								
								echo "<span>".htmlaccent('Saisi le : ')."</span>";
								echo "<input type='text' name='date_saisie' id='date_saisie' value='".$today_time."' disabled>";
								echo "<br>";
								echo "<span>".htmlaccent('par : ')."</span>";
								echo "<input type='text' style='input_texte_300' name='agent_saisie' id='agent_saisie' value='".$tab_session['prenom'].' '.$tab_session['nom']."' disabled>";
																
							echo "</p> \n";

						echo "</td>";					
						
					echo "</tr>";
					
				echo "</table>";
		

				// LIGNE 1				  	

					// Releve des données sur un enregistreur (fichier de données brutes) - (ALL)
					echo "<div id='boxpopup'>\n";
					
						echo "<h2>".htmlaccent('Relève')."</h2>\n";
					
						// Date Releve
						echo "<div id='boite_small'>\n";
							
							echo "<p>".htmlaccent('Date (jj-mm-aaaa)')."</p>\n";	
							echo "<input class='input_texte' style='width:80px;' name='date_releve' id='date_releve' type='text' value=''  >\n"; 
									
						echo "</div>\n";
						
						// Heure Releve
						echo "<div id='boite_small'>\n";
														
							echo "<p>".htmlaccent('Heure (hh:mm)')."</p>";
							echo "<input name='heure_releve' id='heure_releve' value='".$time."' class='input_texte_small' type='text'>";
									
						echo "</div>\n";
						
						// Fichier Releve
						echo "<div id='boite_small'>\n";
														
							echo "<p>".htmlaccent('Nom du fichier de relève / Num Cassette')."</p>";
							echo "<input name='fichier_releve' id='fichier_releve' value='' class='input_texte'  style='width:250px;' type='text'>";
									
						echo "</div>\n";
						
					echo "<hr>\n";
					echo "</div>\n";	
					
					// Appareil - Type, Num, HeureAppareil (ALL)
					echo "<div id='boxpopup'>\n";
					
						echo "<h2>".htmlaccent('Appareil')."</h2>\n";
					
						// Type Appareil
						echo "<div id='boite_small'>\n";
														
							echo "<p>".htmlaccent('Type')."</p>";
							echo "<input name='type_appareil' id='type_appareil' value='' class='input_texte' type='text' >";
									
						echo "</div>\n";
						
						// Numéro Appareil
						echo "<div id='boite_small'>\n";
														
							echo "<p>".htmlaccent('Numéro')."</p>";	
							echo "<input name='num_appareil' id='num_appareil' value='' class='input_texte_small' type='text'>";
											
						echo "</div>\n";
						
						// Heure Appareil
						echo "<div id='boite_small'>\n";
														
							echo "<p>".htmlaccent('Heure (hh:mm:ss)')."</p>";	
							echo "<input name='heure_appareil' id='heure_appareil' value='".$time."' class='input_texte_small' type='text'>";						
									
						echo "</div>\n";

						
					echo "<hr>\n";
					echo "</div>\n";	

				
				// LIGNE 2				  

					// TOTALISATEUR
					echo "<div id='boxpopup' class='elt_boite_plu' style='display:none;'>\n";
						
						echo "<h2>".htmlaccent('Relevé Totalisateur')."</h2>\n";									
						
						echo "<div id='boite_small'>\n";
													
							echo "<p>".htmlaccent('Type de Tot')."</p>";		
							echo "<input name='plu_tot_type' id='plu_tot_type' value='' class='input_texte_small' type='text'>";
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Cumul Arrivée (mm)')."</p>";		
							echo "<input name='plu_tot_first' id='plu_tot_first' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Cumul Départ (mm)')."</p>";		
							echo "<input name='plu_tot_last' id='plu_tot_last' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";

						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Heure Basc.')."</p>";		
							echo "<input name='plu_tot_heure_basc' id='plu_tot_heure_basc' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
					echo "<hr>\n";
					echo "</div>\n";
				
					// CONTROLE PLU
					echo "<div id='boxpopup' class='elt_boite_plu' style='display:none;'>\n";
						
						echo "<h2>".htmlaccent('Contrôle')."</h2>\n";									
						
						echo "<div id='boite_small'>\n";
													
							echo "<p>".htmlaccent('Cumul TOT (mm)')."</p>";		
							echo "<input name='plu_cumul_tot' id='plu_cumul_tot' value='' class='input_texte_small' type='text'>";
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Cumul Pluvio (mm)')."</p>";		
							echo "<input name='plu_cumul_plu' id='plu_cumul_plu' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Diff : TOT - Pluvio (mm)')."</p>";	// ça doit se calculer automatiquement	
							echo "<input name='plu_diff_tot_plu' id='plu_diff_tot_plu' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						// Recalage heure que l'on retrouve aussi dans Limni - à réfléchir si on utilise 2 champs
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Calage heure (hh:mm)')."</p>";		
							echo "<input name='plu_recalage_heure_plu' id='plu_recalage_heure_plu' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Test Auget')."</p>";		
							echo "<input name='plu_test_auget' id='plu_test_auget' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
					echo "<hr>\n";
					echo "</div>\n";
					
					// COTE LIMNIMETRIQUE
					echo "<div id='boxpopup' class='elt_boite_hydro' style='display:none;'>\n";
						
						echo "<h2>".htmlaccent('Côtes limnimétriques')."</h2>\n";									
						
						echo "<div id='boite_small'>\n";
													
							echo "<p>".htmlaccent('Heure (hh:mm:ss)')."</p>";		
							echo "<input name='hydro_heure_cote' id='hydro_heure_cote' value='".$time."' class='input_texte_small' type='text'>";
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('H. sonde (cm)')."</p>";		
							echo "<input name='hydro_h_sonde' id='hydro_h_sonde' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('H. échelle (cm)')."</p>";		
							echo "<input name='hydro_h_echelle_1' id='hydro_h_echelle_1' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('H. échelle 2 (cm)')."</p>";		
							echo "<input name='hydro_h_echelle_2' id='hydro_h_echelle_2' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
					echo "<hr>\n";
					echo "</div>\n";
					
					// CONTROLE HYDRO
					echo "<div id='boxpopup' class='elt_boite_hydro' style='display:none;'>\n";
						
						echo "<h2>".htmlaccent('Contrôle des mesures de hauteur')."</h2>\n";									
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('H. échelle - H sonde (cm)')."</p>";		
							echo "<input name='hech_hsonde' id='hech_hsonde' value='' class='input_texte' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Recalage sonde')."</p>";		
							echo "<input name='hydro_recalage_sonde' id='hydro_recalage_sonde' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Recalage heure (hh:mm)')."</p>";		
							echo "<input name='hydro_recalage_heure_sonde' id='hydro_recalage_heure_sonde' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							//echo "<input class='input_texte' style='width:25px;height:20px;margin-right:10px;' name='check_purge_sonde' id='check_purge_sonde' type='checkbox' >";
							//echo "<span style='float:left;margin-top:5px;margin-left:0px;width:100px;font-size:12px;'>".htmlaccent('Purge / Etat sonde')."</span>";				
							echo "<p>".htmlaccent('Purge / Etat sonde')."</p>";
							echo "<input class='input_texte' style='width:25px;height:20px;margin-right:10px;' name='check_purge_sonde' id='check_purge_sonde' type='checkbox' >";
							
						echo "</div>\n";
						
					echo "<hr>\n";
					echo "</div>\n";


					// RELEVES PIEZO
					echo "<div id='boxpopup' class='elt_boite_piezo' style='display:none;'>\n";
						
						echo "<h2>".htmlaccent('Relevé puits')."</h2>\n";									
						
						echo "<div id='boite_small'>\n";
													
							echo "<p>".htmlaccent('Conductivite (&mu;/cm)')."</p>";		
							echo "<input name='piezo_conductivite' id='piezo_conductivite' value='' class='input_texte_small' type='text'>";
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Températue (°C)')."</p>";		
							echo "<input name='piezo_temperature' id='piezo_temperature' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Recalage différence')."</p>";		
							echo "<input name='piezo_recalage_diff' id='piezo_recalage_diff' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
					echo "<hr>\n";
					echo "</div>\n";

					echo "<div id='boxpopup' class='elt_boite_piezo' style='display:none;'>\n";
						
						echo "<h2>".htmlaccent('Mesure Nappe')."</h2>\n";									
						
						echo "<div id='boite_small'>\n";
													
							echo "<p>".htmlaccent('Nature du repère')."</p>";		
							echo "<input name='piezo_nature_repere' id='piezo_nature_repere' value='' class='input_texte' type='text'>";
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Instrument de mesure')."</p>";		
							echo "<input name='piezo_instrument' id='piezo_instrument' value='' class='input_texte' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Numéro instrument')."</p>";		
							echo "<input name='piezo_num_instrument' id='piezo_num_instrument' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Prof. toit de la nappe (m)')."</p>";		
							echo "<input name='piezo_prof_toitnappe' id='piezo_prof_toitnappe' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";						
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Prof. totale (m)')."</p>";		
							echo "<input name='piezo_prof_totale' id='piezo_prof_totale' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";

					echo "<hr>\n";
					echo "</div>\n";
						

				// LIGNE 3
					// Etat Appareil / APPAREIL
					echo "<div id='boxpopup'>\n";
						
						echo "<h2>".htmlaccent('Etat de l\'appareil')."</h2>\n";									
						
						// HYDRO 
						echo "<div id='boite_small' class='elt_boite_hydro' style='display:none;'>\n";
								
							echo "<p>".htmlaccent('Numéro de la sonde')."</p>";		
							echo "<input name='hydro_num_sonde' id='hydro_num_sonde' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						// PLU
						echo "<div id='boite_small' class='elt_boite_plu' style='display:none;'>\n";
								
							echo "<p>".htmlaccent('Nombre de basculements')."</p>";		
							echo "<input name='plu_nb_basculement' id='plu_nb_basculement' value='' class='input_texte_xsmall' type='text'>";	
									
						echo "</div>\n";

						// PIEZO
						echo "<div id='boite_small' class='elt_boite_piezo' style='display:none;'>\n";
								
							echo "<input class='input_texte' style='width:25px;' name='piezo_efface_memoire' id='piezo_efface_memoire' type='checkbox' >";
							echo "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Effacement Mémoire')."</span>";													
							echo "<hr>";
									
						echo "</div>\n";


						// ALL
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Nombre d\'octets')."</p>";		
							echo "<input name='nb_octet' id='nb_octet' value='' class='input_texte_xsmall' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Numéro de la batterie')."</p>";		
							echo "<input name='num_batterie' id='num_batterie' value='' class='input_texte' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Tension de la batterie')."</p>";		
							echo "<input name='tension_batterie' id='tension_batterie' value='' class='input_texte_xsmall' type='text'>";	
									
						echo "</div>\n";
						
					echo "<hr>\n";
					echo "</div>\n";
					
					// Position de la mesure (PIEZO)
					echo "<div id='boxpopup' class='elt_boite_piezo' style='display:none;'>\n";
						
						echo "<h2>".htmlaccent('Position de la mesure')."</h2>\n";
					
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('X - pos. GPS')."</p>";		
							echo "<input name='piezo_x_terrain' id='piezo_x_terrain' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";						
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Y - pos. GPS')."</p>";		
							echo "<input name='piezo_y_terrain' id='piezo_y_terrain' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";												
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Système coordonnées')."</p>";		
							echo "<input name='piezo_systeme_coord' id='piezo_systeme_coord' value='' class='input_texte' type='text'>";	
									
						echo "</div>\n";											
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Précision GPS')."</p>";		
							echo "<input name='piezo_gps_precision' id='piezo_gps_precision' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
					echo "<hr>\n";
					echo "</div>\n";
					
					// NOUVELLE CASSETTE (A mettre ensuite en option) (PLU / HYDRO)
					echo "<div id='boxpopup' class='elt_boite_pluhydro' style='display:none;'>\n";
						
						echo "<h2>".htmlaccent('Nouvelle cassette')."</h2>\n";									
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Numéro cassette')."</p>";		
							echo "<input name='num_cassette' id='num_cassette' value='' class='input_texte' type='text'>";	
									
						echo "</div>\n";
						
						echo "<div id='boite_small'>\n";
								
							echo "<p>".htmlaccent('Heure initialisation')."</p>";		
							echo "<input name='heure_init_cassette' id='heure_init_cassette' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						// HYDRO
						echo "<div id='boite_small' class='elt_boite_hydro' style='display:none;'>\n";
								
							echo "<p>".htmlaccent('Hauteur sonde (cm)')."</p>";		
							echo "<input name='hydro_h_sonde_cassette' id='hydro_h_sonde_cassette' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
						// PLU
						echo "<div id='boite_small' class='elt_boite_plu' style='display:none;'>\n";
								
							echo "<p>".htmlaccent('Heure du 1er bascul.')."</p>";		
							echo "<input name='plu_heure_bascul1_cassette' id='plu_heure_bascul1_cassette' value='' class='input_texte_small' type='text'>";	
									
						echo "</div>\n";
						
					echo "<hr>\n";
					echo "</div>\n";
				
				
					// Cette partie là ne s'affichera que si besoin avec un slide en attendant :  style='display:none;'
					// OPTION

						// DUREE EENREGISTREMENT
						echo "<div id='boxpopup' style='display:none;'>\n";
							
							echo "<h2>".htmlaccent('Durée de l\'Enregistrement')."</h2>\n";									
							
							echo "<div id='boite_small'>\n";
									
								echo "<p>".htmlaccent('Nb Jour')."</p>";		
								echo "<input name='duree_nb_jour id='duree_nb_jour' value='' class='input_texte_xsmall' type='text'>";	
										
							echo "</div>\n";
							
							echo "<div id='boite_small'>\n";
									
								echo "<p>".htmlaccent('Nb Heure')."</p>";		
								echo "<input name='duree_nb_heure' id='duree_nb_heure' value='' class='input_texte_xsmall' type='text'>";	
										
							echo "</div>\n";
							
							echo "<div id='boite_small'>\n";
									
								echo "<p>".htmlaccent('Nb Min')."</p>";		
								echo "<input name='duree_nb_min' id='duree_nb_min' value='' class='input_texte_xsmall' type='text'>";	
										
							echo "</div>\n";
							
						echo "<hr>\n";
						echo "</div>\n";
						
						
						// DERNIER ENREGISTREMENT
						
						echo "<div id='boxpopup' style='display:none;'>\n";
							
							echo "<h2>".htmlaccent('Dernier Enregistrement')."</h2>\n";									
							
							echo "<div id='boite_small'>\n";
									
								echo "<p>".htmlaccent('Nb Jour')."</p>";		
								echo "<input name='dernier_nb_jour id='dernier_nb_jour' value='' class='input_texte_xsmall' type='text'>";	
										
							echo "</div>\n";
							
							echo "<div id='boite_small'>\n";
									
								echo "<p>".htmlaccent('Nb Jour')."</p>";		
								echo "<input name='dernier_nb_heure' id='dernier_nb_heure' value='' class='input_texte_xsmall' type='text'>";	
										
							echo "</div>\n";
							
							echo "<div id='boite_small'>\n";
									
								echo "<p>".htmlaccent('Nb Min')."</p>";		
								echo "<input name='dernier_nb_min' id='dernier_nb_min' value='' class='input_texte_xsmall' type='text'>";	
										
							echo "</div>\n";
							
						echo "<hr>\n";
						echo "</div>\n";
				
				// LIGNE 4
				
				// OBSERVATIONS
				echo "<div id='boxpopup'>\n";
					
					echo "<h2 style='height:15px;'	>";
						
						echo "<p style='float:left;width:150px;font-size: 13px;font-weight: bold;color: #336699;'>";
							echo htmlaccent('Observations / Actions');
						echo "</p>";

						echo "<div style='float:left;width:150px;'>\n";
							echo "<input class='input_texte' style='width:20px;' name='check_faitmarquant' id='check_faitmarquant' type='checkbox' >";
							echo "<span style='float:left;margin-top:2px;width:90px;font-size:12px;'>".htmlaccent('Fait marquant')."</span>";													
							echo "<hr>";
						echo "</div>\n";
						
					echo "</h2>\n";	
					
					echo "<div id='boite_small' style='margin:0;'>\n";
												
						echo "<textarea name='ra_obs' id='ra_obs' style='width:280px;height:70px;'></textarea>\n";
						//echo "<p>".htmlaccent('Description')."</p>";		
								
					echo "</div>\n";

					
					// Partie des cases à cocher

					// Pour PLU et HYDRO
					echo "<div id='boite_small' class='elt_boite_pluhydro' style='margin:0;display:none;' >\n";

						echo "<div class='elt_boite_hydro' style='display:none;' >\n";
							echo "<input class='input_texte' style='width:25px;' name='check_jaugeage' id='check_jaugeage' type='checkbox' >";
							echo "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Jaugeage')."</span>";													
							echo "<hr>";
						echo "</div>\n";
						
						echo "<div class='elt_boite_plu' style='display:none;' >\n";
							echo "<input class='input_texte' style='width:25px;' name='check_bouchage' id='check_bouchage' type='checkbox' >";
							echo "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Bouchage')."</span>";													
							echo "<hr>";
						echo "</div>\n";
						
						echo "<div class='elt_boite_plu' style='display:none;' >\n";
							echo "<input class='input_texte' style='width:25px;' name='check_huile' id='check_huile' type='checkbox' >";
							echo "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Huile TOT')."</span>";													
							echo "<hr>";
						echo "</div>\n";

						echo "<div class='elt_boite_pluhydro' style='display:none;' >\n";
							echo "<input class='input_texte' style='width:25px;' name='check_debrouss' id='check_debrouss' type='checkbox' >";
							echo "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Débroussaillage')."</span>";														
							echo "<hr>";
						echo "</div>\n";												
					
					echo "</div>\n";
					
					// Pour PLU et HYDRO
					echo "<div id='boite_small' class='elt_boite_pluhydro' style='margin:0;display:none;'>\n";
					
						echo "<input class='input_texte' style='width:25px;' name='check_eaubat' id='check_eaubat' type='checkbox' >";
						echo "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Eau batterie')."</span>";													
						echo "<hr>";
						echo "<input class='input_texte' style='width:25px;' name='check_transfert' id='check_transfert' type='checkbox' >";
						echo "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Transfert')."</span>";													
						echo "<hr>";
						echo "<input class='input_texte' style='width:25px;' name='check_deletememory' id='check_deletememory' type='checkbox' >";
						echo "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Mémoire effacée')."</span>";													
								
					echo "</div>\n";

					// Pour PIEZO
					echo "<div id='boite_small' class='elt_boite_piezo' style='margin:0;display:none;'>\n";
					
						echo "<input class='input_texte' style='width:25px;' name='check_pompage_encours' id='check_pompage_encours' type='checkbox' >";
						echo "<span style='float:left;margin-top:5px;width:110px;font-size:12px;'>".htmlaccent('Pompage en cours')."</span>";													
						echo "<hr>";
						echo "<input class='input_texte' style='width:25px;' name='check_pompage_proche' id='check_pompage_proche' type='checkbox' >";
						echo "<span style='float:left;margin-top:5px;width:100px;font-size:12px;'>".htmlaccent('Pompage proche')."</span>";													
						echo "<hr>";
						echo "<input class='input_texte' style='width:25px;' name='check_piezo_pluie_crue' id='check_piezo_pluie_crue' type='checkbox' >";
						echo "<span style='float:left;margin-top:5px;width:100px;font-size:12px;'>".htmlaccent('Pluie et/ou Crue')."</span>";																							
								
					echo "</div>\n";

					// Pour PIEZO
					echo "<div id='boite_small' class='elt_boite_piezo' style='margin:0;display:none;'>\n";
					
						/* Pour le moment je ne sais pas à quoi cela correspond
						echo "<input class='input_texte' style='width:25px;' name='check_piezo_temps_sec' id='check_piezo_temps_sec' type='checkbox' >";
						echo "<span style='float:left;margin-top:5px;width:100px;font-size:12px;'>".htmlaccent('Temps secondes')."</span>";													
						echo "<hr>";
						*/
						echo "<input class='input_texte' style='width:25px;' name='check_piezo_photos' id='check_piezo_photos' type='checkbox' >";
						echo "<span style='float:left;margin-top:5px;width:100px;font-size:12px;'>".htmlaccent('Photos')."</span>";													
								
					echo "</div>\n";
					
				echo "<hr>\n";
				echo "</div>\n";
			
			
				// NEXT TOURNEE
				echo "<div id='boxpopup' style='width:420px;' >\n";
						
					echo "<h2 style='height:15px;'>";
						
						echo "<p style='float:left;width:240px;font-size: 13px;font-weight: bold;color: #336699;'>";
							echo htmlaccent('Actions à réaliser - Prochaine tournée');
						echo "</p>";

						echo "<div style='float:left;width:150px;'>\n";
							echo "<input class='input_texte' style='width:20px;' name='check_premarquant' id='check_premarquant' type='checkbox' >";
							echo "<span style='float:left;margin-top:2px;width:110px;font-size:12px;'>".htmlaccent('Prévoir marquant')."</span>";													
							echo "<hr>";
						echo "</div>\n";
						
					echo "</h2>\n";									
							
					echo "<div id='boite_small'>\n";
												
						echo "<textarea name='ra_futur' id='ra_futur' style='width:280px;height:70px;'></textarea>\n";
						//echo "<p>".htmlaccent('Description')."</p>";		
								
					echo "</div>\n";
					
				echo "<hr>\n";
				echo "</div>\n";
			
				// LIGNE 5
				
				// AGENT					
				echo "<div id='boxpopup'>\n";
					
					echo "<h2>".htmlaccent('Agents ayant participés')."</h2>\n";									
					
					echo "<div id='boite_small' style='width:400px;'>\n";
						
						$selected = '';		
						if(isset($agent_array))
						{
							foreach($agent_array as $key => $value)
							{
								echo "<div style='float:left;'>\n";
									echo "<input class='input_texte' style='width:25px;padding:0;' name='check_agent_".$key."' id='check_agent_".$key."' type='checkbox' >";	
									echo "<span style='float:left;margin-right:8px;font-size:12px;'>".$value."</span>";
								echo "<hr>\n";
								echo "</div>\n";
							}
						}

					echo "</div>\n";
					
					echo "<div id='boite_small'>\n";
							
						echo "<p>".htmlaccent('Autre(s) personne(s) présente(s)')."</p>";
						echo "<input name='agents_complement' id='agents_complement' value='' class='input_texte'  style='width:300px;' type='text'>";
								
					echo "</div>\n";
					
				echo "<hr>\n";
				echo "</div>\n";

				// Boutton permettant d'afficher la fiche de saisie du profil piézo
				echo "<div id='boxpopup' class='elt_boite_piezo' style='display:none;background:transparent;border:0px;'>\n";
					
					echo "<input type='button' class='button_profil' name='buttonProfil' id='buttonProfil' value='".htmlaccent('Profil de Conductivité')."' onClick='affiche_RA_piezoprofil();'>";	
										
				echo "<hr>\n";
				echo "</div>\n";
				
				
				// --LIGNE --------------------------------
				echo "<hr>";
				
				// Barre de navigation + Bouttons de validation
				
				echo "<div id='popup_barredown'>\n";
					
					echo "<div id='popup_nav' style='width:470px;'>\n";
							
						// Flèches Previous	
						echo "<div id='content_arrow' class='content_arrow'>";
												
							echo "<div id='arrow_previous' style='display:none;'>";
							
								echo "<a id='arrow_first_a'>";
									echo "<img src='".DIR_WS_IMG_ICO."arrow_first.png' style='width:50px;margin-right:30px;cursor:pointer;' title='".htmlaccent('Premier RA')."' onmouseover=\"this.src='".DIR_WS_IMG_ICO."arrow_first_over.png';\" onmouseout=\"this.src='".DIR_WS_IMG_ICO."arrow_first.png';\" >";
								echo "</a>";
								
								echo "<a id='arrow_previous_a'>";
									echo "<img src='".DIR_WS_IMG_ICO."arrow_previous.png' style='width:25px;cursor:pointer;' title='".htmlaccent('RA précédent')."' onmouseover=\"this.src='".DIR_WS_IMG_ICO."arrow_previous_over.png';\" onmouseout=\"this.src='".DIR_WS_IMG_ICO."arrow_previous.png';\" >";
								echo "</a>";
								
							
							echo "</div>";
						
						echo "</div>";
						
						// Compteur de RA
						echo "<div id='content_arrow' class='content_arrow'>";
							
							echo "<input type='text' value='' id='num_fiche' disabled>";
							
						echo "</div>";
						
						// Flèches Next
						echo "<div id='content_arrow' class='content_arrow'>";
						
							echo "<div id='arrow_next' style='display:none;'>";
							
								echo "<a id='arrow_next_a'>";
									echo "<img src='".DIR_WS_IMG_ICO."arrow_next.png' style='width:25px;cursor:pointer;' title='".htmlaccent('RA suivant')."' onmouseover=\"this.src='".DIR_WS_IMG_ICO."arrow_next_over.png';\" onmouseout=\"this.src='".DIR_WS_IMG_ICO."arrow_next.png';\" >";
								echo "</a>";
								
								echo "<a id='arrow_last_a'>";
									echo "<img src='".DIR_WS_IMG_ICO."arrow_end.png' style='width:50px;margin-left:30px;cursor:pointer;' title='".htmlaccent('Dernier RA')."' onmouseover=\"this.src='".DIR_WS_IMG_ICO."arrow_end_over.png';\" onmouseout=\"this.src='".DIR_WS_IMG_ICO."arrow_end.png';\" >";
								echo "</a>";
								
							echo "</div>";
						
						echo "</div>";
						
					echo "</div>\n";	
					
					echo "<div id='popup_nav' style='margin-left:20px;width:550px;'>\n";
						
						echo "<table id='stats_select' cellspacing='0' >";
				
							echo "<tr style='margin:0;'>";
								
								echo "<td class='bold' style='width:350px;'>";
									//<input type='submit' class='button_valid' name='valid_ra' value=\"Valider\"  style='margin-right:20px;'/>

									echo "<div id='bloc_valid_ra' style='display:none;'>";
										echo "<p style='float:left;font-size:14px;text-align:center;'>".htmlaccent('Validation <br> RA')."</p>";
										echo "<input type='checkbox' name='check_valid_ra' id='check_valid_ra' style='float:left;width:30px;height:30px;margin-left:20px;' >";
									echo "</div>";
									
								echo "</td>";

								echo "<td style='width:30px;'>&nbsp;</td>";
								
								echo "<td class='bold'><input type='submit' class='button' name='save_ra' value=\"Enregistrer\"/></td>";
								
								echo "<td style='width:30px;'>&nbsp;</td>";
								
								echo "<td class='bold'><input type='button' class='button_close'  value=\"Annuler\" onclick=\"document.getElementById('box_ra').style.display='none';\"/></td>";
																
							echo "</tr>";
							
						echo "</table>";
						
						
					echo "</div>\n";
				
				echo "</div>\n";
				
			//echo "</form>";
		
		echo "</div>\n";	
		
	echo "</div>\n";
	

echo "</div>\n";

?>


<script type="text/javascript">
	
	// Récupère le popup et le bouton qui l'ouvre
	var popup = document.getElementById('cadre_view');
	var box = document.getElementById('box_ra');
		
	// Ajoute un événement de clic au document
	document.addEventListener("click", function(event)
	{
		// Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
		if (event.target !== popup && event.target === box) 
		{
			// Ferme le popup
			box.style.display = "none";
		}
	});
		  
</script>