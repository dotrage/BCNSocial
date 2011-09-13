<?php
require_once('config.php');
require_once('inc/db.inc');
require_once('inc/twilio.php');

function send_verification($phone){
	$client = new TwilioRestClient(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
	$body = "Thanks for signing up for BCN Social Mobile Reminders.  To activate this service, reply to this message with 'yes'.";
	
	$data = array(
		"From" => TWILIO_CALLER_ID,
		"To" => $phone,
		"Body" => $body,
	);
	
	$result = $client->request("/2010-04-01/Accounts/".TWILIO_ACCOUNT_SID."/SMS/Messages.json", "POST", $data);
	error_log(http_build_query($result));	
	return $result;
}

if (!empty($_POST['user_id']) && !empty($_POST['phone']) && !empty($_POST['mode'])){

	if ($_POST['mode'] == "save"){
		$exists = db_get_row("SELECT * FROM users WHERE user_id = '".$_POST['user_id']."' AND phone IS NULL");

		if ($exists){
			db_query("UPDATE users SET phone='".$_POST['phone']."',verified=0 WHERE user_id = '".$_POST['user_id']."'");
			send_verification($_POST['phone']);
			echo "success";
		}
	
	}
	else if ($_POST['mode'] == "cancel"){
		db_query("UPDATE users SET phone=NULL,verified=0 WHERE user_id = '".$_POST['user_id']."' AND phone = '".$_POST['phone']."'");
		echo "success";
	}

}
?>