<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Enregistrement d'une fiche Station (Modification ou Création)
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


// -------------------------------------------------------------


// Initialisation Variables Globales
$msg_info_send = '';
$msg_info = '';
$erreur = false;
$tab_html = '';
$newStation = false;

$row = 0;
$date_format = 'd-m-Y';

// Vérifier si la requête envoyé depuis le client est bien une requête POST
if($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $id_user_agent = isset($_POST['id_user_agent']) ? $_POST['id_user_agent'] : '';
    $territoire_id = isset($_POST['territoire_id']) ? $_POST['territoire_id'] : '';

    $id_station = isset($_POST['id_station']) ? $_POST['id_station'] : '';
    
    // ----------------------------------------- 
    // Récupération des données du formulaire

    // Général
    $code_station = isset($_POST['code_station']) ? $_POST['code_station'] : '';
    $code_station = post_secure($sql_link,$_POST['code_station']);

    if(!tep_not_null($code_station)) // Si le champs nom est vide
    {
        $erreur = true;
        $msg_info  .= "Le Code Station est un champs obligatoire.<br>";
	}

    $nom_station = isset($_POST['nom_station']) ? $_POST['nom_station'] : '';
    $nom_station = post_secure($sql_link,$_POST['nom_station']);

    if(!tep_not_null($nom_station)) // Si le champs nom est vide
    {
        $erreur = true;
        $msg_info  .= "Le Nom Station est un champs obligatoire.<br>";
	}


    $nom_court = isset($_POST['nom_court']) ? $_POST['nom_court'] : '';
    $nom_court = post_secure($sql_link,$_POST['nom_court']);
    $num_irh = isset($_POST['num_irh']) ? $_POST['num_irh'] : '';
    $num_irh = post_secure($sql_link,$_POST['num_irh']);

    $select_region = isset($_POST['select_region']) ? $_POST['select_region'] : ''; // Province en Calédonie / Iles en PF et WF // Liste
    $select_commune = isset($_POST['select_commune']) ? $_POST['select_commune'] : ''; // Liste

    $site_station = isset($_POST['site_station']) ? $_POST['site_station'] : '';
    $site_station = post_secure($sql_link,$_POST['site_station']); 


    //Type de station et Etats 
    $station_type = isset($_POST['select_type_mesure']) ? $_POST['select_type_mesure'] : ''; // Liste

    $active_station = isset($_POST['select_statut_station']) ? $_POST['select_statut_station'] : ''; // Liste
    $suivi_station = isset($_POST['select_suivi_station']) ? $_POST['select_suivi_station'] : ''; // Liste

    $armee_station = 0;
    if(isset($_POST['check_armee_station'])){$armee_station = 1;}


    // Géographie
    $vallee_station = '';
    //$vallee_station = post_secure($sql_link,$_POST['vallee_station']); // Liste

    $riviere_station = isset($_POST['riviere_station']) ? $_POST['riviere_station'] : '';
    $riviere_station = post_secure($sql_link,$_POST['riviere_station']);

    $regionhydro_station = post_secure($sql_link,$_POST['select_regionhydro']); // Liste
    $tournee_station = post_secure($sql_link,$_POST['select_tournee']); // Liste

    $altitude_station = isset($_POST['altitude_station']) ? $_POST['altitude_station'] : '';
    $altitude_station = post_secure($sql_link,$_POST['altitude_station']);

    $orientation_station = post_secure($sql_link,$_POST['orientation_station']); // Liste

    // Data GPS
    $latitude_station = isset($_POST['latitude_station']) ? $_POST['latitude_station'] : '';
    $latitude_station = post_secure($sql_link,$_POST['latitude_station']);

    $longitude_station = isset($_POST['longitude_station']) ? $_POST['longitude_station'] : '';
    $longitude_station = post_secure($sql_link,$_POST['longitude_station']);

    $utm_station_x = isset($_POST['utm_station_x']) ? $_POST['utm_station_x'] : '';
    $utm_station_x = post_secure($sql_link,$_POST['utm_station_x']);
    
    $utm_station_y = isset($_POST['utm_station_y']) ? $_POST['utm_station_y'] : '';
    $utm_station_y = post_secure($sql_link,$_POST['utm_station_y']);

    $ign_station_x = isset($_POST['ign_station_x']) ? $_POST['ign_station_x'] : '';
    $ign_station_x = post_secure($sql_link,$_POST['ign_station_x']);

    $ign_station_y = isset($_POST['ign_station_y']) ? $_POST['ign_station_y'] : '';
    $ign_station_y = post_secure($sql_link,$_POST['ign_station_y']);

    $lamb_station_x = isset($_POST['lamb_station_x']) ? $_POST['lamb_station_x'] : '';
    //$lamb_station_x = post_secure($sql_link,$_POST['lamb_station_x']);

    $lamb_station_y = isset($_POST['lamb_station_y']) ? $_POST['lamb_station_y'] : '';
    //$lamb_station_y = post_secure($sql_link,$_POST['lamb_station_y']);

    // Dates    
    $date_1_station = isset($_POST['date_installation_station']) ? $_POST['date_installation_station'] : '';
    $date_1_station_us = null;
    if(tep_not_null($date_1_station))
    {
        $date_1_format = DateTime::createFromFormat($date_format, $date_1_station); // date_format est défini dans modif_station.php
        if ($date_1_format && $date_1_format->format($date_format) === $date_1_station) 	
        {
            $date_1_station_us = $date_1_format->format('Y-m-d');
        }
        else
        {
            $erreur = true;
            $msg_info .= "Le format de la date installation n'est pas valide. Veuillez vérifier votre saisie : dd-mm-aaaa<br>";
        }
    }
    

    $date_2_station = isset($_POST['date_fermeture_station']) ? $_POST['date_fermeture_station'] : '';
    $date_2_station_us = null;
    if(tep_not_null($date_2_station))
    {
        $date_2_format = DateTime::createFromFormat($date_format, $date_2_station); // date_format est défini dans modif_station.php
        if ($date_2_format && $date_2_format->format($date_format) === $date_2_station) 	
        {
            if($date_2_format > $date_1_format)
            {
                $date_2_station_us = $date_2_format->format('Y-m-d');
            }
            else
            {
                $erreur = true;
                $msg_info .= "La date de fermeture de la station ne peut pas être antérieure à sa date d'installation.";
            }
        }
        else
        {
            $erreur = true;
            $msg_info .= "Le format de la date installation n'est pas valide. Veuillez vérifier votre saisie : dd-mm-aaaa<br>";
        }
    }


    // Description
    $description_station = isset($_POST['description_station']) ? $_POST['description_station'] : '';
    $description_station = post_secure($sql_link,$_POST['description_station']);
    // Convertis les sauts de ligne en balises <br> pour l'affichage HTML
    $description_station_html = nl2br(htmlspecialchars($description_station, ENT_QUOTES, 'UTF-8'));


    //A ajouter un peu plus tard
    $source_info = '';
    $transmission_station = '';
    //$source_info = post_secure($sql_link,$_POST['source_info']);
    //$transmission_station = post_secure($sql_link,$_POST['transmission_station']);

    // Onglet Access

    $proprietaire = '';
    if(isset($_POST['proprietaire'])){
        $proprietaire = post_secure($sql_link,$_POST['proprietaire']);
    }

    $contact_nom = '';
    if (isset($_POST['contact_nom'])) {
        $contact_nom = post_secure($sql_link, $_POST['contact_nom']);
    }

    $contact_phone = '';
    if (isset($_POST['contact_phone'])) {
        $contact_phone = post_secure($sql_link, $_POST['contact_phone']);
    }

    $contact_mail = '';
    if (isset($_POST['contact_mail'])) {
        $contact_mail = post_secure($sql_link, $_POST['contact_mail']);
    }

    $contact_adresse = '';
    if (isset($_POST['contact_adresse'])) {
        $contact_adresse = post_secure($sql_link, $_POST['contact_adresse']);
    }

    $contact_bp = '';
    if (isset($_POST['contact_bp'])) {
        $contact_bp = post_secure($sql_link, $_POST['contact_bp']);
    }

    $contact_cp = '';
    if (isset($_POST['contact_cp'])) {
        $contact_cp = post_secure($sql_link, $_POST['contact_cp']);
    }

    $contact_commune = 0; // Liste
    if (isset($_POST['contact_commune'])) {
        $contact_commune = post_secure($sql_link, $_POST['contact_commune']);
    }

    $info_access = '';
    if (isset($_POST['info_access'])) {
        $info_access = post_secure($sql_link, $_POST['info_access']);
    }

    $pedestre_access = 0;
    if(isset($_POST['pedestre_access'])){$pedestre_access = 1;}

    $time_access = '';
    if (isset($_POST['time_access'])) {
        $time_access = post_secure($sql_link, $_POST['time_access']);
    }

    $difficulty_access = '';
    if (isset($_POST['difficulty_access'])) {
        $difficulty_access = post_secure($sql_link, $_POST['difficulty_access']);
    }

    $remarque_access = '';
    if (isset($_POST['remarque_access'])) {
        $remarque_access = post_secure($sql_link, $_POST['remarque_access']);
    }



    // -------------------------------
    // Onglet Caractéristique
    $msg_info_caract = '';
    $msg_info_repere = '';

    // Donnees Caractéristiques puits et Repères puits si station piézométrique
    if($station_type == 5) // si station piézométrique
    {
        // CARACTERISTIQUE

        $array_caract = []; // Initialisation d'un tableau vide pour stocker les identifiants des caractéristiques
        if(isset($_POST['new_caract'])){$array_caract[] = 0;}  // Vérifier si 'new_caract' est présent et ajouter 0 au tableau
        
        // Construire la requête SQL pour sélectionner les ID des caractéristiques de la station 
        $sql_caract = "SELECT DISTINCT c.id
                        FROM ".TABLE_STATION_PIEZO_CARACTERISTIQUE." c
                        WHERE id_station = ".intval($id_station); // Utilisation de intval pour sécuriser
        $caract_query = tep_db_query($sql_link,$sql_caract);
        while($caract_tab = tep_db_fetch_array($caract_query))
        {
            $array_caract[] = $caract_tab['id'];
        }


        // Parcourir chaque élément du tableau avec l'index
        foreach ($array_caract as $key => $id_caract) 
        {
            $tab_caracteristique_post[$id_caract]['prof']= isset($_POST['prof_' . $id_caract]) ? post_secure($sql_link, $_POST['prof_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['prof'] = str_replace(',', '.', $tab_caracteristique_post[$id_caract]['prof']);
            if(!validNumeric($tab_caracteristique_post[$id_caract]['prof'])) // vérifier que nous avons un nombre
            {
                $erreur = true;
                $msg_info_caract  .= "Le champs Profondeur, dans la fiche des Caractéristiques d'un Puits, doit être un nombre.<br>";
                $tab_caracteristique_post[$id_caract]['prof'] = 'NULL';
            }
            
            $tab_caracteristique_post[$id_caract]['materiaux_tete']= isset($_POST['materiaux_tete_' . $id_caract]) ? post_secure($sql_link, $_POST['materiaux_tete_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['dim_tete_ext']= isset($_POST['dim_tete_ext_' . $id_caract]) ? post_secure($sql_link, $_POST['dim_tete_ext_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['materiaux_tub_inter']= isset($_POST['materiaux_tub_inter_' . $id_caract]) ? post_secure($sql_link, $_POST['materiaux_tub_inter_' . $id_caract]) : '';
            
            $tab_caracteristique_post[$id_caract]['diam_tub_inter']= isset($_POST['diam_tub_inter_' . $id_caract]) ? post_secure($sql_link, $_POST['diam_tub_inter_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['diam_tub_inter'] = str_replace(',', '.', $tab_caracteristique_post[$id_caract]['diam_tub_inter']);
            if(!validNumeric($tab_caracteristique_post[$id_caract]['diam_tub_inter'])) // vérifier que nous avons un nombre
            {
                $erreur = true;
                $msg_info_caract  .= "Le champs Dimension du tubage, dans la fiche des Caractéristiques d'un Puits, doit être un nombre.<br>";
                $tab_caracteristique_post[$id_caract]['diam_tub_inter'] = 'NULL';
            }
                        
            $tab_caracteristique_post[$id_caract]['schema_tete']= isset($_POST['schema_tete_' . $id_caract]) ? post_secure($sql_link, $_POST['schema_tete_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['materiaux_dalle']= isset($_POST['materiaux_dalle_' . $id_caract]) ? post_secure($sql_link, $_POST['materiaux_dalle_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['dim_dalle']= isset($_POST['dim_dalle_' . $id_caract]) ? post_secure($sql_link, $_POST['dim_dalle_' . $id_caract]) : '';
            
            $tab_caracteristique_post[$id_caract]['dist_capto_tube']= isset($_POST['dist_capto_tube_' . $id_caract]) ? post_secure($sql_link, $_POST['dist_capto_tube_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['dist_capto_tube'] = str_replace(',', '.', $tab_caracteristique_post[$id_caract]['dist_capto_tube']);
            if(!validNumeric($tab_caracteristique_post[$id_caract]['dist_capto_tube'])) // vérifier que nous avons un nombre
            {
                $erreur = true;
                $msg_info_caract  .= "Le champs Dist. Capot/Tubage (1), dans la fiche des Caractéristiques d'un Puits, doit être un nombre.<br>";
                $tab_caracteristique_post[$id_caract]['dist_capto_tube'] = 'NULL';
            }
            
            $tab_caracteristique_post[$id_caract]['dist_tube_dalle']= isset($_POST['dist_tube_dalle_' . $id_caract]) ? post_secure($sql_link, $_POST['dist_tube_dalle_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['dist_tube_dalle'] = str_replace(',', '.', $tab_caracteristique_post[$id_caract]['dist_tube_dalle']);
            if(!validNumeric($tab_caracteristique_post[$id_caract]['dist_tube_dalle'])) // vérifier que nous avons un nombre
            {
                $erreur = true;
                $msg_info_caract  .= "Le champs Dist. Tubage/Dalle (2), dans la fiche des Caractéristiques d'un Puits, doit être un nombre.<br>";
                $tab_caracteristique_post[$id_caract]['dist_tube_dalle'] = 'NULL';
            }
            
            $tab_caracteristique_post[$id_caract]['dist_dalle_sol']= isset($_POST['dist_dalle_sol_' . $id_caract]) ? post_secure($sql_link, $_POST['dist_dalle_sol_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['dist_dalle_sol'] = str_replace(',', '.', $tab_caracteristique_post[$id_caract]['dist_dalle_sol']);
            if(!validNumeric($tab_caracteristique_post[$id_caract]['dist_dalle_sol'])) // vérifier que nous avons un nombre
            {
                $erreur = true;
                $msg_info_caract  .= "Le champs Dist. Dalle/Sol (3), dans la fiche des Caractéristiques d'un Puits, doit être un nombre.<br>";
                $tab_caracteristique_post[$id_caract]['dist_tube_dalle'] = 'NULL';
            }
            
            $tab_caracteristique_post[$id_caract]['etat']= isset($_POST['etat_' . $id_caract]) ? post_secure($sql_link, $_POST['etat_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['utilisation']= isset($_POST['utilisation_' . $id_caract]) ? post_secure($sql_link, $_POST['utilisation_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['equipement_exploitation']= isset($_POST['equipement_exploitation_' . $id_caract]) ? post_secure($sql_link, $_POST['equipement_exploitation_' . $id_caract]) : '';
            $tab_caracteristique_post[$id_caract]['obs']= isset($_POST['obs_' . $id_caract]) ? post_secure($sql_link, $_POST['obs_' . $id_caract]) : '';

            // Date    
            $date_caract = isset($_POST['date_caract_' . $id_caract]) ? $_POST['date_caract_' . $id_caract] : '';
            $date_caract_us = null;
            if(tep_not_null($date_caract))
            {
                $date_caract_format = DateTime::createFromFormat($date_format, $date_caract); // date_format est défini dans modif_station.php
                if ($date_caract_format && $date_caract_format->format($date_format) === $date_caract) 	
                {
                    $tab_caracteristique_post[$id_caract]['date'] = $date_caract_format->format('Y-m-d');
                }
                else
                {
                    $erreur = true;
                    $msg_info_caract .= "Le format de la date, dans la fiche des Caractéristiques d'un Puits, n'est pas valide. Veuillez vérifier votre saisie : dd-mm-aaaa<br>";
                }
            }

            $schema_protect = 0;
            if(isset($_POST['schema_protect_' . $id_caract])){$schema_protect = 1;}
            $tab_caracteristique_post[$id_caract]['schema_protect'] = $schema_protect;

            $presence_capot = 0;
            if(isset($_POST['presence_capot_' . $id_caract])){$presence_capot = 1;}
            $tab_caracteristique_post[$id_caract]['presence_capot'] = $presence_capot;

            $activite = 0;
            if(isset($_POST['activite_' . $id_caract])){$activite = 1;}
            $tab_caracteristique_post[$id_caract]['activite'] = $activite;

        }

        // REPERE

        $array_repere = []; // Initialisation d'un tableau vide pour stocker les identifiants des reperes
        if(isset($_POST['date_debut_valid_0'])){$array_repere[] = 0;}

        // Construire la requête SQL pour sélectionner les ID des caractéristiques de la station 
        $sql_repere = "SELECT DISTINCT r.id
                        FROM ".TABLE_STATION_PIEZO_REPERE." r
                        WHERE id_station = ".intval($id_station); // Utilisation de intval pour sécuriser
        $repere_query = tep_db_query($sql_link,$sql_repere);
        while($repere_tab = tep_db_fetch_array($repere_query))
        {
            $array_repere[] = $repere_tab['id'];
        }


        // Parcourir chaque élément du tableau avec l'index
        foreach ($array_repere as $key => $id_repere) 
        {
            // Dates    
            $date_debut_valid = isset($_POST['date_debut_valid_'.$id_repere]) ? $_POST['date_debut_valid_'.$id_repere] : '';
            $tab_repere_post[$id_repere]['date_debut_valid'] = '';
            $date_debut_valid_us = null;
            if(tep_not_null($date_debut_valid))
            {
                $date_1_format = DateTime::createFromFormat($date_format, $date_debut_valid); // date_format est défini dans modif_station.php
                if ($date_1_format && $date_1_format->format($date_format) === $date_debut_valid) 	
                {
                    $tab_repere_post[$id_repere]['date_debut_valid'] = $date_1_format->format('Y-m-d');
                }
                else
                {
                    $erreur = true;
                    $msg_info_repere .= "Le format de la date de début de validité d'un Repère n'est pas correct. Veuillez vérifier votre saisie : dd-mm-aaaa<br>";
                }
            }
            

            $date_fin_valid = isset($_POST['date_fin_valid_'.$id_repere]) ? $_POST['date_fin_valid_'.$id_repere] : '';
            $tab_repere_post[$id_repere]['date_fin_valid'] = '';
            $date_fin_valid_us = null;
            if(tep_not_null($date_fin_valid))
            {
                $date_2_format = DateTime::createFromFormat($date_format, $date_fin_valid); // date_format est défini dans modif_station.php
                if ($date_2_format && $date_2_format->format($date_format) === $date_fin_valid) 	
                {
                    if($date_2_format > $date_1_format)
                    {
                        $tab_repere_post[$id_repere]['date_fin_valid'] = $date_2_format->format('Y-m-d');
                    }
                    else
                    {
                        $erreur = true;
                        $msg_info_repere .= "La date de fin de validité d'un Repère ne peut pas être antérieure à sa date de début de validité.";
                    }
                }
                else
                {
                    $erreur = true;
                    $msg_info_repere .= "Le format de la date de fin de validité d'un Repère n'est pas correct. Veuillez vérifier votre saisie : dd-mm-aaaa<br>";
                }
            }

            $tab_repere_post[$id_repere]['nature_repere']= isset($_POST['nature_repere_' . $id_repere]) ? post_secure($sql_link, $_POST['nature_repere_' . $id_repere]) : '';
            $tab_repere_post[$id_repere]['code_repere']= isset($_POST['code_repere_' . $id_repere]) ? post_secure($sql_link, $_POST['code_repere_' . $id_repere]) : '';
            
            $tab_repere_post[$id_repere]['z_repere']= isset($_POST['z_repere_' . $id_repere]) ? post_secure($sql_link, $_POST['z_repere_' . $id_repere]) : '';
            $tab_repere_post[$id_repere]['z_repere'] = str_replace(',', '.', $tab_repere_post[$id_repere]['z_repere']);
            if(!validNumeric($tab_repere_post[$id_repere]['z_repere'])) // vérifier que nous avons un nombre
            {
                $erreur = true;
                $msg_info_repere  .= "Le champs Z - Repère, dans la fiche des Repère d'un Puits, doit être un nombre.<br>";
                $tab_repere_post[$id_repere]['z_repere'] = 'NULL';
            }
            
            $tab_repere_post[$id_repere]['precision_repere']= isset($_POST['precision_repere_' . $id_repere]) ? post_secure($sql_link, $_POST['precision_repere_' . $id_repere]) : '';
            $tab_repere_post[$id_repere]['nature_repere_1']= isset($_POST['nature_repere_1_' . $id_repere]) ? post_secure($sql_link, $_POST['nature_repere_1_' . $id_repere]) : '';
            
            $tab_repere_post[$id_repere]['z_repere_g1']= isset($_POST['z_repere_g1_' . $id_repere]) ? post_secure($sql_link, $_POST['z_repere_g1_' . $id_repere]) : '';
            $tab_repere_post[$id_repere]['z_repere_g1'] = str_replace(',', '.', $tab_repere_post[$id_repere]['z_repere_g1']);
            if(!validNumeric($tab_repere_post[$id_repere]['z_repere_g1'])) // vérifier que nous avons un nombre
            {
                $erreur = true;
                $msg_info_repere  .= "Le champs Z - Relevé 1 Géomètre, dans la fiche des Repère d'un Puits, doit être un nombre.<br>";
                $tab_repere_post[$id_repere]['z_repere_g1'] = 'NULL';
            }

            $tab_repere_post[$id_repere]['nature_repere_2']= isset($_POST['nature_repere_2_' . $id_repere]) ? post_secure($sql_link, $_POST['nature_repere_2_' . $id_repere]) : '';
            
            $tab_repere_post[$id_repere]['z_repere_g2']= isset($_POST['z_repere_g2_' . $id_repere]) ? post_secure($sql_link, $_POST['z_repere_g2_' . $id_repere]) : '';
            $tab_repere_post[$id_repere]['z_repere_g2'] = str_replace(',', '.', $tab_repere_post[$id_repere]['z_repere_g2']);
            if(!validNumeric($tab_repere_post[$id_repere]['z_repere_g2'])) // vérifier que nous avons un nombre
            {
                $erreur = true;
                $msg_info_repere  .= "Le champs Z - Relevé 2 Géomètre, dans la fiche des Repère d'un Puits, doit être un nombre.<br>";
                $tab_repere_post[$id_repere]['z_repere_g2'] = 'NULL';
            }

            $tab_repere_post[$id_repere]['obs']= isset($_POST['obs_' . $id_repere]) ? post_secure($sql_link, $_POST['obs_' . $id_repere]) : '';

        }

    }



    // --------------------------------------------------------------------------
    // Enregistrement des données dans la base

    if(!$erreur)
    {   
        // Encapsulation des requêtes dans une transaction pour assurer l'intégrité des données en cas d'échec d'une partie de l'opération
        // Commencer une transaction
        tep_db_query($sql_link, "START TRANSACTION");
        
        try {

            if($id_station < 1) // Si on enregistre une nouvelle Station id_ra=0
            {
                // requête sql pour récupérer les données articles
                $sql_station_verif = "SELECT DISTINCT s.id_station FROM ".TABLE_STATION." s WHERE s.code_station='".$code_station."'";
                $station_verif_query = tep_db_query($sql_link,$sql_station_verif);
                $station_verif = tep_db_fetch_array($station_verif_query);

                if(isset($station_verif))
                {	
                    $erreur = true;
                    $msg_info .= "Ce code ".$code_station." est déjà attribué, il n'est pas possible de créer une nouvelle station avec ce Code Station";
                }
                else
                {
                    $query = "INSERT INTO " . TABLE_STATION . " (id_station,code_station) VALUES ('$id_station','$code_station')";
                    tep_db_query($sql_link, $query);

                    // Récupérer le nouvel identifiant station
                    $id_station = mysqli_insert_id($sql_link); 
                    $newStation = true;     

                    $msg_info_send .= "<span style='font-size:16px;'>";
                        $msg_info_send .= "La nouvelle fiche Station a bien été crée";
                    $msg_info_send .= "</span>";
                    $msg_info_send .= "<br><br>";

                    $msg_info .= "Station : ".$nom_station;

                    // Enregistrement de l'action dans la table ACTION qui suit toutes les actions de la plateforme
                    $type_action = 38;
                    $info_action = "Création nouvelle Fiche Station : ".$code_station." - ".$nom_station ."<br>";
                }            
            }
            else
            {
                $msg_info_send .= "<span style='font-size:16px;'>";
                    $msg_info_send .= "La fiche Station a bien été enregistrée";
                $msg_info_send .= "</span>";
                $msg_info_send .= "<br><br>";

                $msg_info .= "Station : ".$nom_station;
                
                // Enregistrement de l'action Administration
                $type_action = 38;
                $info_action = "Modification d'une Fiche Station : ".$code_station." - ".$nom_station;
            }


            if(!$erreur) // Cette erreur serait liée à la création d'une nouvelle station avec le même code station
            {
                // Mise à jour de la Fiche Station (Nouveau ou Modifié)
                $query = "UPDATE ".TABLE_STATION." SET 
                                                    nom_station='$nom_station', 
                                                    nom_court='$nom_court', 
                                                    code_station='$code_station', 
                                                    num_irh='$num_irh', 
                                                    id_territoire='$territoire_id', 
                                                    id_region='$select_region',
                                                    id_commune='$select_commune', 
                                                    vallee_station='$vallee_station', 
                                                    riviere_station='$riviere_station',
                                                    id_tournee='$tournee_station',
                                                    id_regionhydro='$regionhydro_station',
                                                    altitude_station='$altitude_station', 
                                                    orientation_station='$orientation_station',
                                                    longitude_station='$longitude_station', 
                                                    latitude_station='$latitude_station',
                                                    utm_station_x='$utm_station_x', 
                                                    utm_station_y='$utm_station_y',
                                                    ign_station_x='$ign_station_x', 
                                                    ign_station_y='$ign_station_y',
                                                    lamb_station_x='$lamb_station_x', 
                                                    lamb_station_y='$lamb_station_y',					
                                                    station_type='$station_type',
                                                    date_installation_station=" . ($date_1_station_us === null ? 'NULL' : "'$date_1_station_us'") . ",
                                                    date_fermeture_station=" . ($date_2_station_us === null ? 'NULL' : "'$date_2_station_us'") . ", 
                                                    description_station='$description_station_html',
                                                    active_station='$active_station', 
                                                    suivi='$suivi_station', 					
                                                    armee='$armee_station', 
                                                    source_info='$source_info',
                                                    transmission_station='$transmission_station'
                                                    WHERE id_station=$id_station";

                // Execution de la requête
                tep_db_query($sql_link, $query);

                // ----------------------------------
                // Data Access
                
                $sql_access = "SELECT DISTINCT id_station
                        FROM ".TABLE_STATION_ACCESS."
                        WHERE id_station = ".intval($id_station); // Utilisation de intval pour sécuriser
                $access_query = tep_db_query($sql_link,$sql_access);
                $access_tab = tep_db_fetch_array($access_query);

                if(isset($access_tab))
                {
                    $query_del_access = "DELETE FROM ".TABLE_STATION_ACCESS." WHERE id_station = ".intval($id_station);
                    tep_db_query($sql_link, $query_del_access);
                }

                
                $query_insert_access = "INSERT INTO " . TABLE_STATION_ACCESS . " (id_station) VALUES ('$id_station')";
                tep_db_query($sql_link, $query_insert_access);
                
                $query_update_access = "UPDATE ".TABLE_STATION_ACCESS." SET 
                                                                    proprietaire='$proprietaire', 
                                                                    contact_nom='$contact_nom', 
                                                                    contact_phone='$contact_phone', 
                                                                    contact_mail='$contact_mail', 
                                                                    contact_adresse='$contact_adresse', 
                                                                    contact_bp='$contact_bp',
                                                                    contact_cp='$contact_cp',
                                                                    contact_commune='$contact_commune', 
                                                                    info_access='$info_access', 
                                                                    pedestre_access='$pedestre_access',
                                                                    time_access='$time_access',
                                                                    difficulty_access='$difficulty_access',
                                                                    remarque_access='$remarque_access'
                                                                    WHERE id_station=$id_station";
                tep_db_query($sql_link, $query_update_access);
                

                // ----------------------------------
                // Station Piezo

                if($station_type == 5) // si station piézométrique - Enregistrement des onglet Caractristiques et Repères
                {
                    if(isset($array_caract)) // Données Caractéristiques
                    {
                        foreach ($array_caract as $key => $id_caract) 
                        {
                            $id_caract_update = $id_caract;
                            if($id_caract < 1) // Nouvelle fiche de Caractéristique de puits
                            {
                                $query = "INSERT INTO " . TABLE_STATION_PIEZO_CARACTERISTIQUE . " (id_station) VALUES ('$id_station')";
                                tep_db_query($sql_link, $query);

                                // Récupérer le nouvel identifiant station
                                $id_caract_update = mysqli_insert_id($sql_link); 
                            }

                            $date_caract_us = $tab_caracteristique_post[$id_caract]['date'];

                            // Mise à jour de la Fiche Station (Nouveau ou Modifié)
                            $query = "UPDATE ".TABLE_STATION_PIEZO_CARACTERISTIQUE." SET 
                                                    date=" . ($date_caract_us === null ? "NULL" : "'".$date_caract_us."'") . ", 
                                                    prof='".$tab_caracteristique_post[$id_caract]['prof']."',
                                                    materiaux_tete='".$tab_caracteristique_post[$id_caract]['materiaux_tete']."',
                                                    dim_tete_ext='".$tab_caracteristique_post[$id_caract]['dim_tete_ext']."',
                                                    materiaux_tub_inter='".$tab_caracteristique_post[$id_caract]['materiaux_tub_inter']."',
                                                    diam_tub_inter='".$tab_caracteristique_post[$id_caract]['diam_tub_inter']."',
                                                    materiaux_dalle='".$tab_caracteristique_post[$id_caract]['materiaux_dalle']."',
                                                    dim_dalle='".$tab_caracteristique_post[$id_caract]['dim_dalle']."',
                                                    dist_capto_tube='".$tab_caracteristique_post[$id_caract]['dist_capto_tube']."',
                                                    dist_tube_dalle='".$tab_caracteristique_post[$id_caract]['dist_tube_dalle']."',
                                                    dist_dalle_sol='".$tab_caracteristique_post[$id_caract]['dist_dalle_sol']."',
                                                    presence_capot='".$tab_caracteristique_post[$id_caract]['presence_capot']."',
                                                    etat='".$tab_caracteristique_post[$id_caract]['etat']."',
                                                    activite='".$tab_caracteristique_post[$id_caract]['activite']."',
                                                    utilisation='".$tab_caracteristique_post[$id_caract]['utilisation']."',
                                                    equipement_exploitation='".$tab_caracteristique_post[$id_caract]['equipement_exploitation']."',
                                                    schema_tete='".$tab_caracteristique_post[$id_caract]['schema_tete']."',
                                                    schema_protect='".$tab_caracteristique_post[$id_caract]['schema_protect']."',
                                                    obs='".$tab_caracteristique_post[$id_caract]['obs']."'
                                                    WHERE id=".intval($id_caract_update);
                        
                            tep_db_query($sql_link,$query);

                        }
                    }

                    if(isset($array_repere)) // Données Repères
                    {
                        foreach ($array_repere as $key => $id_repere) 
                        {
                            $id_repere_update = $id_repere;
                            if($id_repere < 1) // Nouvelle fiche de Caractéristique de puits
                            {
                                $query = "INSERT INTO " . TABLE_STATION_PIEZO_REPERE . " (id_station) VALUES ('$id_station')";
                                tep_db_query($sql_link, $query);

                                // Récupérer le nouvel identifiant station
                                $id_repere_update = mysqli_insert_id($sql_link); 
                            }

                            $date_debut_valid_us = $tab_repere_post[$id_repere]['date_debut_valid'];
                            $date_fin_valid_us = $tab_repere_post[$id_repere]['date_fin_valid'];

                            // Mise à jour de la Fiche Station (Nouveau ou Modifié)
                            $query = "UPDATE ".TABLE_STATION_PIEZO_REPERE." SET 
                                                    date_debut_valid=" . ($date_debut_valid_us === null ? "NULL" : "'".$date_debut_valid_us."'") . ", 
                                                    date_fin_valid=" . ($date_fin_valid_us === null ? "NULL" : "'".$date_fin_valid_us."'") . ", 
                                                    nature_repere='".$tab_repere_post[$id_repere]['nature_repere']."',
                                                    code_repere='".$tab_repere_post[$id_repere]['code_repere']."',
                                                    z_repere='".$tab_repere_post[$id_repere]['z_repere']."',
                                                    precision_repere='".$tab_repere_post[$id_repere]['precision_repere']."',
                                                    nature_repere_1='".$tab_repere_post[$id_repere]['nature_repere_1']."',
                                                    z_repere_g1='".$tab_repere_post[$id_repere]['z_repere_g1']."',
                                                    nature_repere_2='".$tab_repere_post[$id_repere]['nature_repere_2']."',
                                                    z_repere_g2='".$tab_repere_post[$id_repere]['z_repere_g2']."',
                                                    obs='".$tab_repere_post[$id_repere]['obs']."'
                                                    WHERE id=".intval($id_repere_update);
                        
                            tep_db_query($sql_link,$query);

                        }
                    }
                }

                


                // Enregistrement de l'action dans la table action
                $today_us = date('Y-m-d H:i:s'); 
                $info_action = post_secure($sql_link,$info_action); 

                $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) 
                                                    VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$today_us."')";
                tep_db_query($sql_link,$query);
            }

            tep_db_query($sql_link, "COMMIT"); // Si toutes les opérations réussissent, confirmer la transaction

        } catch (Exception $e) 
        {        
            tep_db_query($sql_link, "ROLLBACK"); // En cas d'erreur, annuler la transaction
            
            $msg_info_send .= "La correction des données a rencontré un problème lors de l'écriture dans les tables"; // message d'erreur de transaction
            $msg_info_send .= "<br>Une erreur est survenue : " . $e->getMessage(); // Afficher le message d'erreur

            $erreur = true;
        }

    }
    else
    {
        $msg_info_send .= "<span style='font-size:16px;'>";
        $msg_info_send .= "Une erreur est survenue : la fiche Station n'a pas pu être enregistrée";
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


$msg_info_send .= $msg_info.$msg_info_caract.$msg_info_repere; 


// Remplissage du tableau de retour

$responseData = array(
    'erreur' => $erreur,
    'new_station' => $newStation,    
    'id_station' => $id_station,    
    'msg_info' => $msg_info_send
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>