<?php
	require_once('btstrp.php');

	$currenttime = mktime()+(60*60*15)+(60*25);
	echo date("m/d/Y g:i a",$currenttime);
	echo "<hr>";
	
	$sql = "SELECT s.title, r.name as room, s.starttime, u.phone FROM bcn_users_session_assoc a INNER JOIN sessions s ON a.session_id = s.id INNER JOIN users u ON a.user_id = u.user_id INNER JOIN rooms r ON s.room = r.id WHERE u.phone IS NOT NULL AND u.verified = 1 AND s.starttime > '".$currenttime."' AND s.starttime <= '".($currenttime+300)."'";
	echo $sql;
	$reminders = db_get_results($sql);
	
	if (count($reminders)>0){
		foreach ($reminders as $reminder){
			if (strlen($reminder['phone']) >= 9 && $reminder['phone'] == "615-364-8615"){
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
				
				echo $reminder['phone'] . " -- " . $text;
				echo "<br>";
				
				error_log($reminder['phone'] . " -- " . $text);
			}		
		}		
	}
	
	error_log(date("g:i a",$currenttime),"reminder ran");	
	
	echo "<br><br><hr><br>";
	
	$results = db_get_results("SELECT s.starttime FROM sessions s INNER JOIN bcn_users_session_assoc a ON s.id = a.session_id WHERE a.user_id = 12767912 ORDER BY starttime");
	
	foreach($results as $result){
		echo date("g:i a",$result['starttime']);
		echo "<br>";
	}
	
	
	
	
	
	
/*
	
//	$url = "http://www.barcampnashville.org/bcn10/users/tched";
//	$url = "http://www.barcampnashville.org/bcn10/attending&page=".$_GET['p'];

	function getUser($id){

		$url = "http://www.barcampnashville.org/bcn10/user/".$id;
		unset($insert);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);	
	   	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
   		$result = curl_exec($ch);
	   	curl_close($ch);


	//FOR USER PAGE
	    preg_match_all('/<a\shref=\"\/bcn10\/session\/([^\"]*)\">(.*)<\/a>/siU', $result, $sessions);
   		preg_match('/<a\shref=\"http:\/\/twitter.com\/(.*)\"/siU', $result, $twitter);
	   	if (empty($twitter)){
   			preg_match('/<a\shref=\"http:\/\/www.twitter.com\/(.*)\"/siU', $result, $twitter);
	   	}

		if (!empty($twitter[1])){
  			echo $twitter[1]."<hr>";
  			db_query("INSERT IGNORE INTO users (screen_name) VALUES ('".$twitter[1]."')");
			$user = db_get_row("SELECT id FROM users WHERE screen_name = '".$twitter[1]."'");
  			foreach ($sessions[1] as $session){
  				if (isset($insert)){
  					$insert .= ",('".$user['id']."','".$twitter[1]."','".mysql_escape_string($session)."')";  				
  				}
  				else{
  					$insert = "('".$user['id']."','".$twitter[1]."','".mysql_escape_string($session)."')";
  				}
  			}
  			
  			if (isset($insert)){
	  			db_query("INSERT INTO bcn_users_session_assoc (user_id,screen_name,session_slug) VALUES ".$insert);
  				echo "INSERT INTO bcn_users_session_assoc (user_id,screen_name,session_slug) VALUES ".$insert;  			
  			}
		}
		db_query("UPDATE bcn_user_queue SET status = 1 WHERE user_id = '".$id."'");
	}

//FOR USERS PAGES
//  preg_match_all('/<a\sclass=\"user-name\"\shref=\"user\/(.*)<\/a>/siU', $result, $array);
  
  
function get_string_between($string, $start, $end){
	$string = " ".$string;
	$ini = strpos($string,$start);
	if ($ini == 0) return "";
	$ini += strlen($start);
	$len = strpos($string,$end,$ini) - $ini;
	return substr($string,$ini,$len);
}

*/


   	
?>