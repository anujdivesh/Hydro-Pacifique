<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Préparation des exports
Géré par tâche asynchrone - Ajax
Cette page permet l'affichage du suivi de la création des fichiers Export et de proposer le téléchargement
----------------------------------------
*/


$todayTime = new DateTime(); // Crée un objet DateTime pour la date actuelle
$today_formatted = $todayTime->format('YmdHi');
$today_text = $todayTime->format('d-m-Y H:i');
$today_sql = $todayTime->format('Y-m-d H:i:s'); // pour conserver la date de création de l'export 



// Création du dossier d'enregistrement des fichiers
$folder_download = $today_formatted . '_csv_' . $id_user;

$chemin_folder = DIR_WS_DATA_EXPORT . $folder_download;


// --------------------------------------
// Debut page HTML

require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

    require(DIR_WS_STRUCTURE . 'block_graph.php');
    require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
    include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

    echo "<div id='contour_general'>";

        echo "<div id='contenu_centre'>";

            echo "<div id='contenu_box2'>";		

                echo "<h1 id='h1_graph'>";
                                
                    echo "<span>".htmlaccent('Exportation des Chroniques de données')."</span>";
                    
                echo "</h1>";

                echo "<div style='float:left;width:40%;text-align:left;'>\n";

                    echo "<p style='float:left;width:100%;font-size:14px;font-weight:bold;'>".htmlaccent('Compilation des fichiers')."</p>";
                    
                    echo "<div id='barre' class='bar_create_all' style='margin-top:10px;'>";
                        echo"<div id='pourcentage_compil' style='width:0%;'></div>";
                    echo "</div>";

                    /*
                    echo "<p style='float:left;width:100%;margin-top:10px;font-size:14px;font-weight:bold;'>".htmlaccent('Compression du dossier pour le téléchargement')."</p>";

                    echo "<div id='barre' class='bar_create_all' style='margin-top:10px;'>";
                        echo"<div id='pourcentage_compress' style='width:0%;'></div>";
                    echo "</div>";
                    */

                    echo "<p style='float:left;width:100%;font-size:14px;font-weight:bold;margin-top: 25px;'>".htmlaccent('Progression du traitement')."</p>";
                    echo "<textarea id='fileList' style='width:98%;height:300px;margin-bottom: 25px;' readonly>";
                        echo htmlaccent('Date Heure : ').$today_text." \n\n";;                        
                        echo htmlaccent('Compilation des fichiers en cours - Veuillez patienter...');
                    echo "</textarea>";

                    echo "<div id='block_download' style='display:none;'>";

                        $lien_form = tep_href_link('data_chron.php');
                        $name_form = 'form_download';			
                        echo "<form name='".$name_form."' id='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data' target='_blank'>";

                            echo "<input type='hidden' id='file_download' name='file_download' value='".$folder_download."'>";
                            
                            $extension = 'tar';
                            if($zip){$extension = 'zip';}
                            echo "<input type='hidden' id='file_extension' name='file_extension' value='".$extension."'>";
                        
                            echo "<div id='button_titre' onclick=\"document.getElementById('". $name_form."').submit();\">";
                                echo htmlaccent('Télécharger les fichiers créés');
                            echo "</div>";

                        echo "</form>";

                    echo "</div>";
                    
                                    
                echo "</div>";

                echo "<div style='float:right;width:45%;text-align:left;margin-right:5%;'>\n";

                    include(DIR_WS_EXPORT . 'data_chron_listexport.php'); // Liste des exports
                    
                echo "</div>";

            echo "<hr>";
            echo "</div>";
        
        echo "<hr>";
        echo "</div>";

    echo "<hr>";
    echo "</div>";


    echo "<div id='pied_page'>";

        echo "<div id='copyright'>";	
            
            //echo htmlaccent('powered by - &copy; Vai-Natura 2011');
            //echo "<a href='mailto:".MAIL_WEBMASTER."'>".htmlaccent('powered by - &copy; Vai-Natura 2011')."</a>";
            echo "<a href='http://www.vai-natura.com' target='_blank'>".htmlaccent('&copy; 2024 Vai-Natura. All rights reserved.')."</a>";
        echo "</div>";

    echo "</div>";

echo "</body>";

echo "</html>";

// On parcours les stations qui sont sélectionnées et les chroniques qui y sont lié
// Le temps de traitement est très court ici
// L'objectif est de récupérer les infos permettant ensuite d'écrire les fichiers XLSX ou CSV dans des préocédure ajax asynchrones. 
$nb_data_all = 0;
$list_station = '';


