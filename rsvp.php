<?php
	require_once('inc/twitteroauth.php');
	require_once('config.php');
	require_once('inc/db.inc');

	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past	
	
	session_start();		
	
	$user = $_SESSION['user'];

	$referrer = explode("/",str_replace("http://","",$_SERVER['HTTP_REFERER']));

	if ($referrer[0] != "bcnsocial.com" && $referrer[0] != "www.bcnsocial.com"){
		echo "error";
		exit;
	}

	if (empty($user->id) || empty($_POST['session_id']) || empty($_POST['status'])){
		echo "error";
		exit;
	}
	else{	
		$exists = db_get_row("SELECT * FROM bcn_users_session_assoc WHERE user_id = '".$user->id."' AND session_id = '".$_POST['session_id']."'");

		if ($_POST['status'] == "1"){
			if (!$exists){
				db_query("INSERT INTO bcn_users_session_assoc (user_id, screen_name, session_id) VALUES ('".$user->id."','".$user->screen_name."','".$_POST['session_id']."')");
			}
		}
		else if ($_POST['status'] == "2"){
			if ($exists){
				db_query("DELETE FROM bcn_users_session_assoc WHERE user_id = '".$user->id."' AND session_id = '".$_POST['session_id']."'");					
			}			
		}
		echo "ok";
	}
?>