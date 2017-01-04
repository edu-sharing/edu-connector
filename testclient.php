<?php

define('CONNECTOR_URL', 'http://138.201.17.74/eduConDev/');
define('API_URL', 'http://appserver7.metaventis.com:7133/edu-sharing/rest/');
error_reporting(E_ALL);
ini_set('display_errors', 1);

class testclient {

	private $oauth_access_token = '';
	private $oauth_refresh_token = '';
	private $oauth_expires_in = '';
	private $oauth_token_received = '';
    
    public function __construct() {
        $this->getToken();
        $this->showForm();
    }

    private function getToken() {
		$postFields = 'grant_type=password&client_id=eduApp&client_secret=secret&username=admin&password=admin';
		$raw = $this->call ( './../oauth2/token', 'POST', array (), $postFields, false );
		$return = json_decode($raw);
		$this->oauth_access_token = $return->access_token;
		$this->oauth_refresh_token = $return->refresh_token;
		$this->oauth_expires_in = $return->expires_in;
		$this->oauth_token_received = time();
	}



    private function call($url, $httpMethod = '', $additionalHeaders = array(), $postFields = array(), $sendAuth = true) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, API_URL . $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch,CURLOPT_FAILONERROR,true);

		switch ($httpMethod) {
			case 'POST' :
				curl_setopt ( $ch, CURLOPT_POST, true );
				break;
			case 'PUT' :
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				break;
			case 'DELETE' :
				curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "DELETE" );
				break;
			default :
		}
		
		$headers = array_merge ( array ('Accept: application/json'), $additionalHeaders );
		
		if($sendAuth)
			$headers = array_merge ($headers, array ('Authorization: Bearer ' . $this->oauth_access_token));
		
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		
		if (! empty ( $postFields )) {
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
		}
		$exec = curl_exec ( $ch );

		
		if ($exec === false) {
			var_dump(curl_error ( $ch ) );exit();
		}

		$httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		if ($httpcode != 200) {
			var_dump(curl_error($ch));
			echo $httpcode;
			//header("HTTP/1.1 500 Internal Server Error");
			exit();
		}
		curl_close ( $ch );
		
		return $exec;
	}

private function showForm() {


echo '
<html>
<head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<style>
body {
    padding: 30px;
}
label {
    display: block;
    margin-top: 20px;
}
input, select {
    display: block;
    width: 400px;
    height: 24px;
}
button {
    margin-top: 20px;
    width: 400px;
    height: 100px;
}
</style>
</head>
<body>
Repository: '.API_URL.'<br/>
Action: '.CONNECTOR_URL.'
<form target="_blank" method="POST" action="'.CONNECTOR_URL.'">
<label>tool</label><input name="tool" value="edu-tool-onyx">
<label>tool_subtype</label><input name="tool_subtype" value="">
<label>node_id</label><input name="node_id" value="18cb0b60-7622-4f03-9ad7-a532ca564576">
<label>oauth_access_token</label><input name="oauth_access_token" value="'.$this->oauth_access_token.'">
<label>oauth_refresh_token</label><input name="oauth_refresh_token" value="'.$this->oauth_refresh_token.'">
<label>oauth_expires_in</label><input name="oauth_expires_in" value="'.$this->oauth_expires_in.'">
<label>api_url</label><input name="api_url_dummy" value="'.API_URL.'" disabled>
<input type="hidden" name="api_url" value="'.API_URL.'">
<button type="submit">Go</button>
</form>
</body>
</html>
';

}

}

$testclient = new testclient();
