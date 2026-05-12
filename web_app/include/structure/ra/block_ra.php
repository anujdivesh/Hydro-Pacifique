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

echo "<div id='box_ra' class='block_view' >\n";


echo "</div>";

?>


<script type="text/javascript">
	
	// Récupère le popup et le bouton qui l'ouvre
	var popup = document.getElementById('cadre_view');
	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info
	var boxRa = document.getElementById('box_ra');
    var boxRaProfil = document.getElementById('box_ra_piezoprofil');
		
	// Ajoute un événement de clic au document
	document.addEventListener("click", function(event)
	{
		// Vérifie si l'élément cliqué est le bouton de fermeture
        if (event.target.id === 'button_close') 
		{
            // Ferme le popup et le popup d'info s'il a été ouvert
            contenuInfo.style.display = "none";
            boxRa.style.display = "none";
            boxRaProfil.style.display = "none";
        } 

		// Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
		if (event.target !== popup && event.target === boxRa) 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			contenuInfo.style.display = "none";
            boxRa.style.display = "none";
            boxRaProfil.style.display = "none";
		}

        // Box Profil Piézométrique en profondeur
        if (event.target === boxRaProfil) 
        {         
            // Ferme le popup
            boxRaProfil.style.display = "none";
        }

	});

    // Ajout d'un gestionnaire d'événements pour la touche Echap
	document.addEventListener("keydown", function(event) 
	{
		if (event.key === "Escape") 
		{
            contenuInfo.style.display = "none";
            boxRa.style.display = "none";
            boxRaProfil.style.display = "none";
		}
    });


        

	// Fonctions permettant de gérer la saisie des agents présents
	function updateSelectedAgents() 
    {
        // Récupère toutes les cases à cocher correspondant au sélecteur donné
        var checkboxes = Array.from(document.querySelectorAll('input[type="checkbox"][name^="check_agent_"]'));

        // Récupère les valeurs des cases à cocher sélectionnées
        var selectedValues = checkboxes
            .filter(function(checkbox) {
                return checkbox.checked;
            })
            .map(function(checkbox) {
                return checkbox.getAttribute('data-value').trim();
            });

        // Récupère et filtre le texte manuel, en excluant les doublons et les valeurs déjà cochées
        var currentText = document.getElementById('agents_complement').value;
        var manualText = currentText
            .split(' / ')
            .map(function(value) {
                return value.trim();
            })
            .filter(function(value) {
                return value !== '' &&
                    !selectedValues.includes(value) &&
                    !checkboxes.some(function(chk) {
                        return chk.getAttribute('data-value').trim() === value;
                    });
            });

        // Combine le texte manuel filtré et les valeurs cochées, puis met à jour le champ d'entrée
        var combinedText = manualText.concat(selectedValues).join(' / ');
        document.getElementById('agents_complement').value = combinedText;
    }


		  
</script>