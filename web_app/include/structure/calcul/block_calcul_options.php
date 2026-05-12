<?php
/*  
----------------------------------------
Copyright (c) 2025 - Vai-Natura
----------------------------------------
Popup pour l'affichage des options de calcul
----------------------------------------
*/


echo "<div id='box_calcul_options' class='block_view' style='position:absolute;width:350px;height:80%;top:20px;left:10%;background:none;'>\n"; // style='background:transparent;'

	echo "<div id='cadre_view_2' style='float:left;padding:0px;margin:0;' >\n";

        echo "<p style='float:left;width:100%;height:30px;padding:5px 0;color:#fff;background-color:#000;' id='title_box'>";
            echo "<span id='title_box'style='font-size:18px;font-weight:bold;margin-left:5px;'>";
				echo "Options de calcul sur la chronique";
            echo "</span>";

            echo "<span id='button_close' style='float:right;font-size:20px;font-weight:bold;margin-right:15px;cursor:pointer;' title='Fermer'>X</span>";
        echo "</p>\n";  


		echo "<div id='cadre_option' style='width:98%;padding: 10px 0;padding-left:10px;max-height:70vh;overflow-y: auto;'>";	

			// --- Correction par fonction linéaire

			echo "<div style='float:left;padding:10px 3%;border:1.5px solid #000;width:88%;'>\n";

				echo "<div style='float:left;width:80%;'>\n";

						echo "<p style='float:left;width:100%;margin-bottom:10px;font-size:14px;font-weight:bold;'>";
							echo "Correction par Fonction Linéaire";
							echo "<br>";
							echo "<span style='font-size:15px;'>"."Ynew = aY + b"."</span>";
						echo "</p>\n";
						
						// Paramètre a
						echo "<p style='float:left;color:#428bca;padding-top:5px;'>";
							echo "a = ";
						echo "</p>\n";
						echo "<input type='text' class='input_texte_xsmall' id='valeur_a' style='float:left;margin-left:5px;margin-right:20px;' value='1'/>\n";

						// Paramètre b
						echo "<p style='float:left;color:#428bca;padding-top:5px;'>";
							echo "b = ";
						echo "</p>\n";
						echo "<input type='text' class='input_texte_xsmall' id='valeur_b' style='float:left;margin-left:5px;' value='0'/>\n";

				echo "</div>\n";
				
				echo "<div style='float:right;margin-top:25px;'>\n";

					echo "<button id='calcul_valeur' class='inverse_axe' style='float:right;width:50px;height:35px;padding:0;color:".$colorMapping['calcul'].";' 
						title="."Générer la correction".">";
						echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
					echo "</button>\n"; 

				echo "</div>\n";
			
			echo "</div>\n";


			// --- Décalage temporel

			echo "<div style='float:left;margin-top:10px;padding:10px 3%;border:1.5px solid #000;width:88%;'>\n";

				echo "<div style='float:left;width:80%;'>\n";

					echo "<div id='boite_small' style='margin:0;'>\n";

						echo "<p style='float:left;width:100%;margin-bottom:10px;font-size:14px;font-weight:bold;'>";
							echo "Décalage temporel (Abscisse X)";
						echo "</p>\n";
						
						// Opérateur
						echo "<select name='operateur_x' id='operateur_x' style='float:left;width:45px;font-weight: bold;font-size:16px;'>\n";
											
							echo "<option value='+' >+</option>\n";
							echo "<option value='-' >-</option>\n"; 

						echo "</select>\n";
					
						// Valeur                               
						echo "<input type='text' class='input_texte_xsmall' id='valeur_operation_x' style='float:left;' value='0'/>\n";
						echo "<p style='float:left;width:50px;color:#428bca;margin-left:5px;padding-top:7px;'>".htmlaccent('secondes')."</p>\n";
						
					echo "</div>\n";

				echo "</div>\n";
				
				echo "<div style='float:right;margin-top:15px;'>\n";

					echo "<button id='calcul_date' class='inverse_axe' style='float:right;width:50px;height:35px;padding:0;color:".$colorMapping['decalage_date'].";' 
						title="."Générer la correction".">";
						echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
					echo "</button>\n"; 

				echo "</div>\n";
			
			echo "</div>\n";


			// --- Mise en lacunes

			echo "<div style='float:left;margin-top:10px;padding:10px 3%;border:1.5px solid #000;width:88%;'>\n";

				echo "<div style='float:left;width:80%;'>\n";

					echo "<div id='boite_small' style='width:90%;margin:0;'>\n";

						echo "<p style='float:left;width:100%;margin-bottom:10px;font-size:14px;font-weight:bold;'>";
							echo "Mise en lacune";
						echo "</p>\n";
						echo "<input type='text' id='periode_lacune_first' name='periode_lacune_first' 
									style='float:left;width:150px;padding:5px 0;padding-left:5px;border:0;color:#5E686D;' readonly 
									value=''>\n";
						echo "<input type='text' id='periode_lacune_end' name='periode_lacune_end' 
									style='float:left;width:150px;padding:5px 0;padding-left:5px;border:0;color:#5E686D;' readonly 
									value=''>\n";
							//value='"."Sélectionner une période grâce au Zoom du graphique"."'>\n";

					echo "</div>\n";

				echo "</div>\n";
				
				echo "<div style='float:right;margin-top:25px;'>\n";
				
					echo "<button id='calcul_lacune' class='inverse_axe' style='float:right;width:50px;height:35px;padding:0;' 
						title="."Générer la Lacune".">";
						echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
					echo "</button>\n"; 

				echo "</div>\n";
			
			echo "</div>\n";

			// --- Lissage
			// Il faudra ajouter des algorithmes de lissage plus complexe

			// ne fonctionne que sur les chroniques en lignes
			$display = '';
			if($type_chron_array[$typedata_chron]['type_graph'] != 'lines'){$display = 'display:none;';}

			echo "<div style='float:left;margin-top:10px;padding:10px 3%;border:1.5px solid #000;width:88%;".$display."'>\n";

				echo "<div style='float:left;width:210px;'>\n";

					echo "<div id='boite_small' style='width:100%;margin:0;'>\n";

						echo "<p style='float:left;width:100%;margin-bottom:10px;font-size:14px;font-weight:bold;'>";
							echo "Lissage de la chronique";
						echo "</p>\n";

						// Opérateur
						echo "<select name='lissage' id='lissage' style='float:left;width:110px;'>\n";
                                                                    
							echo "<option value='1' >";
								echo "Variation faible";
							echo "</option>\n";

						echo "</select>\n";

						// Valeur                               
						echo "<p style='float:left;width:50px;color:#428bca;margin-left:5px;padding-top:7px;'>".htmlaccent('Seuil (%) : ')."</p>\n";
						echo "<input type='text' id='seuil_liss' style='float:left;width:25px;' value='0'/>\n";

					echo "</div>\n";

				echo "</div>\n";
				
				echo "<div style='float:right;margin-top:18px;'>\n";
				
					echo "<button id='calcul_lissage' class='inverse_axe' style='float:right;width:50px;height:35px;padding:0;color:".$colorMapping['lissage'].";' 
						title="."Lisser la Chronique".">";
						echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
					echo "</button>\n"; 

				echo "</div>\n";
			
			echo "</div>\n";

		echo "<hr>\n";
		echo "</div>\n";	
		
	echo "</div>\n";

echo "</div>\n";

?>


<script type="text/javascript">
	
	var box_calcul_options = document.getElementById('box_calcul_options');
		
	// Ajoute un événement de clic au document
	document.addEventListener("click", function(event)
	{
		// Vérifie si l'élément cliqué est le bouton de fermeture
        if (event.target.id === 'button_close') 
		{
            // Ferme le popup et le popup d'info s'il a été ouvert
            box_calcul_options.style.display = "none";
        } 

		// Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
		if (event.target === box_calcul_options) 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box_calcul_options.style.display = "none";
		}
	});

    // Ajout d'un gestionnaire d'événements pour la touche Echap
	document.addEventListener("keydown", function(event) 
	{
		if (event.key === "Escape") 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box_calcul_options.style.display = "none";
		}
    });


	function affiche_options_calcul() 
	{
		box_calcul_options.style.display = 'block';
	}



		  
</script>