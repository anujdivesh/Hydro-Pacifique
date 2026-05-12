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
$tab_geo_commune = true;
$message_info = '';

$commune_array = array();

// Extraction données de la BDD

// Requête sur TABLE_REGION
$sql_regiongeo = "SELECT DISTINCT id_region, nom_region FROM ".TABLE_REGION." WHERE id_territoire=".$territoire_id." ORDER BY LOWER(nom_region) ASC";
$regiongeo_query = tep_db_query($sql_link,$sql_regiongeo);
while ($regiongeo_tab = tep_db_fetch_array($regiongeo_query))
{				
    $id_regiongeo = $regiongeo_tab['id_region'];    
	$nom_region = htmlaccent(html_entity_decode($regiongeo_tab['nom_region'] ?? $default_string));

    $regiongeo_array[$id_regiongeo] = array('nom_region' => $nom_region);
} 

// Requête sur TABLE_COMMUNE
$sql_commune = "SELECT DISTINCT id_commune, nom_commune, id_region FROM ".TABLE_COMMUNE." WHERE id_territoire=".$territoire_id." ORDER BY id_region ASC, LOWER(nom_commune) ASC";
$commune_query = tep_db_query($sql_link,$sql_commune);
while ($commune_tab = tep_db_fetch_array($commune_query))
{				
    $id_commune = $commune_tab['id_commune'];    
	$nom_commune = $commune_tab['nom_commune'];
	$id_region_commune = $commune_tab['id_region'];

    $del_commune = true;
    $sql_commune_station = "SELECT id_station FROM ".TABLE_STATION."
                        WHERE id_commune=".$id_commune."
                        LIMIT 1";
    $commune_station_query = tep_db_query($sql_link,$sql_commune_station);  
    $commune_station = tep_db_fetch_array($commune_station_query);
    if(isset($commune_station)){$del_commune = false;}    

    $commune_array[$id_commune] = array('nom_commune' => $nom_commune,
                                        'id_region_commune' => $id_region_commune,  
                                        'del_commune' => $del_commune
                                        );
} 




// Construction des données
$row = 0;
$htmlcode = '';

$htmlcode .= "<div class='table-container' style='float:left;height:70vh;'>";
			
    $htmlcode .= "<table id='table_tri' cellspacing='0' style=''>";
    
        $htmlcode .= "<thead>";
            $htmlcode .= "<tr class='header-row' style='background-color: #eef3f8;'>";
                $htmlcode .= "<th style='width:270px;'>".htmlaccent('Nom de la commune')."</th>";	
                $htmlcode .= "<th style='width:270px;'>".htmlaccent('Région associée')."</th>";	
                $htmlcode .= "<th style='width:60px;text-align:center;'>&nbsp;</th>";
            $htmlcode .= "</tr>";
        $htmlcode .= "</thead>";		
        
        // Nouvelle Entrée

        $htmlcode .= "<tr><td colspan='3' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter une Commune')."</td></tr>\n";

        $htmlcode .= "<tr>";
        
            $htmlcode .= "<td><input type='text' style='width:250px;border:2px solid #609966;' name='commune_nom_0' ></td>";
            
            $htmlcode .= "<td>";

                $htmlcode .= "<select name='select_commune_regiongeo_0' id='select_commune_regiongeo_0' style='width:250px;border:2px solid #609966;'  >";
                        
                    $selected = '';									
                    if(isset($regiongeo_array))
                    {
                        foreach($regiongeo_array as $key => $value)
                        {
                            $selected = '';											
                            $htmlcode .= "<option value='".$key."' ".$selected." >".$value['nom_region']."</option>";
                        }
                    }
    
                $htmlcode .= "</select>";
    
            $htmlcode .= "</td>";

            $htmlcode .= "<td>&nbsp;</td>";
            
        $htmlcode .= "<tr>";

        $htmlcode .= "<tr><td colspan='3' class='lignevide'>&nbsp;</td></tr>";

        if(isset($commune_array))
        {   
            // Mise en forme des lignes pour affichage des régions hydrologiques
            foreach($commune_array as $id => $data)
            {				
                if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 

                $htmlcode .= "<tr ".$row_l." id='row_c_".$id."' >";
                    
                    $htmlcode .= "<td>";
                        $htmlcode .= "<input type='text' style='width:250px;' name='commune_nom_".$id."' value='".$data['nom_commune']."'>\n";
                    $htmlcode .= "</td>";

                    $htmlcode .= "<td>";

                        $htmlcode .= "<select name='select_commune_regiongeo_".$id."' id='select_commune_regiongeo_".$id."' style='width:250px;'  >";
                        
                            $selected = '';									
                            if(isset($regiongeo_array))
                            {
                                foreach($regiongeo_array as $key => $value)
                                {
                                    if($key == $data['id_region_commune']){$selected="selected";}	
                                    else{$selected = '';}											
                                    $htmlcode .= "<option value='".$key."' ".$selected." >".$value['nom_region']."</option>";
                                }
                            }
            
                        $htmlcode .= "</select>";

                    $htmlcode .= "</td>";
                    
                    // supprimer
                    $htmlcode .= "<td style='text-align:center;'>";

                        if($data['del_commune'])
                        {
                            $htmlcode .= "<span class='del' title='".htmlaccent('Supprimer')."' onClick=\"delete_commune('".$id."');\">";
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
            $tab_geo_commune = false;
            $message_info .= "Aucune donnée n'a été trouvée";
        }
    
    $htmlcode .= "</table>";

$htmlcode .= "</div>";


// Remplissage du tableau de retour

$responseData = array(
    'tab_geo_commune' => $tab_geo_commune,
    'htmlcode' => $htmlcode,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>