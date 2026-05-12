<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Parge d'export (et d'import) des données de paramétrage de la plateforme
----------------------------------------
*/

require('include/application_top.php');


// -------------------------------------
// Chargement de la librairy pour lecture fichier excel
// Librairy PhpSpreadsheet
//require 'php-excel/vendor/autoload.php';


// Initialisation Variables

$message_info = '';
$data_step = 1; // permet de savoir à quelle étape on se trouve
$verif_form = 0; // permet de valider si l'ensemble des champs sont bien saisies
$row = 0;
$entete = 0;

$select_station_tab = array();

// Dates par défault
$today = date('d-m-Y');
$year_today = date('Y'); 
$month_today = date('m'); 

$date_format = 'd-m-Y';



// Création d'un identification unique de l'exportation

$timestamp = time(); // Obtenir le timestamp Unix actuel
// Formater le timestamp pour obtenir 'yyyymmjjhhmmss'
$id_export = 'HP_ExportParam_' . date('YmdHis', $timestamp); // Option : Ajouter un préfixe ou suffixe pour garantir l'unicité et éviter les interprétations comme date




// --------------------------------------
// Debut page HTML

require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

    echo "<div id='contenu_info' style='display:none;'></div>";

    require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
    include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

    echo "<div id='contour_general'>";

        echo "<div id='contenu_centre'>";
            
            echo "<div id='contenu_box2'>";

                echo "<h1>";
                                
                    echo "<span>".htmlaccent('Export / Import des paramètres de la plateforme')."</span>";
                    
                echo "</h1>";


                echo "<div style='float:left;width:30%;' >\n";   
                
                    echo "<div id='boxpopup' class='select-top' style='width:100%;padding:10px;'>\n";

                        echo "<p style='margin-left:1%;'>";
                            echo "<input type='checkbox' value='zonegeo' checked>";
                            echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                echo htmlaccent('Zones Géoraphiques (Régions Géographiques / Communes / Régions Hydro / Rivières)');
                            echo "</span>";
                        echo "</p>";

                        echo "<p style='margin-left:1%;'>";
                            echo "<input type='checkbox' value='typechron' checked>";
                            echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                echo htmlaccent('Types de Chroniques');
                            echo "</span>";
                        echo "</p>";
                        

                        echo "<p style='margin-left:1%;'>";
                            echo "<input type='checkbox' value='st_nature' checked>";
                            echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                echo htmlaccent('Nature des stations');
                            echo "</span>";
                        echo "</p>";

                        echo "<p style='margin-left:1%;'>";
                            echo "<input type='checkbox' value='codequal' checked>";
                            echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>".htmlaccent('Codes Qualité')."</span>";
                        echo "</p>";

                        echo "<p style='margin-left:1%;'>";
                            echo "<input type='checkbox' value='eqjge' checked>";
                            echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>".htmlaccent('Equipements de Jaugeages (Hélices / Moulinets / Saumon)')."</span>";
                        echo "</p>";
                        

                        echo "<input type='submit' class='button_export' name='button_export' id='button_export_param' style='width:200px;margin-left:1.5%' 
                                        value='".htmlaccent('Exporter les données')."' 
                                        onclick='downloadParam_xls()';
                            />";

                        echo "<div id='wait_file' style='float:right;text-align:center;margin-top:10px;display:none;'>";
                            echo "<img src='".DIR_WS_IMG."wait.gif' style='width:15px;'>";
                            echo "<span style='margin-left:10px;font-size:11px;font-weight:bold;color:#000;'>".htmlaccent('Création du fichier en cours ...')."</span>";
						echo "</div>\n";
                        
                    
                    echo "</div>\n";

                echo "</div>\n";

                

            echo "</div>";

        echo "</div>";


    echo "</div>";

    require('include/application_bottom.php'); 

echo "</body>";

echo "</html>";
					


?>

<script>

	var idTerritoire = <?php echo $territoire_id; ?>;

    var buttonExportParam = document.getElementById('button_export_param');
	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info
	var waitFile = document.getElementById('wait_file'); // pour affichage de l'icone d'attente de création du fichier XLS



    // ---------------------------------------
    // Function pour le téléchargement des Informations Stations
    function downloadParam_xls()
    {        
		buttonExportParam.style.display = 'none';
        waitFile.style.display = 'block';


        // ETAPE 1 : Récupérer tous les checkboxes
		var checkboxes = document.querySelectorAll("input[type='checkbox']");
		var selectedParam = [];

		// Parcourir les checkboxes et ajouter les valeurs des checkboxes cochées à la liste
		checkboxes.forEach(function(checkbox) 
		{
			if (checkbox.checked) 
			{
				selectedParam.push(checkbox.value);
			}
		});

		if(selectedParam.length === 0)
		{
			contenuInfo.innerHTML  = 'Aucun paramètre n\'a été sélectionné, le fichier ne peut pas être créé.';
			contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
			contenuInfo.style.display = 'block';

			waitFile.style.display = 'none';

			return;
		}

        // Construire la liste des paramètres à exporter pour les requêtes SQL
        var listParam = selectedParam.join(",");


        // ETAPE 2 : Préparer la création du fichier XLS en envoyant les infos coté Serveur
        cheminFolder = 'data/export/temp';

        // Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
                            idTerritoire: idTerritoire,
                            listParam: listParam,
                            cheminFolder: cheminFolder,
                        };

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/export/process_hp_param_xls.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4)
            {
                if (xhr.status === 200) 
                {
                    // Analyser la réponse JSON
                    var jsonResponse = JSON.parse(xhr.responseText);  

                    if(jsonResponse['statut'])
                    {
                        // Créer un lien invisible pour déclencher le téléchargement
                        var downloadLink = document.createElement('a');
                        downloadLink.href = cheminFolder+'/'+jsonResponse['xlsFile']; // URL du fichier CSV
                        downloadLink.download = jsonResponse['xlsFile']; // Nom du fichier
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);						
                    }
                    else 
                    {
                        contenuInfo.innerHTML  = 'Erreur lors de la génération du fichier.';
                        contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
                        contenuInfo.style.display = 'block';
                    }

                } 
                else 
                {
                    contenuInfo.innerHTML  = 'Erreur lors de la requête au serveur.';
                    contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
                    contenuInfo.style.display = 'block';
                }
            }

            
			waitFile.style.display = 'none';
            buttonExportParam.style.display = 'block';
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));                        

        console.log(listParam);

    }



</script>

