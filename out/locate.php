<?php
	/* LOCATE USER DESIGNATED IN DIRECT MESSAGE AND REPORT WHEREABOUTS */

	require_once('btstrp.php');
	
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past		

	function find_user($sn){
		$sn = trim($sn);
		$current = mktime()+(60*60*2);

		$exists = db_get_row("SELECT * FROM users WHERE screen_name = '".$sn."'");
		
		if ($exists){
		
			$user = db_get_results("SELECT r.name as room FROM bcn_users_session_assoc u INNER JOIN sessions s ON u.session_id = s.id INNER JOIN rooms r ON s.room = r.id WHERE u.screen_name = '".$sn."' AND s.starttime <= '".$current."' AND (s.starttime+1800) >= '".$current."'");
			
			if (count($user)>1){
				foreach ($user as $u){
					if (isset($roomlist)){
						$roomlist .= " or " . $u['room'];
					}
					else{
						$roomlist = $u['room'];				
					}
				}
				return $sn." should be in ".$roomlist.".";	
			}
			else if (count($user)==1){
				return $sn." should be in ".$user[0]['room'].".";
			}
			else{
				//Future Search
				$current = $current + (60*15);
				$user = db_get_results("SELECT r.name as room FROM bcn_users_session_assoc u INNER JOIN sessions s ON u.session_id = s.id INNER JOIN rooms r ON s.room = r.id WHERE u.screen_name = '".$sn."' AND s.starttime <= '".$current."' AND (s.starttime+1800) >= '".$current."'");
				if (count($user)>1){		
					foreach ($user as $u){
						if (isset($roomlist)){
							$roomlist .= " or " . $u['room'];
						}
						else{
							$roomlist = $u['room'];				
						}
					}
					return $sn." should be in ".$roomlist." shortly.";				
				}
				else if (count($user)==1){	
					return $sn." should be in ".$user[0]['room']." shortly.";
				}
				else{
					return "Sorry, ".$sn." is not scheduled to be in any particular room over the next 30 minutes.";
				}
			}
		}
		else{
			return "Sorry, ".$sn." could not be found in the BCN Social system.";
		}
	}	

	$since_id = get_meta_value("dm_since_id");
	$token = get_meta_value("twitter_token");
	$secret = get_meta_value("twitter_secret");
	
	$twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $token, $secret);
	$messages = $twitter->get('direct_messages', array('since_id' => $since_id));
	
	if (count($messages)>0){			
		foreach ($messages as $message){
			$user_status = find_user($message->text);	
			if ($user_status){					
				send_direct_message($message->sender->id,$user_status);								
			}
		}
		set_meta_value("dm_since_id",$messages[0]->id);
	}		
?>