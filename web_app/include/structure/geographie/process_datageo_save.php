<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Enregistrement des données Géographique de la plateforme (RégionGeo, RegionHydro, Communes, Rivières, Tournées)
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

            // Régions Géographiques

            $sql_regiongeo = "SELECT DISTINCT id_region FROM ".TABLE_REGION." WHERE id_territoire=".$territoire_id;
            $regiongeo_query = tep_db_query($sql_link,$sql_regiongeo);
            while ($regiongeo = tep_db_fetch_array($regiongeo_query)) 
            {
                if(isset($_POST['regiongeo_nom_'.$regiongeo['id_region']]))
                {
                    ${'regiongeo_nom_'.$regiongeo['id_region']} = post_secure($sql_link,$_POST['regiongeo_nom_'.$regiongeo['id_region']]);

                    tep_db_query($sql_link,"UPDATE ".TABLE_REGION." SET nom_region='".${'regiongeo_nom_'.$regiongeo['id_region']}."'
                                                                    WHERE id_region=".$regiongeo['id_region']);
                }
            }

            if(tep_not_null($_POST['regiongeo_nom_0']))
            {
                $regiongeo_nom_0 = post_secure($sql_link,$_POST['regiongeo_nom_0']);    
                
                tep_db_query($sql_link,"INSERT INTO ".TABLE_REGION." (nom_region,id_territoire) 
                                            VALUES ('".$regiongeo_nom_0."','".$territoire_id."')");
            }
            


            // Communes

            $sql_commune = "SELECT DISTINCT id_commune FROM ".TABLE_COMMUNE." WHERE id_territoire=".$territoire_id;
            $commune_query = tep_db_query($sql_link,$sql_commune);
            while ($commune = tep_db_fetch_array($commune_query)) 
            {
                if(isset($_POST['commune_nom_'.$commune['id_commune']]))
                {
                    ${'commune_nom_'.$commune['id_commune']} = post_secure($sql_link,$_POST['commune_nom_'.$commune['id_commune']]);
                    ${'select_commune_regiongeo_'.$commune['id_commune']} = post_secure($sql_link,$_POST['select_commune_regiongeo_'.$commune['id_commune']]);

                    tep_db_query($sql_link,"UPDATE ".TABLE_COMMUNE." SET nom_commune='".${'commune_nom_'.$commune['id_commune']}."',
                                                                        id_region='".${'select_commune_regiongeo_'.$commune['id_commune']}."'
                                                                        WHERE id_commune=".$commune['id_commune']);
                }
            }

            if(tep_not_null($_POST['commune_nom_0']))
            {
                $commune_nom_0 = post_secure($sql_link,$_POST['commune_nom_0']);    
                $select_commune_regiongeo_0 = post_secure($sql_link,$_POST['select_commune_regiongeo_0']);
                
                tep_db_query($sql_link,"INSERT INTO ".TABLE_COMMUNE." (nom_commune,id_region,id_territoire) 
                                            VALUES ('".$commune_nom_0."','".$select_commune_regiongeo_0."','".$territoire_id."')");
            }


            // Régions Hydrologiques

            $sql_regionhydro = "SELECT DISTINCT id FROM ".TABLE_REGIONHYDRO." WHERE id_territoire=".$territoire_id;
            $regionhydro_query = tep_db_query($sql_link,$sql_regionhydro);
            while ($regionhydro = tep_db_fetch_array($regionhydro_query)) 
            {
                if(isset($_POST['regionhydro_nom_'.$regionhydro['id']]))
                {
                    ${'regionhydro_nom_'.$regionhydro['id']} = post_secure($sql_link,$_POST['regionhydro_nom_'.$regionhydro['id']]);
                    ${'regionhydro_description_'.$regionhydro['id']} = post_secure($sql_link,$_POST['regionhydro_description_'.$regionhydro['id']]);

                    tep_db_query($sql_link,"UPDATE ".TABLE_REGIONHYDRO." SET nom='".${'regionhydro_nom_'.$regionhydro['id']}."',
                                                                            description='".${'regionhydro_description_'.$regionhydro['id']}."'
                                                                        WHERE id=".$regionhydro['id']);
                }
            }

            if(tep_not_null($_POST['regionhydro_nom_0']))
            {
                $regionhydro_nom_0 = post_secure($sql_link,$_POST['regionhydro_nom_0']);    
                $regionhydro_description_0 = post_secure($sql_link,$_POST['regionhydro_description_0']);
                
                tep_db_query($sql_link,"INSERT INTO ".TABLE_REGIONHYDRO." (nom,description,id_territoire) 
                                            VALUES ('".$regionhydro_nom_0."','".$regionhydro_description_0."','".$territoire_id."')");
            }


            // Rivieres

            $sql_riviere = "SELECT DISTINCT id FROM ".TABLE_RIVIERE." WHERE id_territoire=".$territoire_id;
            $riviere_query = tep_db_query($sql_link,$sql_riviere);
            while ($riviere = tep_db_fetch_array($riviere_query)) 
            {
                if(isset($_POST['riviere_nom_'.$riviere['id']]))
                {
                    ${'riviere_nom_'.$riviere['id']} = post_secure($sql_link,$_POST['riviere_nom_'.$riviere['id']]);
                    ${'riviere_description_'.$riviere['id']} = post_secure($sql_link,$_POST['riviere_description_'.$riviere['id']]);
                    ${'select_riviere_regionhydro_'.$riviere['id']} = post_secure($sql_link,$_POST['select_riviere_regionhydro_'.$riviere['id']]);

                    tep_db_query($sql_link,"UPDATE ".TABLE_RIVIERE." SET nom='".${'riviere_nom_'.$riviere['id']}."',
                                                                        description='".${'riviere_description_'.$riviere['id']}."',
                                                                        id_regionhydro='".${'select_riviere_regionhydro_'.$riviere['id']}."'
                                                                        WHERE id=".$riviere['id']);
                }
            }

            if(tep_not_null($_POST['riviere_nom_0']))
            {
                $riviere_nom_0 = post_secure($sql_link,$_POST['riviere_nom_0']);    
                $riviere_description_0 = post_secure($sql_link,$_POST['riviere_description_0']);    
                $select_riviere_regionhydro_0 = post_secure($sql_link,$_POST['select_riviere_regionhydro_0']);
                
                tep_db_query($sql_link,"INSERT INTO ".TABLE_RIVIERE." (nom,description,id_regionhydro,id_territoire) 
                                            VALUES ('".$riviere_nom_0."','".$riviere_description_0."','".$select_riviere_regionhydro_0."','".$territoire_id."')");
            }

            // Aquifere

            $sql_aquifere = "SELECT DISTINCT id, nom, description FROM ".TABLE_GEO_AQUIFERE;
            $aquifere_query = tep_db_query($sql_link,$sql_aquifere);
            while ($aquifere = tep_db_fetch_array($aquifere_query)) 
            {
                if(isset($_POST['aquifere_nom_'.$aquifere['id']]))
                {
                    ${'aquifere_nom_'.$aquifere['id']} = post_secure($sql_link,$_POST['aquifere_nom_'.$aquifere['id']]);
                    ${'aquifere_description_'.$aquifere['id']} = post_secure($sql_link,$_POST['aquifere_description_'.$aquifere['id']]);

                    tep_db_query($sql_link,"UPDATE ".TABLE_GEO_AQUIFERE." SET nom='".${'aquifere_nom_'.$aquifere['id']}."',
                                                                            description='".${'aquifere_description_'.$aquifere['id']}."'
                                                                        WHERE id=".$aquifere['id']);
                }
            }

            if(tep_not_null($_POST['aquifere_nom_0']))
            {
                $aquifere_nom_0 = post_secure($sql_link,$_POST['aquifere_nom_0']);    
                $aquifere_description_0 = post_secure($sql_link,$_POST['aquifere_description_0']);    
                
                tep_db_query($sql_link,"INSERT INTO ".TABLE_GEO_AQUIFERE." (nom,description,id_territoire) 
                                            VALUES ('".$aquifere_nom_0."','".$aquifere_description_0."','".$territoire_id."')");
            }

            // Tournées

            $sql_tournee = "SELECT DISTINCT id, nom, description FROM ".TABLE_TOURNEE." WHERE id_territoire=".$territoire_id;
            $tournee_query = tep_db_query($sql_link,$sql_tournee);
            while ($tournee = tep_db_fetch_array($tournee_query)) 
            {
                if(isset($_POST['tournee_nom_'.$tournee['id']]))
                {
                    ${'tournee_nom_'.$tournee['id']} = post_secure($sql_link,$_POST['tournee_nom_'.$tournee['id']]);
                    ${'tournee_description_'.$tournee['id']} = post_secure($sql_link,$_POST['tournee_description_'.$tournee['id']]);

                    tep_db_query($sql_link,"UPDATE ".TABLE_TOURNEE." SET nom='".${'tournee_nom_'.$tournee['id']}."',
                                                                            description='".${'tournee_description_'.$tournee['id']}."'
                                                                        WHERE id=".$tournee['id']);
                }
            }

            if(tep_not_null($_POST['tournee_nom_0']))
            {
                $tournee_nom_0 = post_secure($sql_link,$_POST['tournee_nom_0']);    
                $tournee_description_0 = post_secure($sql_link,$_POST['tournee_description_0']);    
                
                tep_db_query($sql_link,"INSERT INTO ".TABLE_TOURNEE." (nom,description,id_territoire) 
                                            VALUES ('".$tournee_nom_0."','".$tournee_description_0."','".$territoire_id."')");
            }



            // Enregistrement de l'action dans la table action
            $today_us = date('Y-m-d H:i:s'); 
            $type_action = 13; // Action Paramétrages
            $info_action = "Enregistrement des données Géographiques"; 

            $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) 
                                                VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$today_us."')";
            tep_db_query($sql_link,$query);
        

            tep_db_query($sql_link, "COMMIT"); // Si toutes les opérations réussissent, confirmer la transaction

            $msg_info_send .= "Les données géographiques (Régions, Communes, Régions Hydrologiques, Rivières, Aquifères et Tournées) ont bien été enregistrées";

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