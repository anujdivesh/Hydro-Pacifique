<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Lecture initiale des fichiers importés 
Script coté serveur appelé par procédure AJAX depuis import.php
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


// Appels à la librairie phpspreadsheet
/*
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
*/

// ----------------------------------------------
// Récupération de données dans la base

// TABLE IMPORT FILES (Caractéristiques des fichiers importables)
$sql_import_files = "SELECT DISTINCT id, name_ext, multi_feuil, separateur, description, algo 
                    FROM ".TABLE_IMPORT_FILES." 
                    ORDER BY id ASC";
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



// TABLE STATION
$sql_station_all = "SELECT DISTINCT id_station, nom_station, code_station, station_type, active_station
					FROM ".TABLE_STATION;
$station_all_query = tep_db_query($sql_link,$sql_station_all);
while ($station_all = tep_db_fetch_array($station_all_query))
{	
	$station_all_array[$station_all['code_station']] = array('id_station' => $station_all['id_station'],
															'nom_station' => $station_all['nom_station'],
															'station_type' => $station_all['station_type'],
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
															'nom_type_data' => $type_chron_tab['nom_type_data'],
															'unite' => $type_chron_tab['unite'],															
															'id_eq_type_data' => $type_chron_tab['id_eq_type_data']
															);
}



// On valide la possibilité d'importer des RA ou d'autre données qui ne sont pas des chroniques
$type_chron_array['RA'] = array('id_data_type' => 0,
                                'nom_type_data' => 'Rapport Activité',
                                'unite' => '-',															
                                'id_eq_type_data' => 0
                                );



$type_chron_array['JGE'] = array('id_data_type' => 0,
                                'nom_type_data' => 'Jaugeage',
                                'unite' => '-',															
                                'id_eq_type_data' => 0
                                );                                


$type_chron_array['ETL'] = array('id_data_type' => 0,
                                'nom_type_data' => 'Etalonnage',
                                'unite' => '-',															
                                'id_eq_type_data' => 0
                                );  
                                
$type_chron_array['REP'] = array('id_data_type' => 0,
                                'nom_type_data' => 'Repère piézométrie',
                                'unite' => '-',															
                                'id_eq_type_data' => 0
                                );                                  
                                

//print_r($type_chron_array);
// ----------------------------------------------
// Initialisation de variables
$import_valid = true;
$msg_info = '';
$tab_html = '';

$id_station = 0;
$id_chron = 0;
$id_ext_file = 0;


