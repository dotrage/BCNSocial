<?php
	require_once('inc/twitteroauth.php');
	require_once('config.php');
	require_once('inc/db.inc');

	session_start();
	
	if (!empty($_POST['list'])){

		$user = $_SESSION['user'];		
		$lists = $_POST['list'];

		foreach ($lists as $k => $v){
			$exists = db_get_row("SELECT * FROM lists WHERE list_id = '".$v."'");
			
			if ($exists){
				$error = 1;
			}
			else{
				db_query("INSERT IGNORE INTO lists (user_id, list_id) VALUES ('".$user->id."','".$v."')");
			}
			
		}
		
		if ($error){
			echo "error";			
		}
		else{
			echo "success";
		}
		
	}
	else{
		echo "error";
	}
?>