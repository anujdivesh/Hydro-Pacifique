<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Formulaire des fiches agents
Cette page correspond au popup d'une fiche agent 
Il s'agit d'un formuaire qui permet de créer une fiche Agent ou de la modifier
Les différents champs sont les informations liées à l'agent
----------------------------------------
*/
$today = date('d-m-Y'); 
$time = date('H:i');
$today_time = date('d-m-Y H:i');

$day = date('d');
$month = date('m');
$year = date('Y');

$display_box = '';

echo "<div id='box_agent' class='block_view' >\n";

	echo "<div id='cadre_view' class='cadre_view' style='max-height: 90vh; overflow-y: auto;width:900px;' \">";
	
		echo "<div id='cadre_limit' style='height:487px;'>";	
			
			echo "<form id='formAgent' name='formAgent'>";
			
				// Identifiant du RA en cours		
				echo "<input type='hidden' name='id_agent_fiche' id='id_agent_fiche' value='' />";
				
				// TITRE 
				echo "<table id='tab_titre_popup' cellspacing='0'>";
						
					echo "<tr>";
						
						echo "<td class='titre'>";

							echo "<p style='width:80%;margin-top:5px;'>";
								
								echo "<input name='titre_fiche_agent' id='titre_fiche_agent' value='' class='input_texte'  style='width:100%;font-size:24px;font-weight:bold;' type='text' readonly>";
																
							echo "</p> \n";
													
						echo "</td>";
						
					echo "</tr>";
					
				echo "</table>";
				
				// Information
				echo "<div id='boxpopup'>\n";
				
					// Nom
					echo "<div id='boite_small'>\n";
						
						echo "<p style='width:80px;'>".htmlaccent('Nom')."</p>\n";	
						echo "<input name='nom' id='nom' value='' class='input_texte'  style='width:200px;	' type='text'>";
						
					echo "</div>\n";
					
					// Nom marital
					echo "<div id='boite_small'>\n";
						
						echo "<p style='width:80px;'>".htmlaccent('Nom marital')."</p>\n";	
						echo "<input name='nom_marital' id='nom_marital' value='' class='input_texte'  style='width:200px;' type='text'>";
						
					echo "</div>\n";
					
					// Prenom
					echo "<div id='boite_small'>\n";
						
						echo "<p style='width:80px;'>".htmlaccent('Prénom')."</p>\n";	
						echo "<input name='prenom' id='prenom' value='' class='input_texte'  style='width:200px;' type='text'>";
						
					echo "</div>\n";
					
				echo "<hr>\n";
				echo "</div>\n";	
				
				// Activité
				echo "<div id='boxpopup'>\n";
				
					echo "<h2>".htmlaccent('Activité')."</h2>\n";
				
					
					// Institution / Entreprise
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Institution / Entreprise')."</p>\n";	
						echo "<input name='raisonsociale' id='raisonsociale' value='' class='input_texte'  style='width:250px;' type='text'>";
						
					echo "</div>\n";
					
					// Fonction
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Fonction')."</p>\n";	
						echo "<input name='fonction' id='fonction' value='' class='input_texte'  style='width:250px;' type='text'>";
						
					echo "</div>\n";
					
					// Num Inscription
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Numéro d\'inscription')."</p>\n";	
						echo "<input name='numinscription' id='numinscription' value='' class='input_texte'  type='text'>";
						
					echo "</div>\n";
					
					
				echo "<hr>\n";
				echo "</div>\n";
				
				
				// Coordonnées
				echo "<div id='boxpopup'>\n";
				
					echo "<h2>".htmlaccent('Coordonnées')."</h2>\n";
				
					
					// Téléphone
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Téléphone')."</p>\n";	
						echo "<input name='tel' id='tel' value='' class='input_texte' type='text' style='width:80px;'>";
						
					echo "</div>\n";
					
					// Mobile
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Mobile')."</p>\n";	
						echo "<input name='mobile' id='mobile' value='' class='input_texte' type='text' style='width:80px;'>";
						
					echo "</div>\n";
					
					// Fax
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Fax')."</p>\n";	
						echo "<input name='fax' id='fax' value='' class='input_texte' type='text' style='width:80px;'>";
						
					echo "</div>\n";
					
					// Email
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Email')."</p>\n";	
						echo "<input name='email' id='email' value='' class='input_texte_200' type='text' >";
						
					echo "</div>\n";
					
					// Web
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Site web')."</p>\n";	
						echo "<input name='siteweb' id='siteweb' value='' class='input_texte_200' type='text'>";
						
					echo "</div>\n";
					
					
				echo "<hr>\n";
				echo "</div>\n";	
				
				
				// Adresse
				echo "<div id='boxpopup'>\n";
				
					echo "<h2>".htmlaccent('Adresse')."</h2>\n";
				
					
					// Rue
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Rue')."</p>\n";	
						echo "<input name='adresse' id='adresse' value='' class='input_texte' type='text' style='width:250px;'>";
						
					echo "</div>\n";
					
					// Lieu dit 
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Lieu dit')."</p>\n";	
						echo "<input name='lieudit' id='lieudit' value='' class='input_texte' type='text'>";
						
					echo "</div>\n";
					
					// bp
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('BP')."</p>\n";	
						echo "<input name='bp' id='bp' value='' class='input_texte' type='text' style='width:80px;'>";
						
					echo "</div>\n";
					
					// Code postal
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Code Postal')."</p>\n";	
						echo "<input name='codepostal' id='codepostal' value='' class='input_texte' type='text' style='width:80px;' >";
						
					echo "</div>\n";
					
					// Commune
					echo "<div id='boite_small'>\n";
						
						echo "<p>".htmlaccent('Commune')."</p>\n";	
						
						echo "<select name='select_commune' id='select_commune' style='width:140px;'>";

							echo "<option value='0'>-</option>";
							
							$sql_commune = "SELECT DISTINCT c.id_commune, c.nom_commune FROM ".TABLE_COMMUNE." c, ".TABLE_REGION." r WHERE c.id_region=r.id_region AND r.id_territoire=".$territoire_id." ORDER BY c.nom_commune";
							$commune_query = tep_db_query($sql_link,$sql_commune);
							while($commune_list = tep_db_fetch_array($commune_query))
							{
								echo "<option value='".$commune_list['id_commune']."' >".htmlaccent($commune_list['nom_commune'])."</option>";
							}
						
						echo "</select>";
						
					echo "</div>\n";
					
					
				echo "<hr>\n";
				echo "</div>\n";	
				
				// --LIGNE --------------------------------
				echo "<hr>";
				
				// Navigation d'une fiche à l'autre
				echo "<div id='popup_barredown'>\n";

					echo "<div id='popup_nav' style='width:470px;'>\n";

						echo "<div style='float:left;width:150px;margin-top:5px;' >";
							echo "<p style='float:left;font-size:14px;font-weight:bold;text-align:center;padding-top:5px;'>".htmlaccent('Agent Terrain')."</p>";
							echo "<input type='checkbox' name='check_terrain' id='check_terrain' style='float:left;width:20px;height:20px;margin-left:20px;'>";
						echo "</div>";
					
						echo "<div style='float:left;width:150px;margin-top:5px;margin-left:50px;'>";
							echo "<p style='float:left;font-size:14px;font-weight:bold;text-align:center;padding-top:5px;'>".htmlaccent('Agent ').$service_hydro."</p>";
							echo "<input type='checkbox' name='check_service_hydro' id='check_service_hydro' style='float:left;width:20px;height:20px;margin-left:20px;'>";
						echo "</div>";

					echo "</div>";
						
					
					// Bouton d'enregistrement
					echo "<div id='popup_nav' style='float:right;'>\n";
						
						echo "<table id='stats_select' cellspacing='0' style='width:300px;' >";
				
							echo "<tr>";
								
								echo "<td class='bold'>";
									echo "<input type='submit' class='button' id='save_agent' name='save_agent' value='Enregistrer' onclick='saveAgent(event);'/>";
								echo "</td>";
								
								echo "<td>&nbsp;</td>";
								
								echo "<td class='bold'>";
									echo "<input type='button' class='button_close'  value=\"Annuler\" onclick=\"document.getElementById('box_agent').style.display='none';\"/>";
								echo "</td>";
								
							echo "</tr>";
							
						echo "</table>";
						
						
					echo "</div>\n";
				
				echo "</div>\n";
				
			echo "</form>";
		
		echo "</div>\n";	
		
	echo "</div>\n";
	

echo "</div>\n";

?>


<script type="text/javascript">
	
	// Récupère le popup et le bouton qui l'ouvre
	var popup = document.getElementById('cadre_view');
	var box = document.getElementById('box_agent');
		
	// Ajoute un événement de clic au document
	document.addEventListener("click", function(event)
	{
	  // Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
	  if (event.target !== popup && event.target === box) 
	  {
		// Ferme le popup
		box.style.display = "none";
	  }
	});

	// Ajout d'un gestionnaire d'événements pour la touche Echap
	document.addEventListener("keydown", function(event) 
	{
		if (event.key === "Escape") 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box.style.display = "none";
		}
    });
		  
</script>