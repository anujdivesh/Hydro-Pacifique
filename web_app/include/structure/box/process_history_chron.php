<?php
/*  
----------------------------------------
Copyright (c) 2025 - Vai-Natura
----------------------------------------
Export 
- Ce script permet d'afficher l'histoire des modifications et actions sur des chroniques
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
$id_station = $dataJson['idStation'];



// ---------------------------------------------
// Requête SQL - Récupération des données DB


$sql_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, 
								to_periode, id_chon_periode, traitement, type_graph
				FROM ".TABLE_TYPE_DATA."
				ORDER BY id_data_type";

$chron_query = tep_db_query($sql_link,$sql_chron);
while($chron_data = tep_db_fetch_array($chron_query)) 
{
    $id_chron = $chron_data['id_data_type'];

    $init = $chron_data['init_type_data'];
    $nom_chron = $chron_data['nom_type_data'];

    $unite = $chron_data['unite'];

    $chron_array[$id_chron] = array('init' => $init,
                                    'nom_chron' => $nom_chron,
                                    'unite' => $unite
                                    );
}

$sql_users = "SELECT id, login, nom, prenom, info 
                FROM ".TABLE_USER;

$users_query = tep_db_query($sql_link,$sql_users);
while($users_data = tep_db_fetch_array($users_query)) 
{
    $id_user = $users_data['id'];
    $nom = $users_data['nom'];
    $prenom = $users_data['prenom'];
    $info = $users_data['info'];

    $users_array[$id_user] = array('prenom_nom' => $prenom.' '.$nom,
                                    'info' => $info
                                    );
}




// Recherche de l'historique des modifications des chroniques

$where_typechron = '';
//if($id_typechron > 0){$where_typechron = "AND id_chron_modif=".$id_typechron;}

$num_op = 0;

// CHRONIQUE MODIFIER PAR IMPORT
$sql_import = "SELECT id, file_import, dateheure, 
                    id_station, id_chron, id_user,
                    datetime_first, datetime_end
                FROM ".TABLE_IMPORT_SUIVI." 
                WHERE import=1 AND id_station=".$id_station." ".$where_typechron."
                ORDER BY dateheure DESC, id DESC";

$import_query = tep_db_query($sql_link,$sql_import);		
while($import_data = tep_db_fetch_array($import_query))
{
    $num_op++;
    
    $id_chron_modif = $import_data['id_chron'];

    $chron_init = '';
    $chron_nom = '';
    if(isset($chron_array[$id_chron_modif]))
    {
        $chron_init = $chron_array[$id_chron_modif]['init'];
        $chron_nom = $chron_array[$id_chron_modif]['nom_chron'];
    }

    
    if(empty($chron_init))
    {
        $file_import_lower = strtolower($import_data['file_import']);

        if(strpos($file_import_lower, 'jge') !== false)
        {
            $chron_init = 'JGE';
            $chron_nom = 'Jaugeage';            
        }
        elseif (strpos($file_import_lower, 'ra') !== false)
        {
            $chron_init = 'RA';
            $chron_nom = "Rapport d'Activité";            
        }
        elseif (strpos($file_import_lower, 'etl') !== false)
        {
            $chron_init = 'ETL';
            $chron_nom = "Etalonnage (Hauteut -> Débit)";            
        }
        elseif (strpos($file_import_lower, 'rep') !== false)
        {
            $chron_init = 'REP';
            $chron_nom = "Répères puits (Piézométrie)";            
        }

    }

    
    $user_id = $import_data['id_user'];
    $user_prenom_nom = $users_array[$user_id]['prenom_nom'];

    $chron_obs_user = '';

    $info_correction = "Import";

    $datetime_string = $import_data['dateheure'];
    $datetime_correction = '';
    if(!empty($datetime_string) && ($datetime_string<>'0000-00-00 00:00:00'))
    {
        $datetime = new DateTime($datetime_string);
        $datetime_correction = $datetime->format('d-m-Y H:i:s');
    }

    $datetime_string = $import_data['datetime_first'];
    $datetime_first = '';
    if(!empty($datetime_string) && ($datetime_string<>'0000-00-00 00:00:00'))
    {
        $datetime = new DateTime($datetime_string);
        $datetime_first = $datetime->format('d-m-Y H:i:s');
    }

    $datetime_string = $import_data['datetime_end'];
    $datetime_end = '';
    if(!empty($datetime_string) && ($datetime_string<>'0000-00-00 00:00:00'))
    {
        $datetime = new DateTime($datetime_string);
        $datetime_end = $datetime->format('d-m-Y H:i:s');
    }

    $modif_chron_array[$num_op] = array('id_chron_modif' => $id_chron_modif,
                                        'chron' => $chron_init.' - '.$chron_nom,
                                        'datetime_correction' => $datetime_correction,
                                        'info_correction' => $info_correction,
                                        'user' => $user_prenom_nom,
                                        'chron_obs_user' => $chron_obs_user,
                                        'datetime_first' => $datetime_first,
                                        'datetime_end' => $datetime_end
                                        );

}


// CHRONIQUE MODIFIER PAR CALCUL

$sql_modif_chron = "SELECT id, id_station, id_typedata, 
                            id_user, obs_user,
                            datetime_correction,
                            info_correction, 
                            datetime_first,datetime_end,
                            id_chron_modif
                    FROM ".TABLE_DATA_META_CORRECTION."	
                    WHERE valid=1 AND id_station=".$id_station." ".$where_typechron."
                    ORDER BY datetime_correction DESC, id DESC";
                
$modif_chron_query = tep_db_query($sql_link,$sql_modif_chron);
while($modif_chron_data = tep_db_fetch_array($modif_chron_query))
{
    $num_op++;

    $id_correction = $modif_chron_data['id'];

    $id_chron_modif = $modif_chron_data['id_chron_modif'];

    $chron_init = '';
    $chron_nom = '';
    if(isset($chron_array[$id_chron_modif]))
    {
        $chron_init = $chron_array[$id_chron_modif]['init'];
        $chron_nom = $chron_array[$id_chron_modif]['nom_chron'];
    }

    $user_id = $modif_chron_data['id_user'];
    $user_prenom_nom = $users_array[$user_id]['prenom_nom'];

    $chron_obs_user = $modif_chron_data['obs_user'];

    $info_correction = $modif_chron_data['info_correction'];

    $datetime_string = $modif_chron_data['datetime_correction'];
    $datetime_correction = '';
    if(!empty($datetime_string) && ($datetime_string!='0000-00-00 00:00:00'))
    {
        $datetime = new DateTime($datetime_string);
        $datetime_correction = $datetime->format('d-m-Y H:i:s');
    }

    $datetime_string = $modif_chron_data['datetime_first'];
    $datetime_first = '';
    if(!empty($datetime_string) && ($datetime_string!='0000-00-00 00:00:00'))
    {
        $datetime = new DateTime($datetime_string);
        $datetime_first = $datetime->format('d-m-Y H:i:s');
    }

    $datetime_string = $modif_chron_data['datetime_end'];
    $datetime_end = '';
    if(!empty($datetime_string) && ($datetime_string!='0000-00-00 00:00:00'))
    {
        $datetime = new DateTime($datetime_string);
        $datetime_end = $datetime->format('d-m-Y H:i:s');
    }

    $modif_chron_array[$num_op] = array('id_chron_modif' => $id_chron_modif,
                                        'chron' => $chron_init.' - '.$chron_nom,
                                        'datetime_correction' => $datetime_correction,
                                        'info_correction' => $info_correction,
                                        'user' => $user_prenom_nom,
                                        'chron_obs_user' => $chron_obs_user,
                                        'datetime_first' => $datetime_first,
                                        'datetime_end' => $datetime_end
                                        );
}

// TRi de la table par les date d'opération
if(isset($modif_chron_array))
{
    usort($modif_chron_array, function($a, $b) 
    {
        $dateA = DateTime::createFromFormat('d-m-Y H:i:s', $a['datetime_correction']);
        $dateB = DateTime::createFromFormat('d-m-Y H:i:s', $b['datetime_correction']);

        if ($dateA == $dateB) return 0;
        return ($dateA > $dateB) ? -1 : 1; // Tri décroissant (plus récent d'abord)
    });
}
//Génération du code HTML
$html = '';
/*
if(isset($modif_chron_array)) // Si il existe des Chroniques
{
*/	
	$html .= 
	"
	<div class='table-container' style='background-color:#fff;' >							  

		<table id='table_tri' cellspacing='0' style='margin:3px 10px;'>

			<thead>
				<tr class='header-row'>
					<th style='width:150px;'>
                        "."Date de l'opération"."
                    </th>			
					<th style='width:200px;'>
                        "."Données"."
                    </th>	                   	
					<th style='width:250px;'>
                        "."Opération"."
                    </th>
					<th style='width:180px;'>
                        "."Utilisateur"."
                    </th>
                    <th style='width:300px;'>
                        "."Observations"."
                    </th> 	
                    <th style='width:150px;'>
                        "."Début période"."
                    </th>
                    <th style='width:150px;'>
                        "."Fin période"."
                    </th>						
				</tr>
			</thead>
	";	

			$row=1;
            if(isset($modif_chron_array))
            {    
                foreach($modif_chron_array as $key => $value)
                {
                    // Coloration d'une ligne au survol de la souris
                    if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                    else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 	

                    $html .= "<tr ".$row_l." >";
                        
                        // Date Opération
                        $html .= 
                        "
                            <td style=''>".$value['datetime_correction']."</td>
                        ";

                        // Chronique
                        $html .= 
                        "
                            <td style=''>".$value['chron']."</td>
                        ";

                        // Opération
                        $html .= 
                        "
                            <td>".$value['info_correction']."</td>
                        ";

                        // Utilisateur
                        $html .= 
                        "
                            <td style=''>".$value['user']."</td>
                        ";

                        // Observation
                        $html .= 
                        "													
                            <td style=''>".$value['chron_obs_user']."</td>
                        ";

                        // Début période
                        $html .= 
                        "													
                            <td style=''>".$value['datetime_first']."</td>
                        ";

                        // Début période
                        $html .= 
                        "													
                            <td style=''>".$value['datetime_end']."</td>
                        ";

                    $html .= "</tr>";

                    
                    $row++;
                }
            }


		$html .= "</table>";

	$html .= "</div>";
//}


$responseData = array(
    'js_html' => $html
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>