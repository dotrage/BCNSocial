<?php
require_once('config.php');
require_once('inc/db.inc');

/**
 * @file
 * Check if consumer token is set and if so send user to get a request token.
 */

/**
 * Exit with an error message if the CONSUMER_KEY or CONSUMER_SECRET is not defined.
 */
require_once('config.php');
if (CONSUMER_KEY === '' || CONSUMER_SECRET === '') {
  echo 'You need a consumer key and secret to test the sample code. Get one from <a href="https://twitter.com/apps">https://twitter.com/apps</a>';
  exit;
}

$id = $sig = "";

$toplist = db_get_results("SELECT s.id as panel_id, s.title as panel_title, s.slug, COUNT(s.id) AS total FROM bcn_users_session_assoc a INNER JOIN sessions s ON a.session_id = s.id GROUP BY s.id ORDER BY total DESC LIMIT 0,7");

/* Build an image link to start the redirect process. */
$page_content = "<div id=\"main\">
<div style=\"float: left; width: 550px; font-size: 12px;\">
<h2>Make Your BarCamp Nashville Experience More Social</h2>
<p>BCN Social is a connection interface between BarCamp Nashville and Twitter.  You can do some pretty cool stuff with this app like find the sessions your Twitter friends are most interested in or even setup your personal session schedule and receive SMS notifications or Twitter direct messages throughout the day.</p>
<p>BCN Social is not directly affiliated with BarCamp Nashville, but we are the same people who brought you NashMash.</p>
<p>To use the BCN Social application, use the button below to connect to your Twitter account.</p>
<a href=\"/redirect.php\"><img src=\"/images/darker.png\" alt=\"Sign in with Twitter\" border=\"0\"/></a>
<img src=\"/images/screen.png\">
<div style=\"margin-top: 10px; font-size: 10px;\">BCN Social is the creation of <a href=\"http://twitter.com/chrisennis\">@chrisennis</a> and <a href=\"http://twitter.com/functionized\">@functionized</a>, and is in no way directly affiliated with SXSW or Twitter.</div>
</div>
<div style=\"float: left; border-left: solid 1px #C0C0C0; padding-left: 15px; margin-left: 25px; width: 330px; font-size: 12px;\">
<h2>Today's Popular Sessions</h2>";
$row_divider = "";
foreach ($toplist as $item){
	$page_content .= "<div id=\"watch_".$item['panel_id']."\" class=\"panel-list".$row_divider."\" style=\"padding: 7px 0 7px 0\">
	<a href=\"http://www.barcampnashville.org/bcn10/session/".$item['slug']."\" class=\"panel-list-url\" target=\"_blank\">".$item['panel_title']."</a>
	<div class=\"panel-link\">http://www.barcampnashville.org/bcn10/session/".$item['slug']."</div>
	</div>\n";
	$row_divider = " row-divider";
}

$page_content .= "</div>
<div style=\"clear: both\"></div>
</div>";
	
/* Include HTML to display on the page. */
include('html.inc');