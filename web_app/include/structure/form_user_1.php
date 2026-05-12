<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/




echo "<div id='onglet_contenu'>\n";

	echo "<div id='boite1' class='first'>\n";
				
		echo "<div id='boite_small'>\n";		
		
			echo "<h2>".htmlaccent('Login d\'accès <br><span>Ce champ ne peut contenir ni espace, ni accent, ni caractères spéciaux<span>')."</h2>\n";
				
			if($modif){echo "<input name='login' id='login' value='".$login."' class='titre' type='text'>";}
			else{echo "<input name='login' id='login' class='titre' type='text'>";}
		
		echo "</div>\n";				
		
				
	echo "<hr>\n";
	echo "</div>\n";
	
	echo "<div id='boite1'>\n";
				
		echo "<div id='boite_small'>\n";		
		
			echo "<h2>".htmlaccent('Nom de l\'utilisateur')."</h2>\n";
				
			if($modif){echo "<input name='nom' id='nom' value='".$nom."' class='titre' type='text'>";}
			else{echo "<input name='nom' id='nom' class='titre' type='text'>";}
		
		echo "</div>\n";				
		
					
	echo "<hr>\n";
	echo "</div>\n";
	
	
	echo "<div id='boite1'>\n";
				
		echo "<div id='boite_small'>\n";		
		
			echo "<h2>".htmlaccent('Prénom de l\'utilisateur')."</h2>\n";
				
			if($modif){echo "<input name='prenom' id='prenom' value='".$prenom."' class='titre' type='text'>";}
			else{echo "<input name='prenom' id='prenom' class='titre' type='text'>";}
		
		echo "</div>\n";				
		
					
	echo "<hr>\n";
	echo "</div>\n";
	
	echo "<div id='boite1'>\n";
				
		echo "<div id='boite_small'>\n";		
		
			echo "<h2>".htmlaccent('Information complémentaire')."</h2>\n";
				
			if($modif){echo "<input name='info_user' id='info_user' value='".$info."' class='titre' type='text'>";}
			else{echo "<input name='info_user' id='info_user' class='titre' type='text'>";}
		
		echo "</div>\n";	
		
	echo "<hr>\n";
	echo "</div>\n";
	
	
	echo "<div id='boite1'>\n";
				
		echo "<div id='boite_small' >\n";		
		
			if($modif)
			{
				echo "<h3 class='pass' onclick=\"pass_reload(".$ref_id.",".$id_user.");\"><img src='".DIR_WS_IMG_ICO."reload.png' style='width:20px;'>";
					echo htmlaccent('Générer un nouveau mot de passe');
				echo "</h3>\n";
			}
			
			echo "<div id='pass_info'>\n";
				echo "<input name='pass' id='pass' class='pass' type='text' disabled='disabled'>";
				echo "<p>".htmlaccent('Copier ce mot de passe.<br> Pour des questions de sécurité, le mot de passe est crypté. Vous ne pourrez plus y accéder.')."</p>";
			echo "<hr>\n";
			echo "</div>\n";	
			
		echo "</div>\n";	
		
	echo "<hr>\n";
	echo "</div>\n";
	
	
echo "<hr>\n";
echo "</div>\n";
?>
