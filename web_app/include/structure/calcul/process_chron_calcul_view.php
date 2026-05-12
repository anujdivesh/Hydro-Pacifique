<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Procédure pour afficher dans un tableau les corrections en cours.
Processus asynchrone AJX coté serveur
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
$id_correction = $dataInfo['id_correction'];


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

// TABLE STATION
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

// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_graph 
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
                                                        'type_color_border' => $eq_type_tab['type_color_border']
                                                    );
}

// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, to_periode, id_chon_periode
				  FROM ".TABLE_TYPE_DATA." 
				  ORDER BY init_type_data ASC";
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
while ($type_chron_tab = tep_db_fetch_array($type_chron_query))
{
    $axe_nom = '';
    if(isset($data_type_axe_array[$type_chron_tab['axe_data']]['axe'])){$axe_nom = $data_type_axe_array[$type_chron_tab['axe_data']]['axe'];}

	$type_chron_array[$type_chron_tab['id_data_type']] = array('init_type_data' => $type_chron_tab['init_type_data'],
															'nom_type_data' => $type_chron_tab['nom_type_data'],
															'id_eq_type_data' => $type_chron_tab['id_eq_type_data'],
															'axe_nom' => $axe_nom,
															'unite' => $type_chron_tab['unite'],
															'to_periode' => $type_chron_tab['to_periode'],
															'id_chon_periode' => $type_chron_tab['id_chon_periode']
															);
}

// -------------------------------------------------------------


// Initialisation Variables
$tab_html ='';
$text_result_file ='';
$msg_newCorrection = '';
$graph_chron_link_graph = '';
$row = 0;

