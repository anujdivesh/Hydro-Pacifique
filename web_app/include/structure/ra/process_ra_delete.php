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
$id_ra = $dataInfo['id_ra'];
$id_user_agent = $dataInfo['id_user_agent'];

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

// TABLE STATION
$sql_station_all = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station, s.station_type
					FROM ".TABLE_STATION." s";
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
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_color_background,type_graph 
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
                                                        'type_color_border' => $eq_type_tab['type_color_border'],
                                                        'type_color_background' => $eq_type_tab['type_color_background'],
                                                    );
}


// -------------------------------------------------------------


// Initialisation Variables Globales
$type_action = 1; // Action Rapport d'activité
$dateheure_action =  date("Y-m-d H:i:s");
$msg_info = '';
$del = false;


// Requête d'accès aux RA
$sql_RA = "SELECT DISTINCT ra.id_ra, ra.id_station, ra.date_heure_ra, ra.id_eq_type
            FROM ".TABLE_DATA_RA." ra
            WHERE id_ra = ".$id_ra;       

$RA_query = tep_db_query($sql_link,$sql_RA);
$RA_tab = tep_db_fetch_array($RA_query);

if(isset($RA_tab))
{   
    $id_station = $RA_tab['id_station'];
    $code_station = $station_all_array[$id_station]['code_station'];    
    $nom_station = $station_all_array[$id_station]['nom_station'];
    $info_station = $code_station.' - '.$nom_station;

    $date_heure_ra_tab =  explode(" ",$RA_tab['date_heure_ra']);
    $date_ra = dateus_fr($date_heure_ra_tab[0]);
    $heure_ra = $date_heure_ra_tab[1];
    $date_heure_ra_fr = $date_ra.' '.$heure_ra;

    tep_db_query($sql_link,"DELETE FROM ".TABLE_DATA_RA." WHERE id_ra=".$id_ra);
    tep_db_query($sql_link,"DELETE FROM ".TABLE_DATA_RA_PIEZO_PROFIL." WHERE id_ra = ".$id_ra); // Suppression du profil piézo (s'il existe)
    
    $msg_info .= "<span style='font-size:16px;'>";
        $msg_info .= "La fiche RA a bien été supprimée";
    $msg_info .= "</span>";
    $msg_info .= "<br><br>";

    $msg_info .= "Station : ".$info_station." - Date : ".$date_heure_ra_fr;

    $del = true;

    // Enregistrement de l'action dans la table ACTION qui suit toutes les actions de la plateforme
                
    $info_action = "Suppression RA <br>";
    $info_action .= "Station : ".$info_station." - Date : ".$date_heure_ra_fr;    
    $info_action = post_secure($sql_link,$info_action); 

    $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$dateheure_action."')";
    tep_db_query($sql_link,$query);
}
else
{
    $msg_info .= "<span style='font-size:16px;'>";
        $msg_info .= "Une erreur est survenue lors de la suppression du RA.";
    $msg_info .= "</span>";
}

// Remplissage du tableau de retour

$responseData = array(
    'msg_info' => $msg_info,
    'del' => $del
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>