<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
*/

/*
echo "<div id='pied_page'>";

	echo "<div id='copyright'>";	
		
		//echo htmlaccent('powered by - &copy; Vai-Natura 2011');
		//echo "<a href='mailto:".MAIL_WEBMASTER."'>".htmlaccent('powered by - &copy; Vai-Natura 2011')."</a>";
		echo "<a href='http://www.vai-natura.com' TARGET='_blank'>".htmlaccent('&copy; 2024 Vai-Natura. All rights reserved.')."</a>";
	echo "</div>";


echo "</div>";
*/

// Fermeture de session 

if($autorisation){regenerer_id($sql_link);}


tep_db_close($sql_link);
tep_session_end();

?>

<script type="text/javascript">
		
	infoMsg = document.getElementById('contenu_info');
	if(infoMsg)
	{
		infoMsg.addEventListener('click', function() {
			infoMsg.style.display = 'none';
		});

		// Ajout d'un gestionnaire d'événements pour la touche Echap
		document.addEventListener("keydown", function(event) 
		{
			if (event.key === "Escape") 
			{
				// Ferme le popup et le popup d'info s'il a été ouvert
				infoMsg.style.display = "none";
			}
		});

	}
	

	
	
</script>