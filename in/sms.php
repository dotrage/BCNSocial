<?php
	require_once('btstrp.php');

	if (!empty($_REQUEST['Body']) && !empty($_REQUEST['From'])){
	//error_log(http_build_query($_REQUEST));
		$body = $_REQUEST['Body'];
		$from = $_REQUEST['From'];	
		$from = str_replace('+1','',$from);
		$from = phone_number($from);
	
		if (strpos(strtoupper("x".$body),"YES")){
			db_query("UPDATE users SET verified=1 WHERE phone = '".$from."'");
		}
		else{
			$client = new TwilioRestClient(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
				
			$data = array(
				"From" => TWILIO_CALLER_ID,
				"To" => $from,
				"Body" => "Sorry, your request is invalid.  Please visit http://bcnsocial.com for more details.",
			);				
			
			$result = $client->request("/2010-04-01/Accounts/".TWILIO_ACCOUNT_SID."/SMS/Messages.json", "POST", $data);		
			error_log(http_build_query($result));
		}
	}
?>
