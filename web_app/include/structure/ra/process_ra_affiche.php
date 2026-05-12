<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Procédure pour afficher dans un RA dans le bloc d'affichage
Processus asynchrone AJAX coté serveur
----------------------------------------
*/

// ----------------------------------------------
// nécessaire pour la configuration du script

require('../../config.php');
require('../../database_tables.php');

require('../../function/date.php');	
require('../../function/database.php');	
require('../../function/html_output.php');
require('../../function/general.php');

// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');

// Récupération des données JSON envoyées depuis la requête AJAX
$jsonDataInfo = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataInfo = json_decode($jsonDataInfo, true);

// Accéder aux données du tableau récupérer
$territoire_id = $dataInfo['territoire_id'];
$id_ra = $dataInfo['id_ra'];

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

// TABLE USER
$sql_user_list = "SELECT DISTINCT id, id_statut, login, nom, prenom FROM ".TABLE_USER;
$user_list_query = tep_db_query($sql_link,$sql_user_list);
while ($user_list = tep_db_fetch_array($user_list_query))
{
    $id = $user_list['id'];
    $id_statut = $user_list['id_statut'];
	$login = htmlaccent(html_entity_decode($user_list['login'] ?? $default_string));
	$nom = ucfirst(strtolower(htmlaccent(html_entity_decode($user_list['nom'] ?? $default_string))));
	$prenom = ucfirst(strtolower(htmlaccent(html_entity_decode($user_list['prenom'] ?? $default_string))));

	$user_list_array[$id] = array('id_statut' => $id_statut,
                                    'login' => $login,
                                    'nom' => $nom,
                                    'prenom' => $prenom
                                    );
}

// TABLE COMMUNE
$sql_commune = "SELECT DISTINCT c.id_commune, c.nom_commune 
				FROM ".TABLE_COMMUNE." c
				JOIN ".TABLE_REGION." r ON c.id_region=r.id_region
				WHERE r.id_territoire=".$territoire_id."
				ORDER BY c.nom_commune ASC";

$commune_query = tep_db_query($sql_link,$sql_commune);
while ($commune = tep_db_fetch_array($commune_query))
{
	$commune_array[$commune['id_commune']] = $commune['nom_commune'];
}

// TABLE STATION
$sql_station_all = "SELECT DISTINCT id_station, nom_station, code_station, station_type, active_station
					FROM ".TABLE_STATION." 
				    ORDER BY nom_station ASC";
$station_all_query = tep_db_query($sql_link,$sql_station_all);
while ($station_all = tep_db_fetch_array($station_all_query))
{	
    $nom_station = htmlaccent(html_entity_decode($station_all['nom_station'] ?? $default_string));

	$station_all_array[$station_all['id_station']] = array('code_station' => $station_all['code_station'],
															'nom_station' => $nom_station,
															'station_type' => $station_all['station_type'],
															);
}

// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
while ($eq_type_tab = tep_db_fetch_array($eq_type_query))
{
	$eq_type_array[$eq_type_tab['id_eq_type']] = array('nom_eq_type' => $eq_type_tab['nom_eq_type'],
                                                        'unite_eq_type' => $eq_type_tab['unite_eq_type'],
                                                        'valeur_data_type' => $eq_type_tab['valeur_data_type'],
														'type_graph' => $eq_type_tab['type_graph'],
                                                        'type_color_border' => $eq_type_tab['type_color_border']
                                                    );
}

// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, to_periode, id_chon_periode
				  FROM ".TABLE_TYPE_DATA." 
				  ORDER BY init_type_data ASC";
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
while ($type_chron_tab = tep_db_fetch_array($type_chron_query))
{
    $axe_nom = '';
    if(isset($data_type_axe_array[$type_chron_tab['axe_data']]['axe'])){$axe_nom = $data_type_axe_array[$type_chron_tab['axe_data']]['axe'];}

	$type_chron_array[$type_chron_tab['id_data_type']] = array('init_type_data' => $type_chron_tab['init_type_data'],
															'nom_type_data' => $type_chron_tab['nom_type_data'],
															'id_eq_type_data' => $type_chron_tab['id_eq_type_data'],
															'axe_nom' => $axe_nom,
															'unite' => $type_chron_tab['unite'],
															'to_periode' => $type_chron_tab['to_periode'],
															'id_chon_periode' => $type_chron_tab['id_chon_periode']
															);
}

