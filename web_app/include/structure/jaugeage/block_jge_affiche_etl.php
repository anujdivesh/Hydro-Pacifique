<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Ce block permet d'afficher le graph de l'ETL relié au JGE en cours de saisie
Il est accessible à partir de la page data_jge.php 
----------------------------------------
*/


echo "<div id='box_jge_affiche_etl' class='block_view' >\n"; // style='background:transparent;'

	echo "<div id='cadre_view_2' style='width:80%;padding:10px;background-color:#FBF9F1;' >\n";

        echo "<p style='float:left;width:101%;height:40px;padding-left:8px;font-size:24px;font-weight:bold;color:#fff;margin:-10px;background-color:#000;'>";
            echo htmlaccent('Courbe d\'Etalonnage');
        echo "</p>\n";  
	
		echo "<div id='cadre_limit' style='width:100%;height:62vh;margin-top:30px;'>";	

            echo "<div id='info_etl' style='padding:10px;padding-bottom:0px;margin-bottom:10px;'></div>\n";

            echo "<div id='plot_etl' style='height:80%;padding:10px;padding-bottom:0px;'></div>\n";

            echo "<div id='wait_graph' style='width:100%;height:65px;text-align:center;'>";
                echo "<img src='".DIR_WS_IMG."wait.gif' style='width:50px;'>";
                echo "<p>".htmlaccent('Chargement en cours ...')."</p>";
            echo "</div>\n";
        
		
		echo "</div>\n";	
		
	echo "</div>\n";

echo "</div>\n";

?>


<script type="text/javascript">
	
    // Récupère le popup et le bouton qui l'ouvre
    
    var popupETL = document.getElementById('cadre_view_2');    
    var boxETL = document.getElementById('box_jge_affiche_etl');
        
    // Ajoute un événement de clic au document
    document.addEventListener("click", function(event)
    {
        // Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
        if (event.target !== popupETL && event.target === boxETL) 
        {
            // Ferme le popup
            boxETL.style.display = "none";
        }
    });

</script>