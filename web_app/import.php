<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Formulaire d'import des données surtout les chroniques
----------------------------------------
*/

require('include/application_top.php');


// -------------------------------------
// Chargement de la librairy pour lecture fichier excel
// Librairy PhpSpreadsheet
//require 'php-excel/vendor/autoload.php';


// Initialisation Variables

$message_info = '';
$data_step = 1; // permet de savoir à quelle étape on se trouve
$verif_form = 0; // permet de valider si l'ensemble des champs sont bien saisies
$row = 0;
$entete = 0;

$select_station_tab = array();

// Dates par défault
$today = date('d-m-Y');
$date_1 = $today; 
$date_2 = $today; 
$year_today = date('Y'); 
$month_today = date('m'); 

$date_format = 'd-m-Y';

$year_1 = $year_today;
$month_1 =	'01';
$day_1 = '01';
$year_2 = $year_today;
$month_2 =	'12';
$day_2 = cal_days_in_month(CAL_GREGORIAN, $month_2, $year_2); // donne le bon nombre de jour du mois

// Entete de colonne 
if(isset($_POST['entete'])){$entete = 1;}

// --------------------------------------
// Tables SQL

// TABLE REGION
$sql_region = "SELECT DISTINCT id_region, nom_region 
                FROM ".TABLE_REGION." 
                WHERE id_territoire=".$territoire_id;
$region_query = tep_db_query($sql_link,$sql_region);
while ($region = tep_db_fetch_array($region_query))
{
	$region_array[$region['id_region']] = htmlaccent(html_entity_decode($region['nom_region'] ?? $default_string));
}

// LIST STATION
$sql_station_all = "SELECT DISTINCT id_station, nom_station, code_station, station_type, active_station
					FROM ".TABLE_STATION;
$station_all_query = tep_db_query($sql_link,$sql_station_all);
while ($station_all = tep_db_fetch_array($station_all_query))
{	
	$station_all_array[$station_all['code_station']] = array('id_station' => $station_all['id_station'],
															'nom_station' => htmlaccent(html_entity_decode($station_all['nom_station'] ?? $default_string)),
															'station_type' => $station_all['station_type'],
															);
}

// TABLE IMPORT FILES (Caractéristiques des fichiers importables)
$sql_import_files = "SELECT DISTINCT id, name_ext, multi_feuil, separateur, description, algo, valid
                    FROM ".TABLE_IMPORT_FILES." 
					WHERE valid=1
                    ORDER BY name_ext ASC";
$import_files_query = tep_db_query($sql_link,$sql_import_files);									
while ($import_files_tab = tep_db_fetch_array($import_files_query))
{
    $name_ext = htmlaccent(html_entity_decode($import_files_tab['name_ext'] ?? $default_string));

	$import_files[$name_ext] = array('id' => $import_files_tab['id'],
                                    'multi_feuil' => $import_files_tab['multi_feuil'],
                                    'separateur' => $import_files_tab['separateur'],                                                    
                                    'description' => htmlaccent(html_entity_decode($import_files_tab['description'] ?? $default_string)),
                                    'algo' => $import_files_tab['algo'] // ce champs peu contenir l'algo de lecture du type de fichier !!! Attention potentiellement dangereux pour la sécurité
                                    );
}



// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
while ($eq_type_tab = tep_db_fetch_array($eq_type_query))
{
	$eq_type_array[$eq_type_tab['id_eq_type']] = array('nom_eq_type' => htmlaccent(html_entity_decode($eq_type_tab['nom_eq_type'] ?? $default_string)),
                                                        'unite_eq_type' => $eq_type_tab['unite_eq_type'],
                                                        'valeur_data_type' => $eq_type_tab['valeur_data_type'],
														'type_graph' => $eq_type_tab['type_graph']
                                                    );
}

// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, unite
				  FROM ".TABLE_TYPE_DATA." 
				  ORDER BY init_type_data ASC";
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
while ($type_chron_tab = tep_db_fetch_array($type_chron_query))
{
	$type_chron_array[$type_chron_tab['init_type_data']] = array('id_data_type' => $type_chron_tab['id_data_type'],
															'nom_type_data' => htmlaccent(html_entity_decode($type_chron_tab['nom_type_data'] ?? $default_string)),
															'unite' => $type_chron_tab['unite'],															
															'id_eq_type_data' => $type_chron_tab['id_eq_type_data']
															);
}

// TABLE QUALITY_DATA 
$sql_quality_data = "SELECT DISTINCT id_data_qualite, init_qualite_data, nom_qualite_data, info_qualite_data
				  	FROM ".TABLE_DATA_QUALITE;
$quality_data_query = tep_db_query($sql_link,$sql_quality_data);									
while ($quality_data_tab = tep_db_fetch_array($quality_data_query))
{
	$quality_data_array[$quality_data_tab['init_qualite_data']] = array('id_data_qualite' => $quality_data_tab['id_data_qualite'],
																		'nom_qualite_data' => htmlaccent(html_entity_decode($quality_data_tab['nom_qualite_data'] ?? $default_string)),												
																		'info_qualite_data' => htmlaccent(html_entity_decode($quality_data_tab['info_qualite_data'] ?? $default_string))
																		);
}




// --------------------------------------
// CONTROLES FORM


require(DIR_WS_IMPORT . 'form_import_step1.php');



?>