// -------------------------------------------------------------


// Initialisation Variables
$tab_html = '';
$nb_ra = 0;
$nb_ra_valid = 0;
$row = 0;

$today_time = date('d-m-Y H:i');

// Requête d'accès aux RA
$sql_RA = "SELECT DISTINCT ra.id_ra, ra.id_agent_user, ra.id_station, 
                            ra.date_heure_ra, ra.id_eq_type,
                            ra.type_appareil, ra.num_appareil, ra.heure_appareil, ra.plu_taille_auget, ra.etat_ra, 
                            ra.hydro_heure_cote, ra.hydro_h_sonde, ra.hydro_h_echelle_1, ra.hydro_h_echelle_2, ra.hydro_num_sonde,
                            ra.plu_tot_type, ra.plu_tot_first, ra.plu_tot_last, ra.plu_tot_heure_basc,
                            ra.plu_cumul_tot, ra.plu_cumul_plu, ra.plu_diff_tot_plu, ra.plu_recalage_heure_plu, ra.plu_test_auget, ra.plu_nb_basculement,
                            ra.nb_octet, ra.num_batterie, ra.tension_batterie, ra.num_cassette, ra.heure_init_cassette,
                            ra.hydro_h_sonde_cassette, ra.plu_heure_bascul1_cassette,
                            ra.hydro_recalage_sonde, ra.hydro_recalage_heure_sonde, ra.hydro_purge_sonde, 
                            ra.hydro_ra_jaugeage, ra.plu_ra_bouchage, ra.plu_ra_huile_tot, ra.ra_debroussaillage, ra.ra_eau_batterie, ra.ra_transfert_data, ra.ra_delete_memory, 
                            ra.piezo_conductivite, ra.piezo_temperature, ra.piezo_recalage_diff, ra.piezo_nature_repere,
                            ra.piezo_instrument, ra.piezo_num_instrument, ra.piezo_prof_toitnappe, ra.piezo_prof_totale, ra.piezo_x_terrain, ra.piezo_y_terrain, ra.piezo_gps_precision,
                            ra.piezo_systeme_coord, ra.piezo_pompage_encours, ra.piezo_pompage_proche, ra.piezo_pluie_crue, ra.piezo_temps_sec, ra.piezo_photos,
                            ra.ra_obs, ra.ra_futur, ra.name_file_data, ra.obs_file_data, ra.pre_marquant, ra.fait_marquant, ra.agents_complement 
            FROM ".TABLE_DATA_RA." ra
            WHERE id_ra = ".$id_ra;       

$RA_query = tep_db_query($sql_link,$sql_RA);
$RA_tab = tep_db_fetch_array($RA_query);

    $id_ra =  $RA_tab['id_ra'];
    $type_data =  $RA_tab['id_eq_type']; // 1 : Pluvio - 5 : Piézo - 11 : Hydro

    $id_agent_user =  $RA_tab['id_agent_user'];

	$id_station =  $RA_tab['id_station'];
