<?php
/**
 * @file
 * Take the user when they return from Twitter. Get access tokens.
 * Verify credentials and redirect to based on response from Twitter.
 */

/* Start session and load lib */
session_start();
require_once('inc/twitteroauth.php');
require_once('config.php');
require_once('inc/db.inc');

function get_twitter_auth($id=0){
	$user = db_get_row("SELECT access_token, access_token_secret FROM users WHERE user_id = '".$id."'");
	if (!empty($user['access_token']) && !empty($user['access_token_secret'])){
		return $user;
	}
	else{
		return false;
	}
}

function follow_user($user_id,$sn){
	$user = db_get_row("SELECT user_id FROM users WHERE screen_name = '".$sn."'");

	if ($auth = get_twitter_auth($user['user_id'])){
		$twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $auth['access_token'], $auth['access_token_secret']);
	}
	$result = $twitter->post('friendships/create', array('user_id' => $user_id));
}

/* If the oauth_token is old redirect to the connect page. */
if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
  $_SESSION['oauth_status'] = 'oldtoken';
  header('Location: ./clearsessions.php');
}

/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

/* Request access tokens from twitter */
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

/* Save the access tokens. Normally these would be saved in a database for future use. */
$_SESSION['access_token'] = $access_token;

/* Remove no longer needed request tokens */
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

/* Write user into database if not already existing */
$content = $connection->get("account/verify_credentials");
$_SESSION['user'] = $content;
$exists = db_get_row("SELECT * FROM users WHERE user_id = '".$content->id."'");

if (!$exists && $content){
	db_query("INSERT IGNORE INTO users (user_id, screen_name, name, datetime, access_token, access_token_secret, follow) VALUES ('".$content->id."', '".$content->screen_name."', '".$content->name."', '".mktime()."' ,'".$access_token['oauth_token']."' ,'".$access_token['oauth_token_secret']."', 1)");
	follow_user($content->id,"bcnsocial");
	header('Location: ./index.php?follow=1');
	exit;
}
else if ($exists['follow'] == 0 && $content){
	follow_user($content->id,"bcnsocial");
	db_query("UPDATE users SET follow = 1 WHERE id = '".$exists['id']."'");
	header('Location: ./index.php?follow=1');
	exit;
}

/* If HTTP response is 200 continue otherwise send to connect page to retry */
if (200 == $connection->http_code) {
  /* The user has been verified and the access tokens can be saved for future use */
  $_SESSION['status'] = 'verified';
  header('Location: ./index.php');
} else {
  /* Save HTTP status for error dialog on connnect page.*/
  header('Location: ./clearsessions.php');
}
