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
$id_typedata = $dataJson['idTypeData'];

$where_typedata = '';
if($id_typedata > 0)
{$where_typedata = 'WHERE id_eq_type_data='.$id_typedata;}

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

$sql_chronique = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, 
								to_periode, id_chon_periode, traitement, type_graph
				FROM ".TABLE_TYPE_DATA." td
                ".$where_typedata."
				ORDER BY id_eq_type_data ASC, LOWER(init_type_data) ASC";
$chronique_query = tep_db_query($sql_link,$sql_chronique);
while($chronique_data = tep_db_fetch_array($chronique_query)) 
{
	$id_eq_type = $chronique_data['id_eq_type_data'];

	if($id_eq_type>0)
	{
		$id_chron = $chronique_data['id_data_type'];

		$init = $chronique_data['init_type_data'];
		$nom_chron = $chronique_data['nom_type_data'];
		
		
		$nom_data = $eq_type_array[$id_eq_type]['nom_eq_type'];
		$color_data = $eq_type_array[$id_eq_type]['type_color_border'];

		$unite = $chronique_data['unite'];

		$chron_array[$id_chron] = array('init' => $init,
										'nom_chron' => $nom_chron,
										'nom_data' => $nom_data,
										'color_data' => $color_data,
										'unite' => $unite
										);
	}										
}

//Génération du code HTML
$html = '';

if(isset($chron_array)) // Si il existe des Chroniques
{	
	$html .= 
	"
	<div class='table-container' style='background-color:#fff;' >							  

		<table id='table_tri' cellspacing='0' >

			<thead>
				<tr class='header-row'>
					<th style='width:80px;padding-left:10px;font-size:13px;'>".htmlaccent('Acronyme')."</th>			
					<th style='width:350px;font-size:13px;'>".htmlaccent('Intitulé')."</th>	
					<th style='width:80px;font-size:13px;text-align:center;'>".htmlaccent('Unité')."</th>
					<th style='width:150px;font-size:13px;text-align:center;'>".htmlaccent('Type de données')."</th>								
				</tr>
			</thead>
	";	

			// Affichage des derniers RA (30 lignes)
			$row=1;
			foreach($chron_array as $key => $value)
			{
				// Coloration d'une ligne au survol de la souris
				$row++;
				if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
				else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

				$color_type = 'color:'.$value['color_data'].';';

				$html .= "<tr ".$row_l."  style='font-size:12px;'>";
										
					// Acronyme
					$html .= 
					"
						<td style='padding-left:20px;'>
                            ".$value['init']."
						</td>
					";

                    // Intitumé
					$html .= 
					"
						<td>
                            ".$value['nom_chron']."
						</td>
					";

					// Unité
					$html .= 
					"
						<td style='text-align:center;'>
                            ".$value['unite']."
						</td>
					";

					// Type de données (Debit, Pluie, Piezo)	
					$html .= 
					"													
						<td style='text-align:center;'>						
							<span style='".$color_type."'>".$value['nom_data']."</span>
						</td>
					";

				$html .= "</tr>";
			}

			// Compléments Stations Hydro
			if($id_typedata == 0 || $id_typedata == 11)
			{
				// Coloration d'une ligne au survol de la souris
				$row++;
				if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
				else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

				$html .= "<tr ".$row_l.">";
					$html .= "<td colspan=4>&nbsp;</td>";
				$html .= "</tr>";

				// Coloration d'une ligne au survol de la souris
				$row++;
				if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
				else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

				$nom_data = $eq_type_array[11]['nom_eq_type'];
    			$color_data = $eq_type_array[11]['type_color_border'];
				$color_type = 'color:'.$color_data.';';

				$html .= "<tr ".$row_l."  style='font-size:12px;'>";
										
					// Acronyme
					$html .= 
					"
						<td style='padding-left:20px;'>
							JGE
						</td>
					";

					// Intitumé
					$html .= 
					"
						<td>
							Jaugeages Ponctuels
						</td>
					";

					// Unité
					$html .= 
					"
						<td style='text-align:center;'>
							m3/s
						</td>
					";

					// Type de données	
					$html .= 
					"													
						<td style='text-align:center;'>						
							<span style='".$color_type."'>".$nom_data."</span>
						</td>
					";

				$html .= "</tr>";


				// Coloration d'une ligne au survol de la souris
				$row++;
				if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
				else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

				$nom_data = $eq_type_array[11]['nom_eq_type'];
    			$color_data = $eq_type_array[11]['type_color_border'];
				$color_type = 'color:'.$color_data.';';

				$html .= "<tr ".$row_l."  style='font-size:12px;'>";
										
					// Acronyme
					$html .= 
					"
						<td style='padding-left:20px;'>
							ETL
						</td>
					";

					// Intitumé
					$html .= 
					"
						<td>
							Relation  d'Etalonnage
						</td>
					";

					// Unité
					$html .= 
					"
						<td style='text-align:center;'>
							-
						</td>
					";

					// Type de données	
					$html .= 
					"													
						<td style='text-align:center;'>						
							<span style='".$color_type."'>".$value['nom_data']."</span>
						</td>
					";

				$html .= "</tr>";
			}


			// Compléments Stations Piezo
			if($id_typedata == 0 || $id_typedata == 5)
			{
				// Coloration d'une ligne au survol de la souris
				$row++;
				if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
				else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

				$html .= "<tr ".$row_l.">";
					$html .= "<td colspan=4>&nbsp;</td>";
				$html .= "</tr>";

				// Coloration d'une ligne au survol de la souris
				$row++;
				if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
				else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

				$nom_data = $eq_type_array[5]['nom_eq_type'];
    			$color_data = $eq_type_array[5]['type_color_border'];
				$color_type = 'color:'.$color_data.';';

				$html .= "<tr ".$row_l."  style='font-size:12px;'>";
										
					// Acronyme
					$html .= 
					"
						<td style='padding-left:20px;'>
							DIAG
						</td>
					";

					// Intitumé
					$html .= 
					"
						<td>
							Profil de Conductivité Electrique (Diagraphie)
						</td>
					";

					// Unité
					$html .= 
					"
						<td style='text-align:center;'>
							-
						</td>
					";

					// Type de données	
					$html .= 
					"													
						<td style='text-align:center;'>						
							<span style='".$color_type."'>".$nom_data."</span>
						</td>
					";

				$html .= "</tr>";

			}


			// Coloration d'une ligne au survol de la souris
			$row++;
			if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
			else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

			$html .= "<tr ".$row_l.">";
				$html .= "<td colspan=4>&nbsp;</td>";
			$html .= "</tr>";

			// Coloration d'une ligne au survol de la souris
			$row++;
			if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
			else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

			$html .= "<tr ".$row_l."  style='font-size:12px;'>";
									
				// Acronyme
				$html .= 
				"
					<td style='padding-left:20px;'>
						RA
					</td>
				";

				// Intitumé
				$html .= 
				"
					<td>
						Rapport d'Activité
					</td>
				";

				// Unité
				$html .= 
				"
					<td style='text-align:center;'>
						-
					</td>
				";

				// Type de données	
				$html .= 
				"													
					<td style='text-align:center;'>						
						-
					</td>
				";

			$html .= "</tr>";
		

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