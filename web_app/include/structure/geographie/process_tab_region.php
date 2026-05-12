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
$tab_geo_region = true;
$message_info = '';

$regiongeo_array = array();

// Extraction données de la BDD

// Requête sur TABLE_TERRITOIRE
$sql_territoire = "SELECT DISTINCT nom_territoire, init_territoire, theme_region, region_default FROM ".TABLE_TERRITOIRE." WHERE id_territoire=".$territoire_id." ORDER BY LOWER(nom_territoire) ASC";
$territoire_query = tep_db_query($sql_link,$sql_territoire);
while ($territoire = tep_db_fetch_array($territoire_query))
{				
	$nom_territoire = $territoire['nom_territoire'];
	$init_territoire = $territoire['init_territoire'];
	$theme_region = $territoire['theme_region'];
	$region_default = $territoire['region_default'];
} 

// Requête sur TABLE_REGION
$sql_regiongeo = "SELECT DISTINCT id_region, nom_region FROM ".TABLE_REGION." WHERE id_territoire=".$territoire_id." ORDER BY LOWER(nom_region) ASC";
$regiongeo_query = tep_db_query($sql_link,$sql_regiongeo);
while ($regiongeo_tab = tep_db_fetch_array($regiongeo_query))
{				
    $id_regiongeo = $regiongeo_tab['id_region'];    
	$nom_region = $regiongeo_tab['nom_region'];

    $del_regiongeo = true;
    $sql_regiongeo_station = "SELECT id_station FROM ".TABLE_STATION."
                        WHERE id_region=".$id_regiongeo."
                        LIMIT 1";
    $regiongeo_station_query = tep_db_query($sql_link,$sql_regiongeo_station);  
    $regiongeo_station = tep_db_fetch_array($regiongeo_station_query);
    if(isset($regiongeo_station)){$del_regiongeo = false;}    

    $regiongeo_array[$id_regiongeo] = array('nom_region' => $nom_region,
                                            'del_regiongeo' => $del_regiongeo
                                            );
} 




// Construction des données
$row = 0;
$htmlcode = '';

$htmlcode .= "<div class='table-container' style='float:left;height:70vh;'>";

    $htmlcode .= "<table id='table_tri' cellspacing='0' style=''>";
    
        $htmlcode .= "<thead>";
            $htmlcode .= "<tr>";
                $htmlcode .= "<th style='width:270px;'>".htmlaccent('Intitulé - '.$theme_region)."</th>";	
                $htmlcode .= "<th style='width:60px;text-align:center;'>&nbsp;</th>";
            $htmlcode .= "</tr>";
        $htmlcode .= "</thead>";			
            
        // Nouvelle Entrée

        $htmlcode .= "<tr><td colspan='2' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter - '.$theme_region)."</td></tr>\n";

        $htmlcode .= "<tr>";
        
            $htmlcode .= "<td><input type='text' style='width:250px;border:2px solid #609966;' name='regiongeo_nom_0' ></td>";
            
            $htmlcode .= "<td>&nbsp;</td>";
            
        $htmlcode .= "<tr>";

        $htmlcode .= "<tr><td colspan='2' class='lignevide'>&nbsp;</td></tr>";
        
        if(isset($regiongeo_array))
        {
            // Mise en forme des lignes pour affichage des régions hydrologiques
            foreach ($regiongeo_array as $id => $data) 
            {				
                if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 

                $htmlcode .= "<tr ".$row_l."  id='row_rg_".$id."' >";
                    
                    $htmlcode .= "<td>";
                        $htmlcode .= "<input type='text' style='width:250px;' name='regiongeo_nom_".$id."' value='".$data['nom_region']."'>\n";
                    $htmlcode .= "</td>";

                    // Supprimer
                    $htmlcode .= "<td style='text-align:center;'>";

                        if($data['del_regiongeo'])
                        {
                            $htmlcode .= "<span class='del' title='".htmlaccent('Supprimer')."' onClick=\"delete_regiongeo('".$id."');\">";
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
            $tab_geo_region = false;
            $message_info .= "Aucune donnée n'a été trouvée";
        }
        
    $htmlcode .= "</table>";

$htmlcode .= "</div>\n";


// Remplissage du tableau de retour

$responseData = array(
    'tab_geo_region' => $tab_geo_region,
    'htmlcode' => $htmlcode,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>