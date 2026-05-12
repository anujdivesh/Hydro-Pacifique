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

// TABLE RA - 15 derniers enregistrements 
$sql_RA = "SELECT DISTINCT ra.id_ra, s.id_station, s.code_station, s.nom_station, s.id_commune, 
							ra.date_heure_ra, ra.id_eq_type, ra.etat_ra, ra.agents_complement
			FROM ".TABLE_DATA_RA." ra
			JOIN ".TABLE_STATION." s ON ra.id_station=s.id_station
			AND s.id_territoire=".$territoire_id."
			ORDER BY ra.date_heure_ra DESC
			LIMIT 30";

$RA_query = tep_db_query($sql_link,$sql_RA);
while($RA_tab = tep_db_fetch_array($RA_query))
{
	$id_ra =  $RA_tab['id_ra'];

	$id_station =  $RA_tab['id_station'];
	$code_station =  nettoyer_et_echapper($RA_tab['code_station']);
	$nom_station =  nettoyer_et_echapper($RA_tab['nom_station']);
	$id_commune =  $RA_tab['id_commune'];
	
	// Date RA
	$tab_date_heure_ra =  explode(" ",$RA_tab['date_heure_ra']);
	$date_ra =  dateus_fr($tab_date_heure_ra[0]);
	$heure_ra =  $tab_date_heure_ra[1];
	$date_heure_ra =  $date_ra.' '.$heure_ra;
	
	// Equipement
	$id_eq_type_ra = $RA_tab['id_eq_type']; // Débit, pluie, piezo

	// Etat du ra
	$etat_ra = $RA_tab['etat_ra']; 

	// Agents
	$list_agents = nettoyer_et_echapper($RA_tab['agents_complement']);

	$ra_array[$id_ra] = array('etat_ra' => $etat_ra,
							'date_ra' => $date_ra,
							'date_heure_ra' => $date_heure_ra,
							'id_eq_type' => $id_eq_type_ra,
							'id_station' => $id_station,
							'code_station' => $code_station,
							'nom_station' => $nom_station,
							'id_commune' => $id_commune,
							'list_agents' => $list_agents
							);
}

//Génération du code HTML
$html = '';

if(isset($ra_array)) // Si il existe des RA
{	
	$html .= 
	"
	<div class='table-container' style='height:45vh;' >							  

		<table id='table_tri' cellspacing='0' >

			<thead>
				<tr class='header-row'>
					<th style='text-align:center;width:25px;font-size:12px;'>		
					<th style='width:150px;padding-left:20px;font-size:12px;'>".htmlaccent('Date')."</th>			
					<th style='width:120px;font-size:12px;'>".htmlaccent('Type')."</th>
					<th style='width:300px;font-size:12px;'>".htmlaccent('Station')."</th>
					<th style='width:300px;font-size:12px;'>".htmlaccent('Agents')."</th>									
				</tr>
			</thead>
	";	

			// Affichage des derniers RA (30 lignes)
			$row=1;
			foreach($ra_array as $key => $value)
			{
				// Coloration d'une ligne au survol de la souris
				$row++;
				if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
				else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

				$color_type = 'color:'.$eq_type_array[$value['id_eq_type']]['type_color_border'].';';

				$html .= "<tr ".$row_l.">";
										
					$html .= "<td style=text-align:center;cursor:pointer;' >";
						
							if($value['etat_ra'] == 1)
							{													
								$html .= "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title='".htmlaccent('Validé')."'>";							
							}
							else{
								$html .= "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' title='".htmlaccent('A valider')."'>";
							}	

					$html .= "</td>";

					// Data Heure
					$html .= 
					"
						<td style='padding-left:20px;cursor:pointer;' >
						
							<a href='list_ra.php?ra=".$key."' target='_blank' style='font-size:11px;'>
								".$value['date_ra']."
							</a>
						</td>
					";

					// Type de données (Debit, Pluie, Piezo)	
					$html .= 
					"													
						<td style='cursor:pointer;'>						
							<a href='list_ra.php?ra=".$key."' target='_blank' style='font-size:11px;'> 
								<span style='".$color_type."'>".$eq_type_array[$value['id_eq_type']]['nom_eq_type']."</span>
							</a>
						</td>
					";

					// Code et nom de la station liée
					$html .= 
					"													
						<td style='cursor:pointer;' id='link_popup' title='".$value['code_station'].' - '.$value['nom_station']."'>
							<a href='list_ra.php?ra=".$key."' target='_blank' style='font-size:11px;'>
								".affichelettres($value['nom_station'],40)."
							</a>
						</td>
					";

					// Nom des agents ayant participé
					$html .= 
					"													
						<td style='cursor:pointer;' id='link_popup' title='".$value['list_agents']."'>
							<a href='list_ra.php?ra=".$key."' target='_blank' style='font-size:11px;'>
								".$value['list_agents']."
							</a>
						</td>
					";

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