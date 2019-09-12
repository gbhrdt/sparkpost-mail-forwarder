<?php

ini_set('display_errors', 1);
error_reporting(-1);

function sparkpost($method, $uri, $payload = [], $headers = []) {
    $defaultHeaders = [ 'Content-Type: application/json' ];
    $curl = curl_init();
    $method = strtoupper($method);
    $finalHeaders = array_merge($defaultHeaders, $headers);
    $url = 'https://api.sparkpost.com:443/api/v1/'.$uri;
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    if ($method !== 'GET') {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $finalHeaders);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

$payload = file_get_contents('php://input');

try {
	$messages = json_decode($payload, true);
} catch (Exception $ex) {
	die('no payload given');
}

if (empty($messages)) {
	die('no messages given');
}

foreach ($messages as $message) {
	$content = $message['msys']['relay_message']['content'];

	if (!empty($message['msys']['relay_message']['rcpt_to']) && strpos($message['msys']['relay_message']['rcpt_to'], 'your@email.com') === false) {
		return;
	}

 	$apiKey = "SPARKPOST_API_KEY_HERE";

	$replyTo = '';
	foreach ($content['headers'] as $header) {
		if (!empty($header['From'])) {
			$replyTo = $header['From'];
		}
	}

	$sendContent = [
      'from' => 'your@email.com',
      'reply_to' => $replyTo,
      'subject' => $content['subject']
  ];
	if ($content['email_rfc822_is_base64']) {
		$sendContent['email_rfc822'] = base64_decode($content['email_rfc822']);
	} else {
		$sendContent['html'] = $content['html'];
		$sendContent['text'] = $content['text'];
	}

	$payload = [
    'content' => $sendContent,
    'recipients' => [
        [ 'address' => 'your@recipient.com' ],
    ],
	];

	$headers = [ 'Authorization: ' . $apiKey ];

	$email_results = sparkpost('POST', 'transmissions', $payload, $headers);

	// error_log(json_encode(json_decode($email_results, false), JSON_PRETTY_PRINT));
}

?>
