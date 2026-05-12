<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Affichage du Menu de navigation à droite de la page
----------------------------------------
*/

// Récupérer les états des menus pour l'utilisateur
$menu_states = [];

$sql_query = "SELECT menu_id, is_open FROM " . TABLE_USER_MENU . " WHERE id_user = ?";

$stmt = $sql_link->prepare($sql_query);
$stmt->bind_param("i", $id_user);
$stmt->execute();

$result = $stmt->get_result();

while ($row_menu = $result->fetch_assoc()) 
{
    $menu_states[$row_menu['menu_id']] = $row_menu['is_open'];
}

$stmt->close();


echo "<div id='col'>";

	echo "<div id='nav_col'>";

		if($gestion_data > 0)
		{
			echo "<div class='section'>";

				echo "<h4 class='toggle-header' data-menu-id='data' style='border-bottom:1px solid #000;color:#000;'>";
					echo "<span>".TEXT_MENU_DATA."</span>";
					echo "<span class='arrow'>&#9660;</span>"; // Flèche vers le bas
				echo "</h4>";

				echo "<hr>";
						
				echo "<div class='navigation' style='display:none;'>";
							
					echo "<ul>\n";
						
						// Chroniques de données
						echo "<li class='simple'>\n";							
							echo "<a href='data_chron.php' >";
								echo TEXT_MENU_DATA_CHRON;
							echo "</a>";					
						echo "</li>\n";

						// Gestion des corrections
						echo "<li class='simple'>\n";							
							echo "<a href='corrections.php' >".TEXT_MENU_DATA_TRACKCONNECT."</a>";					
						echo "</li>\n";

						// Rapports d'Activités
						echo "<li class='simple'>\n";								
							echo "<a href='list_ra.php' >";
								echo TEXT_MENU_DATA_ACTREPORT;
							echo "</a>";								
						echo "</li>\n";

						// Importation des données
						echo "<li class='simple'>\n";							
							echo "<a href='import.php' >".TEXT_MENU_DATA_IMPORT."</a>";	
						echo "</li>\n";

						// Exportation data 
						echo "<li class='simple'>\n";							
							echo "<a href='data_chron.php?export=true' >".TEXT_MENU_DATA_EXPORT."</a>";					
						echo "</li>\n";
						

						/*

						// Edition de statistiques
						echo "<li class='simple'>\n";
							//echo "<a href='statistiques.php' >".htmlaccent('Statistiques')."</a>";
							echo htmlaccent('Statistiques');
						echo "</li>\n";

						// Edition de rapports automatisés
						echo "<li class='simple'>\n";
							//echo "<a href='statistiques.php' >".htmlaccent('Rapports automatiques')."</a>";
							echo htmlaccent('Rapports automatiques');
						echo "</li>\n";

						*/
					
					echo "</ul>\n";	
					
				echo "</div>";

			echo "</div>";
		}
		
		//---------------------------------------------------------------------------------------------------
		//---------------------------------------------------------------------------------------------------
		// MODULES

			echo "<div class='section'>";

				echo "<h4 class='toggle-header' data-menu-id='mod' style='border-bottom:1px solid #10740e;color:#000;'>";
					echo "<span>".TEXT_MENU_MOD."</span>";
					echo "<span class='arrow'>&#9660;</span>"; // Flèche vers le bas
				echo "</h4>";

				echo "<hr>";

				echo "<div class='navigation' style='display:none;'>";
							
					echo "<ul>\n";

						if($gestion_data > 0)
						{
							// Fiches stations
							echo "<li class='simple'>\n";
								echo "<a href='list_stations.php' >".TEXT_MENU_MOD_STATION."</a>";	
							echo "</li>\n";
						}
				
						// Jaugeage (Moulinets - Saumon)
						echo "<li class='simple'>\n";
							echo "<a href='data_jge.php' >".TEXT_MENU_MOD_JGE."</a>";
						echo "</li>\n";
					
						if($gestion_data > 0)
						{
							// Etalonnage relation pluie-débit
							echo "<li class='simple'>\n";
								echo "<a href='data_etl.php' >".TEXT_MENU_MOD_ETL."</a>";
							echo "</li>\n";
						}

						if($gestion_data > 0)
						{
							// Diagraphie
							echo "<li class='simple'>\n";
								echo "<a href='data_diag_piezo.php' >".TEXT_MENU_MOD_DIAG."</a>";
							echo "</li>\n";
						}

						if($gestion_data > 0)
						{
							// Etalonnage relation pluie-débit
							echo "<li class='simple'>\n";
							echo "<a href='list_agents.php' >".TEXT_MENU_MOD_AGENTS."</a>";	
							echo "</li>\n";
						}


					echo "</ul>\n";	
					
				echo "</div>";

			echo "</div>";


		//---------------------------------------------------------------------------------------------------
		//---------------------------------------------------------------------------------------------------
		// ORGANISATION DES ACTIONS DE TERRAIN

			echo "<div class='section'>";

				echo "<h4 class='toggle-header' data-menu-id='tour' style='border-bottom:1px solid #1abc9c;color:#000;'>";
					echo "<span>".TEXT_MENU_ROUND."</span>";
					echo "<span class='arrow'>&#9660;</span>"; // Flèche vers le bas
				echo "</h4>";

				echo "<hr>";

				echo "<div class='navigation'>";
							
					echo "<ul>\n";

						if($gestion_data > 0)
						{
							echo "<li class='simple'>\n";
								echo "<a href='suivi_terrain.php' >".TEXT_MENU_ROUND_TRACK."</a>";
							echo "</li>\n";
						}

						if($gestion_data > 0)
						{
							echo "<li class='simple'>\n";
								echo "<a href='gestion_tournees.php' >".TEXT_MENU_ROUND_MANAGE."</a>";
							echo "</li>\n";
						}

					echo "</ul>\n";	
					
				echo "</div>";

			echo "</div>";


		//---------------------------------------------------------------------------------------------------
		//---------------------------------------------------------------------------------------------------
		// PARAMETRAGE

		if($gestion_data > 0)
		{
			echo "<div class='section'>";

				echo "<h4 class='toggle-header' data-menu-id='param' style='border-bottom:1px solid #1862bd;color:#000;'>";
					echo "<span>".TEXT_MENU_SET."</span>";
					echo "<span class='arrow'>&#9660;</span>"; // Flèche vers le bas
				echo "</h4>";
				echo "<hr>";

				echo "<div class='navigation'>";
							
					echo "<ul>\n";

						// Zones géographiques pour les stations (Région Hydrologique / Tournée)
						echo "<li class='simple'>\n";
							echo "<a href='gestion_geo.php' >".TEXT_MENU_SET_GEO."</a>";	
						echo "</li>\n";

						// Type de chroniques 
						echo "<li class='simple'>\n";
							echo "<a href='gestion_type_data.php' >".TEXT_MENU_SET_TYPEC."</a>";	
						echo "</li>\n";

						// Définition des Codes Qualité
						echo "<li class='simple'>\n";
							echo "<a href='gestion_quality_data.php' >".TEXT_MENU_SET_QUAL."</a>";	
						echo "</li>\n";
						
						
						// Description des appareils pour les jaugeages avec les informations servant aux calculs
						echo "<li class='simple'>\n";					  
							echo "<a href='gestion_eq_jaugeage.php' >".TEXT_MENU_SET_EQJGE."</a>";
						echo "</li>\n";
						
						// OPTIONS
						echo "<li class='simple'>\n";
							echo "<a href='gestion_options.php' >".TEXT_MENU_SET_OPTION."</a>";	
						echo "</li>\n";

						// Exportation data paramètre
						echo "<li class='simple'>\n";							
							echo "<a href='export_param.php' >".TEXT_MENU_SET_TRANSF."</a>";					
						echo "</li>\n";

						/*
						echo "<li class='simple'>\n";
							echo "<a href='gestion_pdt.php'>".htmlaccent('Pas de temps')."</a>";	
							//echo htmlaccent('Pas de temps');
						echo "</li>\n";
						

						echo "<li class='simple'>\n";
							//echo "<a href='list_equipements.php' >".htmlaccent('Formats d\'importation')."</a>";
							echo htmlaccent('Formats d\'importation');
						echo "</li>\n";
						*/
						
					
					echo "</ul>\n";	
					
				echo "</div>";

			echo "</div>";
		}
		
		//---------------------------------------------------------------------------------------------------
		//---------------------------------------------------------------------------------------------------
		// SUIVI ET CONTROLE DES ACTIONS 
		
		if($gestion_data > 0)
		{
			echo "<div class='section'>";

				echo "<h4 class='toggle-header' data-menu-id='act' style='border-bottom:1px solid #aa1002;color:#000;'>";
					echo "<span>".TEXT_MENU_HP."</span>";
					echo "<span class='arrow'>&#9660;</span>"; // Flèche vers le bas
				echo "</h4>";
				echo "<hr>";
				
				echo "<div class='navigation'>";
							
					echo "<ul>\n";
				
						echo "<li class='simple'>\n";
							echo "<a href='list_imports.php'>".TEXT_MENU_HP_TRACKIMPORT."</a>";	
						echo "</li>\n";

						echo "<li class='simple'>\n";
							echo "<a href='list_exports.php' >".TEXT_MENU_HP_TRACKEXPORT."</a>";						
						echo "</li>\n";
						
						echo "<li class='simple'>\n";
							echo "<a href='list_actions.php' >".TEXT_MENU_HP_ACTIONS."</a>";						
						echo "</li>\n";

					echo "</ul>\n";	
					
				echo "</div>";

			echo "</div>";
		}
		
		//---------------------------------------------------------------------------------------------------
		//---------------------------------------------------------------------------------------------------
		// RESSOURCES
			echo "<div class='section'>";
		
				echo "<h4 class='toggle-header' data-menu-id='ress' style='border-bottom:1px solid #fe9b00;color:#000;'>";
					echo "<span>".TEXT_MENU_RESSOURCE."</span>";
					echo "<span class='arrow'>&#9660;</span>"; // Flèche vers le bas
				echo "</h4>";
						
				echo "<div class='navigation'>";		
					
					echo "<ul>\n";
					
						echo "<li class='simple'>\n";
							echo "<a href='index.php' >".TEXT_MENU_RESSOURCE_FIRST."</a>";
						echo "</li>\n";
						
						echo "<li class='simple'>\n";
							//echo "<a href='help.php'>".htmlaccent('Aide')."</a>";
							echo TEXT_MENU_RESSOURCE_HELP;	
						echo "</li>\n";
						
						echo "<li class='simple'>\n";
							//echo "<a href='conditions.php'>".htmlaccent('Conditions')."</a>";
							echo TEXT_MENU_RESSOURCE_CONDITION;	
						echo "</li>\n";

						echo "<li class='simple'>\n";				
							echo "<a href='mailto:".MAIL_WEBMASTER."'>".TEXT_MENU_RESSOURCE_CONTACT."</a>";				
						echo "</li>\n";
						
					echo "</ul>\n";	
				
				echo "</div>";

			echo "</div>";
	
	echo "<hr>";
	echo "</div>";	
	
