<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

class type_fichier
{
	function description($ext_s,$taille=0,$img_default='')
	{
		$this->structure = array('extension'=>$ext_s,'taille'=>$taille,'img_default'=>$img_default);
	}
}
?>
