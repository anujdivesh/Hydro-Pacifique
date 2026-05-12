<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

class type_img 
{
	function description($ext_s,$largeur=0,$hauteur=0,$taille=0,$img_default='')
	{
		$this->structure = array('extension'=>$ext_s,'largeur'=>$largeur,'hauteur'=>$hauteur,'taille'=>$taille,'img_default'=>$img_default);
	}
}
?>
