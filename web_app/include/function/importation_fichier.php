<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/


/***************************************************/
/* fonction affichage fichier dans formulaire      */
/***************************************************/


function affiche_fich_form($type,$init_langue,$class_img,$modif,$array_img,$chemin)
{
	$html_src = '';
	
	$html_src .= "<div id='boite1'>\n";
	$html_src .= "<h2>".htmlaccent('Charger un fichier')."</h2>\n";
		
	if($modif)
	{
		if(tep_not_null($array_img[$type]))
		{
			$contenu = explode(".", $array_img[$type]);	
			
			$html_src .= "<div class='info_img'>";	
			
				$html_src .= $contenu[0];
			
				$html_src .= "<br>";
				$html_src .= "<input name='file_" . $type . "_" . $init_langue . "' type='file' class='file'>\n";
			
				$html_src .= "<p>".htmlaccent('Poids maximum du fichier (en ko) : ')."<b>" . $class_img->structure['taille']/1000 . "</b></p>";
				$html_src .= "<p>".htmlaccent('Formats autorisés : ')."<b>" . $class_img->structure['extension'] . "</b></p>";
				//$html_src .= "<p class='red'>".htmlaccent('Pour supprimer le fichier, les champs de saisie doivent être vides.')."</p>";
			
			$html_src .= "</div>";
		}
		else
		{	
			$html_src .= "<div class='info_img'>";
			
				$html_src .= "<input name='file_" . $type . "_" . $init_langue . "' type='file' class='file'>\n";	
				
				$html_src .= "<p>".htmlaccent('Poids maximum du fichier (en ko) : ')."<b>" . $class_img->structure['taille']/1000 . "</b></p>";
				$html_src .= "<p>".htmlaccent('Formats autorisés : ')."<b>" . $class_img->structure['extension'] . "</b></p>";
				//$html_src .= "<p class='red'>".htmlaccent('Pour supprimer le fichier, les champs de saisie doivent être vides.')."</p>";			
			
			$html_src .= "</div>";
		}
		
	}
	else
	{
		$html_src .= "<div class='info_img'>";
	
			$html_src .= "<input name='file_" . $type . "_" . $init_langue . "' type='file' class='file'>\n";
			
			$html_src .= "<p>".htmlaccent('Poids maximum du fichier (en ko) : ')."<b>" . $class_img->structure['taille']/1000 . "</b></p>";
			$html_src .= "<p>".htmlaccent('Formats autorisés : ')."<b>" . $class_img->structure['extension'] . "</b></p>";
			//$html_src .= "<p class='red'>".htmlaccent('Pour supprimer le fichier, les champs de saisie doivent être vides.')."</p>";
			
		$html_src .= "</div>";
	}
	
	
	$html_src .= "<hr>";
	$html_src .= "</div>";
	
	return $html_src;	
}



/***************************************************/
/* fonction validation image - galeries		   */
/***************************************************/

function valid_file($name,$file,$class_img,$table,$id,$champ,$chemin)
{
    $error=-1;	    
    
    if(tep_not_null($name) && tep_not_null($_FILES[$file]['name']))
    {
    	
			$ext = extension_img($class_img->structure['extension'],$file);
			
			if($ext=='no'){$error=2;}
			else
			{
				$nom_img_ext = fich_img_ext($name,$ext);
				
				//vérifier si le fichier existe dans les fichiers articles
				$tab_img_query = tep_db_query($sql_link,"SELECT ".$id." FROM " . $table . " WHERE ".$champ."='" . $nom_img_ext . "'");	
				$tab_img = tep_db_fetch_array($tab_img_query);
				
				if(tep_not_null($tab_img[$id])){$error=3;}
				else
				{
					if(!verif_taille($file,$class_img->structure['taille'])){$error=4;}
					else
					{
						if(!upload($file,$nom_img_ext,$chemin)){$error=6;}
						else{$error=0;}					
					}	
				}
			}
     }     
     if(!tep_not_null($name) && tep_not_null($_FILES[$file]['name'])){$error=1;}
     
     return $error;
}


/*****************************************************/
/* pour la suppression des fichier  		     */
/*****************************************************/

function verif_suppr_fich($table,$id,$valeur_id,$id_langue,$champ,$chemin)
{
	//vérifier si une image existe	
	$tab_img_query = tep_db_query($sql_link,"SELECT * FROM ".$table." WHERE ".$id."=".$valeur_id." AND langue_id=".$id_langue);	
	$tab_img = tep_db_fetch_array($tab_img_query);
	
	
	if($tab_img[$champ])
	{	
		if(file_exists($chemin.$tab_img[$champ]))
		{unlink($chemin.$tab_img[$champ]);} 
		
		tep_db_query($sql_link,"UPDATE ".$table." SET ".$champ." = '' WHERE ".$id."=".$valeur_id);
		
		return true;
	}
	else{return false;}
}
