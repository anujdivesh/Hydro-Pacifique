<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Enregistrement des données dans la base
----------------------------------------
*/

// Appels à la librairie phpspreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$msg_import = '';
$meta_id_encours = 0;

// Récupérer le dernier id_meta 
$sql_meta_id = "SELECT MAX(id) as last_id FROM ".TABLE_DATA_META;
$meta_id_query = tep_db_query($sql_link,$sql_meta_id);
$meta_id_tab = tep_db_fetch_array($meta_id_query);        
if($meta_id_tab['last_id'] > 0){$meta_id_encours = $meta_id_tab['last_id'];}

if(!empty($_POST['check_chron']))
{
    // Boucle pour parcourir toutes les cases à cocher cochées        
    foreach($_POST['check_chron'] as $num_check_chron)
    {        
        $data_valid_chron = true;
        $nb_error = 0;
        $msg_erreur_import = '';

        $tab_num_check_chron = explode('_',$num_check_chron);
        $num_chron = $tab_num_check_chron[1];
        
        $code_station = post_secure($sql_link,$_POST['code_station_'.$num_chron]); 
        $init_chron = post_secure($sql_link,$_POST['init_chron_'.$num_chron]); 
        $name_file = post_secure($sql_link,$_POST['name_file_'.$num_chron]); 
        $name_feuil = post_secure($sql_link,$_POST['name_feuil_'.$num_chron]); 
        $dateDebut_us = post_secure($sql_link,$_POST['date_debut_'.$num_chron]); 
        $dateFin_us = post_secure($sql_link,$_POST['date_fin_'.$num_chron]); 

        // Spécification du chemin de destination
        $cheminDestination = DIR_WS_DATA.$name_file;

        // Enregistrement du fichier Excel dans le dossier de destination (On va adapter pour que l'on puisse avoir d'autre type de fichier)
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($cheminDestination);

        $worksheet = $spreadsheet->getSheetByName($name_feuil);
        $lastRow = $worksheet->getHighestRow(); // nombres de ligne sur une feuille

        $data_tab = array();
        $meta_tab = array();
        $list_quality_encours = array();

        $firtRow=1;
        if($entete == 1){$firtRow=2;}

        for($r=$firtRow;$r<=$lastRow;$r++)
        {
            $data_valid_ligne = true;
            $msg_erreur_import_details = '';

            // Date + validation format
            $date_cell = $worksheet->getCell('A'.$r)->getValue();

            $dateTime_cell = isValidDateImport($date_cell);

            if($dateTime_cell === '' || $dateTime_cell === false) 
            {
                // la date n'est pas valide
                $msg_erreur_import_details .= htmlaccent('Le format de date n\'est pas valide.');

                $data_valid_ligne = false;
            }
            else
            {
                $date_cell_us = $dateTime_cell->format('Y-m-d H:i:s');                
            }

            // Valeur + validation numéric et décimal
            $valeur_cell = $worksheet->getCell('B'.$r)->getValue();
            if(isDecimal($valeur_cell))
            {
                $valeur_cell = str_replace(',', '.', $valeur_cell);

                // Formater le nombre avec 4 décimales et sans séparateur de milliers
                $valeur_cell = number_format((float)$valeur_cell, 4, '.', '');
            }
            else
            {
                // la valeur n'est pas valide
                if(!$data_valid_ligne){$msg_erreur_import_details .= " - ";}
                $msg_erreur_import_details .= htmlaccent('La donnée n\'est pas une valeur numérique.');

                $data_valid_ligne = false;
            }

            // Quality + référencement ?    
            $quality_cell = $worksheet->getCell('C'.$r)->getValue();
            if(isset($quality_data_array[$quality_cell]))
            {
                $id_quality_cell = $quality_data_array[$quality_cell]['id_data_qualite'];

                if(isset($list_quality_encours[$id_quality_cell])) 
                {
                    $new_meta = false;
                }
                else
                {
                    $meta_id_encours++;
                    $list_quality_encours[$id_quality_cell] = $meta_id_encours;
                    $new_meta = true;
                }
            }
            else
            {
                // la quality n'est pas référencée
                if(!$data_valid_ligne){$msg_erreur_import_details .= " - ";}
                $msg_erreur_import_details .= htmlaccent('Le code qualité n\'est pas référencé.');

                $data_valid_ligne = false;
            }

            if($data_valid_ligne)
            {
                if($new_meta)
                {
                    $meta_tab[] = array('id' => $list_quality_encours[$id_quality_cell],
                                        'station' => $station_all_array[$code_station]['id_station'],
                                        'chron' => $type_chron_array[$init_chron]['id_data_type'],
                                        'quality' => $id_quality_cell,
                                        'user' => $id_user,
                                        'source' => 'Import',
                                        'file' => $name_file,
                                        'obs' => '',
                                    );
                }
                
                $data_tab[] = array('date' => $date_cell_us,
                                  'valeur' => $valeur_cell,												
                                  'meta_id' => $list_quality_encours[$id_quality_cell]
                                 );            
            }
            else
            {
                $data_valid_chron = false;
                $nb_error++;

                $msg_erreur_import .= "<br>";
                $msg_erreur_import .= htmlaccent('Ligne '.$r.' : ');
                $msg_erreur_import .= $msg_erreur_import_details;
                
            }
        }

        // ENREGISTREMENT DES DONNEES DANS LA BASE        
        if($data_valid_chron)
        {
            // On efface les données entre Date_Debut et Date_Fin chronique, qu'elles existent ou pas
            $rows_deleted = deleteDataAndMeta($sql_link,$station_all_array[$code_station]['id_station'], $type_chron_array[$init_chron]['id_data_type'], $dateDebut_us, $dateFin_us);
            
            
            // Préparation de la requête d'insertion en bloc DATA_ALL   
            $query_insert_bloc_data = "INSERT INTO ".TABLE_DATA_ALL." (dateheure, valeur, id_meta) VALUES (?, ?, ?)";
            
            // Vérification de la préparation de la requête
            if ($stmt = mysqli_prepare($sql_link, $query_insert_bloc_data)) 
            {
                // Lissage des paramètres et exécution de la requête en boucle
                foreach ($data_tab as $row)
                {
                    mysqli_stmt_bind_param($stmt, 'sdi', $row['date'], $row['valeur'], $row['meta_id']);
                    if (!mysqli_stmt_execute($stmt)) {
                        $msg_erreur_import .= "<br>";
                        $msg_erreur_import .= htmlaccent('Erreur lors de l\'enregistrement dans base : ') . mysqli_stmt_error($stmt);
                    }
                }
                // Fermeture de la requête
                mysqli_stmt_close($stmt);                
            } else
            {
                $msg_erreur_import .= "<br>";
                $msg_erreur_import .= htmlaccent('Erreur lors de l\'enregistrement dans base : ') . mysqli_error($sql_link);
            }


            // Préparation de la requête d'insertion en bloc DATA_META
            $query_insert_bloc_meta = "INSERT INTO ".TABLE_DATA_META." (id, id_station, id_typedata, id_codequal, id_user, source, file, obs) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($sql_link, $query_insert_bloc_meta)) 
            {
                // Liage des paramètres et exécution de la requête en boucle
                foreach ($meta_tab as $row)
                {
                    mysqli_stmt_bind_param($stmt, 'iiiiisss', $row['id'], $row['station'], $row['chron'], $row['quality'], $row['user'], $row['source'], $row['file'], $row['obs']);
                    if (!mysqli_stmt_execute($stmt)) {
                        $msg_erreur_import .= "<br>";
                        $msg_erreur_import .= htmlaccent('Erreur lors de l\'enregistrement dans base : ') . mysqli_stmt_error($stmt);
                    }
                }
                // Fermeture de la requête
                mysqli_stmt_close($stmt);
            } else
            {
                $msg_erreur_import .= "<br>";
                $msg_erreur_import .= htmlaccent('Erreur lors de l\'enregistrement dans base : ') . mysqli_error($sql_link);
            }
            
        }
        

        $msg_import .= "<span style='font-weight:bold;'>".htmlaccent('Fichier : ')."</span>".$name_file;
        $msg_import .= "<br>";
        $msg_import .= "<span style='font-weight:bold;'>".htmlaccent('Station : ')."</span>".$code_station.' - '.$station_all_array[$code_station]['nom_station'];
        $msg_import .= "<br>";
        $msg_import .= "<span style='font-weight:bold;'>".htmlaccent('Chronique : ')."</span>".$init_chron;
        $msg_import .= "<br><br>";
        
        if($data_valid_chron)
        {
            $msg_import .= htmlaccent('Les données ont été importées avec succés - ').$lastRow.htmlaccent(' lignes');
            $msg_import .= "<br>";
            if($rows_deleted > 0)
            {
                $msg_import .= htmlaccent('Nombre de données supprimées : ').$rows_deleted;
            }
            $msg_import .= "<br><br>--<br>";

            if(file_exists($cheminDestination)){unlink($cheminDestination);}
            
        }
        else
        {
            $msg_import .= htmlaccent('Les données n\'ont pas pu être importées');
            $msg_import .= "<br>-<br>"; 
            $msg_import .= $nb_error.htmlaccent(' Erreur(s)');
            $msg_import .= "<br>"; 
            $msg_import .= $msg_erreur_import;
            $msg_import .= "<br><br>--------------------------------------------------------------------<br><br>"; 
        }
    }
}


