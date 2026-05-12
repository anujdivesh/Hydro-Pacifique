<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Block de fiche RA
----------------------------------------
*/


$day = date('d');
$month = date('m');
$year = date('Y');

$display_box = '';

echo "<div id='box_diag' class='block_view' >\n";

    echo "<div id='cadre_view' class='cadre_view' style='width:90%;padding: 15px 1%;overflow-y: auto;background-color:#fff;' >\n";

        // Colonne de gauche pour afficher la liste des Diagraphies sélectionnées classées et affichés par station
        echo "<div id='cadre_graph' style='float:left;width:290px;margin-right:10px;'>\n";

            echo "<div id='boxpopup' class='select-top' style='width:90%;margin:0px;padding:5%;'>\n";
                
                echo "<p style=''>";
                    echo "<span style='font-weight: bold;font-size:14px;'>".htmlaccent('Liste des Diagraphies')."</span>";
                echo "</p>";

                echo "<div id='button_visu' style='float:left;width:160px;' onclick='load_graph_diag();'>";	
                    echo  htmlaccent('Actualiser le graphique'); 
                echo "</div>\n";                
                
                echo "<div id='cadre_data_station_lgt' style='width:100%;height:67vh;overflow-y: auto;margin-top:15px;padding-right:25px;padding:0;padding-bottom:5px;display:none;'>\n";
                echo "</div>\n";

                echo "<div id='wait_tab' style='width:100%;height:65px;margin-top:100px;text-align:center;'>";
                    echo "<img src='".DIR_WS_IMG."wait.gif' style='width:50px;'>";
                    echo "<p>".htmlaccent('Chargement en cours ...')."</p>";
                echo "</div>\n"; 

            echo "</div>\n";

        echo "</div>\n";

        
        // Block Graphique
        echo "<div id='cadre_graph' style='float:none;width:auto;height:99%;overflow-y: auto;'>\n";
            
            echo "<div id='boxpopup' class='select' style='width:98%;height:80vh;margin:0;padding:0;border:1px solid #000;'>\n";

                echo "<p class='titre' style='height:15px;font-size:14px;'>";
                
                    echo htmlaccent('Diagraphies comparées');

                echo "</p>";

                // Cadre Graph
                echo "<div id='plot' style='width:95%;height:70vh;margin-left:30px;display:none;'></div>\n";
                
                echo "<div id='wait_graph' style='width:100%;height:65px;margin-top:100px;text-align:center;'>";
                    echo "<img src='".DIR_WS_IMG."wait.gif' style='width:50px;'>";
                    echo "<p>".htmlaccent('Chargement en cours ...')."</p>";
                echo "</div>\n"; 

            echo "</div>\n";	

            
        echo "</div>\n";  

        //echo "<input type='button' class='button_close' id='button_close' style='float:right;margin-right:2%;' value='Fermer' />";

    echo "</div>\n";  

echo "</div>";

?>


<script type="text/javascript">
	
	// Récupère le popup et le bouton qui l'ouvre
	var popup = document.getElementById('cadre_view');
	var box = document.getElementById('box_diag');
		
	// Ajoute un événement de clic au document
	document.addEventListener("click", function(event)
	{
		// Vérifie si l'élément cliqué est le bouton de fermeture
        if (event.target.id === 'button_close') 
		{
            // Ferme le popup et le popup d'info s'il a été ouvert
            box.style.display = "none";
        } 

		// Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
		if (event.target !== popup && event.target === box) 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box.style.display = "none";
		}

	});

    // Ajout d'un gestionnaire d'événements pour la touche Echap
	document.addEventListener("keydown", function(event) 
	{
		if (event.key === "Escape") 
		{
			//boxClose();
            box.style.display = "none";
		}
    });
        



		  
</script>