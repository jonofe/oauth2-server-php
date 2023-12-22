<?php
require_once 'oauth2/server.php';

// oauth2 validation of access token
if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    $server->getResponse()->send();
    file_put_contents('/usr/local/edomi/www/data/log/CUSTOMLOG_Alexa_Actions.log', date("Y-m-d H:i:s")." - OAUTH2  : ERROR! Access Token invalid (".__FILE__.")\n", FILE_APPEND);
    die;
}
file_put_contents('/usr/local/edomi/www/data/log/CUSTOMLOG_Alexa_Actions.log', date("Y-m-d H:i:s")." - OAUTH2  : Access Token validated (".__FILE__.")\n", FILE_APPEND);

$json = file_get_contents('input.json');
header('Content-Type: application/json; charset=utf-8');
echo $json;
?>
