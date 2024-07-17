<?php

function curlGET($url, $username = null, $password = null, array $headers = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if (!empty($headers))
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
		curl_close($ch);
		return $response;
	}