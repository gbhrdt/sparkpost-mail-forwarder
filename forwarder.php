<?php

ini_set('display_errors', 1);
error_reporting(-1);

require_once 'Mail.php';

$username = 'SMTP_Injection';
$password = ''; // YOUR SPAKRPOST API KEY

$from = 'YOUR NAME <from@email.com>'; // FROM
$to = 'YOUR NAME <to@email.com>'; // TO

$payload = file_get_contents('php://input');

try {
    $messages = json_decode($payload, true);
} catch (Exception $ex) {
    die('no payload given');
}

if (empty($messages)) {
    die('no messages given');
}

foreach ($messages as $message)
{
    $content = $message['msys']['relay_message']['content'];

    $subject = $content['subject'];
    $body = $content['text'];

    $headers = array('From' => $from,
        'To' => $to,
        'Subject' => $subject);
    $smtp = Mail::factory('smtp',
        array('host' => 'smtp.sparkpostmail.com',
            'port' => 587,
            'auth' => true,
            'username' => $username,
            'password' => $password));

    $mail = $smtp->send($to, $headers, $body);

    if (PEAR::isError($mail))
    {
        echo($mail->getMessage());
    }
    else
    {
        echo('OK');
    }
}

?>
