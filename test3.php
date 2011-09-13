<?php 
	require_once("btstrp.php");
	
	$host = "http://stream.twitter.com/1/statuses/filter.json?track=bcn10";
	$username = "bcnsocial";
	$password = "bigbe4r";
	
	$ch = curl_init();	
    curl_setopt($ch, CURLOPT_URL, $host);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

    $result = curl_exec($ch);

    echo $result;
    
    curl_close($ch);
	
?>
