<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de valider les correction généré
Protocole sur serveur (AJAX) 
Appelé depuis include/structure/data_chron.php
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
require('../../function/sql_function.php');

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

$timezone_php = $dataInfo['timezone_php'];
    date_default_timezone_set($timezone_php); 
    $now = new DateTime(); // Crée un objet DateTime pour la date actuelle
    $now_us_formatted = $now->format('Y-m-d H:i:s');  // Formatage de la date et stockage dans une variable ou affichage
    $now_fr_formatted = $now->format('d-m-Y H:i:s');  // Formatage de la date et stockage dans une variable ou affichage

$territoire_lang = $dataInfo['territoire_lang'];


$id_correction = $dataInfo['id_correction'];
$tabIdMeta = $dataInfo['tabIdMeta'];
$idChronEncours = $dataInfo['idTypeChron']; // id de la chronique qui sera crée
$idCodeQual = $dataInfo['idCodeQual']; // id du code qualité qui sera liée à cette chronique
$obsUser = post_secure($sql_link,$dataInfo['obsUser']); // Observation de l'utilisateur liée à la modification


// Initialisation Variables
$msg_valid = '';


// On parcours toutes les corrections validées

// Vérifier si les données existent
if (isset($tabIdMeta)) 
{
    // Encapsulation des requêtes dans une transaction pour assurer l'intégrité des données en cas d'échec d'une partie de l'opération
    // Commencer une transaction
    tep_db_query($sql_link, "START TRANSACTION");

    try {
        // Parcourir les valeurs dans $tabIdMeta
        foreach ($tabIdMeta as $idValue) 
        {
            $idValue = (int)$idValue; // Cast pour sécuriser l'input

            $sql_meta_correction = "SELECT id, id_station, id_typedata, id_user, obs, info_correction, datetime_first, datetime_end
                                    FROM ".TABLE_DATA_META_CORRECTION." 
                                    WHERE id = ".$idValue;
            $meta_correction_query = tep_db_query($sql_link,$sql_meta_correction);
            $meta_correction_tab = tep_db_fetch_array($meta_correction_query);

            $id_station = $meta_correction_tab['id_station'];
            $id_chron = $meta_correction_tab['id_typedata'];
            $id_user = $meta_correction_tab['id_user'];
            $obs = $meta_correction_tab['obs'];
            $info_correction = $meta_correction_tab['info_correction'];
            
            $datetime_first = $meta_correction_tab['datetime_first'];
            $datetime_first_formated = new DateTime($datetime_first); // Conversion en objets DateTime
            $datetime_end = $meta_correction_tab['datetime_end'];
            $datetime_end_formated = new DateTime($datetime_end); // Conversion en objets DateTime


            // Atention il fat récupérer datetime_first et datetime_end de la table DATA_CORRECTION pour avoir les bonnes dates 
            // c'est par rapport à la correction du décalage temporel

            $sql_limit_data_delete = "SELECT MIN(dateheure) AS first_date, MAX(dateheure) AS last_date
                                        FROM ".TABLE_DATA_ALL_CORRECTION."
                                        WHERE id_meta = ".$idValue;
            $limit_data_delete_query = tep_db_query($sql_link,$sql_limit_data_delete);
            $limit_data_delete_tab = tep_db_fetch_array($limit_data_delete_query);
            
            $datetime_correction_first = $limit_data_delete_tab['first_date'];
            $datetime_correction_first_formated = new DateTime($datetime_correction_first); // Conversion en objets DateTime
            $datetime_correction_end = $limit_data_delete_tab['last_date'];   
            $datetime_correction_end_formated = new DateTime($datetime_correction_end); // Conversion en objets DateTime
            
            // On réajuste les limites de dates pour bien supprimer toutes les données dans la table_data initiale
            if($datetime_correction_first_formated > $datetime_first_formated){$datetime_correction_first = $datetime_first;}
            if($datetime_correction_end_formated < $datetime_end_formated){$datetime_correction_end = $datetime_end;}





            //     -------------------------------------------------
            //     -------------------------------------------------
            //     -------------------------------------------------    !!!
            //     -------------------------------------------------
            //     -------------------------------------------------

            // LANCEMENT DE LA PROCEDURE DE CORRECTION DES DONNEES




            // Préparation des requêtes à importer

            // Préparation de la requête d'insertion en bloc DATA_META
            $source = 'Correction';

            $sql_insert_bloc_meta = "INSERT INTO ".TABLE_DATA_META." (id_station, id_typedata, id_codequal, id_user, source, obs) 
                                        VALUES (".$id_station.", ".$idChronEncours.", ".$idCodeQual.", ".$id_user.", '".$source."', '".$obs."')";
            tep_db_query($sql_link,$sql_insert_bloc_meta);    
            $meta_id_encours = mysqli_insert_id($sql_link); // Récupérer l'identifiant du dernier enregistrement inséré         
            
            // Création de la requête qui permettra de copier les données de TABLE_DATA_META_CORRECTION vers TABLE_DATA_META
            
            $sql_copyData = "INSERT INTO ".TABLE_DATA_ALL." (dateheure, valeur, id_meta)
                            SELECT dateheure, valeur, ".$meta_id_encours."
                            FROM ".TABLE_DATA_ALL_CORRECTION."
                            WHERE id_meta = ".$idValue;
            
            // !!! ------------------------------------------------- !!!
            // LANCEMENT DE LA PROCEDURE DE CORRECTION DES DONNEES

            // On efface les données entre Date_Debut et Date_Fin chronique, qu'elles existent ou pas
            $rows_deleted = deleteDataAndMeta($sql_link,$id_station, $idChronEncours, $datetime_correction_first, $datetime_correction_end);

            // On enregistre les nouvelles données
            tep_db_query($sql_link,$sql_copyData);

            // On enregistre la modification dans la table TABLE_DATA_META_CORRECTION
            $sql_updateCorrection = "UPDATE ".TABLE_DATA_META_CORRECTION." SET valid=1, datetime_correction='".$now_us_formatted."', id_chron_modif=".$idChronEncours.", obs_user='".$obsUser."' WHERE id = ".$idValue;
            tep_db_query($sql_link,$sql_updateCorrection);
        }
                
        tep_db_query($sql_link, "COMMIT"); // Si toutes les opérations réussissent, confirmer la transaction

        $msg_valid .= "La mise à jour des données a été réalisée avec succès.";
    
    } catch (Exception $e) 
    {        
        tep_db_query($sql_link, "ROLLBACK"); // En cas d'erreur, annuler la transaction
        
        $msg_valid .= "La correction des données a rencontré un problème lors de l'écriture dans les tables"; // message d'erreur de transaction
        $msg_valid .= "<br>Une erreur est survenue : " . $e->getMessage(); // Afficher le message d'erreur
    }

} else {
    $msg_valid .= "Aucune donnée reçue.";
}

// Rmeplissage du tableau de retour

$responseData = array(
    'tabIdMeta' => $tabIdMeta,
    'msg_valid' => $msg_valid  
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;


?>