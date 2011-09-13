<?php 
function db_query($sql,$error=TRUE){
	
	if (!($conn=mysql_connect(DB_HOST, DB_USER, DB_PASS)))  {
		printf("Error connecting to database.");
		exit;
	} 	
	
	if ($error){
		$db = mysql_select_db(DB_NAME,$conn) or die("Unable to connect to database.");
		$result = mysql_query($sql,$conn) or die(mysql_error());
	}
	else{
		$db = mysql_select_db(DB_NAME,$conn);
		$result = mysql_query($sql,$conn);
	}
		
	if (!$result){
		if ($error){
			echo "Error querying database.";
		}
	}
	else{
		return $result;
	}
	
}

function db_get_row($sql,$type="array"){
	
	$result = db_query($sql);
	
	if ($result){
		$row = mysql_fetch_assoc($result);
		if ($type == "array"){
			return $row;
		}
		if ($type == "object"){
			return (object) $row;
		}
	}
	
}

function db_get_results($sql,$type="array"){
	
	$result = db_query($sql);

	$return = array();
	
	while ($row = mysql_fetch_assoc($result)){
		if ($type == "array"){
			$return[] = $row;
		}
		if ($type == "object"){
			$return[] = (object) $row;
		}		
	}
	
	if ($result){
		if ($type == "array"){
			return $return;
		}
		if ($type == "object"){
			return (object) $return;
		}		
	}
}

function db_num_rows($sql){
	
	$result = db_query($sql);
	
	if ($result){
		$row = mysql_num_rows($result);
		return $row;
	}
	
}
?>