<?php
error_log('callback start.');

$channel_id 	= 'xxxxxxxxxxxx';
$channel_secret = 'xxxxxxxxxxxx';
$mid 			= 'xxxxxxxxxxxx';
$proxy_url 		= 'http://xxxxxxxxxxxx';
$docomo_api_url = 'https://api.apigw.smt.docomo.ne.jp/dialogue/v1/dialogue?APIKEY=xxxxxxxxxxxxxxxxxxxxxx'


$json = file_get_contents("php://input");
$decode = json_decode($json, true);
$to = $decode['result'][0]['content']['from'];
$text = $decode['result'][0]['content']['text'];
$res = request_docomo($docomo_api_url, $text);

$res_format = array('contentType' => 1, 'toType' => 1, 'text' => $res);
$posts = [
	'to' 		=> [$to],
	'toChannel' => '1383378250',
	'eventType' => '138311608800106203',
	'content' 	=> $res_format
];

$ch = curl_init('https://trialbot-api.line.me/v1/events');
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
curl_setopt($ch, CURLOPT_PROXY, $proxy_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($posts));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json; charser=UTF-8",
    "X-Line-ChannelID: {$channel_id}",
    "X-Line-ChannelSecret: {$channel_secret}",
    "X-Line-Trusted-User-With-ACL: {$m_id}"
    )
);
$result = curl_exec($ch);
curl_close($ch);

error_log('callback finish.');


function request_docomo($url, $rex_text)
{
	$reqBody = array('utt' => $rex_text);
	$headers = array('Content-Type: application/json; charset=UTF-8',);
	$options = array(
		'http'=>array(
			'method'  => 'POST',
			'header'  => implode( "\r\n", $headers ),
			'content' => json_encode($reqBody),
		)
	);
	$stream = stream_context_create($options);
	$res = json_decode(file_get_contents($url, false, $stream), true);

	return isset($res['utt']) ? $res['utt'] : 'ん？';
}