foreach($station_chron_array as $cle_station => $typedata_array) 
{       
    // Pour chaque station
    $count = count($typedata_array);

    $list_station .= $cle_station.',';

    $n = 0; // Initialisez le compteur de tours


    foreach($typedata_array as $typedata_chron => $sql_chron) 
    {
        $n++; // Incrémentez le compteur de tours

        // Vérifiez si c'est le premier ou le dernier tour de la boucle
        $is_first = ($n === 1) ? true : false;
        $is_last = ($n === $count) ? true : false;

        // Nom du fichier de sortie (CSV)
        $text_chron = '';
        if(isset($type_chron_array[$typedata_chron]['init_type_data'])){$text_chron = $type_chron_array[$typedata_chron]['init_type_data'];}
        else{$text_chron = strtoupper($typedata_chron);} // RA, JGE ou ETL

        $nom_station_filename = ucfirst(strtolower(nettoyerNomFichier($station_all_array[$cle_station]['nom_station'])));
        $Filename = $station_all_array[$cle_station]['code_station'].'_'.$text_chron.'_'.$nom_station_filename.'.csv';
            
        // Création d'un tableau qui contient toutes les infos nécessaires pour la compilation des données dans les procédures AJAX
        $data_info[] = array(
                                'Filename' => $Filename,
                                'folder_download' => addslashes($folder_download),
                                'chemin_folder' => addslashes($chemin_folder),                                
                                'id_station' => $cle_station, 
                                'code_station' => $station_all_array[$cle_station]['code_station'], 
                                'nom_station' => $station_all_array[$cle_station]['nom_station'], 
                                'init_chron' => $text_chron, 
                                'sql_chron' => $sql_chron,                                    
                                'nbdata_chron' => $nbdata_chron_array[$cle_station][$typedata_chron],
                                'entete_col' => $entete_col,
                                'num_chron' => $n,
                                'is_first' => $is_first,
                                'is_last' => $is_last
                            );

                            
        $nb_data_all += $nbdata_chron_array[$cle_station][$typedata_chron];
    }
}
$list_station = rtrim($list_station, ','); // Supprime la dernière virgule


$data_compress = array(
                        'folder_download' => addslashes($folder_download),
                        'chemin_folder' => addslashes($chemin_folder)
                        );


// Le but est ici de supprimer les dossiers de données préalablement générés pour nettoyer le dossier. 

// Vérifier si le dossier principal existe et est un dossier
if (is_dir(DIR_WS_DATA_EXPORT)) 
{
    // Récupérer la liste des sous-dossiers du dossier principal
    $sous_dossiers = glob(DIR_WS_DATA_EXPORT . '*', GLOB_ONLYDIR);
    
    // Parcourir chaque sous-dossier
    foreach ($sous_dossiers as $sous_dossier) 
    {
        // Récupérer la liste des fichiers dans le sous-dossier
        $fichiers = glob($sous_dossier . '/*');
        
        // Parcourir chaque fichier
        foreach ($fichiers as $fichier) 
        {
            // Vérifier que c'est un fichier et non un dossier
            if (is_file($fichier)) 
            {
                
                unlink($fichier);// Supprimer le fichier
            }
        }
        
        // Vérifier si le dossier est maintenant vide avant de tenter de le supprimer
        $fichiers_restants = glob($sous_dossier . '/*');
        
        if(empty($fichiers_restants)) 
        {
            // Supprimer le sous-dossier lui-même si vide
            if(is_dir($sous_dossier)) 
            {
                usleep(500000); // Délai de 500 ms pour assurer de la disponibilité du fichier
                rmdir($sous_dossier);
            }
        }
            
    }
}                    


?>

