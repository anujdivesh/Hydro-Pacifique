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
$tab_geo_regionhydro = true;
$message_info = '';

$regionhydro_array = array();

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

    $del_regionhydro = true;
    $sql_regionhydro_station = "SELECT id_station FROM ".TABLE_STATION."
                        WHERE id_regionhydro=".$id_regionhydro."
                        LIMIT 1";
    $regionhydro_station_query = tep_db_query($sql_link,$sql_regionhydro_station);  
    $regionhydro_station = tep_db_fetch_array($regionhydro_station_query);
    if(isset($regionhydro_station)){$del_regionhydro = false;}    

    $regionhydro_array[$id_regionhydro] = array('nom_regionhydro' => $nom_regionhydro,
                                        'description_regionhydro' => $description_regionhydro,  
                                        'del_regionhydro' => $del_regionhydro
                                        );


} 



// Construction des données
$row = 0;
$htmlcode = '';

$htmlcode .= "<div class='table-container' style='float:left;height:70vh;'>";
			
    $htmlcode .= "<table id='table_tri' cellspacing='0' style=''>";
    
        $htmlcode .= "<thead>";
            $htmlcode .= "<tr class='header-row' style='background-color: #eef3f8;'>";
                $htmlcode .= "<th style='width:270px;'>".htmlaccent('Intitulé')."</th>";	
                $htmlcode .= "<th style='width:470px;'>".htmlaccent('Description')."</th>";	
                $htmlcode .= "<th style='width:60px;text-align:center;'>&nbsp;</th>";
            $htmlcode .= "</tr>";
        $htmlcode .= "</thead>";		
        
        // Nouvelle Entrée

        $htmlcode .= "<tr><td colspan='3' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter une Région Hydrologique')."</td></tr>\n";

        $htmlcode .= "<tr>";
        
            $htmlcode .= "<td><input type='text' style='width:250px;border:2px solid #609966;' name='regionhydro_nom_0' ></td>";

            $htmlcode .= "<td><input type='text' style='width:450px;border:2px solid #609966;' name='regionhydro_description_0' ></td>";

            $htmlcode .= "<td>&nbsp;</td>";
            
        $htmlcode .= "<tr>";

        $htmlcode .= "<tr><td colspan='3' class='lignevide'>&nbsp;</td></tr>";

        if(isset($regionhydro_array))
        {   
            // Mise en forme des lignes pour affichage des régions hydrologiques
            foreach($regionhydro_array as $id => $data)
            {				
                if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 

                $htmlcode .= "<tr ".$row_l." id='row_rh_".$id."' >";
                    
                    $htmlcode .= "<td>";
                        $htmlcode .= "<input type='text' style='width:250px;' name='regionhydro_nom_".$id."' value='".$data['nom_regionhydro']."'>\n";
                    $htmlcode .= "</td>";

                    $htmlcode .= "<td>";
                        $htmlcode .= "<input type='text' style='width:450px;' name='regionhydro_description_".$id."' value='".$data['description_regionhydro']."'>\n";
                    $htmlcode .= "</td>";
                    
                    // supprimer
                    $htmlcode .= "<td style='text-align:center;'>";

                        if($data['del_regionhydro'])
                        {
                            $htmlcode .= "<span class='del' title='".htmlaccent('Supprimer')."' onClick=\"delete_regionhydro('".$id."');\">";
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
            $tab_geo_regionhydro = false;
            $message_info .= "Aucune donnée n'a été trouvée";
        }
    
    $htmlcode .= "</table>";

$htmlcode .= "</div>";


// Remplissage du tableau de retour

$responseData = array(
    'tab_geo_regionhydro' => $tab_geo_regionhydro,
    'htmlcode' => $htmlcode,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>