<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/



/* verification de la validité du password */
function tep_validate_password($plain, $encrypted) 
{
    if (tep_not_null($plain) && tep_not_null($encrypted)) 
    {
	// split apart the hash / salt
      $stack = explode(':', $encrypted);

      if (sizeof($stack) != 2) return false;

		

      if (md5($stack[1] . $plain) == $stack[0]) 
      {
        return true;
      }
    }

    return false;
}


/* codage du password */
function tep_encrypt_password($plain) 
{
    $password = '';

    for ($i=0; $i<10; $i++) 
    {
      $password .= tep_rand();
    }

    $salt = substr(md5($password), 0, 2);

    $password = md5($salt . $plain) . ':' . $salt;

    return $password;

}


//pass alea
function pass_alea() 
{
    $char = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';	
    $pass = '';
    
    $max = strlen($char)-1;
    $taille = rand(6, 8);
    
    for ($i=1;$i<=$taille;$i++) 
    {
        $pass .= $char[rand(0, $max)];
    }
    
    return $pass;
} 



?>
