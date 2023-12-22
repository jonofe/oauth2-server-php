<?php

// include our OAuth2 Server object
require_once __DIR__ . '/server.php';

$request = OAuth2\Request::createFromGlobals();
$response = new OAuth2\Response();

// validate the authorize request
if (!$server->validateAuthorizeRequest($request, $response)) {
    $response->send();
    die;
}

if (isset($_POST['authorized'])) {
    $is_authorized = ($_POST['authorized'] === 'yes');
    $server->handleAuthorizeRequest($request, $response, $is_authorized);
    //if ($is_authorized) {
    // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
    //$code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
    //echo("SUCCESS! Authorization Code: $code");
    //}
    $response->send();
}

if (isset($_POST['login'])) {
    $pdo = new PDO('mysql:host=localhost;dbname=edomiAdmin', 'root', '');
    $user = $_POST['user'];
    $password = $_POST['password'];

    $statement = $pdo->prepare("SELECT * FROM user WHERE login = :user");
    $result = $statement->execute(array('user' => $user));
    $user = $statement->fetch();

    //Überprüfung des Passworts
    if ($user !== false && $password == $user['pass']) {
        $_SESSION['userid'] = $user['id'];
        {
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    input[type='text'] {
                        font-size: 60px;
                    }

                    input[type='submit'] {
                        font-size: 60px;
                    }

                    input[type='password'] {
                        font-size: 60px;
                    }
                </style>
            </head>
            <body>
                <span style="font-size:40pt">
                    <b>EDOMI Login successful!</b><br><br>
                <form method="post">
                    <label>Do you want to authorize the alexa skill to connect to EDOMI?</label><br/><br>
                    <input type="submit" size="30" name="authorized" value="yes">&nbsp;&nbsp;&nbsp;
                    <input type="submit" size="30" name="authorized" value="no">
                </form>
                </span>
            </body>
            </html>
            <?
            exit();
        }

    } else {
        $errorMessage = "<H1>Login FAILED !!!</H1><br>";
    }

}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        input[type='text'] {
            font-size: 60px;
        }

        input[type='submit'] {
            font-size: 100px;
        }

        input[type='password'] {
            font-size: 60px;
        }
    </style>

</head>
<body>

<?php
if (isset($errorMessage)) {
    echo $errorMessage;
}
?>

<SPAN STYLE="font-size:60.0pt">
<b>EDOMI Login</b><br><br>
<form method="post">
Username:<br>
<input type="text" size="20" maxlength="250" name="user"><br><br>
Password:<br>
<input type="password" size="20" maxlength="250" name="password"><br>
    <input type="hidden" name="login" value="1"><br>
<input type="submit" value="Login">
</form>
</body>
</html>