// --------------------------------------
// Debut page HTML

require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu


echo "<div id='contour_general'>";
	
	//if(tep_not_null($message_info)){echo "<div id='contenu_info'>".$message_info."</div>";}
	
	echo "<div id='contenu_centre'>";
		
		echo "<div id='contenu_box2'>";

            echo "<h1>";
                            
                echo "<span>".htmlaccent('Importation de données - Etape 3 : Résumé')."</span>";
                
            echo "</h1>";

            echo "<hr>";
                            
            echo "<div id='onglet_contenu'>\n";

                    echo "<div id='boite1' class='first' style='margin-top:5px;margin-left:0px;'>\n";

                        echo "<p style='margin-bottom:0px;'>";
                            echo "<span style='font-size:14px;font-weight: bold;color:#000;'>".htmlaccent('Log de l\'importation')."</span>";
                        echo "</p>";    
                        
                        echo "<div id='cadre_data_import' >\n";

                            echo $msg_import;

                        echo "<hr>\n";
                        echo "</div>\n";		
                        
                    echo "<hr>\n";
                    echo "</div>\n";	

            echo "<hr>\n";
            echo "</div>\n";

        echo "<hr>\n";
        echo "</div>\n";
	
	echo "<hr>";
	echo "</div>";
	
echo "<hr>";
echo "</div>";

	
require('include/application_bottom.php'); 
	
echo "</body>";

echo "</html>";
									

?>
