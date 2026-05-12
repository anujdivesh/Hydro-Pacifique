<?php
	/*  
	----------------------------------------
	Copyright (c) 2024 - Vai-Natura
	----------------------------------------
	*/
	
	function suiviSession($sql_link)  
	{
		// Info de session
		$session_data = getAdminInfo($sql_link);
		
		// Si la session est ouverte, contrôle des infos de la session pour éviter le vol de session
		if(tep_not_null($session_data))
		{
			if(controleSessionInfo($sql_link,$session_data)) //session en cours valide
			{return true;}
			else
			{
				//le ctrl de session a renvoyé une erreur on enregistre dans la table des ip suspects
				save_ip_suspect($sql_link,'admin_session_modifies');
				return false;
			}
		}	
		else
		{return false;}
	}
	
	
	function regenerer_id($sql_link) 
	{
		// Info de session
		$session_data = getAdminInfo($sql_link) ; 	
		
		// Mise à jour de la session en cours avec renouvellement sid
		// session_regenerate_id(); 
				
		tep_db_query($sql_link,"UPDATE ".TABLE_SESSION." SET sid = \"" . session_id() . 
					"\", last_access = " . time() .
					" WHERE  id = " . $session_data['id']); 
	}
	
	
	function tep_session_end() 
	{
		return session_write_close();
	}
	
	
	
	// Récupération des infos de session en cours*/
	function getAdminInfo($sql_link)  
	{
		// Récupération des infos de la session en cours avec validation de l'existence de l'id_admin
		$sql = "SELECT s.id, s.sid, s.admin_id, s.last_access, s.ip, s.browser, 
						a.nom, a.prenom, a.info, a.email, a.active
						 FROM ".TABLE_SESSION." s
						 JOIN ".TABLE_USER." a ON s.admin_id=a.id
						 WHERE a.active=1 
						 AND s.sid='" . session_id() . "'";
		$session_data_query = tep_db_query($sql_link,$sql);
						 
						 
		return $session_data_array = tep_db_fetch_array($session_data_query);
	}
	
	
	// Vérification des paramètres de la session
	function controleSessionInfo($sql_link,$data) 
	{
		$ctrl = true;
		
		//if(!tep_not_null($data['last_access']) || !tep_not_null($data['ip']) || !tep_not_null($data['browser']) || !tep_not_null($data['admin_id']))
		if(!tep_not_null($data['last_access']) || !tep_not_null($data['admin_id']))
		{
			tep_db_query($sql_link,"UPDATE ".TABLE_SESSION." SET sid='' WHERE  id = ".$data['id']);
			$ctrl = false;
		}

		// On limite les contrôle pour éviter les changement de proxy 
		// On simplifie les contrôles sans s'arrêter sur l'ip ni le navigateur

		/*
		if(getIP() != $data['ip'])
		{
			tep_db_query($sql_link,"UPDATE ".TABLE_SESSION." SET sid='' WHERE id=".$data['id']);
			$ctrl = false;
		}	
	
		if(getUser_agent() != $data['browser'])
		{
			tep_db_query($sql_link,"UPDATE ".TABLE_SESSION." SET sid='' WHERE id=".$data['id']);
			$ctrl = false;
		}
		*/
	
		if(time() - $data['last_access'] >= SESSION_TIMEOUT)
		{
			tep_db_query($sql_link,"UPDATE ".TABLE_SESSION." SET sid='' WHERE id=".$data['id']);
			$ctrl = false;
		}
		return $ctrl;
	}
	
	
	function double_connexion($sql_link,$id,$nom) 
	{
		// Déjà connectée
		$sql = "SELECT * FROM ".TABLE_SESSION." WHERE admin_id=".$id." AND sid<>'' AND (".time()."-last_access)<".SESSION_TIMEOUT;
		$session_encours_query = tep_db_query($sql_link,$sql);    
		$session_encours = tep_db_fetch_array($session_encours_query); 
			
			if(isset($session_encours) && tep_not_null($session_encours['id']))
			{
				save_ip_suspect($sql_link,'double_connect_'.$nom);
				return true;
			}
			
			return false;
	}
	
	function clean_connexion($sql_link) 
	{
		// Déjà connectée
		$sql = "SELECT * FROM ".TABLE_SESSION." WHERE sid<>'' AND (".time()."-last_access)>=".SESSION_TIMEOUT;
		$session_clean_query = tep_db_query($sql_link,$sql);   
		
		while($session_clean = tep_db_fetch_array($session_clean_query))
		{
			//tep_db_query($sql_link,"UPDATE ".TABLE_SESSION." SET sid='' WHERE id=".$session_clean['id']);
			tep_db_query($sql_link,"DELETE FROM ".TABLE_SESSION." WHERE id=".$session_clean['id']);
		}
	}
	
?>