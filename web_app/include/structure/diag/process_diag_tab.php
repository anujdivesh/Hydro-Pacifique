<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet d'afficher le tableau des diagraphies pouvant être affichées
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
$jsonDataTab = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataJson = json_decode($jsonDataTab, true);

// Accéder aux données du tableau récupérer
$list_station = $dataJson['listStation'];

$html_text = '';
$nb_station = 1;

// TABLE STATION
$sql_station = "SELECT DISTINCT id_station, nom_station, code_station
					FROM ".TABLE_STATION." 
				    WHERE id_station IN (".$list_station.")";

$station_query = tep_db_query($sql_link,$sql_station);
while ($station = tep_db_fetch_array($station_query))
{	
    $id_station =  $station['id_station'];
    $code_station =  $station['code_station'];
    $nom_station =  $station['nom_station'];
    
    $check = '';
    if($nb_station < 2){$check = 'checked';}

    $html_text .= "
                    <div style='width:90%;margin: 10px 5%;'>

                        <input type='checkbox' style='float:left;' name='check_station_diac[]' value='".$id_station."' ".$check." onClick='checkboxDiagSelect();'>
                        <p class='toggle-diag' data-menu-diag='".$id_station."'  style='font-size:12px;color:#000;padding-top:3px;'>
                            
                            <span>".$code_station." - ".$nom_station ."</span>
                            <span class='arrow' style='cursor:pointer;'>&#9660;</span>
                            
                        </p>
                    ";


        $visible_diag = 'display:none;';
        //if($nb_station < 2){$visible_diag = 'display:block;';}


        $html_text .= "<div class='navdiag' style='".$visible_diag."'>";                    

            $sql_ra_diac = "SELECT DISTINCT r.id_ra, r.date_heure_ra
                            FROM ".TABLE_DATA_RA." r
                            JOIN
                                ".TABLE_DATA_RA_PIEZO_PROFIL." pp ON pp.id_ra = r.id_ra
                            WHERE r.id_station = ".$id_station."
                            ORDER BY date_heure_ra DESC";
            $ra_diac_query = tep_db_query($sql_link,$sql_ra_diac);

            $html_text .="<table id='table_tri' cellspacing='0'>\n";
            
                $row = 0;
                $rowColor=true;
                while ($ra_diac = tep_db_fetch_array($ra_diac_query))    
                {
                    $id_ra =  $ra_diac['id_ra'];
                    $date_heure_ra = $ra_diac['date_heure_ra'];
                    $date_ra = DateTime::createFromFormat('Y-m-d H:i:s', $date_heure_ra);
                    $formatted_date_ra = $date_ra->format('d-m-Y');

                    if(fmod($row,2)==0)
                    {
                        if($rowColor)
                        {
                            $html_text .="<tr style='background-color: #f3f4fa;'>\n";
                            $rowColor=false;
                        }
                        else
                        {
                            $html_text .="<tr style=''>\n";
                            $rowColor=true;
                        }


                    } 

                            $html_text .="<td>".$formatted_date_ra."</td>\n";
                            
                            $check = '';
                            if($nb_station < 2){$check = 'checked';}
                            $html_text .="<td style='text-align:left;width:50px;'>\n";
                                $html_text .="<input type='checkbox' name='check_diag[]' value='".$id_station."_".$id_ra."' ".$check." >\n";
                            $html_text .="</td>\n";


                    if(fmod($row,2)>0){$html_text .= "</tr>\n";}
                    
                    $row++;
                }   

            $html_text .="</table>\n";	
            
        $html_text .= "</div>";
    
    $html_text .= "</div>";

    $nb_station++;
}    


$responseData = array(
    'html_text' => $html_text
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>