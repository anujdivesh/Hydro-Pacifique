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
$today_formatted = $todayTime->format('dmYHi');


// --------------------------------------
// INIT VAR
/*
$today_dateTime = date('YmdHi');
*/

// Création du dossier d'enregistrement des fichiers
$folder_download = $today_formatted . '_xlsx_' . $id_user;

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

foreach($station_chron_array as $cle_station => $typedata_array) 
{       
    // Pour chaque station
    
    $Filename = $station_all_array[$cle_station]['code_station'].'_'.$today_formatted.'.xlsx';

    // Création d'un tableau qui contient toutes les infos nécessaires pour la compilation des données dans les procédures AJAX
    $data_info[] = array(
        'Filename' => $Filename,
        'folder_download' => addslashes($folder_download),
        'chemin_folder' => addslashes($chemin_folder),                                
        'id_station' => $cle_station, 
        'code_station' => $station_all_array[$cle_station]['code_station'], 
        'nom_station' => $station_all_array[$cle_station]['nom_station'], 
        'typedata_array' => json_encode($typedata_array),
        'type_chron_array' => json_encode($type_chron_array),
        'nbdata_chron_array' => json_encode($nbdata_chron_array),
        'quality_array' => json_encode($quality_array),
        'multi_file' => $multi_file,
        'entete_col' => $entete_col
    );
}

$data_compress = array(
                        'folder_download' => addslashes($folder_download),
                        'chemin_folder' => addslashes($chemin_folder)
                        );


// Préparation du fichier si format xlsx


?>

<script>

    var numStation = 0;
    var pourcentage = 0;
    var id_station_encours = 0;

    var format_export = <?php echo json_encode($format_export); ?>;
    var totalTime = 0;

    // Initialisation du textarea 'fileList'
    var fileList = document.getElementById('fileList');    
    
    // Récupérer les données JSON généré par PHP
    var nb_data_all = <?php echo json_encode($nb_data_all_check); ?>;
    var jsonDataInfo = <?php echo json_encode($data_info); ?>;
    var jsonDataCompress = <?php echo json_encode($data_compress); ?>;

    // ------------------------------------------
    // Procédure AJAX pour généré les fichiers Xlsx

    function executeAjaxExportXlsx()
    {   
        // Configuration de la requête AJAX
        var xhr = new XMLHttpRequest();

        xhr.open('POST', 'include/structure/export/process_export_xlsx.php', true);        
        
        xhr.setRequestHeader('Content-Type', 'application/json');

        // Fin de Procédure AJAX - Compilation de fichiers
        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Traitement de la réponse du serveur
                var reponseTab = JSON.parse(xhr.responseText);
                
                // Accéder aux données de la réponse
                var total_data = reponseTab.total_data;
                var nb_chron = reponseTab.nb_chron;
                var station_time = reponseTab.station_time;
                totalTime += parseFloat(station_time);
                
                var nb_chron_formatted = parseFloat(nb_chron).toLocaleString();

                if(numStation == 0){fileList.value += "\n\n";}
                fileList.value += jsonDataInfo[numStation]['code_station'];
                fileList.value += " - Time : " + station_time + " sec. - Nb Chronique : " + nb_chron_formatted + " - Nb Data : " + total_data + " \n"; 

                // Descendre automatiquement le scroll du textarea jusqu'en bas
                fileList.scrollTop = fileList.scrollHeight;

                // Mise à jour du pourcentage d'avancement
                pourcentage += total_data / nb_data_all;
                document.getElementById('pourcentage_compil').style.width = (pourcentage*100)+'%';

                // Vérifier si numStation dépasse la longueur de $data_info
                if (numStation < jsonDataInfo.length - 1) 
                {
                    // On lance le prochain traitement d'une chronique
                    numStation++;
                    executeAjaxExportXlsx();
                }
                else
                {
                    fileList.value += "\n";
                    fileList.value += "Tous les fichiers ont été générés - Durée totale du traitement : " + Math.round(totalTime) +" sec. - ";
                    fileList.value += "Nb Data : " + nb_data_all.toLocaleString();

                    fileList.value += "\n\n--\n";
                    fileList.value += "Compression des fichiers - Veuillez patienter...";

                    fileList.scrollTop = fileList.scrollHeight;

                    lancerCompressionDossier();
                }
            }
        };
        
        // Envoi de la requête avec les données JSON
        xhr.send(JSON.stringify(jsonDataInfo[numStation]));
    }
  
    // Premier appel pour lancer la requête avec les données JSON
    executeAjaxExportXlsx();

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

                document.getElementById('block_download').style.display = 'block';
            }
        };

        // Envoyer la requête AJAX pour compresser le dossier        
        xhrCompress.send(JSON.stringify(jsonDataCompress));
    }


</script>






<?php


// Fermeture de session 

if($autorisation){regenerer_id($sql_link);}


tep_db_close($sql_link);
tep_session_end();

?>