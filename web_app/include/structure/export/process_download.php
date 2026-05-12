<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Downlaod Files 
----------------------------------------
*/


// Spécifiez le nom du dossier à créer
$file_download = $_POST['file_download'];
$file_extension = $_POST['file_extension'];
$chemin_folder = DIR_WS_DATA_EXPORT . $file_download . '.' . $file_extension; // 'chemin/file.tar' ou 'chemin/file.zip'


// Vérifier si le fichier existe
if (file_exists($chemin_folder)) 
{
    // proposer le téléchargement du fichier TAR ou ZIP
    header('Content-Type: application/"' . $file_extension . '"');
    header('Content-Disposition: attachment; filename="' . $chemin_folder . '"');
    header('Content-Length: ' . filesize($chemin_folder));
    readfile($chemin_folder);  
    exit;
} else {
    // Le fichier n'existe pas, afficher un message d'erreur ou rediriger l'utilisateur
    echo "Le fichier demandé n'existe pas.";
    // ou rediriger l'utilisateur
    // header("Location: page_d_erreur.php");
    exit;
}


?>