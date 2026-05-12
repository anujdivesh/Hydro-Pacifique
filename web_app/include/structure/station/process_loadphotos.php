<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant d'affichage des photos des stations
Protocole sur serveur (AJAX) 
Appelé depuis include/structure/form_station_photos.php
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
$id_station = $dataInfo['id_station'];


// Initialisation Variables
$tab_html = '';
$dossier_photos = '../../../'.DIR_WS_DATA_PHOTOS;

// Dimensions maximales souhaitées pour les images
$max_width = 1440;
$max_height = 1080;

// Récupération de données de la base de données
$sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station
                FROM ".TABLE_STATION." s
                WHERE s.id_station=".$id_station;

$station_query = tep_db_query($sql_link,$sql_station);
$station_tab = tep_db_fetch_array($station_query);
$code_station = $station_tab['code_station'];


// Requête sur la table photos de la station 
$sql_photos = "SELECT DISTINCT id, id_station, date_photo, description_photo,  file_photo
				FROM ".TABLE_STATION_PHOTOS."
                WHERE id_station = ".$id_station."
				ORDER BY date_photo DESC";
$photos_query = tep_db_query($sql_link,$sql_photos);



$num_photo = 0;
while($photos_tab = tep_db_fetch_array($photos_query))
{
    $num_photo++;
    
    // Chemin de l'image
    $image_path = $dossier_photos.$photos_tab['file_photo'];
    
    $tab_html .= "<div id='cadre_photo' >\n";

                    if(is_file($image_path))
                    {
                        $tab_html .= "
                                    <div id='cadre_img' >\n
                                        <img src='".$dossier_photos.$photos_tab['file_photo']."' style=''>
                                    </div>
                                    ";
                    }

                    $date_photo_fr = dateus_fr($photos_tab['date_photo']);
                    if($date_photo_fr == '00-00-0000'){$date_photo_fr = '';}

                    $tab_html .=  "
                                    <div id='cadre_text'>

                                        <p style='float:left;width:90%;'>
                                            <span style=''>"."Date"."</span> : 
                                            ".$date_photo_fr."
                                        </p>
                                        
                                        <p style='float:right;width:10px;text-align:right;'  title='"."Supprimer image"."'>
                                            <a onClick=\"del_photos(".$photos_tab['id'].");\" style='font-size:13px;font-weight:bold;'>X</a>
                                        </p>

                                        <p style='float:left;width:90%;'>
                                            <span style='font-weight:bold;'>"."Description"."</span> : 
                                            ".$photos_tab['description_photo']."
                                        </p>

                                    </div>\n
                                    ";

    $tab_html .= "</div>\n";
}
if($num_photo == 0)
{
    $tab_html .=  "
                    <div id='cadre_photo' >\n
                        <img src='".DIR_WS_DATA_PHOTOS."default.png' style='width:200px;'>
                
                        <div id='cadre_text'>
                            <p style='float:left;width:90%;'>".htmlaccent('Aucune photo disponible')."</p>
                        </div>\n
                    
                    </div>\n
                ";
}

$dataJson = [   
    'tab_html' => $tab_html
];


echo json_encode($dataJson);


?>