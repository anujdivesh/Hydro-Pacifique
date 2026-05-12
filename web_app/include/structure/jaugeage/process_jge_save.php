<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Enregistrement d'une fiche JGE (Modification ou Création)
- post_secure : est une fonction accessible dans include/function/ permettant de contrôler et de corriger les entrées depuis des formulaires. Fonction de Sécurité contre insertion JS et PHP
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



// -------------------------------------------------------------


// Initialisation Variables Globales
$msg_info_send = '';
$msg_info = '';
$erreur = false;
$newJGE = false;

// Vérifier si la requête envoyé depuis le client est bien une requête POST

if($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $id_user_agent = isset($_POST['id_user_agent']) ? $_POST['id_user_agent'] : '';
    $territoire_id = isset($_POST['territoire_id']) ? $_POST['territoire_id'] : '';
    
    $id_jge = isset($_POST['id_jge']) ? $_POST['id_jge'] : '';
    if($id_jge < 1){$newJGE = true;}
    
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
    $sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station, s.active_station, s.id_region
                    FROM ".TABLE_STATION." s 
                    JOIN ".TABLE_REGION." r ON s.id_region=r.id_region
                    WHERE s.station_type=11 AND r.id_territoire=".$territoire_id." 
                    ORDER BY s.nom_station";

    $station_query = tep_db_query($sql_link,$sql_station);
    while ($station = tep_db_fetch_array($station_query))
    {	
        $nom_station =  htmlaccent(html_entity_decode($station['nom_station'] ?? $default_string));
        $code_station =  htmlaccent(html_entity_decode($station['code_station'] ?? $default_string));
        
        $act_station = false;
        if($station['active_station'] == 1){$act_station = true;}
            
            
        $station_array[$station['id_station']] = array('act_station' => $act_station,
                                                        'nom_station' => $nom_station,
                                                        'code_station' => $code_station);
        
    }

    // ----------------------------------------- 
    // Récupération des données du formulaire

    // Général


    $jge_hmoy = isset($_POST['jge_hmoy']) ? $_POST['jge_hmoy'] : '';
    if(!validNumeric($jge_hmoy)) // vérifier que nous avons un nombre
    {
        $erreur = true;
        $msg_info .= "La valeur - Hauteur Moyenne -  doit être un nombre<br>"; 
    }
    $jge_q = isset($_POST['jge_q']) ? $_POST['jge_q'] : '';
    if(!validNumeric($jge_q)) // vérifier que nous avons un nombre
    {
        $erreur = true;
        $msg_info .= "La valeur - Débit - doit être un nombre<br>"; 
    }

    $date_jge = isset($_POST['date_jge']) ? $_POST['date_jge'] : '';
    if(!validDate($date_jge)) // Vérifier format date
    {
        $erreur = true;
        $msg_info .= "La date du Jaugeage n'est pas au bon format : dd-mm-YYYY.<br>"; 
        $date_jge = '';
    }

    $heure_jge = isset($_POST['heure_jge']) ? $_POST['heure_jge'] : '';
    if(!validTime($heure_jge)) // Vérifier format heure
    {
        $erreur = true;
        $msg_info .= "L'heure du Jaugeage n'est pas au bon format : hh:mm:ss ou hh:mm.<br>";
        $heure_jge = '';
    }

    if(!$erreur)
    {
        $dateheure_jge = $date_jge . ' ' . $heure_jge; 
        $dateheure_jge_us = datefr_us($date_jge) . ' ' . $heure_jge; 
    }


    $id_station = isset($_POST['select_station']) ? $_POST['select_station'] : '';
    if($id_station == 0) // Vérifier format heure
    {
        $erreur = true;
        $msg_info .= "Le Jaugeage doit être lié à une station<br>";
    }

    $code_station = isset($station_array[$code_station]['code_station']) ? $station_array[$code_station]['code_station'] : '';
    $nom_station = isset($station_array[$id_station]['nom_station']) ? $station_array[$id_station]['nom_station'] : '';

    $id_code_qual = isset($_POST['select_code_qual']) ? $_POST['select_code_qual'] : '';


    $dist_site = isset($_POST['dist_site']) ? $_POST['dist_site'] : '';
    if(!validNumeric($dist_site)) // vérifier que nous avons un nombre
    {
        $erreur = true;
        $msg_info .= "La valeur - Distance du site - doit être un nombre<br>"; 
    }

    $id_site_jge = isset($_POST['select_site_jge']) ? $_POST['select_site_jge'] : '';

    $x_gps = isset($_POST['x_gps']) ? $_POST['x_gps'] : '';
    $x_gps = post_secure($sql_link,$x_gps);

    $y_gps = isset($_POST['y_gps']) ? $_POST['y_gps'] : '';
    $y_gps = post_secure($sql_link,$y_gps);

    $id_type_jge = isset($_POST['select_type_jge']) ? $_POST['select_type_jge'] : '';
    $id_methode_jge = isset($_POST['select_methode_jge']) ? $_POST['select_methode_jge'] : '';

    $obs = isset($_POST['obs']) ? $_POST['obs'] : '';
    $obs = post_secure($sql_link,$obs);

    $fichier = isset($_POST['file_link']) ? $_POST['file_link'] : '';
    $fichier = post_secure($sql_link,$fichier);

    $agents = isset($_POST['agents_text']) ? $_POST['agents_text'] : '';
    $agents = post_secure($sql_link,$agents);


    // DATA BRAS

    $tab_info_bras = [];

    $nb_bras = isset($_POST['nb_bras']) ? $_POST['nb_bras'] : 0; // 0 si c'est un nouveau bras
    $nb_bras++; // Si il n'y a pas encore de bras : Nouveau JGE ou JGE sans bras alors on en crée 1, sinon le nombre de bras pouvant être augmenté on en crée un autre

    for($nbb=1;$nbb<=$nb_bras;$nbb++)
    {
        $tab_info_bras[$nbb]['id_bras'] = isset($_POST['id_bras_'.$nbb]) ? $_POST['id_bras_'.$nbb] : 0; // 0 si nouveau bras

        $heure_first = isset($_POST['heure_first_'.$nbb]) ? $_POST['heure_first_'.$nbb] : '';
        if(!validTime($heure_first)) // Vérifier format heure
        {
            $erreur = true;
            $msg_info .= "Bras - ".$nbb." : L'heure de début du Jaugeage n'est pas au bon format : hh:mm:ss ou hh:mm.<br>";
        }
        $tab_info_bras[$nbb]['heure_first'] = $heure_first;

        $h_ech_first = isset($_POST['h_ech_first_'.$nbb]) ? $_POST['h_ech_first_'.$nbb] : '';
        if(!validNumeric($h_ech_first)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info .= "Bras - ".$nbb." : La valeur de hauteur de la sonde de la nouvelle cassette doit être un nombre<br>"; 
        }
        $tab_info_bras[$nbb]['h_ech_first'] = $h_ech_first;

        $heure_end = isset($_POST['heure_end_'.$nbb]) ? $_POST['heure_end_'.$nbb] : '';
        if(!validTime($heure_end)) // Vérifier format heure
        {
            $erreur = true;
            $msg_info .= "Bras - ".$nbb." : L'heure de fin du Jaugeage n'est pas au bon format : hh:mm:ss ou hh:mm.<br>";
        }
        $tab_info_bras[$nbb]['heure_end'] = $heure_end;

        $h_ech_end = isset($_POST['h_ech_end_'.$nbb]) ? $_POST['h_ech_end_'.$nbb] : '';
        if(!validNumeric($h_ech_end)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info .= "Bras - ".$nbb." : La valeur de hauteur de la sonde de la nouvelle cassette doit être un nombre<br>";
        }
        $tab_info_bras[$nbb]['h_ech_end'] = $h_ech_end;

        $bras_fond = isset($_POST['fond_text_'.$nbb]) ? $_POST['fond_text_'.$nbb] : '';
        $bras_fond = post_secure($sql_link,$bras_fond);
        $tab_info_bras[$nbb]['fond_text'] = $bras_fond;
        
        $bras_obs = isset($_POST['bras_obs_'.$nbb]) ? $_POST['bras_obs_'.$nbb] : '';
        $bras_obs = post_secure($sql_link,$bras_obs);
        $tab_info_bras[$nbb]['obs'] = $bras_obs;
        
        $select_berge = isset($_POST['select_berge1_'.$nbb]) ? $_POST['select_berge1_'.$nbb] : 0;
        $tab_info_bras[$nbb]['berge_depart'] = $select_berge;
        $select_moulinet = isset($_POST['select_moulinet_'.$nbb]) ? $_POST['select_moulinet_'.$nbb] : 0;
        $tab_info_bras[$nbb]['id_moulinet'] = $select_moulinet;
        $select_helice = isset($_POST['select_helice_'.$nbb]) ? $_POST['select_helice_'.$nbb] : 0;
        $tab_info_bras[$nbb]['id_helice'] = $select_helice;

        $perche_diam = isset($_POST['perche_diam_'.$nbb]) ? $_POST['perche_diam_'.$nbb] : NULL;
        if(!validNumeric($perche_diam)) // vérifier que nous avons un nombre
        {
            $erreur = true;
            $msg_info .= "Bras - ".$nbb." : La valeur du diamètre de la perche doit être un nombre<br>"; 
        }
        $tab_info_bras[$nbb]['perche_diam'] = $perche_diam;

        // Champs des calculs à récupérer
        // Ces champs ne sont pas modifiables par l'utilisateur, il n'est donc pas nécessaire de faire de contrôles sur la valeur numérique des champs

        // Il faut rajouter nb_verticales
        $tab_info_bras[$nbb]['depouil_nbvert'] = 0;
        
        $depouil_bras_hmoy = isset($_POST['depouil_bras_hmoy_'.$nbb]) ? $_POST['depouil_bras_hmoy_'.$nbb] : NULL;
        $tab_info_bras[$nbb]['depouil_hmoy'] = $depouil_bras_hmoy;

        $depouil_bras_profmoy = isset($_POST['depouil_bras_profmoy_'.$nbb]) ? $_POST['depouil_bras_profmoy_'.$nbb] : NULL;
        $tab_info_bras[$nbb]['depouil_profmoy'] = $depouil_bras_profmoy;

        $depouil_bras_distmax = isset($_POST['depouil_bras_distmax_'.$nbb]) ? $_POST['depouil_bras_distmax_'.$nbb] : NULL;        
        $tab_info_bras[$nbb]['depouil_distmax'] = $depouil_bras_distmax;

        $depouil_bras_vmoy = isset($_POST['depouil_bras_vmoy_'.$nbb]) ? $_POST['depouil_bras_vmoy_'.$nbb] : NULL;
        $tab_info_bras[$nbb]['depouil_vmoy'] = $depouil_bras_vmoy;

        $depouil_bras_vsurf = isset($_POST['depouil_bras_vsurf_'.$nbb]) ? $_POST['depouil_bras_vsurf_'.$nbb] : NULL;
        $tab_info_bras[$nbb]['depouil_vsurf'] = $depouil_bras_vsurf;

        $depouil_bras_rh = isset($_POST['depouil_bras_rh_'.$nbb]) ? $_POST['depouil_bras_rh_'.$nbb] : NULL;
        $tab_info_bras[$nbb]['depouil_rh'] = $depouil_bras_rh;
        
        $depouil_bras_surfmouil = isset($_POST['depouil_bras_surfmouil_'.$nbb]) ? $_POST['depouil_bras_surfmouil_'.$nbb] : NULL;
        $tab_info_bras[$nbb]['depouil_surfmouil'] = $depouil_bras_surfmouil;

        $depouil_bras_perimouil = isset($_POST['depouil_bras_perimouil_'.$nbb]) ? $_POST['depouil_bras_perimouil_'.$nbb] : NULL;
        $tab_info_bras[$nbb]['depouil_perimouil'] = $depouil_bras_perimouil;

        $depouil_bras_q = isset($_POST['depouil_bras_q_'.$nbb]) ? $_POST['depouil_bras_q_'.$nbb] : NULL;
        $tab_info_bras[$nbb]['depouil_q'] = $depouil_bras_q;


    }
    

    // --------------------------------------------------------------------------
    // Enregistrement des données dans la base
    $query_jge_new = '';
    $query_jge = '';
    $query_bras_new = '';
    $query_bras = '';
    $query_pts = '';
    $query_delete_pts = '';

    //$erreur=true;
    if(!$erreur)
    {   
        // Encapsulation des requêtes dans une transaction pour assurer l'intégrité des données en cas d'échec d'une partie de l'opération
        // Commencer une transaction
        
        tep_db_query($sql_link, "START TRANSACTION");
        
        try {
           
            if($newJGE) // Si on enregistre un nouveau jaugeage 
            {
                $query_jge_new = "INSERT INTO " . TABLE_DATA_JGE . " (id_station) VALUES ('$id_station')";
                tep_db_query($sql_link, $query_jge_new);

                // Récupérer le nouvel identifiant station
                $id_jge = mysqli_insert_id($sql_link);

                $msg_info_send .= "<span style='font-size:16px;'>";
                    $msg_info_send .= "La nouvelle fiche Jaugeage a bien été crée";
                $msg_info_send .= "</span>";
                $msg_info_send .= "<br><br>";

                $msg_info .= "Station : ".$nom_station;

                // Enregistrement de l'action dans la table ACTION qui suit toutes les actions de la plateforme
                $type_action = 10; // JGE
                $info_action = "Création d'une nouvelle Fiche Jaugeage : ".$code_station." - ".$nom_station ." - du ".$dateheure_jge."<br>";
            }
            else
            {
                // Enregistrement de l'action Administration
                $type_action = 10;
                $info_action = "Modification d'une Fiche Jaugeage : ".$code_station." - ".$nom_station;
            }

                // Mise à jour de la Fiche Jaugeage (Nouveau ou Modifié)
                $query_jge = "UPDATE ".TABLE_DATA_JGE." SET 
                                                    x_gps='".$x_gps."', 
                                                    y_gps='".$y_gps."', 
                                                    datetime='".$dateheure_jge_us."', 
                                                    nb_bras='".$nb_bras."',  
                                                    dist_site='".$dist_site."', 
                                                    id_site='".$id_site_jge."',
                                                    id_methode='".$id_methode_jge."', 
                                                    id_typejge='".$id_type_jge."', 
                                                    depouil_hmoy='".$jge_hmoy."', 
                                                    depouil_q='".$jge_q."', 
                                                    code_qualite='".$id_code_qual."',
                                                    obs='".$obs."',
                                                    fichier='".$fichier."',
                                                    agents='".$agents."'
                                                    WHERE id=$id_jge";
                                                    /*
                                                    depouil_hmoy='$riviere_station."',
                                                    depouil_q='$tournee_station."',
                                                    depouil_sect='$regionhydro_station."',
                                                    depouil_vmoy='$altitude_station."', 
                                                    depouil_vsurf='$orientation_station."',
                                                    depouil_rh='$longitude_station."', 
                                                    depouil_profmoy='$latitude_station."',
                                                    depouil_nbvert='$utm_station_x."', 
                                                    code_qualite='$id_code_qual."',
                                                    obs='$ign_station_x'
                                                    WHERE id=$id_jge";
                                                    */
                                                    

                // Execution de la requête
                tep_db_query($sql_link, $query_jge);

                
                // Modifier les données d'un bras
                
                for($nbb=1;$nbb<=$nb_bras;$nbb++)
                {         
                    $verif_id_bras = true;          

                    // On vérifie d'abord si au moins un champ est rempli
                    $heure_first = $tab_info_bras[$nbb]['heure_first'];
                    $h_ech_first = $tab_info_bras[$nbb]['h_ech_first'];
                    $heure_end = $tab_info_bras[$nbb]['heure_end'];
                    $h_ech_end = $tab_info_bras[$nbb]['h_ech_end'];

                    if (!empty($heure_first) || !empty($h_ech_first) || !empty($heure_end) || !empty($h_ech_end)) 
                    {
                        // Vérifier que tous les champs sont remplis, sinon, déclencher une erreur
                        if (empty($heure_first) || empty($h_ech_first) || empty($heure_end) || empty($h_ech_end)) 
                        {
                            // Au moins un champ est rempli, mais au moins un autre est vide : Erreur
                            $msg_info_send .= "Erreur : Si l'un de ces champs est rempli, ils doivent tous être remplis.";
                            $erreur = true;
                            $verif_id_bras = false; // Conserver true pour indiquer que des valeurs partielles existent
                            throw new Exception("Erreur : Les champs Heures et Echelles doivent être toutes saisies.");
                        }                        
                    } 
                    else
                    {
                        $verif_id_bras = false;
                    }
                    
                    if($verif_id_bras)
                    {
                        // Ajouter un bras : si id_bras < 1 (=0)
                        if($tab_info_bras[$nbb]['id_bras'] < 1)
                        {
                           $query_bras_new = "INSERT INTO " . TABLE_DATA_JGE_BRAS . " (id_jge) VALUES ('$id_jge')";
                           tep_db_query($sql_link, $query_bras_new);

                           // Récupérer le nouvel identifiant de bras
                           $tab_info_bras[$nbb]['id_bras'] = mysqli_insert_id($sql_link);                            
                        }

                        $query_bras = "UPDATE ".TABLE_DATA_JGE_BRAS." SET
                                                num_bras='".$nbb."',  
                                                id_moulinet='".$tab_info_bras[$nbb]['id_moulinet']."', 
                                                id_helice='".$tab_info_bras[$nbb]['id_helice']."',                                                             
                                                perche_diam='".$tab_info_bras[$nbb]['perche_diam']."',  
                                                berge_depart='".$tab_info_bras[$nbb]['berge_depart']."',
                                                heure_first='".$tab_info_bras[$nbb]['heure_first']."',
                                                h_ech_first='".$tab_info_bras[$nbb]['h_ech_first']."',
                                                heure_end='".$tab_info_bras[$nbb]['heure_end']."',
                                                h_ech_end='".$tab_info_bras[$nbb]['h_ech_end']."',
                                                fond_text='".$tab_info_bras[$nbb]['fond_text']."',
                                                depouil_hmoy='".$tab_info_bras[$nbb]['depouil_hmoy']."',
                                                depouil_nbvert='".$tab_info_bras[$nbb]['depouil_nbvert']."',
                                                depouil_profmoy='".$tab_info_bras[$nbb]['depouil_profmoy']."',
                                                depouil_distmax='".$tab_info_bras[$nbb]['depouil_distmax']."',
                                                depouil_vmoy='".$tab_info_bras[$nbb]['depouil_vmoy']."',
                                                depouil_vsurf='".$tab_info_bras[$nbb]['depouil_vsurf']."',
                                                depouil_surfmouil='".$tab_info_bras[$nbb]['depouil_surfmouil']."',
                                                depouil_perimouil='".$tab_info_bras[$nbb]['depouil_perimouil']."',
                                                depouil_rh='".$tab_info_bras[$nbb]['depouil_rh']."',
                                                depouil_q='".$tab_info_bras[$nbb]['depouil_q']."',
                                                obs='".$tab_info_bras[$nbb]['obs']."'
                                                WHERE id=".$tab_info_bras[$nbb]['id_bras'].";";
        

                        // Execution de la requête
                        tep_db_query($sql_link, $query_bras);

                        // Initialisation de la chaîne de requête SQL
                        $query_pts = "INSERT INTO ".TABLE_DATA_JGE_PTS." (id_bras, num_vert, dist_depart, prof_max, prof_pts, nb_tours, tps_pts, vitesse_calc) VALUES ";
                        $values_pts = [];// Tableau pour stocker les valeurs à insérer
                        
                        $nb_pts = 150;
                        $jge_data_valid = true;
                        for($num_pts=0;$num_pts<$nb_pts;$num_pts++)
                        {
                            $jge_bra_vert = isset($_POST['jge_bra_vert_'.$nbb.'_'.$num_pts]) ? $_POST['jge_bra_vert_'.$nbb.'_'.$num_pts] : '';
                            $jge_bra_dist = isset($_POST['jge_bra_dist_'.$nbb.'_'.$num_pts]) ? $_POST['jge_bra_dist_'.$nbb.'_'.$num_pts] : '';
                            $jge_bra_profmax = isset($_POST['jge_bra_profmax_'.$nbb.'_'.$num_pts]) ? $_POST['jge_bra_profmax_'.$nbb.'_'.$num_pts] : '';
                            $jge_bra_profmesure = isset($_POST['jge_bra_profmesure_'.$nbb.'_'.$num_pts]) ? $_POST['jge_bra_profmesure_'.$nbb.'_'.$num_pts] : '';
                            $jge_bra_nbtour = isset($_POST['jge_bra_nbtour_'.$nbb.'_'.$num_pts]) ? $_POST['jge_bra_nbtour_'.$nbb.'_'.$num_pts] : '';
                            $jge_bra_tps = isset($_POST['jge_bra_tps_'.$nbb.'_'.$num_pts]) ? $_POST['jge_bra_tps_'.$nbb.'_'.$num_pts] : '';
                            $jge_bra_vitesse = isset($_POST['jge_bra_vitesse_'.$nbb.'_'.$num_pts]) ? $_POST['jge_bra_vitesse_'.$nbb.'_'.$num_pts] : '';

                            
                            // Vérification et conversion des données (fonction validate_and_convert se trouve dans include/function/genepal.php)
                            if($jge_data_valid){validate_and_convert($jge_bra_vert,$jge_data_valid,'int');}
                            if($jge_data_valid){validate_and_convert($jge_bra_dist,$jge_data_valid,'float');}
                            if($jge_data_valid){validate_and_convert($jge_bra_profmax,$jge_data_valid,'float');}
                            if($jge_data_valid){validate_and_convert($jge_bra_profmesure,$jge_data_valid,'float');}
                            if($jge_data_valid){validate_and_convert($jge_bra_nbtour,$jge_data_valid,'int');}
                            if($jge_data_valid){validate_and_convert($jge_bra_tps,$jge_data_valid,'int');}
                            if($jge_data_valid){validate_and_convert($jge_bra_vitesse,$jge_data_valid,'float');}

                            if(isset($jge_bra_profmesure) && $jge_bra_profmesure !== '')
                            {
                                if($jge_data_valid)
                                {
                                    // On prépare la variable pour l'enregistrement des données
                                    $values_pts[] = "('".$tab_info_bras[$nbb]['id_bras']."', 
                                                            '".$jge_bra_vert."', 
                                                            '".$jge_bra_dist."', 
                                                            '".$jge_bra_profmax."', 
                                                            '".$jge_bra_profmesure."', 
                                                            '".$jge_bra_nbtour."', 
                                                            '".$jge_bra_tps."', 
                                                            '".$jge_bra_vitesse."'
                                                        )";
                                }
                                else
                                {
                                    $msg_info_send .= "<span style='font-size:16px;'>";
                                    $msg_info_send .= "Une erreur est survenue : la fiche Jaugeage n'a pas pu être enregistrée";
                                    $msg_info_send .= "</span>";
                                    $msg_info_send .= "<br><br>";
                                    $msg_info_send .= "Certaine données dans la table des Points de Jaugeage ne sont pas au bon format (Format numérique attendu)";
                                    break;
                                }
                            }
                        }

                        // Une fois que tous les points sont passés
                        
                        if(!empty($values_pts))
                        {
                            $query_delete_pts = "DELETE FROM ".TABLE_DATA_JGE_PTS." WHERE id_bras=".$tab_info_bras[$nbb]['id_bras'].";";
                            $query_pts .= implode(", ", $values_pts); // Ajout de toutes les valeurs à la requête

                            // Execution des requêtes POINTS
                            tep_db_query($sql_link, $query_delete_pts); // On supprime d'abord les points existants
                            tep_db_query($sql_link, $query_pts); // On insert les nouveaux points
                        }

                    }
                  
                }

            // Les enregistrements se sont bien passés, on enregistre l'action    
            // Enregistrement de l'action dans la table action
            $today_us = date('Y-m-d H:i:s'); 
            $info_action = post_secure($sql_link,$info_action); 

            $query_action = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) 
                                                VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$today_us."')";
            tep_db_query($sql_link,$query_action);


            
            tep_db_query($sql_link, "COMMIT"); // Si toutes les opérations réussissent, confirmer les transactions


            $msg_info_send .= "<span style='font-size:16px;'>";
                $msg_info_send .= "Les données de la fiche Jaugeage ont bien été enregistrées";
            $msg_info_send .= "</span>";
            $msg_info_send .= "<br><br>";

            $msg_info .= "Station : ".$nom_station;


        } catch (Exception $e) 
        {        
            //tep_db_query($sql_link, "ROLLBACK"); // En cas d'erreur, annuler la transaction
            
            $msg_info_send .= "L'enregistrement des données a rencontré un problème lors de l'écriture dans les tables"; // message d'erreur de transaction
            $msg_info_send .= "<br><br>Une erreur est survenue : " . $e->getMessage(); // Afficher le message d'erreur

            $erreur = true;
        }

    }
    else
    {
        $msg_info_send .= "<span style='font-size:16px;'>";
        $msg_info_send .= "Une erreur est survenue : la fiche Jaugeage n'a pas pu être enregistrée";
        $msg_info_send .= "</span>";
        $msg_info_send .= "<br><br>";

        $erreur = true;
    }
}
else
{
    $msg_info_send .= "<span style='font-size:16px;'>";
    $msg_info_send .= "Une erreur est survenue lors de l'envoi des données sur le serveur.";
    $msg_info_send .= "</span>";
    $msg_info_send .= "<br><br>";

    $erreur = true;
}
    


$msg_info_send .= $msg_info; 


// Remplissage du tableau de retour

$responseData = array(
    'erreur' => $erreur,
    'id_station' => $id_station,   
    'id_jge' => $id_jge,    
    'msg_info' => $msg_info_send
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>