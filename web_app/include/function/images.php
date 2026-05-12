<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/


/***************************************************/
/* fonction affichage image dans formulaire        */
/***************************************************/


function affiche_img_form($type,$class_img,$modif,$array_img,$chemin,$type_base,$lien_suppr)/*,$suppr_img)*/
{
	$width='';$height='';
		
	$html_src = '';
	
	$html_src .= "<div id='boite_media'>\n";
	
		
		if($modif)
		{
			if(tep_not_null($array_img[$type]))
			{			
				$html_src .= "<h2>Photo " . ucfirst($type) . " : ".$array_img[$type]."</h2>\n";
				$html_src .= "<img src='" . $chemin . $array_img[$type] . "' style='cursor:default;' ".$width." ".$height.">\n";	
				$html_src .= "<img src='".DIR_WS_IMG_ICO."b_drop.png' class='del' title='Supprimer la photo' onClick=\"confirm_suppr('" . $lien_suppr . "','la photo','" . $array_img[$type] . "');\">\n";				
			}
			else
			{
				$html_src .= "<h2>Photo " . ucfirst($type) . "</h2>\n";
				$html_src .= "<img src='".DIR_WS_IMG_DEFAULT.$class_img->structure['img_default']."' style='cursor:default;' ".$width." ".$height.">\n";
			}
			
		}
		else
		{
			$html_src .= "<h2>Photo " . ucfirst($type) . "</h2>\n";
			$html_src .= "<img src='".DIR_WS_IMG_DEFAULT.$class_img->structure['img_default']."' style='cursor:default;' ".$width." ".$height.">\n";
		}
	
		$html_src .= "<hr>";
		$html_src .= "<input name='file_" . $type . "' type='file' class='file'>\n";					
		$html_src .= "<input class='button' style='margin:0;' type='submit' value='Enregistrer'>\n";
	
	
		$html_src .= "<div class='info_img'>";
				
			$largeur = $class_img->structure['largeur'];if($largeur==''){$largeur='-';}
			$hauteur = $class_img->structure['hauteur'];if($hauteur==''){$hauteur='-';}
			
			$html_src .= "<p>".htmlaccent('Taille fixe de l\'image lxh (en pixel) : ')."<b>" . $largeur . "x" . $hauteur . "</b></p>";
			$html_src .= "<p>".htmlaccent('Poids maximum de l\'image (en ko) : ')."<b>" . $class_img->structure['taille']/1000 . "</b></p>";
			$html_src .= "<p>".htmlaccent('Formats autorisés : ')."<b>" . $class_img->structure['extension'] . "</b></p>";	
			
		$html_src .= "<hr>";
		$html_src .= "</div>";
	
	$html_src .= "<hr>";
	$html_src .= "</div>";
	
	return $html_src;	
}



/***************************************************/
/* fonction validation image - galeries		   */
/***************************************************/

function valid_img($file,$class_img,$table,$id,$champ,$chemin)
{
    $error=-1;	    
	
    if(tep_not_null($_FILES[$file]['name']))
    {
			$ext = extension_img($class_img->structure['extension'],$file);
			
			if($ext=='no'){$error=2;}
			else
			{
				$nom_img_ext = $_FILES[$file]['name'];
				
				//vérifier si l'image existe dans les images articles
				$tab_img_query = tep_db_query($sql_link,"SELECT ".$id." FROM " . $table . " WHERE ".$champ."='" . $nom_img_ext . "'");	
				$tab_img = tep_db_fetch_array($tab_img_query);
				
				if(tep_not_null($tab_img[$id])){$error=3;}
				else{
					if(!verif_taille($file,$class_img->structure['taille'])){$error=4;}
					else{
						if(!verif_dim_exact($file,$class_img->structure['largeur'],$class_img->structure['hauteur'])){$error=5;}
						else
						{
							if(!upload($file,$nom_img_ext,$chemin)){$error=6;}
							else
							{
								chmod ($chemin.$nom_img_ext, 0755);							
								$error=0;
							}
						}	
					}	
				}
			}
     }     
     //if(!tep_not_null($name) && tep_not_null($_FILES[$file]['name'])){$error=1;}
     
     return $error;
}


/***************************************************/
/* fonction suppression image			   */
/***************************************************/
function verif_suppr_img($table,$id,$valeur_id,$champ,$chemin)
{
	//vérifier si une image existe	
	$tab_img_query = tep_db_query($sql_link,"SELECT * FROM ".$table." WHERE ".$id."=".$valeur_id);	
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










/***************************************************/
/***************************************************/
/* fonctions que l'on réutilisera 		   */
/***************************************************/
/***************************************************/


function fich_img_ext($nom,$ext)
{
	$nom = noaccent($nom);
	$nom = preg_replace('/([^.a-z0-9]+)/i', '_',  $nom);  
	$nom_ext = $nom . $ext;
	
	return $nom_ext;
}


/* extension du fichier */
function extension_img($list,$fich)
{

	$extensions_valides = explode(",", $list);
	$extension_upload = substr(strrchr($_FILES[$fich]['name'], '.'),1);


	if(in_array($extension_upload,$extensions_valides)){$ext = "." .  $extension_upload;}
	else{$ext = "no";}

	return $ext ;
}

/* contrôle pour dimensions */
function verif_dim_exact($fich,$largeur,$hauteur)
{
	$dim=false;
	$dim_i=0;
	
	$imgt = getimagesize($_FILES[$fich]['tmp_name']);
	
	$cond_largeur=$imgt[0].$largeur;
	$cond_hauteur=$imgt[1].$hauteur;
	
	if(($cond_largeur == 0) && ($cond_hauteur == 0)){return true;}
	
	$condition_l='if(("'.$largeur.'"=="") || ('.$cond_largeur.')){$dim_i++;}';
	$condition_h='if(("'.$hauteur.'"=="") || ('.$cond_hauteur.')){$dim_i++;}';
	
	eval($condition_l);
	eval($condition_h);
	
	if($dim_i==2){$dim=true;}
		
	return $dim;
}

/* contrôle pour taille (en Ko) */
function verif_taille($fich,$taille_max)
{
	$taille = true;
	
	if($_FILES[$fich]['size'] > $taille_max){$dim = false;}
	
	return $taille;
}

/* upload */
function upload($fich,$nom,$chemin)
{
	$upload = true;
	
	$savefile = $chemin.$_FILES[$fich]['name'];
   	$temp = $_FILES[$fich]['tmp_name'];
   		
   	if(move_uploaded_file($temp, $savefile)){rename($savefile,$chemin.$nom);}
	else{$upload = false;}
	
	return $upload;			
}//end function


/* retailler une image proportionnellement */
function resize($img,$largeur_max,$hauteur_max)
{
	$rapport = 1;
	
	$imgt = getimagesize($img);
	$largeur = $imgt[0];
 	$hauteur = $imgt[1];
 	
 	
 	
 	if($largeur >= $hauteur)
 	{
 		$rapport = $largeur / $largeur_max;
 		//$largeur_max = $largeur;
 		$hauteur_max = $hauteur / $rapport;
 	}
 	else
 	{
 		$rapport = $hauteur / $hauteur_max;
 		//$hauteur_max = $hauteur;
 		$largeur_max = $largeur / $rapport;
 	}
	
	
	$taille_min[0] = $largeur_max;
	$taille_min[1] = $hauteur_max;
	
	return $taille_min;			
}







?>
