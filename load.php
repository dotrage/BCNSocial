<?php
	require_once('inc/twitteroauth.php');
	require_once('config.php');
	require_once('inc/db.inc');

	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past	
	
	if (MEMCACHE_ENABLED){
		$mc = new Memcache;
		$mc->addServer('localhost', 11211);	
	}
	
	session_start();		
	
	function get_sessions($times){
		global $user;
	
		$return = array();
	
		foreach ($times as $time){
			$data = db_get_results("SELECT s.id, s.title, s.slug, IF(a.screen_name IS NOT NULL,1,0) as rsvp FROM sessions s LEFT JOIN bcn_users_session_assoc a ON s.id = a.session_id AND a.user_id = '".$user->id."' WHERE s.starttime = '".$time['starttime']."' ORDER BY s.room");
			$return[$time['starttime']] = $data;
		}
		
		return $return;
	}
	
	if (!empty($_GET['m']) && !empty($_GET['u']) && !empty($_GET['view']) && !empty($_SESSION['user'])){

		$user = $_SESSION['user'];		
		
		$row_divider = "";
		
		switch ($_GET['view']){
			case "my-schedule":
				$rooms = db_get_results("SELECT * FROM rooms");			
				$times = db_get_results("SELECT starttime FROM sessions GROUP BY starttime ORDER BY starttime");
				$sessions = get_sessions($times);
				
				echo "<script>
					function showInfo(id){
						if($(\"#info-box\").is(':visible') && $(\"#info-box\").attr(\"current\") == id){
							hideInfo();
						}
						else{					
							var pos = $(\"#session-\"+id).offset();  
							var height = $(\"#session-\"+id).height();  						
						
							$(\"#info-box\").css({
								left: (pos.left) + 'px',
								top: (pos.top + height + 15) + 'px',
								width: '280px'
							});						
							$(\"#info-box\").load('session.php?sid='+id);						
							$(\"#info-box\").show();
							$(\"#info-box\").attr(\"current\",id);
						}
					}
					
					function hideInfo(){					
						$(\"#info-box\").hide();
						$(\"#info-box\").html('');						
						$(\"#info-box\").css({
							left: 0,
							top: 0
						});
					}
					
					function selectSession(id){
						hideInfo();
						if ($(\"#session-\"+id+\"-checkbox:checked\").val() != null){
							$.post(\"rsvp.php\", { session_id: id, status: 1 },
								function (data){
									if (data != 'error'){
										$(\"#session-\"+id).animate({'backgroundColor': '#f47b29', 'color': 'white'}, 'slow');		
										$(\"#session-\"+id).css({ 'font-weight': 'bold'});									
									}
								}
							);
						}
						else{
							$.post(\"rsvp.php\", { session_id: id, status: 2 },
								function (data){
									if (data != 'error'){
										$(\"#session-\"+id).animate({'backgroundColor': '#f7f7f7', 'color': '#666'}, 'slow');
										$(\"#session-\"+id).css({ 'font-weight': 'normal'});
									}
								}
							);						

						}
					}
				</script>";
				
				echo "<div class=\"time-col\">Time</div>";

				foreach ($rooms as $room){
					echo "<div class=\"room-col\">".$room['name']."</div>";
				}
				
				echo "<div class=\"clear\"></div>";
				
				foreach ($times as $time){
					echo "<div class=\"time-col\">".date("g:i a",$time['starttime'])."</div>";
					foreach($sessions[$time['starttime']] as $session){
						if ($session['rsvp'] == "1"){
							$bgcolor = " session-box-selected";	
							$checked = " checked=\"checked\"";
						}
						else{
							$bgcolor = " session-box-unselected";
							$checked = "";
						}
						echo "<div class=\"session-col\">";
						echo "<div class=\"session-box".$bgcolor."\" id=\"session-".$session['id']."\">
						<input id=\"session-".$session['id']."-checkbox\" type=\"checkbox\"".$checked." style=\"float: left;\" onchange=\"selectSession(".$session['id'].");\"><div onclick=\"showInfo(".$session['id'].");\">".$session['title']."</div></div>";
						echo "</div>";
					}
					echo "<div class=\"clear\"></div>";					
				}			
				
				echo "<div id=\"info-box\" current=\"\"></div>";
				
				echo "<div style=\"margin-top: 10px;\">Your schedule can be viewed by others at:</div>
				<div style=\"font-size: 24px; font-weight: bold\"><a href=\"http://".$user->screen_name.".bcnsocial.com\" target=\"_blank\">http://".$user->screen_name.".bcnsocial.com</a></div>";
						
				break;
			case "my-friends":
				$access_token = $_SESSION['access_token'];
				$user = $_SESSION['user'];
				
				if (!empty($user->friends_count) && $user->friends_count < 2000){
					
					$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
					$friends = $connection->get('friends/ids', array('id' => $user->id));
					
					if ($friends){
						foreach ($friends as $f){
							if (isset($q)){
								//$query .= " OR w.user_id = '".$f."'";
								$q .= ",'".$f."'";
							}
							else{
								//$query = "w.user_id = '".$f."'";
								$q = "'".$f."'";							
							}
						}
						
						$query = "user_id IN (".$q.")";												
						$query .= " AND user_id <> '".$user->id."'"; 
						
						if (empty($_GET['start'])){
							$start = 0;
						}
						else{
							$start = $_GET['start'];
						}				
						
						$sql = "SELECT a.session_id as panel_id, s.title as panel_title, s.slug, s.starttime as datetime, COUNT(s.id) as total FROM bcn_users_session_assoc a INNER JOIN sessions s ON a.session_id = s.id WHERE ".$query." GROUP BY s.id ORDER BY total DESC, s.title";
						
						if (MEMCACHE_ENABLED){
							$results = $mc->get(md5($sql));
							if (!$results){
								$results = db_get_results($sql);
								$mc->set(md5($sql),$results,MEMCACHE_COMPRESSED,mktime()+1800);
							}
						}
						else{
							$results = db_get_results($sql);
						}
						
											
						if ($results){
							foreach ($results as $item){
								echo "<div id=\"watch_".$item['panel_id']."\" class=\"panel-list".$row_divider."\">
								<a href=\"http://www.barcampnashville.org/bcn10/session/".$item['slug']."\" class=\"panel-list-url\" target=\"_blank\">".$item['panel_title']."</a>
								<div class=\"panel-link\">This session has been tagged by <a class=\"session-friend-count\" id=\"".$item['panel_id']."\" href=\"javascript:void(-1);\">".$item['total']."</a> of your Twitter friends.</div>
								<div id=\"friends-list-".$item['panel_id']."\" class=\"friend-list\">";
								$sql = "SELECT screen_name FROM bcn_users_session_assoc WHERE session_id = '".$item['panel_id']."' AND ".$query." ORDER BY screen_name";
								if (MEMCACHE_ENABLED){
									$friends = $mc->get(md5($sql));
									if (!$friends){
										$friends = db_get_results($sql);
										$mc->set(md5($sql),$friends,MEMCACHE_COMPRESSED,mktime()+1800);
									}
								}
								else{
									$friends = db_get_results($sql);
								}
								foreach ($friends as $friend){
									echo $friend['screen_name'] . "<br>";
								}
								echo "</div>
								</div>";
								$row_divider = " row-divider";
							}
							
							echo "<script>
							
							function showFriends(id){
								$(\"#friends-list-\"+id).show();
							}
							
							function hideFriends(id){
								$(\"#friends-list-\"+id).hide();
							}							
							
							$(\".session-friend-count\").hover(
								function (){
									id = $(this).attr('id');
									
									var pos = $(this).offset();  
									var height = $(this).height(); 									
									$(\"#friends-list-\"+id).css({
										left: (pos.left) + 'px',
										top: (pos.top + height + 5) + 'px'								
									});
									$(\"#friends-list-\"+id).show();
								},
								function (){
									id = $(this).attr('id');								
									hideFriends(id);									
								}
							);
							</script>";
						}
						else{
							echo "None of your friends have reserved any sessions yet.";
						}
					}
					else{
						echo "You don't appear to have any friends on Twitter.";
					}				
				}
				else{
					$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
					$lists_db = db_get_results("SELECT list_id FROM lists WHERE user_id = '".$user->id."'");
					
					if ($lists_db){
						$list_arr = array();
						foreach ($lists_db as $item){
							$list = $connection->get($user->screen_name.'/'.$item['list_id'].'/members');
							foreach ($list->users as $u){
								if (!in_array($u->id,$list_arr)){
									$list_arr[] = $u->id;
								}
							}														
						}
						
						if (count($list_arr)>0){
							foreach($list_arr as $item){
								if (isset($q)){
									$q .= ",".$item;
								}
								else{
									$q = $item;
								}
							}
							$query = " user_id IN (".$q.")";
							
							if (empty($_GET['start'])){
								$start = 0;
							}
							else{
								$start = $_GET['start'];
							}				
							
						$sql = "SELECT a.session_id as panel_id, s.title as panel_title, s.slug, s.starttime as datetime, COUNT(s.id) as total FROM bcn_users_session_assoc a INNER JOIN sessions s ON a.session_id = s.id WHERE ".$query." GROUP BY s.id ORDER BY total DESC, s.title";
											
						if (MEMCACHE_ENABLED){
							$results = $mc->get(md5($sql));
							if (!$results){
								$results = db_get_results($sql);
								$mc->set(md5($sql),$results,MEMCACHE_COMPRESSED,mktime()+1800);
							}
						}
						else{
							$results = db_get_results($sql);
						}											
											
						if ($results){
							foreach ($results as $item){
								echo "<div id=\"watch_".$item['panel_id']."\" class=\"panel-list".$row_divider."\">
								<a href=\"http://www.barcampnashville.org/bcn10/session/".$item['slug']."\" class=\"panel-list-url\" target=\"_blank\">".$item['panel_title']."</a>
								<div class=\"panel-link\">This session has been tagged by <a class=\"session-friend-count\" id=\"".$item['panel_id']."\" href=\"javascript:void(-1);\">".$item['total']."</a> of your Twitter friends.</div>
								<div id=\"friends-list-".$item['panel_id']."\" class=\"friend-list\">";
								
								$sql = "SELECT screen_name FROM bcn_users_session_assoc WHERE session_id = '".$item['panel_id']."' AND ".$query." ORDER BY screen_name";
								if (MEMCACHE_ENABLED){
									$friends = $mc->get(md5($sql));
									if (!$friends){
										$friends = db_get_results($sql);
										$mc->set(md5($sql),$friends,MEMCACHE_COMPRESSED,mktime()+1800);
									}
								}
								else{
									$friends = db_get_results($sql);
								}
								
								foreach ($friends as $friend){
									echo $friend['screen_name'] . "<br>";
								}
								echo "</div>
								</div>";
								$row_divider = " row-divider";
							}
							
							echo "<script>
							
							function showFriends(id){
								$(\"#friends-list-\"+id).show();
							}
							
							function hideFriends(id){
								$(\"#friends-list-\"+id).hide();
							}							
							
							$(\".session-friend-count\").hover(
								function (){
									id = $(this).attr('id');
									
									var pos = $(this).offset();  
									var height = $(this).height(); 									
									$(\"#friends-list-\"+id).css({
										left: (pos.left) + 'px',
										top: (pos.top + height + 5) + 'px'								
									});
									$(\"#friends-list-\"+id).show();
								},
								function (){
									id = $(this).attr('id');								
									hideFriends(id);									
								}
							);
							</script>";
						}
						else{
							echo "None of your friends have reserved any sessions yet.";
						}
						}
						else{						
							echo "No lists found";
						}						
						
					}
					else{					
						$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
						$lists = $connection->get($user->screen_name.'/lists');
						if (count($lists) > 0){
							echo "Dude.  You have too many members.  So, choose up to 4 of your Twitter lists below to see the list's panels.<br>
							<form id=\"tag-list-form\"><ul style=\"list-style-type: none;\">\n";
							foreach ($lists->lists as $list){
								echo "<li style=\"margin: 2px 0 2px 0;\">";
								echo "<input type=\"checkbox\" name=\"list[".$list->id."]\" id=\"list_id\" value=\"".$list->id."\">".$list->full_name;
								echo "</li>";							
							}		
							echo "</ul>";		
							echo "<input type=\"button\" id=\"tag-list-button\" value=\"Tag Lists to View Your Friends Panel Suggestions\"></form>";
							
							echo "<script>
							$(\"#tag-list-button\").click(function(){
								var data = $(\"#tag-list-form\").serialize();
								if (data){
									tag_lists(data,3);
								}
							});		
							</script>";
						}	
						else{
							echo "Dude, You have too many friends.  We'd prefer you tag which Twitter lists you wish to see but you don't appear to have any.";
						}	
					}
				}
				
				break;
				
			case "notifications":
				$account = db_get_row("SELECT * FROM users WHERE user_id = '".$user->id."'");
			
				echo "<h2>SMS Reminder Notifications</h2>";
				
				if (empty($account['phone'])){
					echo "<p>You can receive SMS messages on your mobile phone to remind you when and where your next session is going to be.</p>
					<p>Enter your mobile number below and check the box verifying that you wish to receive an SMS message before each session that you are registered to attend.</p>
					<div class=\"spacerVert6\"></div>
					<div id=\"phone-form\">
					Your Mobile Number: 
					<input type=\"text\" name=\"mnumber\" id=\"mnumber\">
					<div class=\"spacerVert6\"></div>
					<input type=\"checkbox\" id=\"magree\" value=\"1\"> I confirm that I wish to receive SMS reminders 5 minutes prior to each session that I am registered for.
					<div class=\"spacerVert6\"></div>
					<input type=\"button\" id=\"msubmit\" value=\"Save\">
					</div>";
				}
				else{
					echo "<p>You are currently set to receive SMS reminders on <strong>".$account['phone']."</strong>.  To cancel SMS notifications, click the 'Cancel Notifications' button below</p>
					<br><br>					
					<input type=\"button\" id=\"mcancel\" value=\"Cancel Notifications\">";
				}
				
				echo "<div id=\"phone-result\"></div>";
				
				echo "<script>
				$(function() {
	$(\"#mnumber\").keyup(function() {
		var curchr = this.value.length;
		var curval = $(this).val();

		if (curchr == 3) {
			$(\"#mnumber\").val(curval + \"-\");
		} else if (curchr == 7) {
			$(\"#mnumber\").val(curval + \"-\");
		}
	});
	
	$(\"#msubmit\").click(function(){
		var phone = $(\"#mnumber\").val();
		
		if ($(\"#magree:checked\").val() != null && phone.length >= 9){
			save_phone(".$user->id.",phone,'save');
		}
	});		
	
	$(\"#mcancel\").click(function(){
		save_phone(".$user->id.",'".$account['phone']."','cancel');
	});
});

</script>";
				
				break;
				
			case "about":
				echo "<h2>About this App</h2>
				<p class=\"about-page\">BCN Social is an app created by <a href=\"http://twitter.com/chrisennis\" target=\"_blank\">Chris Ennis</a> as an example site for the BarCamp session <a href=\"http://www.barcampnashville.org/bcn10/session/0-2-million-page-views-10-days\" target=\"_blank\">0 to 2 Million Page Views in 10 Days</a>.  This app was inspired by <a href=\"http://friendspanels.com\" target=\"_blank\">FriendsPanels</a> and <a href=\"http://sched.org\" target=\"_blank\">Sched</a>.  This app was built to show how web apps can drive traffic and interest by being useful for the public.</p>
				<p class=\"about-page\"><strong>What can you do with this site?</strong><br>
				With the BCN Social App, you can setup your schedule dynamically and share it with your Twitter friends.  For a good example of how this works, check out <a href=\"http://chrisennis.bcnsocial.com\" target=\"_blank\">http://chrisennis.bcnsocial.com</a>.  You can also view this schedule in a mobile format.  Check out <a href=\"http://chrisennis.bcnsocial.com/m\" target=\"_blank\">http://chrisennis.bcnsocial.com/m</a> for a demo of this.</p>
				<p class=\"about-page\">In addition to setting up your schedule, you can also receive SMS messages to remind you before each session you choose to attend.  Finally, you can DM a friends Twitter handle to <a href=\"http://twitter.com/bcnsocial\" target=\"_blank\">@bcnsocial</a> and we'll tell you what session that friend is scheduled to be in at that time.</p>";
				break;				
			case "follow":
				echo "<h2>Better With A Follow</h2>
				<p class=\"about-page\">There's a feature in this app that depends on you following @bcnsocial on Twitter and @bcnsocial following you back.</p>
				<p class=\"about-page\">If you're following us and you choose to use the locate a user feature that tells you what room another user is in at that particular time, then you can request that information through a direct message and we can send a response to you on Twitter.</p>
				<p class=\"about-page\">Choose how you wish to proceed:
				<br><br>
				<div id=\"follow-buttons\">
				<input type=\"button\" id=\"follow-button\" value=\"Follow @bcnsocial On Twitter\"> OR <input type=\"button\" id=\"no-follow-button\" value=\"Do Not Follow @bcnsocial On Twitter\">
				</div>
				</p>
				<script>
					function gohome(){
						document.location = \"index.php\";
					}
				
					$(\"#follow-button\").click(
						function (){
							$.post(\"follow.php\", { status: 1 },
								function (data){
									if (data != 'error'){
										$(\"#follow-buttons\").html(data);
										setTimeout(\"gohome()\",5000);
									}
								}
							);		
						}					
					);
					$(\"#no-follow-button\").click(
						function (){
							$.post(\"follow.php\", { status: 2 },
								function (data){
									if (data != 'error'){
										$(\"#follow-buttons\").html(data);
										setTimeout(\"gohome()\",5000);
									}
								}
							);			
						}					
					);					
				</script>";
				
				break;		
			case "stream":
				echo "The BCN10 Live Stream will be available the day of BarCamp Nashville.";
				break;
		}
	}
	else{
		//header("HTTP/1.1 500 Internal Server Error");
		echo "Unable to load your sessions.";
	}
?>