<?php
	
	function phone_number($sPhone){
	    $sPhone = ereg_replace("[^0-9]",'',$sPhone);
	    if(strlen($sPhone) != 10) return(False);
	    $sArea = substr($sPhone,0,3);
	    $sPrefix = substr($sPhone,3,3);
	    $sNumber = substr($sPhone,6,4);
	    $sPhone = $sArea."-".$sPrefix."-".$sNumber;
	    return($sPhone);
	}

	function send_sms($phone,$text){
		$client = new TwilioRestClient(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
			
		$data = array(
			"From" => TWILIO_CALLER_ID,
			"To" => $phone,
			"Body" => $text,
		);
	
		$result = $client->request("/2010-04-01/Accounts/".TWILIO_ACCOUNT_SID."/SMS/Messages.json", "POST", $data);		
		error_log(http_build_query($result));
	}
	
	function get_meta_value($name){
		$value = db_get_row("SELECT value FROM meta WHERE name = '".$name."'");
		$return = $value['value'];
		return $return;
	}

	function set_meta_value($name,$value){
		if (!empty($name) && !empty($value)){
			db_query("UPDATE meta SET value = '".$value."' WHERE name = '".$name."'");
		}
	}

	function get_twitter_auth($id=0){
		$user = db_get_row("SELECT access_token, access_token_secret FROM users WHERE user_id = '".$id."'");
		if (!empty($user['access_token']) && !empty($user['access_token_secret'])){
			return $user;
		}
		else{
			return false;
		}
	}	
	
	function send_direct_message($id,$text){
		if (!empty($id) && !empty($text) && $text != ""){
			$token = get_meta_value("twitter_token");
			$secret = get_meta_value("twitter_secret");
			$twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $token, $secret);
			$return = $twitter->post('direct_messages/new', array("user_id" => $id, "text" => $text));
			return $return;
		}
	}	
	
	function follow_user($user_id,$sn){
		$user = db_get_row("SELECT user_id FROM users WHERE screen_name = '".$sn."'");
	
		if ($auth = get_twitter_auth($user['user_id'])){
			$twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $auth['access_token'], $auth['access_token_secret']);
		}
		$result = $twitter->post('friendships/create', array('user_id' => $user_id));
	}	
	
	function follow_bcnsocial(){
		$access_token = $_SESSION['access_token'];
		$twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
		$result = $twitter->post('friendships/create', array('user_id' => "199846770"));
		return $result;			
	}		
?>