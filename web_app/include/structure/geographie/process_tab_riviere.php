<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer une région géographique
Appelé depuis gestion_geo.php -> pform_geo_regiongeo.php
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

$territoire_id = $dataInfo['territoireId'];

// Initialisation Variables
$tab_geo_riviere = true;
$message_info = '';

$riviere_array = array();

// Extraction données de la BDD

// Requête sur TABLE_REGIONHYDRO
$sql_regionhydro = "SELECT DISTINCT id, nom, description 
                    FROM ".TABLE_REGIONHYDRO." 
                    WHERE id_territoire=".$territoire_id." ORDER BY LOWER(nom) ASC";
$regionhydro_query = tep_db_query($sql_link,$sql_regionhydro);
while ($regionhydro = tep_db_fetch_array($regionhydro_query))
{	
    $id_regionhydro = $regionhydro['id'];    
	$nom_regionhydro = $regionhydro['nom'];
	$description_regionhydro= $regionhydro['description'];
    if(isset($regionhydro_station)){$del_regionhydro = false;}    

    $regionhydro_array[$id_regionhydro] = array('nom_regionhydro' => $nom_regionhydro,
                                                'description_regionhydro' => $description_regionhydro
                                                );
} 

// Requête sur TABLE_RIVIERE
$sql_riviere = "SELECT DISTINCT id, nom, description, id_regionhydro, id_territoire FROM ".TABLE_RIVIERE." WHERE id_territoire=".$territoire_id." ORDER BY LOWER(nom) ASC";
$riviere_query = tep_db_query($sql_link,$sql_riviere);
while ($riviere_tab = tep_db_fetch_array($riviere_query))
{				
    $id_riviere = $riviere_tab['id'];    
	$nom_riviere = $riviere_tab['nom']; 
	$description_riviere = $riviere_tab['description'];
	$id_regionhydro_riviere = $riviere_tab['id_regionhydro'];

    $del_riviere = true;
    $sql_riviere_station = "SELECT id_station FROM ".TABLE_STATION."
                        WHERE id_riviere=".$id_riviere."
                        LIMIT 1";
    $riviere_station_query = tep_db_query($sql_link,$sql_riviere_station);  
    $riviere_station = tep_db_fetch_array($riviere_station_query);
    if(isset($riviere_station)){$del_riviere = false;}    

    $riviere_array[$id_riviere] = array('nom_riviere' => $nom_riviere,
                                        'description_riviere' => $description_riviere,
                                        'id_regionhydro_riviere' => $id_regionhydro_riviere,  
                                        'del_riviere' => $del_riviere
                                        );
} 







// Construction des données
$row = 0;
$htmlcode = '';

$htmlcode .= "<div class='table-container' style='float:left;height:70vh;'>";
			
    $htmlcode .= "<table id='table_tri' cellspacing='0' style=''>";
    
        $htmlcode .= "<thead>";
            $htmlcode .= "<tr class='header-row' style='background-color: #eef3f8;'>";
                $htmlcode .= "<th style='width:270px;'>".htmlaccent('Nom de la rivière')."</th>";	
                $htmlcode .= "<th style='width:370px;'>".htmlaccent('Description')."</th>";	
                $htmlcode .= "<th style='width:270px;'>".htmlaccent('Région Hydrologique associée')."</th>";	
                $htmlcode .= "<th style='width:60px;text-align:center;'>&nbsp;</th>";
            $htmlcode .= "</tr>";
        $htmlcode .= "</thead>";		
        
        // Nouvelle Entrée

        $htmlcode .= "<tr><td colspan='3' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter une Rivière')."</td></tr>\n";

        $htmlcode .= "<tr>";
        
            $htmlcode .= "<td><input type='text' style='width:250px;border:2px solid #609966;' name='riviere_nom_0' ></td>";

            $htmlcode .= "<td><input type='text' style='width:350px;border:2px solid #609966;' name='riviere_description_0' ></td>";
            
            $htmlcode .= "<td>";

                $htmlcode .= "<select name='select_riviere_regionhydro_0' id='select_riviere_regionhydro_0' style='width:250px;border:2px solid #609966;'  >";
                        
                    $selected = '';									
                    if(isset($regionhydro_array))
                    {
                        foreach($regionhydro_array as $key => $value)
                        {
                            $selected = '';											
                            $htmlcode .= "<option value='".$key."' ".$selected." >".$value['nom_regionhydro']."</option>";
                        }
                    }
    
                $htmlcode .= "</select>";
    
            $htmlcode .= "</td>";

            $htmlcode .= "<td>&nbsp;</td>";
            
        $htmlcode .= "<tr>";

        $htmlcode .= "<tr><td colspan='3' class='lignevide'>&nbsp;</td></tr>";

        if(isset($riviere_array))
        {   
            // Mise en forme des lignes pour affichage des régions hydrologiques
            foreach($riviere_array as $id => $data)
            {				
                if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 

                $htmlcode .= "<tr ".$row_l." id='row_r_".$id."' >";
                    
                    $htmlcode .= "<td>";
                        $htmlcode .= "<input type='text' style='width:250px;' name='riviere_nom_".$id."' value='".$data['nom_riviere']."'>\n";
                    $htmlcode .= "</td>";

                    $htmlcode .= "<td>";
                        $htmlcode .= "<input type='text' style='width:350px;' name='riviere_description_".$id."' value='".$data['description_riviere']."'>\n";
                    $htmlcode .= "</td>";

                    $htmlcode .= "<td>";

                        $htmlcode .= "<select name='select_riviere_regionhydro_".$id."' id='select_riviere_regionhydro_".$id."' style='width:250px;'  >";
                        
                            $selected = '';									
                            if(isset($regionhydro_array))
                            {
                                foreach($regionhydro_array as $key => $value)
                                {
                                    if($key == $data['id_regionhydro_riviere']){$selected="selected";}	
                                    else{$selected = '';}											
                                    $htmlcode .= "<option value='".$key."' ".$selected." >".$value['nom_regionhydro']."</option>";
                                }
                            }            
                        $htmlcode .= "</select>";

                    $htmlcode .= "</td>";
                    
                    // supprimer
                    $htmlcode .= "<td style='text-align:center;'>";

                        if($data['del_riviere'])
                        {
                            $htmlcode .= "<span class='del' title='".htmlaccent('Supprimer')."' onClick=\"delete_riviere('".$id."');\">";
                                $htmlcode .= "X";
                            $htmlcode .= "</span>";
                        }
                        else{$htmlcode .= "<span>-</span>";}
                    
                    $htmlcode .= "</td>\n";
                
                $htmlcode .= "</tr>";					
            }
        }
        else
        {
            $tab_geo_riviere = false;
            $message_info .= "Aucune donnée n'a été trouvée";
        }
    
    $htmlcode .= "</table>";

$htmlcode .= "</div>";


// Remplissage du tableau de retour

$responseData = array(
    'tab_geo_riviere' => $tab_geo_riviere,
    'htmlcode' => $htmlcode,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>