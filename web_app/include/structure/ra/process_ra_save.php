<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Enregistrement d'une fiche RA (Modification ou Création)
- post_secure : est une fonction accessible dans include/function/ permettant de contrôler et de corriger les entrées depuis des formulaires. Focntion de Sécurité contre insertion JS et PHP
- Pour la vérification des champs valeur numéric, date ou heure, les valeurs vide sont valides. Il faut se référer aux fonctions de contrôle validDate(), validTime(), valid

Processus asynchrone AJAX coté serveur
----------------------------------------
*/

// ----------------------------------------------
// nécessaire pour la configuration du script

require('../../config.php');
require('../../database_tables.php');

require('../../function/date.php');	
require('../../function/gestion_erreur.php');	
require('../../function/database.php');	
require('../../function/html_output.php');
require('../../function/general.php');

// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');



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

// -------------------------------------------------------------


// Initialisation Variables Globales
$msg_info_send = '';
$msg_info = '';
$erreur = false;
$tab_html = '';
$newRA = false;
$row = 0;

// Vérifier si la requête envoyé depuis le client est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $id_user_agent = isset($_POST['id_user_agent']) ? $_POST['id_user_agent'] : '';
    $territoire_id = isset($_POST['territoire_id']) ? $_POST['territoire_id'] : '';
    $id_ra = isset($_POST['id_ra']) ? $_POST['id_ra'] : '';
    $type_data = isset($_POST['type_data']) ? $_POST['type_data'] : '';
    
    $check_valid_ra = 0;
    if(isset($_POST['check_valid_ra'])){$check_valid_ra=1;}   

    $select_station_ra = isset($_POST['select_station_ra']) ? $_POST['select_station_ra'] : '';

    if(!isset($station_all_array[$select_station_ra]))
    {
        $erreur = true;
        $msg_info .= "La station n'existe pas.<br>"; 
    }
    
    $date_heure_saisie_fr = isset($_POST['date_heure_saisie_fr']) ? $_POST['date_heure_saisie_fr'] : '';
    $date_heure_saisie_us = '';
    if(tep_not_null($date_heure_saisie_fr))
    {
        $date_heure_saisie_tab =  explode(" ",$date_heure_saisie_fr);
        $date_heure_saisie_us =  datefr_us($date_heure_saisie_tab[0]).' '.$date_heure_saisie_tab[1];
    }
    //$date_heure_ra = $date_heure_saisie_us;

    // ----------------------------------------------------------
    // ALL - Relève

        $date_releve = isset($_POST['date_releve']) ? $_POST['date_releve'] : '';
        if(!validDate($date_releve)) // Vérifier format date
        {
            $erreur = true;
            $msg_info .= "La date de relève n'est pas au bon format : dd-mm-YYYY.<br>"; 
            $date_releve = '';
        }

        $heure_releve = isset($_POST['heure_releve']) ? $_POST['heure_releve'] : '';
        if(!validTime($heure_releve)) // Vérifier format heure
        {
            $erreur = true;
            $msg_info .= "L'heure de relève n'est pas au bon format : hh:mm:ss ou hh:mm.<br>";
            $heure_releve = '';
        }

        if(!$erreur)
        {
            $date_heure_ra = $date_releve . ' ' . $heure_releve; 
            $date_heure_ra_us = datefr_us($date_releve) . ' ' . $heure_releve; 
        }

        $fichier_releve = isset($_POST['fichier_releve']) ? $_POST['fichier_releve'] : '';
        $fichier_releve = post_secure($sql_link,$fichier_releve);


    // ----------------------------------------------------------
    // ALL - Appareil

        $type_appareil = isset($_POST['type_appareil']) ? $_POST['type_appareil'] : '';
        $type_appareil = post_secure($sql_link,$type_appareil);
        $num_appareil = isset($_POST['num_appareil']) ? $_POST['num_appareil'] : '';
        $num_appareil = post_secure($sql_link,$num_appareil);

        $heure_appareil = isset($_POST['heure_appareil']) ? $_POST['heure_appareil'] : '';
        if(!validTime($heure_appareil)) // Vérifier format heure
        {
            $erreur = true;
            $msg_info .= "L'heure de l'appareil n'est pas au bon format : hh:mm:ss ou hh:mm.<br>";
            $heure_releve = '';
        }

        // ALL - Etat appareil
        $hydro_num_sonde = isset($_POST['hydro_num_sonde']) ? $_POST['hydro_num_sonde'] : '';
        $hydro_num_sonde = post_secure($sql_link,$hydro_num_sonde);
        $nb_octet = isset($_POST['nb_octet']) ? $_POST['nb_octet'] : '';
        $nb_octet = post_secure($sql_link,$nb_octet);
        $num_batterie = isset($_POST['num_batterie']) ? $_POST['num_batterie'] : '';
        $num_batterie = post_secure($sql_link,$num_batterie);
        $tension_batterie = isset($_POST['tension_batterie']) ? $_POST['tension_batterie'] : '';
        $tension_batterie = post_secure($sql_link,$tension_batterie);


        // ALL - Nouvelle Cassette (ancien matériel, obsolète dans le futur) : Pas sûr que l'on conserve
        $num_cassette = isset($_POST['num_cassette']) ? $_POST['num_cassette'] : '';
        $num_cassette = post_secure($sql_link,$num_cassette);

        $heure_init_cassette = isset($_POST['heure_init_cassette']) ? $_POST['heure_init_cassette'] : ''; //heure_cote
        if(!validTime($heure_init_cassette)) // Vérifier format heure
        {
            $erreur = true;
            $msg_info .= "L'heure d'initialisation de la nouvelle cassette n'est pas au bon format : hh:mm:ss ou hh:mm').<br>"; // Vérifier format heure
            $heure_init_cassette = '';
        }
        $hydro_h_sonde_cassette = isset($_POST['hydro_h_sonde_cassette']) ? $_POST['hydro_h_sonde_cassette'] : '';
        if(!validNumeric($hydro_h_sonde_cassette)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info .= "La valeur de hauteur de la sonde de la nouvelle cassette doit être un nombre<br>"; 
            $hydro_h_sonde_cassette = '';
        }
        $plu_heure_bascul1_cassette  = isset($_POST['plu_heure_bascul1_cassette']) ? $_POST['plu_heure_bascul1_cassette'] : '';
        if(!validTime($plu_heure_bascul1_cassette )) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info .= "L'heure du 1er basculement de la nouvelle cassette n'est pas au bon format : hh:mm:ss ou hh:mm <br>"; // Vérifier format heure
            $plu_heure_bascul1_cassette  = '';
        }

        // PLU - Totalisateur
        $plu_tot_type = isset($_POST['plu_tot_type']) ? $_POST['plu_tot_type'] : '';
        $plu_tot_type = post_secure($sql_link,$plu_tot_type);
        
        $plu_tot_first = isset($_POST['plu_tot_first']) ? $_POST['plu_tot_first'] : '';
        if(!validNumeric($plu_tot_first)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur du cumul du totalisateur (First) doit être un nombre.<br>"; 
            $plu_tot_first = '';
        }
        $plu_tot_last = isset($_POST['plu_tot_last']) ? $_POST['plu_tot_last'] : '';
        if(!validNumeric($plu_tot_last)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur du cumul du totalisateur (Last) doit être un nombre.<br>"; 
            $plu_tot_last = '';
        }
        $plu_tot_heure_basc = isset($_POST['plu_tot_heure_basc']) ? $_POST['plu_tot_heure_basc'] : '';
        if(!validTime($plu_tot_heure_basc)) // Vérifier format heure
        {
            $erreur = true;
            $msg_info  .= "L'heure du basculement du Totalisateur n'est pas au bon format : hh:mm:ss ou hh:mm.<br>"; 
            $plu_tot_heure_basc = '';
        }


        // PLU - Contrôle 
        $plu_cumul_tot = isset($_POST['plu_cumul_tot']) ? $_POST['plu_cumul_tot'] : '';
        if(!validNumeric($plu_cumul_tot)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur du cumul du Totalisateur doit être un nombre. <br>";
            $plu_cumul_tot = '';
        }
        $plu_cumul_plu = isset($_POST['plu_cumul_plu']) ? $_POST['plu_cumul_plu'] : '';
        if(!validNumeric($plu_cumul_plu)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur du cumul du Pluviographe doit être un nombre. <br>";
            $plu_cumul_plu = '';
        }
        $plu_recalage_heure_plu = isset($_POST['plu_recalage_heure_plu']) ? $_POST['plu_recalage_heure_plu'] : '';
        if(!validTime($plu_recalage_heure_plu)) // Vérifier format heure
        {
            $erreur = true;
            $msg_info  .= "L'heure du recalage du contrôle n'est pas au bon format : hh:mm:ss ou hh:mm. <br>";
            $plu_recalage_heure_plu = '';
        }
        $plu_test_auget = isset($_POST['plu_test_auget']) ? $_POST['plu_test_auget'] : '';
        $plu_test_auget = post_secure($sql_link,$plu_test_auget);
        
        $plu_nb_basculement = isset($_POST['plu_nb_basculement']) ? $_POST['plu_nb_basculement'] : '';
        if(!validNumeric($plu_nb_basculement)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur du Nb de basculements doit être un nombre. <br>";
            $plu_nb_basculement = '';
        }



    // HYDRO - Côte limnimétrique

        $hydro_heure_cote = isset($_POST['hydro_heure_cote']) ? $_POST['hydro_heure_cote'] : '';
        if(!validTime($hydro_heure_cote)) // Vérifier format heure
        {
            $erreur = true;
            $msg_info  .= "L''heure de relevé de la côte limnimétrique n\'est pas au bon format : hh:mm:ss ou hh:mm.<br>";
            $hydro_heure_cote = '';
        }
        $hydro_h_sonde = isset($_POST['hydro_h_sonde']) ? $_POST['hydro_h_sonde'] : '';
        if(!validNumeric($hydro_h_sonde)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur de la hauteur de sonde doit être un nombre.<br>";
            $hydro_h_sonde = '';
        }
        $hydro_h_echelle_1 = isset($_POST['hydro_h_echelle_1']) ? $_POST['hydro_h_echelle_1'] : '';
        if(!validNumeric($hydro_h_echelle_1)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur de la hauteur d'échelle doit être un nombre.<br>";
            $hydro_h_echelle_1 = '';
        }
        $hydro_h_echelle_2 = isset($_POST['hydro_h_echelle_2']) ? $_POST['hydro_h_echelle_2'] : '';
        if(!validNumeric($hydro_h_echelle_2)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur de la hauteur d'échelle 2 doit être un nombre <br>";
            $hydro_h_echelle_2 = '';
        }

        // Contrôle des mesure de hauteur
        // $hech_hsonde = post_secure($sql_link,$_POST['hech_hsonde']); // Le calcul est fait automatiquement = h_echelle - hsonde donc à priori on enregistre pas
        
        $hydro_recalage_sonde = isset($_POST['hydro_recalage_sonde']) ? $_POST['hydro_recalage_sonde'] : '';
        $hydro_recalage_sonde = post_secure($sql_link,$hydro_recalage_sonde);

        $hydro_recalage_heure_sonde = isset($_POST['hydro_recalage_heure_sonde']) ? $_POST['hydro_recalage_heure_sonde'] : '';
        if(!validTime($hydro_recalage_heure_sonde))// Vérifier format heure
        {
            $erreur = true;
            $msg_info  .= "L'heure de recalage de la sonde n\'est pas au bon format : hh:mm:ss ou hh:mm.<br>";
            $recalage_heure_sonde = '';
        }
        
        $check_purge_sonde=0;
        if(isset($_POST['check_purge_sonde'])){$check_purge_sonde=1;}



    // Piézo - Relevés Puits
    
        $piezo_toitnappesonde = isset($_POST['piezo_toitnappesonde']) ? $_POST['piezo_toitnappesonde'] : '';
        if(!validNumeric($piezo_toitnappesonde)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur de la profondeur du toit de la nappe (mesure sonde fixe) doit être un nombre.<br>";
            $piezo_toitnappesonde = '';
        }

        $piezo_conductivite = isset($_POST['piezo_conductivite']) ? $_POST['piezo_conductivite'] : '';
        if(!validNumeric($piezo_conductivite)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur de la conductivité relevée dans le puits doit être un nombre.<br>";
            $piezo_conductivite = '';
        }
        $piezo_temperature = isset($_POST['piezo_temperature']) ? $_POST['piezo_temperature'] : '';
        if(!validNumeric($piezo_temperature)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur de la température relevée dans le puits doit être un nombre.<br>";
            $piezo_temperature = '';
        }
        $piezo_recalage_diff = isset($_POST['piezo_recalage_diff']) ? $_POST['piezo_recalage_diff'] : '';
        if(!validNumeric($piezo_recalage_diff)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur du recalage dans le relevé du puits doit être un nombre.<br>";
            $piezo_recalage_diff = '';
        }
        
        $piezo_recalage_sonde = isset($_POST['piezo_recalage_sonde']) ? $_POST['piezo_recalage_sonde'] : '';
        
        $piezo_recalage_heure_sonde = isset($_POST['piezo_recalage_heure_sonde']) ? $_POST['piezo_recalage_diff'] : '';
        if(!validTime($piezo_recalage_heure_sonde))// Vérifier format heure
        {
            $erreur = true;
            $msg_info  .= "L'heure de recalage de la sonde piezo n\'est pas au bon format : hh:mm:ss ou hh:mm.<br>".$piezo_recalage_heure_sonde;
            $recalage_heure_sonde = '';
        }

               
        // Piézo - Mesure Nappe
        $piezo_nature_repere = isset($_POST['piezo_nature_repere']) ? $_POST['piezo_nature_repere'] : '';
        $piezo_nature_repere = post_secure($sql_link,$piezo_nature_repere);
        $piezo_instrument = isset($_POST['piezo_instrument']) ? $_POST['piezo_instrument'] : '';
        $piezo_instrument = post_secure($sql_link,$piezo_instrument);
        $piezo_num_instrument = isset($_POST['piezo_num_instrument']) ? $_POST['piezo_num_instrument'] : '';
        $piezo_num_instrument = post_secure($sql_link,$piezo_num_instrument);

        $piezo_prof_toitnappe = isset($_POST['piezo_prof_toitnappe']) ? $_POST['piezo_prof_toitnappe'] : '';
        if(!validNumeric($piezo_prof_toitnappe)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur de la profondeur du toit de la nappe (mesure mannuelle) doit être un nombre.<br>";
            $piezo_prof_toitnappe = '';
        }
        $piezo_prof_totale = isset($_POST['piezo_prof_totale']) ? $_POST['piezo_prof_totale'] : '';
        if(!validNumeric($piezo_prof_totale)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur de la profondeur totale du puits doit être un nombre.<br>";
            $piezo_prof_totale = '';
        }


        // Piézo - Position de la mesure
        $piezo_x_terrain = isset($_POST['piezo_x_terrain']) ? $_POST['piezo_x_terrain'] : '';
        if(!validNumeric($piezo_x_terrain)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur X de la position GPS doit être un nombre.<br>";
            $piezo_x_terrain = '';
        }
        $piezo_y_terrain = isset($_POST['piezo_y_terrain']) ? $_POST['piezo_y_terrain'] : '';
        if(!validNumeric($piezo_y_terrain)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info  .= "La valeur Y de la position GPS doit être un nombre.<br>";
            $piezo_y_terrain = '';
        }
        $piezo_gps_precision = isset($_POST['piezo_gps_precision']) ? $_POST['piezo_gps_precision'] : '';
        $piezo_gps_precision = post_secure($sql_link,$piezo_gps_precision);
        $piezo_systeme_coord = isset($_POST['piezo_systeme_coord']) ? $_POST['piezo_systeme_coord'] : '';
        $piezo_systeme_coord = post_secure($sql_link,$piezo_systeme_coord);

        // Profil piézométrique     
        if($type_data == 5)
        {
            for($i = 1; $i <= 15; $i++) 
            {
                $prof = isset($_POST['piezo_profil_prof_'.$i]) ? $_POST['piezo_profil_prof_'.$i] : '';
                $prof = post_secure($sql_link,$prof);

                $conduct = isset($_POST['piezo_profil_conduct_'.$i]) ? $_POST['piezo_profil_conduct_'.$i] : '';
                $conduct = post_secure($sql_link,$conduct);

                $temp = isset($_POST['piezo_profil_temp_'.$i]) ? $_POST['piezo_profil_temp_'.$i] : '';
                $temp = post_secure($sql_link,$temp);
                
                // Validation des données
                if (tep_not_null($prof) && validNumeric($prof) && tep_not_null($conduct) && validNumeric($conduct)) 
                {   
                    // Validation de la température (elle peut être vide ou numérique valide)
                    if (tep_not_null($temp) && !validNumeric($temp)) 
                    {
                        $temp = ''; // Si la température n'est pas valide, on la vide
                    }

                    // Ajout des données validées au tableau
                    $tab_ra_profil[] = [
                                            'prof' => $prof,
                                            'conduct' => $conduct,
                                            'temp' => $temp
                                        ];
                }
            }
        }

        // ALL - Observations / Actions

        $ra_obs = isset($_POST['ra_obs']) ? $_POST['ra_obs'] : '';
        $ra_obs = post_secure($sql_link,$ra_obs);

        // FAIT MARQUANT
        $check_faitmarquant=0;
        if(isset($_POST['check_faitmarquant'])){$check_faitmarquant=1;}

        // PRE MARQUANT
        $check_premarquant=0;
        if(isset($_POST['check_premarquant'])){$check_premarquant=1;}
                
        // HYDRO
        $check_jaugeage=0;
        if(isset($_POST['check_jaugeage'])){$check_jaugeage=1;}

        // PLU
        $check_bouchage=0;
        if(isset($_POST['check_bouchage'])){$check_bouchage=1;}

        // PLU
        $check_huile=0;
        if(isset($_POST['check_huile'])){$check_huile=1;}

        // PLU + HYDRO
        $check_debrouss=0;
        if(isset($_POST['check_debrouss'])){$check_debrouss=1;}

        // PLU + HYDRO
        $check_eaubat=0;
        if(isset($_POST['check_eaubat'])){$check_eaubat=1;}

        // PLU + HYDRO
        $check_transfert=0;
        if(isset($_POST['check_transfert'])){$check_transfert=1;}

        // ALL
        $check_deletememory=0;
        if(isset($_POST['check_deletememory'])){$check_deletememory=1;}

        // PIEZO
        $check_pompage_encours=0;
        if(isset($_POST['check_pompage_encours'])){$check_pompage_encours=1;}

        $check_pompage_proche=0;
        if(isset($_POST['check_pompage_proche'])){$check_pompage_proche=1;}

        $check_piezo_pluie_crue=0;
        if(isset($_POST['check_piezo_pluie_crue'])){$check_piezo_pluie_crue=1;}

        $check_piezo_temps_sec=0;
        if(isset($_POST['check_piezo_temps_sec'])){$check_piezo_temps_sec=1;}

        $check_piezo_photos=0;
        if(isset($_POST['check_piezo_photos'])){$check_piezo_photos=1;}

        // ALL - Texte à faire lors du prochain passage
        $ra_futur = isset($_POST['ra_futur']) ? $_POST['ra_futur'] : '';
        $ra_futur = post_secure($sql_link,$ra_futur);

        // Agents complémentaires
        $agents_complement = isset($_POST['agents_complement']) ? $_POST['agents_complement'] : '';
        $agents_complement = post_secure($sql_link,$agents_complement);




    // --------------------------------------------------------------------------
    // Enregistrement des données dans la base

        if(!$erreur)
        {
            $type_action = 1; // Action Rapport d'activité
            $dateheure_action =  date("Y-m-d H:i:s");

            if($id_ra < 1) // Si on enregistre un nouvel RA id_ra=0
            {
                $query = "INSERT INTO ".TABLE_DATA_RA." (etat_ra, id_agent_user, id_station, id_eq_type, date_heure_ra) 
                                                VALUES ('".$check_valid_ra."','".$id_user_agent."','".$select_station_ra."','".$type_data."','".$date_heure_ra_us."')";	

                // Execution de la requête
                tep_db_query($sql_link, $query);

                // Récupérer le nouvel identifiant
                $id_ra = mysqli_insert_id($sql_link); 
                $newRA = true;     

                $msg_info_send .= "<span style='font-size:16px;'>";
                    $msg_info_send .= "La nouvelle fiche RA a bien été crée";
                $msg_info_send .= "</span>";
                $msg_info_send .= "<br><br>";

                $msg_info .= "Station : ".$station_all_array[$select_station_ra]['nom_station']." - Date : ".$date_heure_ra;


                // Enregistrement de l'action dans la table ACTION qui suit toutes les actions de la plateforme
                
                $info_action = "Création d'un nouvel RA <br>";
                $info_action .= $msg_info;
                $info_action = post_secure($sql_link,$info_action); 

                $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$dateheure_action."')";
                tep_db_query($sql_link,$query);
            }


            // Mise à jour du RA (Nouveau ou Modifié)
            $query = "UPDATE ".TABLE_DATA_RA." SET etat_ra='".$check_valid_ra."',
                                                datetime_saisie='".$date_heure_saisie_us."',
                                                id_station='".$select_station_ra."',
                                                date_heure_ra='".$date_heure_ra_us."',                                                
                                                name_file_data='".$fichier_releve."',
                                                type_appareil='".$type_appareil."',
                                                num_appareil='".$num_appareil."',
                                                heure_appareil='".$heure_appareil."',
                                                hydro_heure_cote='".$hydro_heure_cote."',
                                                hydro_h_sonde='".$hydro_h_sonde."',
                                                hydro_h_echelle_1='".$hydro_h_echelle_1."',
                                                hydro_h_echelle_2='".$hydro_h_echelle_2."',
                                                hydro_num_sonde='".$hydro_num_sonde."',
                                                plu_tot_type='".$plu_tot_type."',                                                        
                                                plu_tot_first='".$plu_tot_first."',
                                                plu_tot_last='".$plu_tot_last."',
                                                plu_tot_heure_basc='".$plu_tot_heure_basc."',
                                                plu_cumul_tot='".$plu_cumul_tot."',
                                                plu_cumul_plu='".$plu_cumul_plu."',                                                                                                                
                                                plu_recalage_heure_plu='".$plu_recalage_heure_plu."',
                                                plu_test_auget='".$plu_test_auget."',
                                                plu_nb_basculement='".$plu_nb_basculement."',
                                                nb_octet='".$nb_octet."',
                                                num_batterie='".$num_batterie."',
                                                tension_batterie='".$tension_batterie."',
                                                num_cassette='".$num_cassette."',
                                                heure_init_cassette='".$heure_init_cassette."',
                                                hydro_h_sonde_cassette='".$hydro_h_sonde_cassette."',
                                                plu_heure_bascul1_cassette='".$plu_heure_bascul1_cassette."',
                                                hydro_recalage_sonde='".$hydro_recalage_sonde."',
                                                hydro_recalage_heure_sonde='".$hydro_recalage_heure_sonde."',
                                                hydro_purge_sonde='".$check_purge_sonde."',
                                                hydro_ra_jaugeage='".$check_jaugeage."',
                                                plu_ra_bouchage='".$check_bouchage."',
                                                plu_ra_huile_tot='".$check_huile."',
                                                ra_debroussaillage='".$check_debrouss."',
                                                ra_eau_batterie='".$check_eaubat."',
                                                ra_transfert_data='".$check_transfert."',
                                                ra_delete_memory='".$check_deletememory."',
                                                piezo_toitnappesonde='".$piezo_toitnappesonde."',
                                                piezo_conductivite='".$piezo_conductivite."',
                                                piezo_temperature='".$piezo_temperature."',
                                                piezo_recalage_diff='".$piezo_recalage_diff."',
                                                piezo_recalage_sonde='".$piezo_recalage_sonde."',
                                                piezo_recalage_heure_sonde='".$piezo_recalage_heure_sonde."',
                                                piezo_nature_repere='".$piezo_nature_repere."',
                                                piezo_instrument='".$piezo_instrument."',
                                                piezo_num_instrument='".$piezo_num_instrument."',
                                                piezo_prof_toitnappe='".$piezo_prof_toitnappe."',
                                                piezo_prof_totale='".$piezo_prof_totale."',
                                                piezo_x_terrain='".$piezo_x_terrain."',
                                                piezo_y_terrain='".$piezo_y_terrain."',
                                                piezo_gps_precision='".$piezo_gps_precision."',
                                                piezo_systeme_coord='".$piezo_systeme_coord."',
                                                piezo_pompage_encours='".$check_pompage_encours."',
                                                piezo_pompage_proche='".$check_pompage_proche."',
                                                piezo_pluie_crue='".$check_piezo_pluie_crue."',
                                                piezo_temps_sec='".$check_piezo_temps_sec."',
                                                piezo_photos='".$check_piezo_photos."',
                                                agents_complement='".$agents_complement."',                                 
                                                ra_obs='".$ra_obs."',
                                                fait_marquant='".$check_faitmarquant."',   
                                                ra_futur='".$ra_futur."',
                                                pre_marquant='".$check_premarquant."'     
                                            WHERE id_ra=".$id_ra;


            // Execution de la requête
            tep_db_query($sql_link, $query);

            // ------------------------------------------------------
            // Enregistrement du profil en profondeur : piézo
            
            if($type_data == 5)
            {  
                // Suppression les anciennes données associées à l'identifiant RA : id_ra
                tep_db_query($sql_link, "DELETE FROM ".TABLE_DATA_RA_PIEZO_PROFIL." WHERE id_ra = ".$id_ra);

                if(isset($tab_ra_profil))
                {
                    $sqlSave_profilPiezo = '';

                    foreach($tab_ra_profil as $key => $value)
                    {
                        $sqlSave_profilPiezo .= "('".$id_ra."','".$value['prof']."','".$value['conduct']."','".$value['temp']."'),";                    
                    }
                    
                    // Suppression de la virgule finale dans la chaîne SQL
                    $sqlSave_profilPiezo = rtrim($sqlSave_profilPiezo, ',');

                    // Enregistrer le nouveau profil piézométrique
                    tep_db_query($sql_link, "INSERT INTO ".TABLE_DATA_RA_PIEZO_PROFIL." (id_ra, profondeur, conductivite, temperature) VALUES " . $sqlSave_profilPiezo);
                }
            }

            // Message pour la mise à jour si ce n'est pas un nouveau RA                                    
            if(!$newRA)
            {
                $msg_info_send .= "<span style='font-size:16px;'>";
                    $msg_info_send .= "La fiche RA a bien été enregistrée";
                $msg_info_send .= "</span>";
                $msg_info_send .= "<br><br>";

                $msg_info .= "Station : ".$station_all_array[$select_station_ra]['nom_station']." - Date : ".$date_heure_ra;

                // Enregistrement de l'action dans la table ACTION qui suit toutes les actions de la plateforme
                
                $info_action = "Modification RA <br>";
                $info_action .= $msg_info;                 
                $info_action = post_secure($sql_link,$info_action); 

                $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$dateheure_action."')";
                tep_db_query($sql_link,$query);
            }
        }
        else
        {
            $msg_info_send .= "<span style='font-size:16px;'>";
            $msg_info_send .= "Erreur : la fiche RA n'a bien pu être enregistrée";
            $msg_info_send .= "</span>";
            $msg_info_send .= "<br><br>";
        }
}
else
{
    $msg_info_send .= "<span style='font-size:16px;'>";
    $msg_info_send .= "Une erreur est survenue lors de l'envoi des données sur le serveur.";
    $msg_info_send .= "</span>";
    $msg_info_send .= "<br><br>";
}

$msg_info_send .= $msg_info; 

// Remplissage du tableau de retour

$responseData = array(
    'id_ra' => $id_ra,    
    'type_data' => $type_data,
    'new_ra' => $newRA,
    'erreur' => $erreur,
    'msg_info' => $msg_info_send
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>