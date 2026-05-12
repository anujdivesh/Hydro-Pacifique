<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Popup de vérification avant suppression de fiche RA
----------------------------------------
*/


$day = date('d');
$month = date('m');
$year = date('Y');

$display_box = '';

echo "<div id='box_del_ra' class='block_view' >\n";

    

echo "</div>";

?>


<script type="text/javascript">
	
	// Récupère le popup et le bouton qui l'ouvre
	var popup_del = document.getElementById('cadre_view_del');
	var box_del = document.getElementById('box_del_ra');
		
	// Ajoute un événement de clic au document
	document.addEventListener("click", function(event)
	{
		// Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
		if (event.target !== popup_del && event.target === box_del) 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box_del.style.display = "none";
		}
	});

		  
</script>