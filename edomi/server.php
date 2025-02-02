<?php

$dsn      = 'mysql:dbname=oauth2;host=localhost';
$username = 'root';
$password = '';

// error reporting (this is a demo, after all!)
ini_set('display_errors',1);error_reporting(E_ALL);

// Autoloading (composer is preferred, but for this example let's just do this)
require_once('oauth2-server-php/src/OAuth2/Autoloader.php');
OAuth2\Autoloader::register();

// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
$storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));

// Pass a storage object or array of storage objects to the OAuth2 server class
$server = new OAuth2\Server($storage, [
    'access_lifetime' => 30,
    'always_issue_new_refresh_token' => false,
    'unset_refresh_token_after_use' => false,
    'refresh_token_lifetime' => 315360000
]); // refresh token valid for 10 years

// Add the "Client Credentials" grant type (it is the simplest of the grant types)
// $server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));

// Add the "Authorization Code" grant type (this is where the oauth magic happens)
$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));

$server->addGrantType( new OAuth2\GrantType\RefreshToken($storage,[
    'always_issue_new_refresh_token' => false,
    'unset_refresh_token_after_use'  => false
]));

?>

