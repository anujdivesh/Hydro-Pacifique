<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Lecture initiale des fichiers et des feuilles si Excel
----------------------------------------
*/

$error = -1;
$new_id = -1;

// Appels à la librairie phpspreadsheet
/*
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
*/


if(isset($_FILES['fileInput'])) 
{
    $fichiers = $_FILES['fileInput'];
    
    // Vérifier s'il y a des fichiers importés avec succès
    if($fichiers['error'][0] === UPLOAD_ERR_OK) 
    {
        // Parcourir les fichiers importés
        for($i = 0; $i < count($fichiers['tmp_name']); $i++) 
        {
            $import_station = true;
            
            $msg_ext_file = '';
            $msg_station = '';
                        
            $nomFichier = $fichiers['name'][$i];
            $cheminTemporaire = $fichiers['tmp_name'][$i];

            $file_code_station = explode('_',$nomFichier);
            $ext_file = substr(strrchr($nomFichier, '.'),1);

            // Spécification du chemin de destination
            $cheminDestination = DIR_WS_DATA.$nomFichier;

            // Avant de lancer une copie du fichier dans le dossier Destination, on vérifie eet on supprime si un fichier du même nom est déjà uploadé. 
            // ça évitera une erreur 
            if(file_exists($cheminDestination)){unlink($cheminDestination);}

            // Enregistrement du fichier Excel dans le dossier de destination
            if(move_uploaded_file($cheminTemporaire, $cheminDestination))
            {
                // CTRL pour extension autorisée
                if(isset($import_files[$ext_file]))
                {
                    // CTRL si la station est référencée
                    if(isset($station_all_array[$file_code_station[0]]))
                    {
                        $id_station_encours = $station_all_array[$file_code_station[0]]['id_station'];                     
                    }
                    else
                    {
                        $msg_station .= htmlaccent('Aucune station n\'a pu être identifiée dans le nom du fichier.');
                        $msg_station .= "<br>";
                        $msg_station .= htmlaccent('Le fichier ne peut pas être importé.');
                        $msg_station .= "<br>";
                        $msg_station .= htmlaccent('Vous pouvez créer une fiche Station à partir de la rubrique \'Station\'.');
                        $msg_station .= "<br>";

                        // Fichier non conforme, supprimez-le
                        if (file_exists($cheminDestination)){unlink($cheminDestination);}

                        $import_station = false;
                    }
                }
                else
                {
                    $msg_ext_file .= htmlaccent('L\'extension de fichier '.$ext_file.' n\'est pas référencée. Le fichier ne peut être importé');
                    $msg_ext_file .= "<br>";

                    // Fichier non conforme, supprimez-le
                    if (file_exists($cheminDestination)){unlink($cheminDestination);}

                    $import_station = false;
                }
            }
            else
            {
                $msg_station .= htmlaccent('Une erreur est survenue sur le serveur lors de l\'importation du fichier.');
                $msg_station .= "<br>";

                $import_station = false;
            }

            $tab_station_import[$file_code_station[0]] = array('file_name' => $nomFichier,
                                                                'import_valid' => $import_station,                                                              
                                                                'msg' => $msg_ext_file.$msg_station
                                                              );    
            

            // Ouverture du fichier si les premières conditions sont passées
            // si Excel lire le nom des feuilles : xxxxxxx_CI_1 . Chaque feuille correspond à une chronique
            
            if($import_station) // Si le fichier et la station sont valides
            {
                if($import_files[$ext_file]['multi_feuil'] > 0)
                {   
                    // Enregistrement du fichier Excel dans le dossier de destination
                    $reader = IOFactory::createReader('Xlsx');
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load($cheminDestination);

                    // Récupérer les noms des feuilles
                    $sheetNames = $spreadsheet->getSheetNames();

                    // Parcourir les noms des feuilles et les afficher
                    foreach ($sheetNames as $sheetName) 
                    {
                        $import_chron = true;
                        
                        $var_sheet_code_chron = '';
                        $msg_datechron = '';
                        $msg_typechron = '';

                        $date_debut_chron = '-';
                        $date_debut_chron_us = '-';
                        $date_fin_chron = '-';
                        $date_fin_chron_us = '-';

                        $lastRow = 0;
                        $chron_dataexit_nb = 0;
                        
                        $sheet_code_chron = explode('_',$sheetName);
                        
                        // Si le nom de la feuil est valide codestation_initchron
                        if(isset($sheet_code_chron[0]) && isset($sheet_code_chron[1]))
                        {
                            // Si le code_station de la feuille correspond au code_station du fichier
                            if($sheet_code_chron[0] == $file_code_station[0])
                            {
                                $var_sheet_code_chron = $sheet_code_chron[1];                                
                                
                                // Si le type de chronique est référencé
                                if(isset($type_chron_array[$var_sheet_code_chron]))
                                {
                                    $id_type_data_station = $station_all_array[$file_code_station[0]]['station_type'];
                                    $id_type_data_chron = $type_chron_array[$var_sheet_code_chron]['id_eq_type_data'];

                                    if($id_type_data_chron == $id_type_data_station)
                                    {
                                        $worksheet = $spreadsheet->getSheetByName($sheetName);
                                        $firtRow = 1;
                                        $lastRow = $worksheet->getHighestRow(); // nombres de ligne sur une feuille
                                                            
                                        //-----------------------------
                                        // DATES
                                        
                                        // Date Ligne 1
                                        
                                        if($entete == 1){$firtRow=2;}

                                        $date_ligne1 = $worksheet->getCell('A' . $firtRow)->getValue();
                                        $dateTime_ligne1 = isValidDateImport($date_ligne1);

                                        if($dateTime_ligne1 === '' || $dateTime_ligne1 === false) 
                                        {
                                            $import_chron = false;
                                        
                                            $msg_datechron .= htmlaccent('Le format de date sur la première ligne du tableau n\'est pas valide.');                           
                                            $msg_typechron .= "\n";	
                                        } else 
                                        {
                                            $date_debut_chron = $dateTime_ligne1->format('d/m/Y H:i:s');                                        
                                            $date_debut_chron_us = $dateTime_ligne1->format('Y-m-d H:i:s'); 
                                        }
                                        

                                        // Date Ligne 2
                                        $date_ligne2 = $worksheet->getCell('A' . $lastRow)->getValue();
                                        $dateTime_ligne2 = isValidDateImport($date_ligne2);
                                        
                                        if($dateTime_ligne2 === '' || $dateTime_ligne2 === false) 
                                        {
                                            $import_chron = false;
                                        
                                            $msg_datechron .= htmlaccent('Le format de date sur la dernière ligne du tableau n\'est pas valide.');                           
                                            $msg_typechron .= "\n";	
                                        } else 
                                        {
                                            $date_fin_chron = $dateTime_ligne2->format('d/m/Y H:i:s');                                        
                                            $date_fin_chron_us = $dateTime_ligne2->format('Y-m-d H:i:s');  
                                        }
                                        
            
                                        // Vérification que la date de début est antérieure à la date de fin   
                                        if($import_chron)
                                        {
                                            if($dateTime_ligne2 < $dateTime_ligne1)
                                            {
                                                $import_chron = false;
                                                
                                                $msg_datechron .= htmlaccent('La date sur la dernière ligne du tableau ne doit pas être antérieure à la date sur la première ligne');                           
                                                $msg_typechron .= "\n";	
                                            }
                                        }
            
                                        //-----------------------------
                                        // Vérification si des données existent pour cette station sur ce type de chronique et dans l'interval indiqué
                                        
                                        if($import_chron)
                                        {
                                            $sql_chron_dataexist = "SELECT COUNT(*) as nb_data
                                                                    FROM ".TABLE_DATA_ALL." da
                                                                    JOIN ".TABLE_DATA_META." dm ON da.id_meta=dm.id
                                                                    WHERE dm.id_station = ".$id_station_encours."
                                                                    AND dm.id_typedata = ".$type_chron_array[$var_sheet_code_chron]['id_data_type']."
                                                                    AND da.dateheure >= '".$date_debut_chron_us."'
                                                                    AND da.dateheure <= '".$date_fin_chron_us."'";
                                            
                                            $chron_dataexist_query = tep_db_query($sql_link,$sql_chron_dataexist); 
                                            $chron_dataexist = tep_db_fetch_array($chron_dataexist_query);
            
                                            $chron_dataexit_nb = $chron_dataexist['nb_data'];
                                        }
                                    }
                                    else
                                    {
                                        $import_chron = false;

                                        $msg_typechron .= htmlaccent('Ce type de chronique ne correspond pas au type de données attendu pour une station '.$eq_type_array[$id_type_data_station]['nom_eq_type']);
                                        $msg_typechron .= "\n";
                                    }    
                                }
                                else
                                {
                                    $import_chron = false;

                                    $msg_typechron .= htmlaccent('Le code de la chronique '.$var_sheet_code_chron.' n\'est pas référencée, la feuille ne peut pas être importée.');
                                    $msg_typechron .= "\n";
                                    $msg_typechron .= htmlaccent('Vous pouvez créer un code de chronique dans la rubrique \'Type de chronique\' du menu.');                            
                                    $msg_typechron .= "\n";
                                }
                            }
                            else
                            {
                                $import_chron = false;

                                $msg_typechron .= htmlaccent('Le nom de la feuille n\'est pas conforme : le code station n\'est pas identique à celui défini dans le nom de fichier.');                            
                                $msg_typechron .= "\n";
                            }
                        }
                        else
                        {
                            $import_chron = false;

                            $msg_typechron .= htmlaccent('Le nom de la feuille n\'est pas conforme : codestation_codechronique (-------_----).');                            
                            $msg_typechron .= "\n";
                        }


                        //-----------------------------
                        // Save Data Chron dans Tab pour affichage
                        ${'chron_'.$file_code_station[0].'_array'}[] = array('feuilExcel' => $sheetName,
                                                                            'import_valid' => $import_chron,
                                                                            'init_type_chron' => $var_sheet_code_chron,
                                                                            'nb_data' => $lastRow,
                                                                            'date_debut' => $date_debut_chron,
                                                                            'date_fin' => $date_fin_chron,
                                                                            'date_debut_us' => $date_debut_chron_us,
                                                                            'date_fin_us' => $date_fin_chron_us,
                                                                            'data_exist' => $chron_dataexit_nb,
                                                                            'msg' => $msg_datechron.$msg_typechron
                                                                            );
                        
                    }

                }
            }    
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
                            
                echo "<span>".htmlaccent('Importation de données - Etape 2 : Sélection des chroniques')."</span>";
                
            echo "</h1>";

            echo "<hr>";
                            
            $lien_form = tep_href_link('import.php');			
            echo "<form name='select_import_step2' action='".$lien_form."' method='post' enctype='multipart/form-data'>";
                
                echo "<input type='hidden' name='entete' id='entete' value='".$entete."' />\n";    
            
                echo "<div id='onglet_contenu'>\n";

                    echo "<div id='boite1' class='first' style='margin-top:5px;margin-left:0px;'>\n";

                        echo "<div style='float:left;padding-left:0px;'>\n";                        

                            echo "<div id='boxpopup' class='select-top' style='width:300px;height:50px;'>\n";
                            
                                echo "<input type='submit' class='button_import' name='b_import_save' value='Importer les chroniques sélectionnées' />";
                    
                            echo "<hr>\n";
                            echo "</div>\n";

                            echo "<hr>\n";


                            foreach($tab_station_import as $key_code_station => $tab_station)
                            {
                                echo "<div id='cadre_data_import' >\n";

                                    // Nom du fichier
                                    echo "<p class='titre_box' style='font-size:12px;margin-bottom:10px;'>";
                                        
                                        echo "<span style='font-weight:normal;font-size:14px;color:#d9534f;'>";
                                            echo htmlaccent('Fichier : ').$tab_station['file_name'];
                                        echo "</span>";
                                        
                                        if($tab_station['import_valid'])
                                        {
                                            echo "<br>";
                                            echo htmlaccent('Station : ').$key_code_station.' - '.$station_all_array[$key_code_station]['nom_station']; // code + nom station ;
                                        }
                                        
                                    echo "</p>\n";


                                    if($tab_station['import_valid'])
                                    {
                                        if(isset(${'chron_'.$key_code_station.'_array'}))
                                        {                                    
                                            echo "<table id='table_tri' cellspacing='0' >\n";
                                                
                                                echo "<thead>\n";
                                                    echo "<tr>\n";
                                                        echo "<th>".htmlaccent('Feuil Excel')."</th>\n";  
                                                        echo "<th>".htmlaccent('Chronique')."</th>\n";  
                                                        echo "<th>".htmlaccent('Unité')."</th>\n";					
                                                        echo "<th>".htmlaccent('Nb data')."</th>\n";						
                                                        echo "<th>".htmlaccent('Date début')."</th>\n";										
                                                        echo "<th>".htmlaccent('Date fin')."</th>\n";	
                                                        echo "<th style='text-align:center;'>".htmlaccent('Données existantes')."</th>\n"; 									
                                                        echo "<th style='font-size:12px;color:#000;text-align:center;cursor:pointer' onclick=\"toggleCheckboxes('".$key_code_station."');\">";
                                                            echo "<span class='selectAll'>".htmlaccent('Select +/-')."</span>";
                                                        echo "</th>\n";
                                                    echo "</tr>\n";
                                                echo "</thead>\n";
                                            
                                                // Ligne vide sans contenu
                                                echo "<tr><td colspan='8' style='height:10px;'></td></tr>\n";    
                                            
                                                for($md=0;$md<sizeof(${'chron_'.$key_code_station.'_array'});$md++)
                                                {
                                                    $row++;
                                                    $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";
                                                    
                                                    
                                                    // IMPORT VALID
                                                    $import_valid = ${'chron_'.$key_code_station.'_array'}[$md]['import_valid'];

                                                    // FEUIL EXCEL
                                                    $feuilExcel = ${'chron_'.$key_code_station.'_array'}[$md]['feuilExcel'];
                                                    
                                                    // pour faciliter la lecture du code des var plus courtes sont utilisées
                                                    $init_chron = ${'chron_'.$key_code_station.'_array'}[$md]['init_type_chron'];

                                                    if(isset($type_chron_array[${'chron_'.$key_code_station.'_array'}[$md]['init_type_chron']]['nom_type_data']))
                                                    {
                                                        $intitule_chron = $type_chron_array[${'chron_'.$key_code_station.'_array'}[$md]['init_type_chron']]['nom_type_data'];
                                                        $unite_chron = $type_chron_array[${'chron_'.$key_code_station.'_array'}[$md]['init_type_chron']]['unite'];
                                                    }
                                                    else
                                                    {
                                                        $intitule_chron = htmlaccent('Inconnu');
                                                        $init_chron = htmlaccent('-');
                                                        $unite_chron = htmlaccent('-');
                                                    }
                                                    
                                                    $nb_data_chron = number_format(${'chron_'.$key_code_station.'_array'}[$md]['nb_data'], 0, '.', ' ');
                                                    
                                                    // DATE DEBUT       
                                                    $date_debut_chron = ${'chron_'.$key_code_station.'_array'}[$md]['date_debut'];
                                                    $date_debut_chron_us = ${'chron_'.$key_code_station.'_array'}[$md]['date_debut_us'];
                                                    
                                                    // DATE FIN         
                                                    $date_fin_chron = ${'chron_'.$key_code_station.'_array'}[$md]['date_fin'];
                                                    $date_fin_chron_us = ${'chron_'.$key_code_station.'_array'}[$md]['date_fin_us'];

                                                    // DATA EXISTANTES
                                                    if(${'chron_'.$key_code_station.'_array'}[$md]['data_exist'] > 0){$data_exist = 'oui';}
                                                    else{$data_exist = 'non';}

                                                    // MESSAGE CHRON
                                                    $msg_chron = ${'chron_'.$key_code_station.'_array'}[$md]['msg'];


                                                    // CHAMPS FORMULAIRE HIDDEN
                                                    echo "<input type='hidden' name='code_station_".$md."' value='".$key_code_station."' />\n";
                                                    echo "<input type='hidden' name='init_chron_".$md."' value='".$init_chron."' />\n";                                                    
                                                    echo "<input type='hidden' name='name_file_".$md."' value='".$tab_station['file_name']."' />\n";
                                                    echo "<input type='hidden' name='name_feuil_".$md."' value='".$feuilExcel."' />\n";
                                                    echo "<input type='hidden' name='date_debut_".$md."' value='".$date_debut_chron_us."' />\n";
                                                    echo "<input type='hidden' name='date_fin_".$md."' value='".$date_fin_chron_us."' />\n";
                                                    
                                                    echo "<tr ".$row_l." >\n"; 
                                                    
                                                        echo "<td class='t_cont_m' style='height:20px;'>".$feuilExcel."</td>\n";

                                                        echo "<td class='t_cont_s' style='height:20px;cursor: pointer;' title='".$intitule_chron."'>".$init_chron."</td>\n";
                                                        echo "<td class='t_cont_s' style='height:20px;'>".$unite_chron."</td>\n";	

                                                        if($nb_data_chron > 0){echo "<td class='t_cont_s' style='height:20px;'>".$nb_data_chron."</td>\n";}
                                                        else{echo "<td class='t_cont_s' style='height:20px;'>-</td>\n";}	

                                                        echo "<td class='t_cont_m' style='height:20px;'>".$date_debut_chron."</td>\n";	
                                                        echo "<td class='t_cont_m' style='height:20px;'>".$date_fin_chron."</td>\n";
                                                        
                                                        // Données existantes
                                                        echo "<td class='t_cont_s' style='height:20px;text-align:center;'>".$data_exist."</td>\n";

                                                        echo "<td class='t_cont_m' style='height:20px;text-align:center;'>\n";
                                                            
                                                            if($import_valid) // Si la chronique est importable
                                                            {
                                                                if($nb_data_chron > 0) 
                                                                {
                                                                    if($data_exist == 'oui')
                                                                    {
                                                                        // Si pas assez de droit alors pas possibilité d'écraser les données   
                                                                        // Variable $id_statut_user définie dans application_top.php permettant de vérifier le niveau d'accréditation
                                                                        if($id_statut_user <= 3)
                                                                        {
                                                                            echo "<input type='checkbox' name='check_chron[]' value='".$key_code_station."_".$md."' >\n";
                                                                        }
                                                                        else
                                                                        {
                                                                            $msg_chron .= htmlaccent('Vous n\'avez pas les droits pour supprimer des données existantes.');
                                                                            echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:16px;margin:0;cursor: pointer;' title='".$msg_chron."'>";
                                                                        }                                                            
                                                                    }
                                                                    else
                                                                    {
                                                                        echo "<input type='checkbox' name='check_chron[]' value='".$key_code_station."_".$md."' >\n";
                                                                    }
                                                                }
                                                                else
                                                                {
                                                                    echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:16px;margin:0;cursor: pointer;' title='".$msg_chron."'>";
                                                                }
                                                            }
                                                            else
                                                            {
                                                                echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:16px;margin:0;cursor: pointer;' title='".$msg_chron."'>";
                                                            }

                                                        echo "</td>\n";	

                                                    echo "</tr>\n";
                                                }	
                                            
                                            echo "</table>\n";    
                                        }
                                    }
                                    else
                                    {
                                        echo "<p style='margin-bottom:0px;'>\n";
                                            echo "<span>".$tab_station['msg']."</span>\n";
                                        echo "</p>\n";
                                    }

                                echo "</div>\n";
                            }    

                        echo "<hr>\n";
                        echo "</div>\n";

                    echo "<hr>\n";
                    echo "</div>\n";		
                        
                echo "<hr>\n";
                echo "</div>\n";	 

            echo "</form >\n";

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

<script id="source" type="text/javascript">

function toggleCheckboxes(id_station) 
{
	let checkboxes;
    checkboxes = document.querySelectorAll('input[type="checkbox"]');
	if (id_station>0) {
		checkboxes = document.querySelectorAll('input[type="checkbox"][value^="'+id_station+'"]'); // value^= signifie dont le texte commence par id_station
	}

	// Vérification de l'état des checkboxes
	let allChecked = true;
	for (let i = 0; i < checkboxes.length; i++) {
		const checkbox = checkboxes[i];
		if (!checkbox.checked) {
		allChecked = false;
		break;
		}
	}

	// Cocher ou décocher tous les éléments de la liste en fonction de l'état actuel
	if (allChecked) {
		for (let i = 0; i < checkboxes.length; i++) {
		const checkbox = checkboxes[i];
		checkbox.checked = false;
		}
	} else {
		for (let i = 0; i < checkboxes.length; i++) {
		const checkbox = checkboxes[i];
		checkbox.checked = true;
		}
	}

}

</script>