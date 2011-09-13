<?php 
	$user = db_get_row("SELECT * FROM users WHERE screen_name = '".$screenname."'");
	$userview = "1";
	
	function get_sessions($uid,$times){
		global $user;
	
		$return = array();
	
		foreach ($times as $time){
			$data = db_get_results("SELECT s.id, s.title, s.slug, IF(a.screen_name IS NOT NULL,1,0) as rsvp FROM sessions s LEFT JOIN bcn_users_session_assoc a ON s.id = a.session_id AND a.user_id = '".$uid."' WHERE s.starttime = '".$time['starttime']."' ORDER BY s.room");
			$return[$time['starttime']] = $data;
		}
		
		return $return;
	}	
	
	if ($user){
	
		$rooms = db_get_results("SELECT * FROM rooms");			
		$times = db_get_results("SELECT starttime FROM sessions GROUP BY starttime ORDER BY starttime");
		$sessions = get_sessions($user['user_id'],$times);	
	
		$page_content = "<script>
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
				</script>";
		
		$page_content .= "<div class=\"time-col\">Time</div>";

		foreach ($rooms as $room){
			$page_content .=  "<div class=\"room-col\">".$room['name']."</div>";
		}
				
		$page_content .= "<div class=\"clear\"></div>";
				
		foreach ($times as $time){
			$page_content .= "<div class=\"time-col\">".date("g:i a",$time['starttime'])."</div>";
			foreach($sessions[$time['starttime']] as $session){
				if ($session['rsvp'] == "1"){
					$bgcolor = " session-box-selected";	
					$checked = " checked=\"checked\"";
				}
				else{
					$bgcolor = " session-box-unselected";
					$checked = "";
				}
				$page_content .= "<div class=\"session-col\">";
				$page_content .= "<div class=\"session-box".$bgcolor."\" id=\"session-".$session['id']."\">
				<div onclick=\"showInfo(".$session['id'].");\">".$session['title']."</div></div>";
				$page_content .= "</div>";
			}
			$page_content .= "<div class=\"clear\"></div>";					
		}			
				
		$page_content .= "<div id=\"info-box\" current=\"\"></div>";		
	}
	else{
		$page_content = "<div id=\"main\"><div style=\"font-size: 24px;\">For shame, <strong><a href=\"http://twitter.com/".$screenname."\" target=\"_blank\" class=\"discreet-link\">@".$screenname."</a></strong> is not using Friends Panels.</div>
		<p>You should tell ".$screenname." to list the panels he is interested in on Friends Panels.  Its a quick and easy way to see interesting panels through the friends that share your interests.</p>
		<p>Its also a convenient way to increase exposure for your panels without completely annoying your friends on Twitter with your continuous shameless plugs for your panel submission.</p>
		
		</div>";		
	}
?>