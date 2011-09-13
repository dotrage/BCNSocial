<?php
	require_once('inc/twitteroauth.php');
	require_once('config.php');
	require_once('inc/db.inc');

	session_start();
	
	if (!empty($_POST['m']) && !empty($_POST['u']) && !empty($_POST['url']) && strrpos($_POST['url'],"panelpicker.sxsw.com/ideas/view/") && !empty($_SESSION['user'])){

		$user = $_SESSION['user'];		
		$url = $_POST['url'];
		$user_id = $_POST['m'];
		$url_arr = explode("?",$url);		
		$panel_id = str_replace("http://panelpicker.sxsw.com/ideas/view/","",$url_arr[0]);		
		
		$exists = db_get_row("SELECT * FROM panels WHERE user_id = '".$user_id."' AND panel_id = '".$panel_id."'");
		
		if ($exists){
			echo "error";			
		}
		else{
			$html = file_get_contents($url);							
			eregi("<title>(.*)</title>", $html, $title);			
			$panel_title = mysql_escape_string(trim(str_replace("SXSW 2011 PanelPicker - ","",$title[1])));
						
			db_query("INSERT INTO panels (user_id,screen_name,name,panel_id,panel_title,datetime) VALUES ('".$user_id."','".$user->screen_name."','".$user->name."','".$panel_id."','".$panel_title."','".mktime()."')");
			echo "success";
		}
		
	}
	else{
		echo "error";
	}
?>