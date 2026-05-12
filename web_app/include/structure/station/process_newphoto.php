<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Calcul Chronique
- Ce script permet de construire de nouvelles chroniques à partir d'une chronique existe
- Calcul loi Ynew = aX+b
- Correction de la ligne de temps
- Création de Chroniques temporelle : Jour, Mois, Année
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


// Accéder aux données du tableau récupérer
$id_station = $_POST['id_station'];
$desc_photo = $_POST['desc_photo'];
$date_photo = $_POST['date_photo'];

// Initialisation de nouvelles variables
$upload_valid = true;
$message_info = '';
$date_photo_us = '';
$nb_photo = 0;
$date_format = 'd-m-Y';

$valid_extensions = array('jpeg', 'jpg', 'png'); // Vérifier le format du fichier (extension)
$max_file_size = 2 * 1024 * 1024; // 2 Mo (en octets)
$chemin_dossier = '../../../'.DIR_WS_DATA_PHOTOS;

// Récupération de données de la base de données
$sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station
                FROM ".TABLE_STATION." s
                WHERE s.id_station=".$id_station;

$station_query = tep_db_query($sql_link,$sql_station);
$station_tab = tep_db_fetch_array($station_query);
$code_station = $station_tab['code_station'];

// Requête pour compter le nombre de photos de la station
$sql_num_photos = "SELECT id
                     FROM ".TABLE_STATION_PHOTOS." s
                     WHERE id_station=".$id_station."
                     ORDER BY id DESC
                     LIMIT 1";
$num_photos_query = tep_db_query($sql_link, $sql_num_photos);
$num_photos_data = tep_db_fetch_array($num_photos_query);
$num_photos = 0;
if(isset($num_photos_data['id'])){$num_photos = $num_photos_data['id'];}

$name_photo = $code_station.'_'.($num_photos+1);


// Vérification du format du champs date
if(tep_not_null($date_photo))
{
	$date_photo_format = DateTime::createFromFormat($date_format, $date_photo); 
	if($date_photo_format && $date_photo_format->format($date_format) === $date_photo) 	
	{
		$date_photo_us = $date_photo_format->format('Y-m-d');
	}
	else
	{
		$upload_valid = false;        
        $message_info .= "La date n'est pas au format attendu : dd-mm-yyyy";
	}
}

// Récupération du fichier photo à charger et enregistrer
if($upload_valid)
{
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_photo'])) 
    {
        // Traitement du téléchargement du fichier
        $file_name = $_FILES['file_photo']['name'];
        $file_tmp = $_FILES['file_photo']['tmp_name'];
        $file_size = $_FILES['file_photo']['size'];
        $file_error = $_FILES['file_photo']['error'];

        // Vérifier le format du fichier photo (extension)
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        if (!in_array(strtolower($file_extension), $valid_extensions)) 
        {
            // Gérer les erreurs de téléchargement du fichier
            $upload_valid = false;
            if(tep_not_null($message_info)){$message_info .= "<br>";}
            $message_info .= "Le fichier de la nouvelle photo n'est pas au bon format.<br>Formats acceptés : .jpg, .jpge, .png\n";
        }

        // Vérifier la taille du fichier (en octets)   
        if ($file_size > $max_file_size) 
        {
            $upload_valid = false;
            if(tep_not_null($message_info)){$message_info .= "<br>";}
            $message_info .= "La taille du fichier de la nouvelle photo ne doit pas dépasser 2 Mo.\n";
        }

        // Vérifier s'il y a des erreurs lors du téléchargement du fichier
        if($upload_valid && ($file_error === UPLOAD_ERR_OK)) 
        {
            // Déplacer le fichier téléchargé vers un emplacement souhaité
            $file_name_new = $name_photo . '.' . strtolower($file_extension);
            $destination = $chemin_dossier . $file_name_new;
            move_uploaded_file($file_tmp, $destination);

            $query_newphoto = "INSERT INTO " . TABLE_STATION_PHOTOS . " (id_station,date_photo, description_photo, file_photo) 
                                VALUES ('$id_station','$date_photo_us','$desc_photo','$file_name_new')";
            tep_db_query($sql_link, $query_newphoto);	

            if(tep_not_null($message_info)){$message_info .= "<br>";}
            $message_info .= "La photo a été enregistrée avec succès.\n";
        } 
        else 
        {
            // Gérer les erreurs de téléchargement du fichier
            if(tep_not_null($message_info)){$message_info .= "<br>-<br>";}
            $message_info .= "Une erreur est survenue lors du chargement du fichier.\n";
        }
    }    
    else 
    {
        // Si la méthode de requête n'est pas POST ou si aucun fichier n'a été téléchargé
        if(tep_not_null($message_info)){$message_info .= "<br>";}
        $message_info .= "Aucun fichier n'a pu être chargé.\n";
    }
}

echo $message_info;

?>