<?php
	require_once('inc/twitteroauth.php');
	require_once('config.php');
	require_once('inc/db.inc');

	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past	
	
	session_start();

	function follow_user(){
			$access_token = $_SESSION['access_token'];
			$twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
			$result = $twitter->post('friendships/create', array('user_id' => "199846770"));
			return $result;			
	}	
	
	if (!empty($_POST['status']) && $_POST['status'] == "1"){

		$user = $_SESSION['user'];		
		$result = follow_user();
		echo "You are now following @bcnsocial on Twitter.  Hang on and we'll forward you on to the BCN Social Schedule App.";
		
	}
	else if (!empty($_POST['status']) && $_POST['status'] == "2"){
		echo "You are not following @bcnsocial on Twitter.  Hang on and we'll forward you on to the BCN Social Schedule App.";
	}
	else{
		echo "error";
	}
?>