echo "<hr>";
echo "</div>";	


/*
@ob_flush();
@flush();
@ob_flush();
@flush();
 */   
?>

<script>

	// Passer les états des menus à JavaScript
	const menuStates = <?php echo json_encode($menu_states); ?>;

	$(document).ready(function() 
	{
		// Appliquer l'état initial des menus
		$('.toggle-header').each(function() 
		{
			const menuId = $(this).data('menu-id');
			const isOpen = menuStates[menuId] === 1;

			const navigation = $(this).nextAll('.navigation').first();
			const arrow = $(this).find('.arrow');

			if (isOpen) {
				navigation.show();
				arrow.html('&#9650;'); // Flèche vers le haut
			} else {
				navigation.hide();
				arrow.html('&#9660;'); // Flèche vers le bas
			}
		});

		$('.toggle-header').click(function() 
		{
			// Trouver le contenu associé à ce titre		
			const id_user = <?php echo json_encode($id_user); ?>;
			const navigation = $(this).nextAll('.navigation').first();
			const menuId = $(this).data('menu-id'); // Récupérer l'identifiant du menu
			const isOpen = navigation.is(':visible');
			

			navigation.slideToggle('slow', function() 
			{
				// Changer l'icône de la flèche
				const arrow = $(this).prevAll('.toggle-header').find('.arrow');
				
				if (navigation.is(':visible')) 
				{
					arrow.html('&#9650;'); // Flèche vers le haut
				} else 
				{
					arrow.html('&#9660;'); // Flèche vers le bas
				}
				

				const dataToSend = {
									id_user: id_user,
									menu_id: menuId,
									is_open: !isOpen // L'état sera inversé après le slideToggle
								};

				// Convertir l'objet en JSON
				const jsonData = JSON.stringify(dataToSend);				
				
				// Effectuer une requête AJAX asynchrone
				const xhr = new XMLHttpRequest();
				xhr.open("POST", "include/structure/box/process_menu.php", true);
				xhr.setRequestHeader("Content-Type", "application/json");
				xhr.send(jsonData);

			});
		});
	});

</script>