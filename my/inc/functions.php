<?php

	function ipnum($IPaddress) 
	{ 
		if ($IPaddress == "") { 
			return 0; 
		} else { 
			$ips = split ("\.", "$IPaddress"); 
			return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256); 
		} 
	} 
	
	function create_code(){
		$code = ipnum($_SERVER['REMOTE_ADDR']).mktime();
		return $code;
	}
	
	function update_user($token){
		if (!empty($token['user_id'])){
			$exists = db_get_row("SELECT * FROM users WHERE id = ".$token['user_id']);
			if ($exists){
				db_query("UPDATE users SET screen_name = '".$token['screen_name']."', token = '".$token['oauth_token']."', secret = '".$token['oauth_token_secret']."' WHERE id = ".$token['user_id']);
			}
			else{
				db_query("INSERT IGNORE INTO users (id,screen_name,token,secret) VALUES (".$token['user_id'].",'".$token['screen_name']."','".$token['oauth_token']."','".$token['oauth_token_secret']."')");		
			}
		}
		else{
			return 0;
		}
	}
	
	function twittertime ($str){
		if ($str != "")
		{
			$ttArr = split(' ', $str);		
			return strtotime("$ttArr[0], $ttArr[1] $ttArr[2], $ttArr[5] $ttArr[3]");
		}
	}
	
	function save_tweet($token,$tweet,$code){
		/*
		$exists = db_get_row("SELECT * FROM tweets WHERE token = '".$token."' AND code = '".$code."' AND status = 0");
		if (!$exists){
			$tweet = "I blame ".$tweet." on John Rich.";
			db_query("INSERT IGNORE INTO tweets (token,text,code,status) VALUES ('".$token."','".mysql_escape_string($tweet)."','".$code."',0)");
		}
		*/
		$tweet = "I blame ".$tweet." on John Rich.";
		$_SESSION['tweet_'.$token] = $tweet;
	}
	
	function send_tweet($token){
		global $connection; 
		
		//$tweets = db_get_results("SELECT * FROM tweets WHERE token = '".$token."' AND status = 0");
		
		if (!empty($_SESSION['tweet_'.$token])){
			
			$tweets = array($_SESSION['tweet_'.$token]);
			
			//echo "<br><br>";
			
			foreach ($tweets as $tweet){
				$result = $connection->post('statuses/update', array('status' => $tweet));				
				if ($result){
		//			db_query("UPDATE tweets SET status=1,id=".$result->id.",created_at='".twittertime($result->created_at)."',source='".$result->source."',user_id='".$result->user->id."',user_name='".$result->user->name."',user_screen_name='".$result->user->screen_name."',user_profile_image_url='".$result->user->profile_image_url."' WHERE id = ".$tweet['id']);
					db_query("INSERT IGNORE INTO tweets (id,created_at,text,source,user_id,user_name,user_screen_name,user_profile_image_url,token,status) VALUES ('".$result->id."','".twittertime($result->created_at)."','".$result->text."','".$result->source."','".$result->user->id."','".$result->user->name."','".$result->user->screen_name."','".$result->user->profile_image_url."','".$token."',1)");
					$sent = 1;
				}
			}

		}
		
		if (isset($sent)){
			return 1;
		}
		else{
			return 0;
		}
	}	