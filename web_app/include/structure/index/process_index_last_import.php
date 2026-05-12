<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet de générer les données de la carte dans la page des index.php (la page d'accueil)
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
$jsonDataMap = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataJson = json_decode($jsonDataMap, true);

// Accéder aux données du tableau récupérer
$territoire_id = $dataJson['territoireId'];


// ---------------------------------------------
// Requête SQL - Récupération des données DB

$default_string = '';

// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_color_background, type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
while ($eq_type_tab = tep_db_fetch_array($eq_type_query))
{
	$eq_type_array[$eq_type_tab['id_eq_type']] = array('id_eq_type' => $eq_type_tab['id_eq_type'],
														'nom_eq_type' => htmlaccent(html_entity_decode($eq_type_tab['nom_eq_type'] ?? $default_string)),
                                                        'unite_eq_type' => $eq_type_tab['unite_eq_type'],
                                                        'valeur_data_type' => $eq_type_tab['valeur_data_type'],
														'type_color_border' => $eq_type_tab['type_color_border'],
                                                        'type_color_background' => $eq_type_tab['type_color_background'],
														'type_graph' => $eq_type_tab['type_graph']
                                                    );
}

// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, to_periode, id_chon_periode
				  FROM ".TABLE_TYPE_DATA." 
				  ORDER BY init_type_data ASC";
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
while ($type_chron_tab = tep_db_fetch_array($type_chron_query))
{
	$type_chron_array[$type_chron_tab['id_data_type']] = array('init_type_data' => html_entity_decode($type_chron_tab['init_type_data'] ?? $default_string),
															'nom_type_data' => html_entity_decode($type_chron_tab['nom_type_data'] ?? $default_string),
															'id_eq_type_data' => html_entity_decode($type_chron_tab['id_eq_type_data'] ?? $default_string),
															'axe_nom' => html_entity_decode($data_type_axe_array[$type_chron_tab['axe_data']]['axe'] ?? $default_string),
															'unite' => html_entity_decode($type_chron_tab['unite'] ?? $default_string),
															'to_periode' => html_entity_decode($type_chron_tab['to_periode'] ?? $default_string),
															'id_chon_periode' => html_entity_decode($type_chron_tab['id_chon_periode'] ?? $default_string)
															);
}

// TABLE STATION avec les conditions des différents champs de sélection 
$sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station, s.station_type
				FROM ".TABLE_STATION." s
				WHERE s.id_territoire=".$territoire_id."
				ORDER BY code_station DESC";
$station_query = tep_db_query($sql_link,$sql_station);
while ($station = tep_db_fetch_array($station_query))
{	
	$station_array[$station['id_station']] = array('nom_station' => htmlaccent(html_entity_decode($station['nom_station'] ?? $default_string)),
													'code_station' => htmlaccent(html_entity_decode($station['code_station'] ?? $default_string)),
													'station_type' => $station['station_type']
												);
}

// TABLE SQL - Recupération des données utilisateurs
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


// TABLE IMPORT - Recupération de la liste des Imports à afficher
$nb_imports = 0;
$sql_import = "SELECT DISTINCT id, id_import, file_import, dateheure, id_station, id_chron, 
                                id_user, nb_data, datetime_first, datetime_end
				FROM ".TABLE_IMPORT_SUIVI."
				WHERE import=1               
				ORDER BY dateheure DESC                
                LIMIT 40";
$import_query = tep_db_query($sql_link,$sql_import);
while ($import_tab = tep_db_fetch_array($import_query))
{	
	$id =  $import_tab['id'];
	$id_import =  $import_tab['id_import'];
	
	$file_import =  $import_tab['file_import'];

	$dateheure = new DateTime($import_tab['dateheure']); // Crée un objet DateTime 
	$dateheure_formatted = $dateheure->format('d-m-Y'); // Formatte la date au format "d-m-Y"

	$date_first = new DateTime($import_tab['datetime_first']); // Crée un objet DateTime 
	$date_first_formatted = $date_first->format('d-m-Y'); // Formatte la date au format "d-m-Y"

	$date_end = new DateTime($import_tab['datetime_end']); // Crée un objet DateTime 
	$date_end_formatted = $date_end->format('d-m-Y'); // Formatte la date au format "d-m-Y"
		
	$id_station =  $import_tab['id_station'];  
	$code_station = $station_array[$id_station]['code_station'];    
	$nom_station = $station_array[$id_station]['nom_station'];
	$station_type = $station_array[$id_station]['station_type'];

	$id_chron =  $import_tab['id_chron'];  
	$init_chron = '';
	if(isset($type_chron_array[$id_chron]['init_type_data'])){$init_chron = $type_chron_array[$id_chron]['init_type_data'];}
	$nom_chron = '';
	if(isset($type_chron_array[$id_chron]['init_type_data'])){$nom_chron = $type_chron_array[$id_chron]['nom_type_data'];}   	

    $graph_chron_link = $id_station."_".$station_type."_".$id_chron;

	$id_user_hp =  $import_tab['id_user'];   
	$login_user_hp = $user_list_array[$id_user_hp]['login']; 
	$nom_user_hp = $user_list_array[$id_user_hp]['nom'];     
	$prenom_user_hp = $user_list_array[$id_user_hp]['prenom']; 

	$nb_data =  $import_tab['nb_data'];  

	$file_exist_txt = false;
	$file_info_link = DIR_WS_DATA_IMPORT.$import_tab['id_import'].'_'.$init_chron.'.txt';
	if(file_exists($file_info_link)){$file_exist_txt = true;}
	
	// Tableau station avec toutes les données	
	$import_array[$id] = array('id_import' => $id_import,
								'file_import' => $file_import,
								'dateheure_formatted' => $dateheure_formatted,
								'date_first_formatted' => $date_first_formatted,
								'date_end_formatted' => $date_end_formatted,
								'code_station' => $code_station,
								'nom_station' => $nom_station,
                                'graph_chron_link' => $graph_chron_link,
								'init_chron' => $init_chron,
								'nom_chron' => $nom_chron,
								'login_user_hp' => $login_user_hp,
								'nom_user_hp' => $nom_user_hp,
								'prenom_user_hp' => $prenom_user_hp,
								'nb_data' => $nb_data,
								'file_exist_txt' => $file_exist_txt,
								'file_info_link' => $file_info_link                            
								);
}
if(isset($import_array)){$nb_imports = sizeof($import_array);}

//Génération du code HTML
$html = '';

if(isset($import_array)) // Si il existe des RA
{	
	$html .= 
	"
	<div class='table-container' style='height:45vh;' >							  

		<table id='table_tri' cellspacing='0' >

			<thead>
				<tr class='header-row'>
					<th style='width:150px;padding-left:20px;font-size:12px;'>".htmlaccent('Date')."</th>	
					<th style='width:200px;font-size:12px;'>".htmlaccent('Utilisateur')."</th>		
					<th style='width:250px;font-size:12px;'>".htmlaccent('Station')."</th>
					<th style='width:200px;font-size:12px;'>".htmlaccent('Chronique')."</th>	                    
					<th style='width:80px;font-size:12px;'>".htmlaccent('Consulter')."</th>								
				</tr>
			</thead>
	";	

			// Affichage des derniers RA (30 lignes)
			$row=1;
			foreach($import_array as $key => $value)
			{
				// Coloration d'une ligne au survol de la souris
				$row++;
				if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
				else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

				
				$html .= "<tr ".$row_l.">";
										
					// Data Heure
					$html .= 
					"
						<td style='center'>
                            ".$value['dateheure_formatted']."
                        </td>
					";

					// Utilisateur
					$html .= 
					"
						<td>
                            ".$value['prenom_user_hp']." ".$value['nom_user_hp']."
                        </td>
					";

					// Code et nom de la station liée
					$html .= 
					"													
						<td style='cursor:pointer;' id='link_popup' >
							".$value['code_station'].' - '.$value['nom_station']."
						</td>
					";

					// Chronique	
					$html .= 
					"													
						<td>
                            ".$value['init_chron']." ".$value['nom_chron']."
                        </td>
					";

					// Nom des agents ayant participé
					$html .= "<td style='text-align: center;'>";

						if(tep_not_null($value['init_chron']))
						{
							$tabForm = [
                                            ['name' => 'graph_chron', 'value' => $value['graph_chron_link']],
                                            ['name' => 'date1_encours', 'value' => $value['date_first_formatted']],
                                            ['name' => 'date2_encours', 'value' => $value['date_end_formatted']]
                                        ];
                            $tabFormJson = json_encode($tabForm);
                            $tabFormJson = htmlspecialchars($tabFormJson, ENT_QUOTES, 'UTF-8');

							$html .= "<img src='".DIR_WS_IMG_ICO."graph.png' style='width:15px;cursor:pointer;' 
                                                title='"."Consulter les données importées"."' 
                                            onclick=\"event.preventDefault();linkSubmitForm('data_chron.php', ".$tabFormJson.");\">";
						}
						else{$html .= "-"; }  
                    
                    $html .= "</td>";

				$html .= "</tr>";
			}
		

		$html .= "</table>";

	$html .= "</div>";
}


$responseData = array(
    'js_html' => $html
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>