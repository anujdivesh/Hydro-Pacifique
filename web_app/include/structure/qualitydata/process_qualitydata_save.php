<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Enregistrement des Qualité de données (Code Qualité)
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
/*
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
                                    */


// -------------------------------------------------------------


// Initialisation Variables Globales
$msg_info_send = '';
$msg_info = '';
$erreur = false;
$tab_html = '';
$newDataGeo = false;

$row = 0;
$date_format = 'd-m-Y';

// Vérifier si la requête envoyé depuis le client est bien une requête POST
if($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $id_user_agent = isset($_POST['id_user_agent']) ? $_POST['id_user_agent'] : '';
    $territoire_id = isset($_POST['territoire_id']) ? $_POST['territoire_id'] : '';
    
    // --------------------------------------------------------------------------
    // Enregistrement des données dans la base

       
        // Encapsulation des requêtes dans une transaction pour assurer l'intégrité des données en cas d'échec d'une partie de l'opération
        // Commencer une transaction
        tep_db_query($sql_link, "START TRANSACTION");
        
        try {

            // Quality Data
            $sql_quality = "SELECT DISTINCT id_data_qualite FROM ".TABLE_DATA_QUALITE;
                                //WHERE init_qualite_data<>''";
            $quality_query = tep_db_query($sql_link,$sql_quality);
            while ($quality = tep_db_fetch_array($quality_query)) 
            {
                $id_quality = $quality['id_data_qualite'];

                $quality_init = post_secure($sql_link,$_POST['quality_init_'.$id_quality]);
                $quality_nom = post_secure($sql_link,$_POST['quality_nom_'.$id_quality]);
                $quality_info = post_secure($sql_link,$_POST['quality_info_'.$id_quality]);		
                $quality_select_type = post_secure($sql_link,$_POST['quality_select_type_'.$id_quality]);

                tep_db_query($sql_link,"UPDATE ".TABLE_DATA_QUALITE." SET init_qualite_data='".$quality_init."',
                                                                    nom_qualite_data='".$quality_nom."',
                                                                    info_qualite_data='".$quality_info."',
                                                                    id_eq_type='".$quality_select_type."'
                                                                    WHERE id_data_qualite=".$id_quality);
            }


            // Nouveau Code Qualité
            if(tep_not_null($_POST['quality_init_0']))
            {
                $quality_init_0 = post_secure($sql_link,$_POST['quality_init_0']);
                $quality_nom_0 = post_secure($sql_link,$_POST['quality_nom_0']);
                $quality_info_0 = post_secure($sql_link,$_POST['quality_info_0']);	
                $quality_select_type_0 = post_secure($sql_link,$_POST['quality_select_type_0']);
                
                $sql_verifquality = "SELECT DISTINCT id_data_qualite FROM ".TABLE_DATA_QUALITE."
                                     WHERE init_qualite_data='".$quality_init_0."'";
                $verifquality_query = tep_db_query($sql_link,$sql_verifquality);	
                $verifquality_array = tep_db_fetch_array($verifquality_query);

                if(isset($verifquality_array['id_data_qualite']) && tep_not_null($verifquality_array['id_data_qualite']))	// Si un code qualité identique existe déjà
                {
                    $erreur = true;
                    $msg_info_send .= htmlaccent('Un Code Qualité avec un intitulé identique - '.$quality_init_0.' - existe déjà. Il ne peut pas être ajouter une seconde fois.');
                }
                else
                {
                    tep_db_query($sql_link,"INSERT INTO ".TABLE_DATA_QUALITE." (init_qualite_data,nom_qualite_data,info_qualite_data,id_eq_type) 
                                                                VALUES ('".$quality_init_0."','".$quality_nom_0."','".$quality_info_0."','".$quality_select_type_0."')");	
                }
            }

            // -------------------------------------------------
            // Enregistrement de l'action dans la table action
            $today_us = date('Y-m-d H:i:s'); 
            $type_action = 13; // Action Paramétrages
            $info_action = "Enregistrement des Codes Qualités"; 

            $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) 
                                                VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$today_us."')";
            tep_db_query($sql_link,$query);
        

            tep_db_query($sql_link, "COMMIT"); // Si toutes les opérations réussissent, confirmer la transaction

        } catch (Exception $e) 
        {        
            tep_db_query($sql_link, "ROLLBACK"); // En cas d'erreur, annuler la transaction
            
            $msg_info_send .= "L'enregistrement des données géographiques a rencontré un problème lors de l'écriture dans les tables"; // message d'erreur de transaction
            $msg_info_send .= "<br>Une erreur est survenue : " . $e->getMessage(); // Afficher le message d'erreur

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


// Remplissage du tableau de retour

$responseData = array(
    'erreur' => $erreur,
    'msg_info' => $msg_info_send
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>