<?php 
	require_once('config.php');
	require_once('inc/db.inc');

	if (!empty($_GET['url']) && strrpos($_GET['url'],"panelpicker.sxsw.com/ideas/view/") && !empty($_GET['m']) && !empty($_GET['u'])){
		
		if ($_GET['u'] == md5("g33k1234".$_GET['m']."k00l4id")){
		
			$url = $_GET['url'];
			$user_id = $_GET['m'];
			$url_arr = explode("?",$url);		
			$panel_id = str_replace("http://panelpicker.sxsw.com/ideas/view/","",$url_arr[0]);

			$exists = db_get_row("SELECT id FROM watch WHERE user_id = '".$user_id."' AND panel_id = '".$panel_id."'");
			
			if ($exists){
				$message = "You have already liked this panel.";				
			}
			else{
				$mine = db_get_row("SELECT id FROM panels WHERE user_id = '".$user_id."' AND panel_id = '".$panel_id."'");
				
				if ($mine){
					$message = "You cannot like your own panel";
				}
				else{
					$html = file_get_contents($url);							
					eregi("<title>(.*)</title>", $html, $title);	
					$panel_title = mysql_escape_string(trim(str_replace("SXSW 2011 PanelPicker - ","",$title[1])));					
					
					db_query("INSERT INTO watch (user_id,panel_id,panel_title,datetime) VALUES ('".$user_id."','".$panel_id."','".$panel_title."','".mktime()."')");			
					
					$message = "You've successfully added this panel to your like list";
				}
			}
		}
		else{	
			$message = "Cannot add this panel";
		}
	}
	else{
		$message =  "Invalid Request";				
	}
	
	$page_content = "<div id=\"main\">".$message."<br><br>
	<a href=\"javascript:history.go(-1);\">&#171; Return to the Previous Page</a>
	</div>";
	
	/* Include HTML to display on the page */
	include('html.inc');	
?>

</body>
</html>

