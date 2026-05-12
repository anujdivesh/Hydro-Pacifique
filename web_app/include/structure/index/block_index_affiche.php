<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Ce block permet d'afficher les derniers RA entrés dans la plateforme
Cette fenêtre s'affiche quand on clique sur un lien d'information dans la page index.php
----------------------------------------
*/

echo "<div id='box_data' class='block_view' >\n"; // style='background:transparent;'

	echo "<div id='cadre_view_2' style='float:left;width:50%;margin-top:20px;margin-left:25%;padding:0px;' >\n";

        echo "<p style='float:left;width:100%;height:30px;padding:5px 0;color:#fff;background-color:#000;'>";
            echo "<span id='title_box'style='font-size:20px;font-weight:bold;margin-left:5px;'>";
            echo "</span>";
            echo "<span id='button_close' style='float:right;font-size:20px;font-weight:bold;margin-right:15px;cursor:pointer;' title='Fermer'>X</span>";
        echo "</p>\n";  
	
		echo "<div id='cadre_limit' style='height:50%;margin-top: 0px;padding:10px 5px;'>";	

            // Cadre d'affichage
            echo "<div id='cadre_index_cell' style='width:95%;'>";	
            
                echo "<div id='cadre_wait' style='width:100%;height:50px;margin-top:10px;text-align:center;'>";	   
                    echo "<img src='".DIR_WS_IMG."wait.gif' style='width:50px;'>";                 
                echo "</div>";
            
            echo "</div>";

		echo "</div>\n";	
		
	echo "</div>\n";

echo "</div>\n";

?>

<script>

    // Ajoute un événement de clic au document
	document.addEventListener("click", function(event)
	{
		// Vérifie si l'élément cliqué est le bouton de fermeture
        if (event.target.id === 'button_close') 
		{
            // Ferme le popup et le popup d'info s'il a été ouvert
            boxData.style.display = "none";
        } 

		// Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
		if (event.target === boxData) 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			boxData.style.display = "none";
		}
	});

    // Ajout d'un gestionnaire d'événements pour la touche Echap
	document.addEventListener("keydown", function(event) 
	{
		if (event.key === "Escape") 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			boxData.style.display = "none";
		}
    });
    


</script>