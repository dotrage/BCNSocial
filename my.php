<?php

session_start();
require_once('inc/twitteroauth.php');
require_once('config.php');
require_once('inc/db.inc');

/* If access tokens are not available redirect to connect page. */
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
    header('Location: ./clearsessions.php');
}
/* Get user access tokens out of the session. */
$access_token = $_SESSION['access_token'];

/* Create a TwitterOauth object with consumer/user tokens. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

/* If method is set change API call made. Test is called by default. */
//$content = $connection->get('account/verify_credentials');

$content = $connection->get("account/verify_credentials");

$id = $content->id;
$sig = md5("g33k1234".$id."k00l4id");

//echo $content->screen_name;

echo "Hello ".$content->name."!<br><br>";

echo "<a href=\"javascript:var%20m='".$id."';var%20u='".$sig."';var%20url='http://friendspanels.com/watch.php?url='+encodeURIComponent(location.href)+'&m='+m+'&u='+u;void(location.href=url);\">Bookmark this</a>";

/* Some example calls */
//$connection->get('users/show', array('screen_name' => 'abraham')));
//$connection->post('statuses/update', array('status' => date(DATE_RFC822)));
//$connection->post('statuses/destroy', array('id' => 5437877770));
//$connection->post('friendships/create', array('id' => 9436992)));
//$connection->post('friendships/destroy', array('id' => 9436992)));

/*
$friends = $connection->get('friends/ids', array('screen_name' => $content->screen_name));

foreach ($friends as $f){
	if (isset($query)){
		$query .= " OR user_id = '".$f."'";
	}
	else{
		$query = "user_id = '".$f."'";
	}	
}

echo "SELECT * FROM sessions WHERE ".$query;
*/

//print_r($friends);

/* Include HTML to display on the page */
//include('html.inc');

?>