// Upload et traitement du fichier
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) 
{
    $file = $_FILES['file'];

    //print_r($file);

    // Récupérer les données Méta envoyé aussi par la procédure AJAX
    if (isset($_POST['meta'])) 
    {
        $metaData = json_decode($_POST['meta'], true); // Décoder le JSON
        $id_user = $metaData['id_user']; // Accéder à id_user
        $id_import = $metaData['id_import']; // Accéder à id_import
    }

    if ($file['error'] === UPLOAD_ERR_OK) // Vérifier s'il n'y a pas d'erreur
    { 
        $fileName = basename($file['name']); // Nom du fichier
        $tempPath = $file['tmp_name']; // Chemin temporaire
        $destination =  '../../../data/uploads/files/' . $fileName; // Chemin de destination

        $msg_info .= "\nFichier : ".$fileName;

        // Déplacez le fichier vers le répertoire de destination
        if (move_uploaded_file($tempPath, $destination)) 
        {
            $decoup_file = explode('.',$fileName);
            $titre_file = explode('_',$decoup_file[0]);
            $ext_file = end($decoup_file); // dernière partie du tableau c'est à dire l'extension
            
            if(isset($import_files[$ext_file])) // Si l'extention du fichier est valide : référencé dans la table import_file
            {
                $id_ext_file = $import_files[$ext_file]['id'];

                if(isset($station_all_array[$titre_file[0]])) // Si le code station dans le nom du fichier existe : référencé dans la table station
                {
                    $id_station = $station_all_array[$titre_file[0]]['id_station'];
                    $code_station = $titre_file[0];
                    $nom_station = $station_all_array[$titre_file[0]]['nom_station'];
                    $type_data = $station_all_array[$titre_file[0]]['station_type'];
                                        
                    if($import_files[$ext_file]['multi_feuil'] == 0) // Si le fichier de données est multifeuil (type xlsx) ou ne contient qu'une feuil de données (1 chronique)
                    {
                        // On est dans un fichier simple sans multifeuil
                        if(isset($type_chron_array[$titre_file[1]])) // Si le nom de la chronique est référencé : table data_type
                        {                            
                            $id_chron = $type_chron_array[$titre_file[1]]['id_data_type'];
                            $init_chron = $titre_file[1];
                            $nom_chron = $type_chron_array[$titre_file[1]]['nom_type_data'];                            
                            $unite_chron = $type_chron_array[$titre_file[1]]['unite'];

                            $timestamp = time(); // Obtenir le timestamp Unix actuel                           
                            $datetime = date('Y-m-d H:i:s', $timestamp); // Formater le timestamp pour obtenir 'yyyy-mm-jj hh:mm:ss'

                            $update_import = "INSERT INTO ".TABLE_IMPORT_SUIVI." (id_import,file_import,file_ext,dateheure,id_station,id_chron,id_user,import)
                                                                    VALUE ('".$id_import."','".$fileName."','".$ext_file."','".$datetime."','".$id_station."','".$id_chron."','".$id_user."',0)";

                            tep_db_query($sql_link,$update_import);
                            $last_insert_id = mysqli_insert_id($sql_link); // Obtenir l'ID généré par le champ AUTO_INCREMENT

                            $msg_info .= " - Conforme.\n";
                            $msg_info .= "Station : ".$nom_station."\n";
                            $msg_info .= "Chronique : ".$init_chron." - ".$nom_chron."\n";

                            $graph_chron_link_graph = $id_station."_".$type_data."_".$id_chron;

                            $tab_html .= " <tr>
                                                <td style='height:25px;'>".$fileName."</td>
                                                <td style='height:25px;cursor: pointer;' title='".$nom_station."'>".$code_station." - ".affichemots($nom_station,8)."</td>
                                                <td style='height:25px;text-align:center;cursor: pointer;' title='".$nom_chron."'>
                                                    <input type='text' id='dataInit_".$last_insert_id."' value='".$init_chron."' readonly style='border:none;font-size:11px;cursor: pointer;'>
                                                </td>
                                                <td style='height:25px;text-align:center;'>".$unite_chron."</td>
                                                <td style='height:25px;text-align:center;'>
                                                    <input type='checkbox' name='checkFile[]' value='import_".$last_insert_id."' checked>
                                                </td>
                                                <td style='height:25px;text-align:center;'>
                                                    <img src='".DIR_WS_IMG_ICO."check.png' style='width:15px;display:none;' id='check_".$last_insert_id."' title='".htmlaccent('Données chargées')."'>
                                                    <img src='".DIR_WS_IMG_ICO."delete.png' style='width:15px;display:none;' id='nocheck_".$last_insert_id."' title='".htmlaccent('Aucune donnée n\'a pu être chargée')."'>
                                                    <img src='".DIR_WS_IMG."wait.gif' style='width:20px;display:none;' id='wait_".$last_insert_id."' title='".htmlaccent('Données en cours de traitement')."'>
                                                </td>
                                                <td style='height:25px;text-align:center;'>
                                                    <a href='".DIR_WS_DATA_IMPORT.$id_import."_".$init_chron.".txt' target='blank_' >
                                                        <img src='".DIR_WS_IMG_ICO."detail.png' style='width:15px;display:none;' id='note_".$last_insert_id."' title='".htmlaccent('Détails de l\'importation')."'>
                                                    </a>
                                                </td>
                                            ";

                                            if($init_chron!='RA' && $init_chron!='JGE' && $init_chron!='ETL' && $init_chron!='REP')
                                            {
                                                $tabForm = [
                                                                ['name' => 'graph_chron', 'value' => $graph_chron_link_graph]
                                                            ];
                                                $tabFormJson = json_encode($tabForm);
                                                $tabFormJson = htmlspecialchars($tabFormJson, ENT_QUOTES, 'UTF-8');

                                                $consult_import = "<img src='".DIR_WS_IMG_ICO."graph.png' style='width:15px;cursor:pointer;display:none;' 
                                                                    id='graph_".$last_insert_id."' 
                                                                    title='"."Consulter les données importées"."' 
                                                                onclick=\"event.preventDefault();linkSubmitForm('data_chron.php', ".$tabFormJson.");\">";

                                                $tab_html .= " 
                                                            <td style='height:25px;text-align:center;'>                                                    
                                                                ".$consult_import."
                                                            </td>
                                                        ";
                                            }
                                            else
                                            {$tab_html .= "<td><span id='graph_".$last_insert_id."'>-</span></td>";}

                            $tab_html .= "</tr>";
                        }
                        else
                        {
                            $import_valid = false;
                            $msg_info .= "\nAucune Chronique de données référencée n'a pu être identifiée dans le nom du fichier.";
                        }
                    }
                }
                else
                {
                    $import_valid = false;
                    $msg_info .= "\nAucune Station n'a pu être identifiée dans le nom du fichier.";
                }

            }
            else
            {
                $import_valid = false;
                $msg_info .= "\nLe type de fichier n'est pas valide. Extension : ".$ext_file." non référencée.";
            }

        } 
        else 
        {
            $import_valid = false;
            $msg_info .= "Erreur lors du déplacement du fichier";
        }
        
    } 
    else 
    {
        $import_valid = false;
        $msg_info .= "Erreur de téléversement: " . $file['error'];
    }
} 
else 
{
    $import_valid = false;
    $msg_info .= "Aucun fichier n'a été reçu";
}

$msg_info .= "\n"; // pour rajouter un saut de ligne
$dataJson = [   
                'msg_info' => $msg_info,
                'tab_html' => $tab_html
            ];


echo json_encode($dataJson);
			

?>