<script>

    var numIteration = 0;
    var pourcentage = 0;
    var id_station_encours = 0;
    
    var idTerritoire = <?php echo $territoire_id; ?>;
    var listStation = '<?php echo $list_station; ?>';
    var nb_data_all = <?php echo $nb_data_all; ?>;
    var totalTime = 0;

    // Initialisation du textarea 'fileList'
    var fileList = document.getElementById('fileList');    
    
    // Récupérer les données JSON généré par PHP
    var jsonDataInfo = <?php echo json_encode($data_info); ?>;
    var jsonDataCompress = <?php echo json_encode($data_compress); ?>;

    var cheminFolder = '<?php echo $chemin_folder; ?>';
    
    // ------------------------------------------
    // Procédure AJAX pour généré les fichiers CSV

    function executeAjaxExportCsv()
    {   

        // Configuration de la requête AJAX
        var xhr = new XMLHttpRequest();

        xhr.open('POST', 'include/structure/export/process_export_csv.php', true);  
        
        xhr.setRequestHeader('Content-Type', 'application/json');

        // Fin de Procédure AJAX - Compilation de fichiers
        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Traitement de la réponse du serveur
                var reponse = JSON.parse(xhr.responseText);
                totalTime += parseFloat(reponse);
                
                var nbdata_chron_formatted = formatNumberThousandsSeparator(jsonDataInfo[numIteration]['nbdata_chron']);
                fileList.value += jsonDataInfo[numIteration]['init_chron'];
                fileList.value += " - Time : " + reponse + " sec. - Nb Data : " + nbdata_chron_formatted + " \n"; 

                // Descendre automatiquement le scroll du textarea jusqu'en bas
                fileList.scrollTop = fileList.scrollHeight;

                // Mise à jour de la par d'avancement
                pourcentage += jsonDataInfo[numIteration]['nbdata_chron'] / nb_data_all;
                
                document.getElementById('pourcentage_compil').style.width = (pourcentage*100)+'%';
                
                if (numIteration < jsonDataInfo.length - 1) // Vérifier si numIteration dépasse la longueur de $data_info
                {                    
                    numIteration++; // On incrémente pour passer à la chronique suivante
                    executeAjaxExportCsv(); // On lance le prochain traitement d'une chronique
                }
                else
                {
                    //executeAjaxExportXls(); // Trop lent, ce n'est pas efficace
                    
                    fileList.value += "\n";
                    fileList.value += "Tous les fichiers ont été générés - Durée totale du traitement : " + Math.round(totalTime) +" sec. - ";
                    fileList.value += "Nb Data : " + formatNumberThousandsSeparator(nb_data_all);

                    fileList.value += "\n\n--\n";
                    fileList.value += "Compression des fichiers - Veuillez patienter...";

                    fileList.scrollTop = fileList.scrollHeight;

                    lancerCompressionDossier();                  
                }
            }
        };
        
        if(numIteration == 0){fileList.value += "\n";}
        if(jsonDataInfo[numIteration]['id_station'] !== id_station_encours)
        {            
            fileList.value += "\n" + jsonDataInfo[numIteration]['code_station'] + ' - ' + jsonDataInfo[numIteration]['nom_station'] + "\n";
            id_station_encours = jsonDataInfo[numIteration]['id_station'];
        }        

        // Envoi de la requête avec les données JSON
        xhr.send(JSON.stringify(jsonDataInfo[numIteration]));
    }

    // Function pour le téléchargement des Informations Stations
    function downloadStation_xls()
    {		
		// ETAPE 1 : Préparer la création du fichier XLS en envoyant les infos coté Serveur

		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							idTerritoire: idTerritoire,
							listStation: listStation,
							cheminFolder: cheminFolder,
                        };

                        
		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/export/process_station_download_xls.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(dataToSend));
    }

    // Premier appel pour lancer la requête avec les données JSON
    downloadStation_xls(); // On charge les infos Stations pour commencer
    executeAjaxExportCsv();

    // ----------------------------------------------

    // Fonction pour lancer la compression du dossier après la fin de la première procédure AJAX de création des fichiers csv
    function lancerCompressionDossier() 
    {
        var xhrCompress = new XMLHttpRequest();
        /*
        if(zipJS){xhrCompress.open('POST', 'include/structure/export/process_compress_zip.php', true);}
        else{xhrCompress.open('POST', 'include/structure/export/process_compress_tar.php', true);}
        */
        xhrCompress.open('POST', 'include/structure/export/process_compress_tar.php', true);

        xhrCompress.setRequestHeader('Content-Type', 'application/json');

        xhrCompress.onreadystatechange = function() 
        {
            if (xhrCompress.readyState === 4 && xhrCompress.status === 200) 
            {                  
                // Traitement de la réponse du serveur
                var reponse = JSON.parse(xhrCompress.responseText);                                
                fileList.value += reponse;

                // Descendre automatiquement le scroll jusqu'en bas
                fileList.scrollTop = fileList.scrollHeight;

                //document.getElementById('block_download').style.display = 'block';

                saveFileResult();
            }
        };

        // Envoyer la requête AJAX pour compresser le dossier        
        xhrCompress.send(JSON.stringify(jsonDataCompress));
    }

    // Fonction pour lancer l'enregistrement du résultat de l'export dans un fichier texte
    function saveFileResult()
    {
        var text_result =  fileList.value;

        var jsonTextResult = {
                            text_result: text_result,
                            id_user: '<?php echo $id_user; ?>',
                            date_export: '<?php echo $today_sql; ?>',
                            folder_download: '<?php echo addslashes($folder_download); ?>',
                            chemin_folder: '<?php echo $chemin_folder; ?>',
                        };
        
        var xhrResult = new XMLHttpRequest();
        xhrResult.open('POST', 'include/structure/export/process_export_result.php', true);

        xhrResult.setRequestHeader('Content-Type', 'application/json');

        xhrResult.onreadystatechange = function() 
        {
            if (xhrResult.readyState === 4 && xhrResult.status === 200) 
            {                  
                document.getElementById('block_download').style.display = 'block';
            }
        };

        // Envoi de la requête avec les données JSON
        xhrResult.send(JSON.stringify(jsonTextResult));
    }

</script>



<?php


// Fermeture de session 

if($autorisation){regenerer_id($sql_link);}


tep_db_close($sql_link);
tep_session_end();

?>