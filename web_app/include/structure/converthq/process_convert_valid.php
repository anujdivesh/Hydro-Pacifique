<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet de convertir les données d'une station hydrométrique de hauteur d'eau en débit
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
require('../../function/stats.php');	
require('../../function/sql_function.php');

// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');

// DATA TABLE STATION
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

// DATA TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, 
                                    axe_data, unite, to_periode, id_chon_periode
				  FROM ".TABLE_TYPE_DATA." 
				  ORDER BY init_type_data ASC";
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
while ($type_chron_tab = tep_db_fetch_array($type_chron_query))
{
	$type_chron_array[$type_chron_tab['id_data_type']] = array('init_type_data' => $type_chron_tab['init_type_data'],
															'nom_type_data' => $type_chron_tab['nom_type_data']
															);
}

// Récupération des données JSON envoyées depuis la requête AJAX
$jsonDataGraph = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataJson = json_decode($jsonDataGraph, true);

// Accéder aux données du tableau récupérer
$timezone_php = $dataJson['timezone_php'];

$typedata_chron_q = $dataJson['typedataChronQ'];
$datetime_correction_first = $dataJson['xDateMin'];
$datetime_correction_end = $dataJson['xDateMax'];
$station_chron = $dataJson['idStation'];
$id_user = $dataJson['id_user']; 
$id_meta_correction = $dataJson['id_meta_correction']; 
$idCodeQual = 0;

// Initialisation des variables pour l'affichage du graph
date_default_timezone_set($timezone_php); 
$today = new DateTime(); // Crée un objet DateTime pour la date du jour
$today_formated = $today->format('Y-m-d H:i:s');  // Formatage de la date

// !!! ------------------------------------------------- !!!
// LANCEMENT DE LA PROCEDURE DE VALIDATION DE LA CONVERSION DES DONNEES


$source_info = "Conversion";
$info_correction = "Conversion Côte(C) -> Débit(Q)";
$obs = '';

/*
tep_db_query($sql_link, "START TRANSACTION");

try {
*/
    $sql_insert_bloc_meta = "INSERT INTO ".TABLE_DATA_META." (id_station, id_typedata, id_codequal, id_user, source, obs) 
                            VALUES (".$station_chron.", ".$typedata_chron_q.", ".$idCodeQual.", ".$id_user.", '".$source_info."', '".$obs."')";
    tep_db_query($sql_link,$sql_insert_bloc_meta);    
    $meta_id_encours = mysqli_insert_id($sql_link); // Récupérer l'identifiant du dernier enregistrement inséré    

    // Création de la requête qui permettra de copier les données de TABLE_DATA_ALL_CORRECTION vers TABLE_DATA_ALL
    $sql_copyData = "INSERT INTO ".TABLE_DATA_ALL." (dateheure, valeur, id_meta)
                        SELECT dateheure, valeur, ".$meta_id_encours."
                        FROM ".TABLE_DATA_ALL_CORRECTION."
                        WHERE id_meta = ".$id_meta_correction;

    // !!! ------------------------------------------------- !!!
    // LANCEMENT DE LA PROCEDURE DE CORRECTION DES DONNEES

    // On efface les données entre Date_Debut et Date_Fin de la chronique qui est corrigé.
    // On ne veut pas modifier le reste de la chronique si des données existent
    $sql_info_meta = "SELECT DISTINCT id, datetime_first, datetime_end
                    FROM " . TABLE_DATA_META_CORRECTION . "
                    WHERE id = " . $id_meta_correction . "
                    AND source = 'Conversion'
                    ORDER BY id DESC
                    LIMIT 1";
    $info_meta_query = tep_db_query($sql_link,$sql_info_meta); 
    $info_meta = tep_db_fetch_array($info_meta_query);

    $rows_deleted = deleteDataAndMeta($sql_link,$station_chron, $typedata_chron_q, $info_meta['datetime_first'], $info_meta['datetime_end']);

    
    // On enregistre les nouvelles données
    tep_db_query($sql_link,$sql_copyData);

    // On supprime toutes les données de conversion qui ont été générée dans les tables CORRECTION
    
    // On récupère les IDs à supprimer
    $sql_meta_del = "SELECT DISTINCT id
                    FROM " . TABLE_DATA_META_CORRECTION . "
                    WHERE id_station = " . (int)$station_chron . "
                    AND source = 'Conversion'";
    $meta_del_query = tep_db_query($sql_link,$sql_meta_del); 
        
    // On prépare une liste des IDs à supprimer
    $ids_to_delete = [];
    while ($meta_del = tep_db_fetch_array($meta_del_query)) 
    {
        $ids_to_delete[] = (int)$meta_del['id'];
    }
    
    if (!empty($ids_to_delete)) 
    {
        // Conversion des IDs en une chaîne pour la requête SQL
        $ids_list = implode(',', $ids_to_delete);
    
        // Suppression des données associées dans les deux tables
        tep_db_query($sql_link, "DELETE FROM " . TABLE_DATA_ALL_CORRECTION . " WHERE id_meta IN ($ids_list)");
        tep_db_query($sql_link, "DELETE FROM " . TABLE_DATA_META_CORRECTION . " WHERE id IN ($ids_list)");

        // On enregistre la modification dans la table TABLE_DATA_META_CORRECTION
        //$sql_updateCorrection = "UPDATE ".TABLE_DATA_META_CORRECTION." SET valid=1, id_chron_modif=".$typedata_chron_q." WHERE id IN ($ids_list)";
        //tep_db_query($sql_link,$sql_updateCorrection);
    }

    // Enregistrement de l'action dans la table ACTION qui suit toutes les actions de la plateforme
    $type_action = 39; // Action Conversion C -> Q
    $info_action = "Conversion du chronique 'Côte' en Débit<br>";
    $info_action .= "Station : ".$station_all_array[$station_chron]['nom_station']."<br>";
    $info_action .= "Chron. : ".$type_chron_array[$typedata_chron_q]['init_type_data']." - ".$type_chron_array[$typedata_chron_q]['nom_type_data'];
    $info_action = post_secure($sql_link,$info_action); 

    // On enregistre la modification dans la table TABLE_DATA_META_CORRECTION
    /*
    $sql_updateCorrection = "UPDATE ".TABLE_DATA_META_CORRECTION." SET valid=1, datetime_correction='".$now_us_formatted."', id_chron_modif=".$idChronEncours.", obs_user='".$obsUser."' WHERE id = ".$idValue;
    tep_db_query($sql_link,$sql_updateCorrection);
    */


    $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) VALUES (".$id_user.",'".$type_action."','".$info_action."','".$today_formated."')";
    tep_db_query($sql_link,$query);

    //tep_db_query($sql_link, "COMMIT");

    $result = "La nouvelle chronique de données a bien été enregistrée.";
/*
} catch (Exception $e) 
{
    tep_db_query($sql_link, "ROLLBACK");
    $result = "Une erreur s'est produite pendant l'enregistrement des données.";
}
*/


$responseData = array(
    'js_text' =>  $result
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>