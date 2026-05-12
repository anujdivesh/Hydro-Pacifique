<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Compression d'un dossier - Format tar pour téléchargement 'Sans compression'
----------------------------------------
*/

$resultTar_text = '';

// Récupération des données JSON envoyées depuis la requête AJAX
$jsonDataCompress = file_get_contents('php://input');

// Décodage des données JSON en tableau associatif
$dataCompress = json_decode($jsonDataCompress, true);

// Accès aux données individuelles
$chemin_folder = $dataCompress['chemin_folder'];
$folder = $dataCompress['folder_download'];
$chemin_folder_compress = '../../../' . $chemin_folder;


if(is_dir($chemin_folder_compress)) // Si le dossier existe
{
    // Obtenir la liste des fichiers dans le dossier
    $fichiers = scandir($chemin_folder_compress);
    $total_files = sizeof($fichiers);
    
    // -------------------------------------
    // Tar Archive (sans compression)

    // nom du fichier TAR à créer
    $tarFilename =  '../../../data/export/' . $folder.'.tar';
    
    // créer un objet ZipArchive
    $phar = new PharData($tarFilename);
   

    // Parcourir la liste des fichiers
    $startTime = microtime(true);

        // Ajouter le contenu du dossier à l'archive TAR
    $phar->buildFromDirectory($chemin_folder_compress);

    // Temps à la fin du script
    $endTime = microtime(true);

    // Calcul de la durée d'exécution en secondes
    $total_time = number_format($endTime - $startTime,1);

    // Obtenir la taille du fichier
    $fileSize = filesize($tarFilename);
    $fileSizeMb = round($fileSize / (1024 * 1024), 2); // Conversion en mégaoctets (Mo) avec arrondi à 2 décimales

    $resultTar_text .= "\n\nLes fichiers sont disponibles au téléchargement (format tar)";
    //$resultTar_text .= "\nDurée de traitement : ".round($total_time)." sec. - Nombre de fichiers traités : ".$total_files;
    $resultTar_text .= "\nFichier : ".$folder.".tar - Taille : ".$fileSizeMb." Mo";

    echo json_encode($resultTar_text, JSON_UNESCAPED_UNICODE);
}



?>