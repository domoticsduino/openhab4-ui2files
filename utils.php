<?php


function curlGET(string $url, string $username = null, string $password = null, array $headers = null): array{
	return curl($url, "GET", null, $username, $password, $headers);
}

function curlPOST(string $url, string $jsonPayload = null, string $username = null, string $password = null, array $headers = null): array{
	return curl($url, "POST", $jsonPayload, $username, $password, $headers);
}

function curl(string $url, string $type, string $jsonPayload = null, string $username = null, string $password = null, array $headers = null): array{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	switch($type){
		case "PUT":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			break;
		case "POST":
			curl_setopt($ch, CURLOPT_POST, 1);
			break;
	}
	if (in_array($type, ["POST", "PUT"]) && !empty($jsonPayload)){
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
		if(empty($headers))
			$headers = [];
		array_push($headers, "Content-Type: application/json");
	}
	if(!empty($headers))
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if(!empty($username)){
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	}
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
	curl_setopt($ch, CURLOPT_VERBOSE, false);
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return ["response" => $response, "httpcode" => $httpCode];
}