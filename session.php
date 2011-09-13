<?php
	require_once('inc/twitteroauth.php');
	require_once('config.php');
	require_once('inc/db.inc');

	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past	
	
	if (!empty($_GET['sid'])){
		$session = db_get_row("SELECT * FROM sessions WHERE id = '".$_GET['sid']."'");	
		
		if (!$session){
			echo "Session Not Found.";
		}
		else{
			echo "<div style=\"font-weight: bold;\">".$session['title']."</div>";
			echo "<div class=\"spacerVert6\"></div>";
			echo "<div style=\"padding-bottom: 10px; border-bottom: solid 1px #efefef;\">".str_replace("\n","<br>",$session['description'])."</div>";	
			echo "<div style=\"padding: 6px 0 6px 0; border-bottom: solid 1px #efefef;\">Speakers:  ".$session['speaker']."</div>";
			echo "<div class=\"spacerVert6\"></div>";			
			echo "<a href=\"javascript:void(-1);\" onclick=\"hideInfo();\">Close</a>";
		}
	}
	else{
		echo "Session Not Found.";
	}
?>