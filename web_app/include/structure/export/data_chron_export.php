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


// --------------------------------------
// INIT VAR

// Création du dossier d'enregistrement des fichiers
if($format_export == 'csv'){$folder_download = $today_formatted . '_csv_' . $id_user;}
if($format_export == 'xlsx'){$folder_download = $today_formatted . '_xlsx_' . $id_user;}


$chemin_folder = DIR_WS_DATA_EXPORT . $folder_download;
//echo "<script>alert('".$chemin_folder."')</script>";


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

                    echo "<img src='".DIR_WS_IMG_ICO."wait.gif' style='float:right;width:40px;' id='img_wait'/>";
                    
                                    
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

foreach($station_chron_array as $cle_station => $typedata_array) 
{       
    // Pour chaque station
    $count = count($typedata_array);
    $n = 0; // Initialisez le compteur de tours


    foreach($typedata_array as $typedata_chron => $sql_chron) 
    {
        $n++; // Incrémentez le compteur de tours

        // Vérifiez si c'est le premier ou le dernier tour de la boucle
        $is_first = ($n === 1) ? true : false;
        $is_last = ($n === $count) ? true : false;

        // Nom du fichier de sortie (CSV ou XLSX)
        if($format_export == 'csv')
        {$Filename = $station_all_array[$cle_station]['code_station'].'_'.$type_chron_array[$typedata_chron]['init_type_data'].'_'.$today_formatted.'.csv';}
        else
        {$Filename = $station_all_array[$cle_station]['code_station'].'_'.$today_formatted.'.xlsx';}
        
        // Création d'un tableau qui contient toutes les infos nécessaires pour la compilation des données dans les procédures AJAX
        $data_info[] = array(
                                'Filename' => $Filename,
                                'folder_download' => addslashes($folder_download),
                                'chemin_folder' => addslashes($chemin_folder),                                
                                'id_station' => $cle_station, 
                                'code_station' => $station_all_array[$cle_station]['code_station'], 
                                'nom_station' => $station_all_array[$cle_station]['nom_station'], 
                                'init_chron' => $type_chron_array[$typedata_chron]['init_type_data'], 
                                'sql_chron' => $sql_chron,                                    
                                'nbdata_chron' => $nbdata_chron_array[$cle_station][$typedata_chron],  
                                'quality_array' => json_encode($quality_array),
                                'entete_col' => $entete_col,
                                'num_chron' => $n,
                                'is_first' => $is_first,
                                'is_last' => $is_last
                            );

        $nb_data_all += $nbdata_chron_array[$cle_station][$typedata_chron];
    }
}

$data_compress = array(
                        'folder_download' => addslashes($folder_download),
                        'chemin_folder' => addslashes($chemin_folder)
                        );


// Préparation du fichier si format xlsx


?>

<script>

    var numIteration = 0;
    var pourcentage = 0;
    var id_station_encours = 0;

    var format_export = <?php echo json_encode($format_export); ?>;
    var nb_data_all = <?php echo $nb_data_all; ?>;
    var totalTime = 0;

    // Initialisation du textarea 'fileList'
    var fileList = document.getElementById('fileList');    
    
    // Récupérer les données JSON généré par PHP
    var jsonDataInfo = <?php echo json_encode($data_info); ?>;
    var jsonDataCompress = <?php echo json_encode($data_compress); ?>;
    //if(format_export == 'xlsx'){xhr.open('POST', 'include/structure/export/process_export_xlsx.php', true);}

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
                
                var nbdata_chron_formatted = parseFloat(jsonDataInfo[numIteration]['nbdata_chron']).toLocaleString();
                fileList.value += jsonDataInfo[numIteration]['init_chron'];
                fileList.value += " - Time : " + reponse + " sec. - Nb Data : " + nbdata_chron_formatted + " \n"; 

                // Descendre automatiquement le scroll du textarea jusqu'en bas
                fileList.scrollTop = fileList.scrollHeight;

                // Mise à jour de la par d'avancement
                pourcentage += jsonDataInfo[numIteration]['nbdata_chron'] / nb_data_all;
                
                document.getElementById('pourcentage_compil').style.width = (pourcentage*100)+'%';

                // Vérifier si numIteration dépasse la longueur de $data_info
                if (numIteration < jsonDataInfo.length - 1) 
                {
                    // Récupérer les données JSON généré par PHP pour l'export suivant
                    
                    // On lance le prochain traitement d'une chronique
                    numIteration++;
                    executeAjaxExportcsv();
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
        
        if(numIteration == 0){fileList.value += "\n";}
        if(jsonDataInfo[numIteration]['id_station'] !== id_station_encours)
        {            
            fileList.value += "\n" + jsonDataInfo[numIteration]['nom_station'] + "\n";
            id_station_encours = jsonDataInfo[numIteration]['id_station'];
        }        

        // Envoi de la requête avec les données JSON
        xhr.send(JSON.stringify(jsonDataInfo[numIteration]));
    }

    // ------------------------------------------
    // Procédure AJAX pour généré les fichiers Xlsx

    function executeAjaxExportXlsx()
    {   
        // Configuration de la requête AJAX
        var xhr = new XMLHttpRequest();

        xhr.open('POST', 'include/structure/export/process_export_xlsx_spout.php', true);        
        
        xhr.setRequestHeader('Content-Type', 'application/json');

        // Fin de Procédure AJAX - Compilation de fichiers
        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Traitement de la réponse du serveur
                var reponse = JSON.parse(xhr.responseText);
                totalTime += parseFloat(reponse);
                
                //var nbdata_chron_formatted = parseFloat(jsonDataInfo[numIteration]['nbdata_chron']).toLocaleString();
                //fileList.value += jsonDataInfo[numIteration]['init_chron'];
                //fileList.value += " - Time : " + reponse + " sec. - Nb Data : " + nbdata_chron_formatted + " \n"; 
                fileList.value += " - Time : " + reponse + " sec. \n"; 

                // Descendre automatiquement le scroll du textarea jusqu'en bas
                fileList.scrollTop = fileList.scrollHeight;

                // Mise à jour de la par d'avancement
                //pourcentage += jsonDataInfo[numIteration]['nbdata_chron'] / nb_data_all;
                
                document.getElementById('pourcentage_compil').style.width = (pourcentage*100)+'%';

                // Vérifier si numIteration dépasse la longueur de $data_info
                if (numStation < jsonDataInfo.length - 1) 
                {
                    // Récupérer les données JSON généré par PHP pour l'export suivant
                    
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
        
        if(numIteration == 0){fileList.value += "\n";}
        if(jsonDataInfo[numIteration]['id_station'] !== id_station_encours)
        {            
            fileList.value += "\n" + jsonDataInfo[numIteration]['nom_station'] + "\n";
            id_station_encours = jsonDataInfo[numIteration]['id_station'];
        }        

        // Envoi de la requête avec les données JSON
        xhr.send(JSON.stringify(jsonDataInfo[numIteration]));
    }
  
    // Premier appel pour lancer la requête avec les données JSON
    if(format_export == 'csv'){executeAjaxExportCsv();}
    if(format_export == 'xlsx'){executeAjaxExportXlsx();}

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

                document.getElementById('img_wait').style.display = 'none';
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