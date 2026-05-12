<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
*/

// Verification que l'IP qui tente de se connecté n'est pas blacklisté : ip_out
function ip_out($sql_link)
{	
	$ip_out_query = tep_db_query($sql_link,"SELECT * FROM ".TABLE_IP_OUT." WHERE ip='".getIP()."'");
	$ip_out_array = tep_db_fetch_array($ip_out_query);
	
	if(isset($ip_out_array) && tep_not_null($ip_out_array['id'])){return true;}
	else{return false;}
}



// Vérification d'un changement d'IP en cours de session : ip_suspect
function ip_suspect($sql_link)
{	
	//info de session, on vérifie que c'est bien le premier accès du moment
	$session_info = getAdminInfo($sql_link);

	if(!tep_not_null($session_info))
	{
	  	$ip_suspect_query = tep_db_query($sql_link,"SELECT * FROM ".TABLE_IP_SUSPECT." WHERE ip='".getIP().
								   "' AND dns='".gethostbyaddr(getIP())."'");
 	 	$ip_suspect_array = tep_db_fetch_array($ip_suspect_query);

	  	if(tep_not_null($ip_suspect_array['id']) && ($ip_suspect_array['last_access'] != date("Y/m/d H:i")))
	  	{
			  tep_db_query($sql_link,"UPDATE ".TABLE_IP_SUSPECT." SET last_access='" . date("Y/m/d H:i") . 
									"' WHERE id='" . $ip_suspect_array['id'] . "'");				
		  	return true;
	  	}
	  	else{return false;}
	}
	else{return false;}
}


// Enregistrement d'un IP suspect
function save_ip_suspect($sql_link,$type)
{
	if(tep_not_null(getIP()))
	{
		$ctrl_last_access = date("Y/m/d H:i");
		
		tep_db_query($sql_link,"INSERT INTO ".TABLE_IP_SUSPECT."(ip,dns,browser,type,date,heure,last_access) " . 
						  "VALUES ('" . getIP() .
						           "', '" . gethostbyaddr(getIP()) .
						           "', '" . getUser_agent() .
						           "', '" . $type .
					        	   "', now()" .
					           	   ", current_time()" .
					           	   ",'" . $ctrl_last_access . "')");
	}
}



// Fonction pour éviter l'aspiration du site - mais plus d'actualité. 
function ip_anti_aspirateur($sql_link)
{
	$compteur_page = 0;
	$date_connect = date("Y/m/d H:i");
	
	// Suppression des ip contrôlés dont la date ne correspond plus
	tep_db_query($sql_link,"DELETE FROM ".TABLE_IP_ASPIRATEUR." WHERE date_connect <> '".$date_connect."'");
	
	// Récupération puis contrôle des ip qui se sont connectés dans la minute
	$ip_ctrl_query = tep_db_query($sql_link,"SELECT * FROM ".TABLE_IP_ASPIRATEUR." WHERE ip='".getIP()."'");
	$ip_ctrl_array = tep_db_fetch_array($ip_ctrl_query);	
	if(tep_not_null($ip_ctrl_array['ip']))
	{
		$compteur_page = $ip_ctrl_array['nb_pages'] + 1;
		tep_db_query($sql_link,"UPDATE ".TABLE_IP_ASPIRATEUR." SET nb_pages=" . $compteur_page . 
							   " WHERE ip='" . $ip_ctrl_array['ip'] . "'");	
	}
	else
	{
		$compteur_page = 1;
		tep_db_query($sql_link,"INSERT INTO ".TABLE_IP_ASPIRATEUR."(ip,date_connect,nb_pages) " . 
						     "VALUES ('" . getIP() .
						       	      "', '" . $date_connect .
						       	      "', 1)");	
	}
	
	
	// Si on a plus de 25 pages édités par minutes alors on rempli la table ip_out
	if($compteur_page > 25)
	{
		save_ip_suspect('auto_aspiration');		           
		return true;				           
	}
	else{return false;}
}


// Stopper une tentative de connexion par robot, pas plus de 5 tentatives d'accès consécutives sont autorisées.