$id_meta_correct = 0;
if($id_correction > 0)
{
    

    // Récupération des données en cours de correction
    $sql_correction = "SELECT c.id, c.id_user, c.datetime_correction, c.id_station, c.id_chron_init, c.datetime_first, c.datetime_end
                        FROM ".TABLE_DATA_CORRECTION." c
                        WHERE c.id = ".$id_correction;
    $correction_query = tep_db_query($sql_link,$sql_correction);
    $correction_tab = tep_db_fetch_array($correction_query);

    // Date de la création de la correction
    $datetime_correction_tab = explode(' ',$correction_tab['datetime_correction']);
    $datetime_correction_formated = dateus_fr($datetime_correction_tab[0]).' '.$datetime_correction_tab[1];

    // Station
    $id_station = $correction_tab['id_station'];
    $code_station = $station_all_array[$id_station]['code_station'];
    $nom_station = $station_all_array[$id_station]['nom_station'];
    // Type de données (Hydro, Pluvio, ...)
    $station_type = $station_all_array[$id_station]['station_type'];
    $intitule_station_type = $eq_type_array[$station_type]['nom_eq_type'];
    // Type de Chronique (CI, CIE, ...)
    $id_chron_init = $correction_tab['id_chron_init'];
    $init_chron = $type_chron_array[$id_chron_init]['init_type_data'];
    $nom_chron = $type_chron_array[$id_chron_init]['nom_type_data'];
    // Période de la correction
    $datetime_first_correction_tab = explode(' ',$correction_tab['datetime_first']);
    $datetime_first_correction_formated = dateus_fr($datetime_first_correction_tab[0]).' '.$datetime_first_correction_tab[1];
    $datetime_end_correction_tab = explode(' ',$correction_tab['datetime_end']);
    $datetime_end_correction_formated = dateus_fr($datetime_end_correction_tab[0]).' '.$datetime_end_correction_tab[1];
    // User
    $id_user = $correction_tab['id_user']; 
    $login_user = $user_list_array[$id_user]['login']; 
    $prenom_user = $user_list_array[$id_user]['prenom']; 
    $nom_user = $user_list_array[$id_user]['nom']; 

    // Debut Ecriture du texte pour fichier de suivi de la correction
    $text_result_file .= "Bloc de correction - ".$datetime_correction_formated."
                            --\n
                                Utilisateur : ".$login_user." - ".$prenom_user." ".$nom_user."\n
                                Station : ".$code_station." - ".$nom_station."\n
                                Type : ".$intitule_station_type."\n
                                Chronique : ".$init_chron." - ".$nom_chron."\n
                                Période : ".$datetime_first_correction_formated." - ".$datetime_end_correction_formated."\n
                            --\n\n\n
                                Liste des corrections \n
                            --\n";


        $sql_meta_correction = "SELECT mc.id, mc.id_station, mc.id_typedata, mc.info_correction, mc.axe_correction, mc.datetime_first, mc.datetime_end, 
                                      mc.valid, mc.id_chron_modif
                                FROM ".TABLE_DATA_META_CORRECTION." mc
                                WHERE id_correction = ".$id_correction;
        $meta_correction_query = tep_db_query($sql_link,$sql_meta_correction);
        while($meta_correction_tab = tep_db_fetch_array($meta_correction_query))
        {
            $id_meta_correct = $meta_correction_tab['id'];

            $info_correction = $meta_correction_tab['info_correction'];

            $datetime_first_meta = $meta_correction_tab['datetime_first'];
            $datetime_first_meta_tab = explode(' ',$datetime_first_meta);
            $datetime_first_meta_formated = dateus_fr($datetime_first_meta_tab[0]).' '.$datetime_first_meta_tab[1];

            $datetime_end_meta = $meta_correction_tab['datetime_end'];
            $datetime_end_meta_tab = explode(' ',$datetime_end_meta);
            $datetime_end_meta_formated = dateus_fr($datetime_end_meta_tab[0]).' '.$datetime_end_meta_tab[1];

            // Type de Chronique (CI, CIE, ...)
            $id_chron_modif = 0;
            $init_chron_modif = '';
            $nom_chron_modif = '';

            $id_chron_modif = $meta_correction_tab['id_chron_modif'];
            if($id_chron_modif > 0)
            {
                $init_chron_modif = $type_chron_array[$id_chron_modif]['init_type_data'];
                $nom_chron_modif = $type_chron_array[$id_chron_modif]['nom_type_data'];
            }

            $download_textTab = '';
            $valid_textTab = '';
            $del_textTab = '';
            $text_result_file_temp = '';

            $detail_correction_part = explode(':', $info_correction); // permet de vérifier si on a une nouvelle chronique que l'on peut télécharger
            if(count($detail_correction_part) > 1) 
            {
                if($meta_correction_tab['valid'] > 0)
                {
                    $download_textTab = "<img src='".DIR_WS_IMG_ICO."download.png' style='width:15px;cursor:pointer;' id='download_".$id_meta_correct."' title='".htmlaccent('Télécharger la chronique')."' onclick='download_chron(".$id_meta_correct.");'>";
                    $valid_textTab = "<img src='".DIR_WS_IMG_ICO."check.png' style='width:15px;' id='valid_".$id_meta_correct."' title='".htmlaccent('Correction appliquée')."'>";            
                    $valid_textTab .= " -> ".$init_chron_modif;
                    $del_textTab = "&nbsp;";

                    $text_result_file_temp = "Validée dans la chronique ".$init_chron_modif;
                }
                else
                {
                    $download_textTab = "<img src='".DIR_WS_IMG_ICO."download.png' style='width:15px;cursor:pointer;' id='download_".$id_meta_correct."' title='".htmlaccent('Télécharger la chronique')."' onclick=\"download_chron(".$id_meta_correct.");\">";
                    $valid_textTab = "<input type='checkbox' name='checkCorrection[]' value='meta_".$id_meta_correct."' id='meta_".$id_meta_correct."'>";
                    $del_textTab = "<a style='font-size:12px;font-weight:bold;' id='del_".$id_meta_correct."' onClick='delCorrection(".$id_meta_correct.");' title='".htmlaccent('Supprimer la correction')."'>
                                    X
                                    </a>";

                    $text_result_file_temp = "Non validée";
                }
            }
            else
            {
                $download_textTab = "";
                if($meta_correction_tab['valid'] > 0)
                {
                    $valid_textTab = "<img src='".DIR_WS_IMG_ICO."check.png' style='width:15px;' id='valid_".$id_meta_correct."' title='".htmlaccent('Correction appliquée')."'>";            
                    $valid_textTab .= " -> ".$init_chron_modif;
                    $del_textTab = "&nbsp;";

                    $text_result_file_temp = "Validée dans la chronique ".$init_chron_modif;
                }
                else
                {
                    $valid_textTab = "<input type='checkbox' name='checkCorrection[]' value='meta_".$id_meta_correct."' id='meta_".$id_meta_correct."'>";
                    $del_textTab = "<a style='font-size:12px;font-weight:bold;' id='del_".$id_meta_correct."' onClick='delCorrection(".$id_meta_correct.");' title='".htmlaccent('Supprimer la correction')."'>
                                    X
                                    </a>";

                    $text_result_file_temp = "Non validée";
                }
                /*
                $download_textTab = "";
                $valid_textTab = "<input type='checkbox' name='checkCorrection[]' value='meta_".$id_meta_correct."' id='meta_".$id_meta_correct."'>";
                $del_textTab = "<a style='font-size:12px;font-weight:bold;' id='del_".$id_meta_correct."' onClick='delCorrection(".$id_meta_correct.");' title='".htmlaccent('Supprimer la correction')."'>
                                X
                                </a>";

                $text_result_file_temp = "Non validée";
                */
            }
            
            // Coloration d'une ligne au survol de la souris
            $row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";
            
            $tab_html .= "  <tr ".$row_l." >
                                <td style='height:25px;'>".$meta_correction_tab['info_correction']."</td>
                                <td style='height:25px;'>".$datetime_first_meta_formated."</td>
                                <td style='height:25px;'>".$datetime_end_meta_formated."</td>      
                                <td style='height:25px;text-align:center;'>
                                    ".$valid_textTab."
                                </td>
                                <td style='height:25px;text-align:center;'>
                                    ".$download_textTab."
                                </td>                      
                                <td style='height:25px;text-align:center;'>
                                    ".$del_textTab."                            
                                </td>
                            </tr>
                        ";

            
            $text_result_file .= $info_correction." : [".$datetime_first_meta_formated." | ".$datetime_first_meta_formated."] => ".$text_result_file_temp."\n";
        }
        
    // Enregistrement des corrections dans un fichier texte pour le suivi des actions

    $folder =  '../../../data/corrections'; // Chemin de destination
    $resultFilename = $folder.'/'.$code_station.'_'.$type_chron_array[$id_chron_init]['init_type_data'].'_'.$id_correction.'.txt';
    if (file_exists($resultFilename)){unlink($resultFilename);} // Supprimer le fichier existant s'il existe
    file_put_contents($resultFilename, mb_convert_encoding($text_result_file, 'ISO-8859-1', 'UTF-8'), FILE_APPEND); // Ecrire le résultat de l'import dans un fichier texte
}


if($id_meta_correct < 1)
{
    $tab_html .= "<tr>
                    <td colspan='9' style='height:15px;'>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan='9' style='height:15px;font-weight:bold;font-size:12px;'>	 
                        Aucune correction en cours
                    </td>	 
                </tr>";
}




// Remplissage du tableau de retour

$responseData = array(
    'tab_html' => $tab_html,
    'id_meta' => $id_meta_correct
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>