/*
	$nom_station =  nettoyer_et_echapper($RA_tab['nom_station']);
	
    $code_station =  nettoyer_et_echapper($RA_tab['code_station']);
*/
    // TITRE - Cartouche du haut - (ALL)
    $tab_html .= "<table id='tab_titre_popup' cellspacing='0'>";
						
        $tab_html .= "<tr>";
            
            $tab_html .= "<td class='titre'>";
                
                // Affichage Type de Mesure - Liste défilante
                $tab_html .= "<p style='margin-right:20px;'>";

                    $tab_html .= "<select name='select_type_ra' id='select_type_ra' class='titre_ra' style='width:150px;height:30px;' onchange='select_boxRA_typeData(this.value);'>";
                                
                        $selected = '';		

                        if(isset($eq_type_array))
                        {
                            foreach($eq_type_array as $key => $value)
                            {
                                if($key === $type_data){$selected="selected";}	
                                else{$selected = '';}											
                                $tab_html .= "<option value='".$key."' ".$selected.">".$value['nom_eq_type']."</option>";
                            }
                        }
                    $tab_html .= "</select>";
                    
                $tab_html .= "</p> \n";
                
                // Affichage Station
                $tab_html .= "<p style='width:400px;'>";
                    
                    $tab_html .= "<select name='select_station_ra' id='select_station_ra' class='titre_ra' style='width:100%;height:30px;' >";
                            
                        $selected = '';			

                        if(isset($station_all_array))
                        {
                            foreach($station_all_array as $key => $value)
                            {
                                //if($key == $select_station_encours){$selected="selected";}	
                                //else{$selected = '';}											
                                $tab_html .= "<option value='".$key."' ".$selected.">".$value['code_station']." - ".$value['nom_station']."</option>";
                            }
                        }
                    
                    $tab_html .= "</select>";	
                    
                $tab_html .= "</p> \n";
                
                // Puce validation RA 
                if($RA_tab['etat_ra'] > 0){$tab_html .= "<img src='".DIR_WS_IMG_ICO."puce_verte.png' id='valid_puce_ok' title='".htmlaccent('RA validé')."'>";}
                else{$tab_html .= "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' id='valid_puce_no' title='".htmlaccent('RA non validé')."'>";}
                
                // Affichage de la date de Saisie de de l'agent qui s'est connectée sur la plateforme pour saisir qui a saisie
                $tab_html .= "<p style='float:right;margin-top:0px;'>";
                    
                    $tab_html .= "<span>".htmlaccent('Saisi le : ')."</span>";
                    $tab_html .= "<input type='text' name='date_saisie' id='date_saisie' value='".$today_time."' disabled>";
                    $tab_html .= "<br>";
                    $tab_html .= "<span>".htmlaccent('par : ')."</span>";
                    //$tab_html .= "<input type='text' style='input_texte_300' name='agent_saisie' id='agent_saisie' value='".$tab_session['prenom'].' '.$tab_session['nom']."' disabled>";
                                                    
                $tab_html .= "</p> \n";

            $tab_html .= "</td>";					
            
        $tab_html .= "</tr>";
        
    $tab_html .= "</table>";


    // ----------------------------------------------------------------------------
    // LIGNE 1				  	

    // Releve des données sur un enregistreur (fichier de données brutes) - (ALL)
    $tab_html .= "<div id='boxpopup'>\n";
    
        $tab_html .= "<h2>".htmlaccent('Relève')."</h2>\n";
    
        // Date Releve
        $date_heure_ra_tab =  explode(" ",$RA_tab['date_heure_ra']);
        $date_ra = dateus_fr($date_heure_ra_tab[0]);
        $heure_ra = $date_heure_ra_tab[1];

        $tab_html .= "<div id='boite_small'>\n";
            
            $tab_html .= "<p>".htmlaccent('Date (jj-mm-aaaa)')."</p>\n";	
            $tab_html .= "<input class='input_texte' style='width:80px;' name='date_releve' id='date_releve' type='text' value='".$date_ra."' >\n"; 
                    
        $tab_html .= "</div>\n";
        
        // Heure Releve
        $tab_html .= "<div id='boite_small'>\n";
                                        
            $tab_html .= "<p>".htmlaccent('Heure (hh:mm)')."</p>";
            $tab_html .= "<input name='heure_releve' id='heure_releve' value='".$heure_ra."' class='input_texte_small' type='text'>";
                    
        $tab_html .= "</div>\n";
        
        // Fichier Releve
        $tab_html .= "<div id='boite_small'>\n";
                                        
            $tab_html .= "<p>".htmlaccent('Nom du fichier de relève / Num Cassette')."</p>";
            $tab_html .= "<input name='fichier_releve' id='fichier_releve' value='".$RA_tab['name_file_data']."' class='input_texte'  style='width:250px;' type='text'>";
                    
        $tab_html .= "</div>\n";
        
    $tab_html .= "<hr>\n";
    $tab_html .= "</div>\n";	
    
    // Appareil - Type, Num, HeureAppareil (ALL)
    $tab_html .= "<div id='boxpopup'>\n";
    
        $tab_html .= "<h2>".htmlaccent('Appareil')."</h2>\n";
    
        // Type Appareil
        $tab_html .= "<div id='boite_small'>\n";
                                        
            $tab_html .= "<p>".htmlaccent('Type')."</p>";
            $tab_html .= "<input name='type_appareil' id='type_appareil' value='".$RA_tab['type_appareil']."' class='input_texte' type='text' >";
                    
        $tab_html .= "</div>\n";
        
        // Numéro Appareil
        $tab_html .= "<div id='boite_small'>\n";
                                        
            $tab_html .= "<p>".htmlaccent('Numéro')."</p>";	
            $tab_html .= "<input name='num_appareil' id='num_appareil' value='".$RA_tab['num_appareil']."' class='input_texte_small' type='text'>";
                            
        $tab_html .= "</div>\n";
        
        // Heure Appareil
        $tab_html .= "<div id='boite_small'>\n";
                                        
            $tab_html .= "<p>".htmlaccent('Heure (hh:mm:ss)')."</p>";	
            $tab_html .= "<input name='heure_appareil' id='heure_appareil' value='".$RA_tab['heure_appareil']."' class='input_texte_small' type='text'>";						
                    
        $tab_html .= "</div>\n";

        
    $tab_html .= "<hr>\n";
    $tab_html .= "</div>\n";


    // ----------------------------------------------------------------------------
    // LIGNE 2						  

    // PLUVIO
    if($type_data == 1)
    {
        // TOTALISATEUR
        $tab_html .= "<div id='boxpopup' class='elt_boite_plu'>\n";
        
            $tab_html .= "<h2>".htmlaccent('Relevé Totalisateur')."</h2>\n";									
            
            $tab_html .= "<div id='boite_small'>\n";
                                        
                $tab_html .= "<p>".htmlaccent('Type de Tot')."</p>";		
                $tab_html .= "<input name='plu_tot_type' id='plu_tot_type' value='".$RA_tab['plu_tot_type']."' class='input_texte_small' type='text'>";
                        
            $tab_html .= "</div>\n";
            
            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('Cumul Arrivée (mm)')."</p>";		
                $tab_html .= "<input name='plu_tot_first' id='plu_tot_first' value='".$RA_tab['plu_tot_first']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";
            
            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('Cumul Départ (mm)')."</p>";		
                $tab_html .= "<input name='plu_tot_last' id='plu_tot_last' value='".$RA_tab['plu_tot_last']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";

            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('Heure Basc.')."</p>";		
                $tab_html .= "<input name='plu_tot_heure_basc' id='plu_tot_heure_basc' value='".$RA_tab['plu_tot_heure_basc']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";
            
        $tab_html .= "<hr>\n";
        $tab_html .= "</div>\n";

        // CONTROLE PLU
        $tab_html .= "<div id='boxpopup' class='elt_boite_plu' >\n";
                
            $tab_html .= "<h2>".htmlaccent('Contrôle')."</h2>\n";									
            
            $tab_html .= "<div id='boite_small'>\n";
                                        
                $tab_html .= "<p>".htmlaccent('Cumul TOT (mm)')."</p>";		
                $tab_html .= "<input name='plu_cumul_tot' id='plu_cumul_tot' value='".$RA_tab['plu_cumul_tot']."' class='input_texte_small' type='text'>";
                        
            $tab_html .= "</div>\n";
            
            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('Cumul Pluvio (mm)')."</p>";		
                $tab_html .= "<input name='plu_cumul_plu' id='plu_cumul_plu' value='".$RA_tab['plu_cumul_plu']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";
            
            $plu_diff_tot_plu = (float)$RA_tab['plu_cumul_tot'] - (float)$RA_tab['plu_cumul_plu'];

            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('Diff : TOT - Pluvio (mm)')."</p>";	// ça doit se calculer automatiquement	
                $tab_html .= "<input name='plu_diff_tot_plu' id='plu_diff_tot_plu' value='".$plu_diff_tot_plu."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";
            
            // Recalage heure que l'on retrouve aussi dans Limni - à réfléchir si on utilise 2 champs
            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('Calage heure (hh:mm)')."</p>";		
                $tab_html .= "<input name='plu_recalage_heure_plu' id='plu_recalage_heure_plu' value='".$RA_tab['plu_recalage_heure_plu']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";
            
            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('Test Auget')."</p>";		
                $tab_html .= "<input name='plu_test_auget' id='plu_test_auget' value='".$RA_tab['plu_test_auget']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";

            // Il manque peut être plu_nb_basculement
            
        $tab_html .= "<hr>\n";
        $tab_html .= "</div>\n";
    }
    
    if($type_data == 11)
    {
        // COTE LIMNIMETRIQUE
        $tab_html .= "<div id='boxpopup' class='elt_boite_hydro' '>\n";
                                
            $tab_html .= "<h2>".htmlaccent('Côtes limnimétriques')."</h2>\n";									

            $tab_html .= "<div id='boite_small'>\n";
                                        
                $tab_html .= "<p>".htmlaccent('Heure (hh:mm:ss)')."</p>";		
                $tab_html .= "<input name='hydro_heure_cote' id='hydro_heure_cote' value='".$RA_tab['hydro_heure_cote']."' class='input_texte_small' type='text'>";
                        
            $tab_html .= "</div>\n";

            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('H. sonde (cm)')."</p>";		
                $tab_html .= "<input name='hydro_h_sonde' id='hydro_h_sonde' value='".$RA_tab['hydro_h_sonde']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";

            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('H. échelle (cm)')."</p>";		
                $tab_html .= "<input name='hydro_h_echelle_1' id='hydro_h_echelle_1' value='".$RA_tab['hydro_h_echelle_1']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";

            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('H. échelle 2 (cm)')."</p>";		
                $tab_html .= "<input name='hydro_h_echelle_2' id='hydro_h_echelle_2' value='".$RA_tab['hydro_h_echelle_2']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";

        $tab_html .= "<hr>\n";
        $tab_html .= "</div>\n";

        // CONTROLE HYDRO
        $tab_html .= "<div id='boxpopup' class='elt_boite_hydro' style='display:none;'>\n";

            $tab_html .= "<h2>".htmlaccent('Contrôle des mesures de hauteur')."</h2>\n";									

            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('H. échelle - H sonde (cm)')."</p>";		
                $tab_html .= "<input name='hydro_h_sonde' id='hydro_h_sonde' value='".$RA_tab['hydro_h_sonde']."' class='input_texte' type='text'>";	
                        
            $tab_html .= "</div>\n";

            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('Recalage sonde')."</p>";		
                $tab_html .= "<input name='hydro_recalage_sonde' id='hydro_recalage_sonde' value='".$RA_tab['hydro_recalage_sonde']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";

            $tab_html .= "<div id='boite_small'>\n";
                    
                $tab_html .= "<p>".htmlaccent('Recalage heure (hh:mm)')."</p>";		
                $tab_html .= "<input name='hydro_recalage_heure_sonde' id='hydro_recalage_heure_sonde' value='".$RA_tab['hydro_recalage_heure_sonde']."' class='input_texte_small' type='text'>";	
                        
            $tab_html .= "</div>\n";

            $tab_html .= "<div id='boite_small'>\n";
                    
                //$tab_html .= "<input class='input_texte' style='width:25px;height:20px;margin-right:10px;' name='check_purge_sonde' id='check_purge_sonde' type='checkbox' >";
                //$tab_html .= "<span style='float:left;margin-top:5px;margin-left:0px;width:100px;font-size:12px;'>".htmlaccent('Purge / Etat sonde')."</span>";				
                $tab_html .= "<p>".htmlaccent('Purge / Etat sonde')."</p>";
                $tab_html .= "<input class='input_texte' style='width:25px;height:20px;margin-right:10px;' name='check_purge_sonde' id='check_purge_sonde' type='checkbox' >";
                
            $tab_html .= "</div>\n";

        $tab_html .= "<hr>\n";
        $tab_html .= "</div>\n";


    }

