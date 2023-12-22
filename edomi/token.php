<?php
// include our OAuth2 Server object
require_once __DIR__.'/server.php';

$post = json_encode($_POST);
file_put_contents('/usr/local/edomi/www/data/log/CUSTOMLOG_Alexa_Actions.log', date("Y-m-d H:i:s")." - OAUTH2  : $post\n", FILE_APPEND);

// Handle a request for an OAuth2.0 Access Token and send the response to the client
$server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();

?>
