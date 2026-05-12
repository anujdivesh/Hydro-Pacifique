<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet d'afficher le tableau des ETL lié à une station
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
$jsonDataGraph = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataJson = json_decode($jsonDataGraph, true);

// Accéder aux données du tableau récupérer
$id_station = $dataJson['idStation'];


// Récupération des données ETL dans la table TABLE_DATA_ETL

$nb_etl=0;
$first_id_etl=0;
$ETL_array = []; // Syntaxe moderne

$sql_ETL = "SELECT DISTINCT id, datetime_first, datetime_end
            FROM ".TABLE_DATA_ETL." etl
            WHERE id_station=".$id_station."
            ORDER BY datetime_end DESC";
$ETL_query = tep_db_query($sql_link,$sql_ETL);
while($ETL_tab = tep_db_fetch_array($ETL_query))
{
    $id_etl = $ETL_tab['id'];

    $dateAuFormatInitial = $ETL_tab['datetime_first'];
    $dateObjet = new DateTime($dateAuFormatInitial);
    $datetime_first = $dateObjet->format("d-m-Y H:i:s");        
    $date_first = $dateObjet->format("d-m-Y");

    $datetime_end = '-';
    $date_end = '-';

    $dateAuFormatInitial = $ETL_tab['datetime_end'];        
    if(tep_not_null($dateAuFormatInitial))
    {
        $dateObjet = new DateTime($dateAuFormatInitial);
        $datetime_end = $dateObjet->format("d-m-Y H:i:s");   
        $date_end = $dateObjet->format("d-m-Y");
    }

    $ETL_array[$id_etl] = array('datetime_first' => $datetime_first,
                                'date_first' => $date_first,
                                'datetime_end' => $datetime_end,
                                'date_end' => $date_end,
                                );

    if($nb_etl < 1){$first_id_etl=$id_etl;}                                    
    $nb_etl++;
}

$html_text = '';
$html_text .="<div class='table-container' style='height:45vh;padding-right:10px;'>\n";

    if($nb_etl > 0)
    {
        $html_text .="<table id='table_tri' cellspacing='0'>\n";
                                
            $html_text .="<thead>\n";
                $html_text .="<tr class='header-row'>\n";
                    $html_text .="<th style='width:10%;font-size:11px;'>".htmlaccent('Ref.')."</th>\n";
                    $html_text .="<th style='width:40%;font-size:11px;'>".htmlaccent('Date Début')."</th>\n";
                    $html_text .="<th style='width:40%;font-size:11px;'>".htmlaccent('Date Fin')."</th>\n";
                    $html_text .="<th style='width:10%;font-size:11px;color:#000;text-align:center;cursor:pointer' >\n";	
                        $html_text .="<span class='selectAll'>".htmlaccent('Select')."</span>\n";
                    $html_text .="</th>\n";  				 
                $html_text .="</tr>\n";
            $html_text .="</thead>\n";

            if($nb_etl > 0)
            {
                $row = 1;                                      
                foreach ($ETL_array as $key => $value)
                {        
                    if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                    else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
                    
                    $html_text .="<tr ".$row_l.">\n";

                        $html_text .="<td style='padding-left:5px;' >".$row."</td>\n";
                        $html_text .="<td style='color:#016A70;'>".$value['datetime_first']."</td>\n";
                        $html_text .="<td style='color:#016A70;'>".$value['datetime_end']."</td>\n";

                        $check = '';
                        if($row < 2){$check = 'checked';}
                        $html_text .="<td style='text-align:center;'>\n";
                            $html_text .="<input type='checkbox' name='check_ETL[]' value='".$key."_".$row."' ".$check." >\n";
                        $html_text .="</td>\n";

                    $html_text .="</tr>\n";

                    $row++;                                                                                         
                }
            }            

        $html_text .="</table>\n";	
    }
    else
    {
        $html_text .="<p style='margin-top:25px;text-align:center;' >".htmlaccent('Aucune données n\'a été trouvée')."</p>\n";
    }

$html_text .="</div>\n";

$responseData = array(
    'html_text' => $html_text,
    'ETL_array' => $ETL_array
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>