function ip_login_enforce($sql_link)
{
	$compteur_essai_log = 0;
	$date_connect = date("Y/m/d H:i");
	
	// Supression des ip contrôlés dont la date ne correspond plus
	tep_db_query($sql_link,"DELETE FROM ".TABLE_IP_LOGIN." WHERE date_connect <> '".$date_connect."'");
	
	// Récupération puis contrôle des ip qui se sont connectés dans la minute
	$ip_ctrl_query = tep_db_query($sql_link,"SELECT * FROM ".TABLE_IP_LOGIN." WHERE ip='".getIP()."'");
	$ip_ctrl_array = tep_db_fetch_array($ip_ctrl_query);	
	if(isset($ip_ctrl_array) && tep_not_null($ip_ctrl_array['ip']))
	{
		$compteur_essai_log = $ip_ctrl_array['nb_tentatives'] + 1;
		tep_db_query($sql_link,"UPDATE ".TABLE_IP_LOGIN." SET nb_tentatives=" . $compteur_essai_log . 
							   " WHERE ip='" . $ip_ctrl_array['ip'] . "'");	
	}
	else
	{
		$compteur_essai_log = 1;
		tep_db_query($sql_link,"INSERT INTO ".TABLE_IP_LOGIN."(ip,date_connect,nb_tentatives) " . 
						     "VALUES ('" . getIP() .
						       	      "', '" . $date_connect .
						       	      "', 1)");	
	}
	
	
	// Si on a plus de 10 pages édités par minutes alors on rempli la table ip_out
	if($compteur_essai_log > 5)
	{
		save_ip_suspect('auto_login_force');			           
		return true;				           
	}
	else{return false;}
}



/* Information IP de l'utilisateur */
function getIP()
{
	if(getenv('HTTP_X_FORWARDED_FOR'))
	{
		return getenv('HTTP_X_FORWARDED_FOR');
	}
	elseif(getenv('HTTP_CLIENT_IP'))
	{
		return getenv('HTTP_CLIENT_IP');
	}
	else
	{
		return getenv('REMOTE_ADDR');
	}
}

/* Information Navigateur de l'utilisateur */
function getUser_agent()
{
	return getenv('HTTP_USER_AGENT');
}


/* Redefinir et redécouper HTTP_USER_AGENT */
function getBrowser($user_agent)
{
	$browser = "";
	
	if ((ereg("Nav", $user_agent)) || (ereg("Gold", $user_agent)) ||
			(ereg("X11", $user_agent)) || (ereg("Mozilla", $user_agent)) ||
			(ereg("Netscape", $user_agent))
			AND (!ereg("MSIE", $user_agent)) 
			AND (!ereg("Konqueror", $user_agent))
			AND (!ereg("Firefox", $user_agent))
			AND (!ereg("Safari", $user_agent)))
	        	
	        $browser = "Netscape";
	elseif (ereg("Opera", $user_agent))
	        $browser = "Opera";
	elseif (ereg("MSIE", $user_agent))
	        $browser = "MSIE";
	elseif (ereg("Lynx", $user_agent))
	        $browser = "Lynx";
	elseif (ereg("WebTV", $user_agent))
	        $browser = "WebTV";
	elseif (ereg("Konqueror", $user_agent))
	        $browser = "Konqueror";
	elseif (ereg("Safari", $user_agent))
	        $browser = "Safari";
	elseif (ereg("Firefox", $user_agent))
	        $browser = "Firefox";
	elseif ((eregi("bot", $user_agent)) || (ereg("Google", $user_agent)) ||
	(ereg("Slurp", $user_agent)) || (ereg("Scooter", $user_agent)) ||
	(eregi("Spider", $user_agent)) || (eregi("Infoseek", $user_agent)))
	        $browser = "Bot";
	else
	        $browser = "Autre";
	        
	
	return $browser;
}


// Récupérer l'OS utilisé par l'ordinateur qui tente de se connecter
function getOS($user_agent)
{
	$os = "";

	if (ereg("Win", $user_agent))
	    $os = "Windows";
	elseif ((ereg("Mac", $user_agent)) || (ereg("PPC", $user_agent)))
	    $os = "Mac";
	elseif (ereg("Linux", $user_agent))
	    $os = "Linux";
	elseif (ereg("FreeBSD", $user_agent))
	    $os = "FreeBSD";
	elseif (ereg("SunOS", $user_agent))
	    $os = "SunOS";
	elseif (ereg("IRIX", $user_agent))
	    $os = "IRIX";
	elseif (ereg("BeOS", $user_agent))
	    $os = "BeOS";
	elseif (ereg("OS/2", $user_agent))
	    $os = "OS/2";
	elseif (ereg("AIX", $user_agent))
	    $os = "AIX";
	else
	    $os = "Autre";

	return $os;	
}

?>