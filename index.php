<?php
require_once('btstrp.php');

if($_SERVER['HTTP_HOST'] != "bcnsocial.com" && $_SERVER['HTTP_HOST'] != "www.bcnsocial.com"){
	$domain = explode(".",$_SERVER['HTTP_HOST']);
	$screenname = $domain[0];	
	
	$id = $sig = "";
	
	require_once("user.php");
	include('html.inc');	
	exit;
	
}

session_start();

/* If access tokens are not available redirect to connect page. */
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
    header('Location: ./clearsessions.php');
    exit;
}

if (empty($_SESSION['user'])){
	/* Get user access tokens out of the session. */
	$access_token = $_SESSION['access_token'];
	
	/* Create a TwitterOauth object with consumer/user tokens. */
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
	
	/* If method is set change API call made. Test is called by default. */
	//$content = $connection->get('account/verify_credentials');
	
	$content = $connection->get("account/verify_credentials");
	$_SESSION['user'] = $content;
}
$user = $_SESSION['user'];
$id = $user->id;
$sig = md5("g33k1234".$id."k00l4id");

//echo $content->screen_name;

/* Some example calls */
//$connection->get('users/show', array('screen_name' => 'abraham')));
//$connection->post('statuses/update', array('status' => date(DATE_RFC822)));
//$connection->post('statuses/destroy', array('id' => 5437877770));
//$connection->post('friendships/create', array('id' => 9436992)));
//$connection->post('friendships/destroy', array('id' => 9436992)));

//$friends = $connection->get('friends/ids', array('screen_name' => $content->screen_name));

//print_r($friends);

if (!empty($_GET['follow'])){
	$page_content = "<div id=\"ajax\"></div>

	<script type=\"text/javascript\">	
		single_loader(\"follow\");
	</script>";
}
else{
	$page_content = "<div id=\"tabs\">
			<div id=\"tabs-1\" class=\"tabs\">My Schedule</div>
			<div id=\"tabs-2\" class=\"tabs\">My Friends</div>		
			<div id=\"tabs-3\" class=\"tabs\">Notifications</div>
			<div id=\"tabs-4\" class=\"tabs\">About This App</div>			
			<div style=\"clear: both;\"></div>					
		</div>
		<div id=\"ajax\"></div>

	<script type=\"text/javascript\">
	
		$(\"#tabs-1\").click(function (){loader(\"tabs-1\",\"my-schedule\")});
		$(\"#tabs-2\").click(function (){loader(\"tabs-2\",\"my-friends\")});	
		$(\"#tabs-3\").click(function (){loader(\"tabs-3\",\"notifications\")});			
		$(\"#tabs-4\").click(function (){loader(\"tabs-4\",\"about\")});			
		
		loader(\"tabs-1\",\"my-schedule\");
	</script>";
}

	/* Include HTML to display on the page */
	include('html.inc');	
?>
