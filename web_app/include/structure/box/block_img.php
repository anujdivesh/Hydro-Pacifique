<?php
/*  
----------------------------------------
Copyright (c) 2025 - Vai-Natura
----------------------------------------
Popup pour l'affichage d'information liée aux Chroniques de Données
----------------------------------------
*/

// Requête sur TYPE DE MESURE (Hydrométrie, Pluviométrie, Piézométrie, ...)


echo "<div id='box_img' class='block_view' >\n"; // style='background:transparent;'

	echo "<div id='cadre_view_2' style='float:left;width:40%;margin-top:20px;margin-left:30%;padding:0px;' >\n";

        echo "<p style='float:left;width:100%;height:30px;padding:5px 0;color:#fff;background-color:#000;'>";
            echo "<span id='title_info_chron'style='font-size:20px;font-weight:bold;margin-left:5px;'>";
            echo "</span>";

            echo "<span id='button_close' style='float:right;font-size:20px;font-weight:bold;margin-right:15px;cursor:pointer;' title='Fermer'>X</span>";
        echo "</p>\n";  

    echo "</div>\n";

echo "</div>\n";

?>


<script>
	
	var box_img = document.getElementById('box_img');
		
	// Ajoute un événement de clic au document
	document.addEventListener("click", function(event)
	{
		// Vérifie si l'élément cliqué est le bouton de fermeture
        if (event.target.id === 'button_close') 
		{
            // Ferme le popup et le popup d'info s'il a été ouvert
            box_img.style.display = "none";
        } 

		// Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
		if (event.target === box_info_chron) 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box_img.style.display = "none";
		}
	});

    // Ajout d'un gestionnaire d'événements pour la touche Echap
	document.addEventListener("keydown", function(event) 
	{
		if (event.key === "Escape") 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box_img.style.display = "none";
		}
    });



		  
</script>