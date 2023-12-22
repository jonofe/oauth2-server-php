<?php
$edomiUrl = 'http://localhost';		// you can also change it to your internal IP or DNS name of your EDOMI Server (don't use the external DNS name of the reverse proxy here!!!
$edomiRemoteUser = '#######';		// change to EDOMI remote user
$edomiRemotePassword = '######';	// change to EDOMI remote password
$edomiRemoteKoId = ###;			// change to ID des Response iKO (see step 7 of documentation)

require_once 'oauth2/server.php';

// read incoming json from alexa
$json = file_get_contents('php://input');

// get headers and log them
$headers = json_encode(getallheaders());
file_put_contents('/usr/local/edomi/www/data/log/CUSTOMLOG_Alexa_Actions.log', date("Y-m-d H:i:s")." - HEADERS : $headers (".__FILE__.")\n", FILE_APPEND);

// oauth2 validation of access token
if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    $server->getResponse()->send();
    file_put_contents('/usr/local/edomi/www/data/log/CUSTOMLOG_Alexa_Actions.log', date("Y-m-d H:i:s")." - OAUTH2  : ERROR! Access Token invalid (".__FILE__.")\n", FILE_APPEND);
    die;
}
file_put_contents('/usr/local/edomi/www/data/log/CUSTOMLOG_Alexa_Actions.log', date("Y-m-d H:i:s")." - OAUTH2  : Access Token validated (".__FILE__.")\n", FILE_APPEND);


file_put_contents('/usr/local/edomi/www/data/log/CUSTOMLOG_Alexa_Actions.log', date("Y-m-d H:i:s")." - RECEIVED: $json (".__FILE__.")\n", FILE_APPEND);

$action = json_decode($json, true);

if (json_last_error()===JSON_ERROR_NONE)
{
	if (array_key_exists('event_id', $action) && array_key_exists('event_response',$action) && array_key_exists('event_response_type',$action)) {
		$url = $edomiUrl.'/remote/?login='.$edomiRemoteUser.'&pass='.$edomiRemotePassword.'&koid='.$edomiRemoteKoId.'&kovalue='.urlencode($json);
		file_put_contents('/usr/local/edomi/www/data/log/CUSTOMLOG_Alexa_Actions.log', date("Y-m-d H:i:s")." - FWD-URL : $url\n\n", FILE_APPEND);
		file_get_contents($url);
	}
}

?>
