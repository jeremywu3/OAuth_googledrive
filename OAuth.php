<?php 
	// get button_id
	$button_id = $_REQUEST["button_id"];
	$refreshtoken = $_REQUEST["refreshtoken"];
	$refreshtoken_decode = urldecode($refreshtoken);

	// read client information
	$client_info_content = file_get_contents('client_secret.json');
	
	$client_info_json = json_decode($client_info_content, true);
        $client_id = $client_info_json["web"]["client_id"];
        $client_secret = $client_info_json["web"]["client_secret"];

	// refresh token
	$token_endpoint = "https://www.googleapis.com/oauth2/v4/token";
	$post_data = array(
		'client_id' => $client_id,
                'client_secret' => $client_secret,
    		'refresh_token'=>$refreshtoken_decode,	  
                'grant_type' => 'refresh_token'
	);
	$post_options = array(
	    'http' => array(
	        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
	        'method'  => 'POST',
	        'content' => http_build_query($post_data)
	    )
	);
	$post_context  = stream_context_create($post_options);

	// Token response
	$post_result = file_get_contents($token_endpoint, false, $post_context);
	$result_json = json_decode($post_result, true);
	$accesstoken = $result_json["access_token"];
	$expiretime = $result_json["expires_in"];

	// DB information
	$db_location = "localhost";
	$db_username = "root";
	$db_password = "123456";
	$db_name = "oauth";

	// Create connection
	$connection = new mysqli($db_location, $db_username, $db_password, $db_name);
	// Check connection
	if ($connection->connect_error) {
	    die("Connection failed: " . $connection->connect_error);
	} 

	// Do sql
	if(isset($accesstoken)&&!is_null($accesstoken)){
		$connection->query("SET NAMES 'UTF8'");
		$sql_statement = "UPDATE token SET accesstoken='".$accesstoken."' WHERE refreshtoken='".$refreshtoken_decode."'";
		$connection->query($sql_statement);
	}

	// close connection
	$connection->close();

	echo $accesstoken;
?>