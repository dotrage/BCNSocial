<?php
	/* REMIND USERS OF NEXT SESSION */

	require_once('btstrp.php');
	
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past		
	
	$currenttime = mktime()+(60*60*2);

	$reminders = db_get_results("SELECT s.title, r.name as room, s.starttime, u.phone FROM bcn_users_session_assoc a INNER JOIN sessions s ON a.session_id = s.id INNER JOIN users u ON a.user_id = u.user_id INNER JOIN rooms r ON s.room = r.id WHERE u.phone IS NOT NULL AND u.verified = 1 AND s.starttime > '".$currenttime."' AND s.starttime <= '".($currenttime+300)."'");
	
	if (count($reminders)>0){
		foreach ($reminders as $reminder){
			if (strlen($reminder['phone']) >= 9){			//if (str($reminder['phone']) >= 9){
				$text = "Next session @ ".date("g:i a",$reminder['starttime'])." in ".$reminder['room'].": ";
				$rem_length = 160 - strlen($text);
				
				if (strlen($reminder['title']) > $rem_length){
					$title = substr($reminder['title'],0,$rem_length);
				}
				else{
					$title = $reminder['title'];
				}
				$text .= $title;
				
				send_sms($reminder['phone'],$text);
			}		
		}	
	}
	
?>