/*

				
					
					
					// COTE LIMNIMETRIQUE
					$tab_html .= "<div id='boxpopup' class='elt_boite_hydro' style='display:none;'>\n";
						
						$tab_html .= "<h2>".htmlaccent('Côtes limnimétriques')."</h2>\n";									
						
						$tab_html .= "<div id='boite_small'>\n";
													
							$tab_html .= "<p>".htmlaccent('Heure (hh:mm:ss)')."</p>";		
							$tab_html .= "<input name='hydro_heure_cote' id='hydro_heure_cote' value='".$time."' class='input_texte_small' type='text'>";
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('H. sonde (cm)')."</p>";		
							$tab_html .= "<input name='hydro_h_sonde' id='hydro_h_sonde' value='' class='input_texte_small' type='text'>";	
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('H. échelle (cm)')."</p>";		
							$tab_html .= "<input name='hydro_h_echelle_1' id='hydro_h_echelle_1' value='' class='input_texte_small' type='text'>";	
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('H. échelle 2 (cm)')."</p>";		
							$tab_html .= "<input name='hydro_h_echelle_2' id='hydro_h_echelle_2' value='' class='input_texte_small' type='text'>";	
									
						$tab_html .= "</div>\n";
						
					$tab_html .= "<hr>\n";
					$tab_html .= "</div>\n";
					
					// CONTROLE HYDRO
					$tab_html .= "<div id='boxpopup' class='elt_boite_hydro' style='display:none;'>\n";
						
						$tab_html .= "<h2>".htmlaccent('Contrôle des mesures de hauteur')."</h2>\n";									
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('H. échelle - H sonde (cm)')."</p>";		
							$tab_html .= "<input name='hech_hsonde' id='hech_hsonde' value='' class='input_texte' type='text'>";	
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('Recalage sonde')."</p>";		
							$tab_html .= "<input name='hydro_recalage_sonde' id='hydro_recalage_sonde' value='' class='input_texte_small' type='text'>";	
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('Recalage heure (hh:mm)')."</p>";		
							$tab_html .= "<input name='hydro_recalage_heure_sonde' id='hydro_recalage_heure_sonde' value='' class='input_texte_small' type='text'>";	
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							//$tab_html .= "<input class='input_texte' style='width:25px;height:20px;margin-right:10px;' name='check_purge_sonde' id='check_purge_sonde' type='checkbox' >";
							//$tab_html .= "<span style='float:left;margin-top:5px;margin-left:0px;width:100px;font-size:12px;'>".htmlaccent('Purge / Etat sonde')."</span>";				
							$tab_html .= "<p>".htmlaccent('Purge / Etat sonde')."</p>";
							$tab_html .= "<input class='input_texte' style='width:25px;height:20px;margin-right:10px;' name='check_purge_sonde' id='check_purge_sonde' type='checkbox' >";
							
						$tab_html .= "</div>\n";
						
					$tab_html .= "<hr>\n";
					$tab_html .= "</div>\n";


					// RELEVES PIEZO
					$tab_html .= "<div id='boxpopup' class='elt_boite_piezo' style='display:none;'>\n";
						
						$tab_html .= "<h2>".htmlaccent('Relevé puits')."</h2>\n";									
						
						$tab_html .= "<div id='boite_small'>\n";
													
							$tab_html .= "<p>".htmlaccent('Conductivite (&mu;/cm)')."</p>";		
							$tab_html .= "<input name='piezo_conductivite' id='piezo_conductivite' value='' class='input_texte_small' type='text'>";
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('Températue (°C)')."</p>";		
							$tab_html .= "<input name='piezo_temperature' id='piezo_temperature' value='' class='input_texte_small' type='text'>";	
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('Recalage différence')."</p>";		
							$tab_html .= "<input name='piezo_recalage_diff' id='piezo_recalage_diff' value='' class='input_texte_small' type='text'>";	
									
						$tab_html .= "</div>\n";
						
					$tab_html .= "<hr>\n";
					$tab_html .= "</div>\n";

					$tab_html .= "<div id='boxpopup' class='elt_boite_piezo' style='display:none;'>\n";
						
						$tab_html .= "<h2>".htmlaccent('Mesure Nappe')."</h2>\n";									
						
						$tab_html .= "<div id='boite_small'>\n";
													
							$tab_html .= "<p>".htmlaccent('Nature du repère')."</p>";		
							$tab_html .= "<input name='piezo_nature_repere' id='piezo_nature_repere' value='' class='input_texte' type='text'>";
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('Instrument de mesure')."</p>";		
							$tab_html .= "<input name='piezo_instrument' id='piezo_instrument' value='' class='input_texte' type='text'>";	
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('Numéro instrument')."</p>";		
							$tab_html .= "<input name='piezo_num_instrument' id='piezo_num_instrument' value='' class='input_texte_small' type='text'>";	
									
						$tab_html .= "</div>\n";
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('Prof. toit de la nappe (m)')."</p>";		
							$tab_html .= "<input name='piezo_prof_toitnappe' id='piezo_prof_toitnappe' value='' class='input_texte_small' type='text'>";	
									
						$tab_html .= "</div>\n";						
						
						$tab_html .= "<div id='boite_small'>\n";
								
							$tab_html .= "<p>".htmlaccent('Prof. totale (m)')."</p>";		
							$tab_html .= "<input name='piezo_prof_totale' id='piezo_prof_totale' value='' class='input_texte_small' type='text'>";	
									
						$tab_html .= "</div>\n";

					$tab_html .= "<hr>\n";
					$tab_html .= "</div>\n";

*/

// Remplissage du tableau de retour

$responseData = array(
    'tab_html' => $tab_html
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>