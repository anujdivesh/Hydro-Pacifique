<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Enregistrement des Chronique de données (CI, CIE, QI, ...) et des informations d'afficchages sur les axes des graphiques
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
$newTypeData = false;

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

            // Chronique de données (CI, CIE, QI, ...)
            $sql_typedata = "SELECT DISTINCT id_data_type, init_type_data FROM ".TABLE_TYPE_DATA;
            $typedata_query = tep_db_query($sql_link,$sql_typedata);
            while ($typedata = tep_db_fetch_array($typedata_query)) 
            {
                $id_chron = $typedata['id_data_type'];
                
                if(isset($_POST['chron_init_'.$id_chron]))
                {
                    ${'chron_init_'.$id_chron} = post_secure($sql_link,$_POST['chron_init_'.$id_chron]);
                    ${'chron_nom_'.$id_chron} = post_secure($sql_link,$_POST['chron_nom_'.$id_chron]);
                    ${'chron_select_type_'.$id_chron} = post_secure($sql_link,$_POST['chron_select_type_'.$id_chron]);
                    ${'chron_select_axe_'.$id_chron} = post_secure($sql_link,$_POST['chron_select_axe_'.$id_chron]);
                    ${'chron_unite_'.$id_chron} = post_secure($sql_link,$_POST['chron_unite_'.$id_chron]);
                    ${'chron_select_to_periode_'.$id_chron} = post_secure($sql_link,$_POST['chron_select_to_periode_'.$id_chron]);
                    ${'chron_select_chron_periode_'.$id_chron} = post_secure($sql_link,$_POST['chron_select_chron_periode_'.$id_chron]);
                    ${'chron_select_traitement_'.$id_chron} = post_secure($sql_link,$_POST['chron_select_traitement_'.$id_chron]);
                    ${'chron_select_typegraph_'.$id_chron} = post_secure($sql_link,$_POST['chron_select_typegraph_'.$id_chron]);

                    // On vérifie qu'il n'existe pas une chronique avec le même acronyme                
                    $sql_verif_typedata = "SELECT EXISTS (
                                                            SELECT 1 FROM ".TABLE_TYPE_DATA." WHERE init_type_data='".${'chron_init_'.$id_chron}."' LIMIT 1
                                                        ) AS typedata_exists";
                    $verif_typedata_query = tep_db_query($sql_link,$sql_verif_typedata);	
                    $verif_typedata_array = tep_db_fetch_array($verif_typedata_query);

                    if((${'chron_init_'.$id_chron} != $typedata['init_type_data']) && ($verif_typedata_array['typedata_exists'] == 1))	// Si le type de données existe déjà
                    {		
                        $erreur = true;
                        $msg_info_send .= htmlaccent('Une Chronique avec un intitulé identique - '.${'chron_init_'.$id_chron}.' - existe déjà. Elle ne peut pas être ajoutée une seconde fois.');
                        $msg_info_send .= "<br>";
                    }
                    else
                    {
                        $sql_udpate_chron = "UPDATE ".TABLE_TYPE_DATA." SET init_type_data='".${'chron_init_'.$id_chron}."',
                                                                            nom_type_data='".${'chron_nom_'.$id_chron}."',
                                                                            id_eq_type_data='".${'chron_select_type_'.$id_chron}."',
                                                                            axe_data='".${'chron_select_axe_'.$id_chron}."',
                                                                            unite='".${'chron_unite_'.$id_chron}."',
                                                                            to_periode='".${'chron_select_to_periode_'.$id_chron}."',
                                                                            id_chon_periode='".${'chron_select_chron_periode_'.$id_chron}."',
                                                                            traitement='".${'chron_select_traitement_'.$id_chron}."',
                                                                            type_graph='".${'chron_select_typegraph_'.$id_chron}."'
                                                                        WHERE id_data_type=".$id_chron;

                        tep_db_query($sql_link,$sql_udpate_chron);
                    }	
                }
            }

            // Nouveau Type de Chronique
            if(tep_not_null($_POST['chron_init_0']))
            {
                $chron_init_0 = post_secure($sql_link,$_POST['chron_init_0']);
                $chron_nom_0 = post_secure($sql_link,$_POST['chron_nom_0']);
                $chron_select_type_mesure_0 = post_secure($sql_link,$_POST['chron_select_type_mesure_0']);
                $chron_select_axe_0 = post_secure($sql_link,$_POST['chron_select_axe_0']);
                $chron_unite_0 = post_secure($sql_link,$_POST['chron_unite_0']);
                $chron_select_to_periode_0 = post_secure($sql_link,$_POST['chron_select_to_periode_0']);
                $chron_select_chron_periode_0 = post_secure($sql_link,$_POST['chron_select_chron_periode_0']);
                $chron_select_traitement_0 = post_secure($sql_link,$_POST['chron_select_traitement_0']);
                $chron_select_typegraph_0 = post_secure($sql_link,$_POST['chron_select_typegraph_0']);

                $sql_verif_typedata = "SELECT EXISTS (
                                                        SELECT 1 FROM ".TABLE_TYPE_DATA." WHERE init_type_data='".$chron_init_0."' LIMIT 1
                                                    ) AS typedata_exists";
                $verif_typedata_query = tep_db_query($sql_link,$sql_verif_typedata);	
                $verif_typedata_array = tep_db_fetch_array($verif_typedata_query);

                if($verif_typedata_array['typedata_exists'] == 1)	// Si le type de données existe déjà
                {
                    $erreur = true;
                    $msg_info_send .= htmlaccent('Une Chronique avec un intitulé identique - '.$init_0.' - existe déjà. Elle ne peut pas être ajoutée une seconde fois.');$msg_info_send .= "<br>";     
                    $msg_info_send .= "<br>";                         
                }
                else
                {
                    tep_db_query($sql_link,"INSERT INTO ".TABLE_TYPE_DATA." (init_type_data,
                                                                            nom_type_data,
                                                                            id_eq_type_data,
                                                                            axe_data,
                                                                            unite,
                                                                            to_periode,
                                                                            id_chon_periode,
                                                                            traitement,
                                                                            type_graph) 
                                                                VALUES ('".$chron_init_0."',
                                                                        '".$chron_nom_0."',
                                                                        '".$chron_select_type_mesure_0."',
                                                                        '".$chron_select_axe_0."',
                                                                        '".$chron_unite_0."',
                                                                        '".$chron_select_to_periode_0."',
                                                                        '".$chron_select_chron_periode_0."',
                                                                        '".$chron_select_traitement_0."',
                                                                        '".$chron_select_typegraph_0."'
                                                                        )");	
                }	
            }


            // Modification des Axes de données

            $sql_datatype_axe = "SELECT DISTINCT id, axe, unite FROM ".TABLE_DATA_TYPE_AXE;
            $data_typeaxe_query = tep_db_query($sql_link,$sql_datatype_axe);
            while ($datatype_axe = tep_db_fetch_array($data_typeaxe_query)) 
            {
                $id_axe = $datatype_axe['id'];
                
                ${'axe_nom_'.$id_axe} = post_secure($sql_link,$_POST['axe_nom_'.$id_axe]);
                ${'axe_unite_'.$id_axe} = post_secure($sql_link,$_POST['axe_unite_'.$id_axe]);

                $sql_verif_axe = "SELECT EXISTS (
                                    SELECT 1 FROM ".TABLE_DATA_TYPE_AXE." WHERE axe='".${'axe_nom_'.$id_axe}."' LIMIT 1
                                ) AS axe_exists";
                $verif_axe_query = tep_db_query($sql_link,$sql_verif_axe);	
                $verif_axe_array = tep_db_fetch_array($verif_axe_query);

                if((${'axe_nom_'.$id_axe} != $datatype_axe['axe']) && ($verif_axe_array['axe_exists'] == 1))	// Si l'Axe existe déjà
                {
                    $erreur = true;    
                    $msg_info_send .= htmlaccent('Un Axe de données avec un intitulé identique - '.${'axe_nom_'.$id_axe}.' - existe déjà. Il ne peut pas être ajouté une seconde fois.');
                    $msg_info_send .= "<br>"; 
                }
                else
                {
                    tep_db_query($sql_link,"UPDATE ".TABLE_DATA_TYPE_AXE." SET axe ='".${'axe_nom_'.$id_axe}."',
                                                                            unite='".${'axe_unite_'.$id_axe}."'
                                                                        WHERE id=".$id_axe);
                }
            }

            // Nouvel Axe de données
            if(tep_not_null($_POST['axe_nom_0']))
            {
                $axe_nom_0 = post_secure($sql_link,$_POST['axe_nom_0']);
                $axe_unite_0 = post_secure($sql_link,$_POST['axe_unite_0']);

                $sql_verif_axe = "SELECT EXISTS (
                                                    SELECT 1 FROM ".TABLE_DATA_TYPE_AXE." WHERE axe='".$axe_nom_0."' LIMIT 1
                                                ) AS axe_exists";
                $verif_axe_query = tep_db_query($sql_link,$sql_verif_axe);	
                $verif_axe_array = tep_db_fetch_array($verif_axe_query);

                if($verif_axe_array['axe_exists'] == 1)	// Si l'Axe existe déjà
                {
                    $erreur = true;   
                    $msg_info_send .= htmlaccent('Un Axe de données avec un intitulé identique - '.$axe_nom_0.' - existe déjà. Il ne peut pas être ajouté une seconde fois.');
                    $msg_info_send .= "<br>";  
                }
                else
                {
                    tep_db_query($sql_link,"INSERT INTO ".TABLE_DATA_TYPE_AXE." (axe,unite) 
                                                                VALUES ('".$axe_nom_0."','".$axe_unite_0."')");	
                }	
            }

            $msg_info_send .= "Les configurations des Chroniques et des Axes ont bien été enregistrées";



            // ---------------------------------------

            // Enregistrement de l'action dans la table action
            $today_us = date('Y-m-d H:i:s'); 
            $type_action = 13; // Action Paramétrages
            $info_action = "Enregistrement des données liées aux types de Chroniques"; 

            $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) 
                                                VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$today_us."')";
            tep_db_query($sql_link,$query);
        

            tep_db_query($sql_link, "COMMIT"); // Si toutes les opérations réussissent, confirmer la transaction

        } catch (Exception $e) 
        {        
            tep_db_query($sql_link, "ROLLBACK"); // En cas d'erreur, annuler la transaction
            
            $msg_info_send .= "L'enregistrement des données Chroniques et Axes a rencontré un problème lors de l'écriture dans les tables"; // message d'erreur de transaction
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