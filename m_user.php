<?php 
require_once('config.php');
require_once('inc/db.inc');

if($_SERVER['HTTP_HOST'] != "bcnsocial.com" && $_SERVER['HTTP_HOST'] != "www.bcnsocial.com"){
	$domain = explode(".",$_SERVER['HTTP_HOST']);
	$screenname = $domain[0];	
	
	$id = $sig = "";
	
	$mobile = 1;
	
	$user = db_get_row("SELECT * FROM users WHERE screen_name = '".$screenname."'");
	$userview = "1";
	
	if ($user){
		
		$sessions = db_get_results("SELECT s.id, s.title, s.starttime, r.name as room, s.slug, IF(a.screen_name IS NOT NULL,1,0) as rsvp FROM sessions s INNER JOIN rooms r ON s.room = r.id LEFT JOIN bcn_users_session_assoc a ON s.id = a.session_id AND a.user_id = '".$user['user_id']."' ORDER BY s.starttime, s.room");		

		$page_content = "<table class=\"schedule-grid\" cellpadding=\"3\" cellspacing=\"0\">
		<tr>
		<td style=\"border-bottom: solid 1px #999;\" width=\"60\">Time</td>
		<td style=\"border-bottom: solid 1px #999;\" width=\"140\">Room</td>		
		<td style=\"border-bottom: solid 1px #999;\">Session</td>
		</tr>\n";
				
		foreach ($sessions as $session){
			if ($session['rsvp']){
				$rsvp_class = "reserved";
			}
			else{
				$rsvp_class = "not-reserved";
			}
		
			if (strlen($session['title'])>60){
				$title = substr($session['title'],0,60)."...";
			}
			else{
				$title = $session['title'];
			}
		
			$page_content .= "<tr>
			<td class=\"".$rsvp_class."\" valign=\"top\" style=\"border-bottom: solid 1px #999;\">".date("g:i a",$session['starttime'])."</td>
			<td class=\"".$rsvp_class."\" valign=\"top\" style=\"border-bottom: solid 1px #999;\">".$session['room']."</td>
			<td class=\"".$rsvp_class."\" valign=\"top\" style=\"border-bottom: solid 1px #999;\">".$title."</td>			
			</tr>\n";
		}
		
		$page_content .= "</table>";
	}
	else{
		$page_content = "This user has not setup a schedule.";		
	}

	include('html.inc');	
	exit;
	
}


?>