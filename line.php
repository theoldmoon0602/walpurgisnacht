<?php

if (!function_exists('getallheaders')) 
{ 
    function getallheaders() 
    { 
	$headers = []; 
	foreach ($_SERVER as $name => $value) 
	{ 
	    if (substr($name, 0, 5) == 'HTTP_') 
	    { 
		$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
	    } 
	} 
	return $headers; 
    } 
} 
function callApi($url, $options = [])
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return string instead of write to stdout
    $headers = [];
    if (isset($options['headers'])) {
	$headers = $options['headers'];
    }
    if (isset($options['body'])) {
	$body = $options['body'];
	if (is_array($body)) {
	    $body = http_build_query($body);
	}
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	$headers['Content-Type'] = 'application/x-www-form-urlencoded';
    }
    if (isset($options['json'])) {
	$json = $options['json'];
	if (is_array($json)) {
	    $json = json_encode($json, JSON_UNESCAPED_UNICODE);
	}
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	$headers['Content-Type'] = 'application/json';
    }
    $httpheader = [];
    foreach ($headers as $k => $v) {
	$httpheader []= "$k: $v";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    $info['response'] = $result;
    curl_close($ch);
    return $info;
}
class Request {
    private $signature;
    private $body;
    private $json;
    private $accessToken;
    private $channelSecret;
    private $logger;
    public function __construct($accessToken, $channelSecret, $logger = null) {
	$this->accessToken = $accessToken;
	$this->channelSecret = $channelSecret;
	$this->logger = $logger;
	$headers = getallheaders();
	if (isset($headers['X-Line-Signature'])) {
	    $this->signature = $headers['X-Line-Signature'];
	}
	else {
	    if ($this->logger) {
		$this->logger->info("X-Line-Signature is not set");
	    }
	}
	$this->body = file_get_contents("php://input");
    }
    public function verify() {
	$hash = hash_hmac('sha256', $this->body, $this->channelSecret, true);
	if ($this->signature !== base64_encode($hash)) {
	    return false;
	}
	$this->json = json_decode($this->body, true);
	return true;
    }
    public function getBody() {
	return $this->body;
    }
    public function getMessage() {
	if ($this->json['events'][0]["type"] !== "message") {
	    if ($this->logger) {
		$this->logger->info("getMessage called for non message event");
	    }
	    return "";
	}
	return $this->json['events'][0]["message"]["text"];
    }
    private function buildHeader() {
	return [
	    'Content-Type' => 'application/json',
	    'Authorization' => "Bearer {{$this->accessToken}}"
	];
    }
    public function reply($obj) {
	$json = [
	    'replyToken' => $this->json['events'][0]['replyToken'],
	    'messages' => $obj,
	];
	return callApi("https://api.line.me/v2/bot/message/reply", [
	    'headers' => $this->buildHeader(),
	    'json' => $json
	]);
    }
    public function text($text) {
	return [
	    [
		'type'=>'text',
		'text'=>$text,
	    ]
	];
    }
    public function image($url, $thumburl) {
	return [
	    [
		'type' => 'image',
		'originalContentUrl' => $url,
		'previewImageUrl' => $thumburl,
	    ]
	];
    }
    public function push($to, $obj) {
	$json = [
	    'to' => $to,
	    'messages' => $obj,
	];
	return callApi("https://api.line.me/v2/bot/message/push", [
	    'headers' => $this->buildHeader(),
	    'json' => $json
	]);
    }
}

class PoorLogger {
	private $logfile;
	public function __construct($logfile) {
		$this->logfile = $logfile;
	}
	public function info($message, $context = []) {
		$this->log("info", $message, $context);
	}
	public function log($level, $message, $context = []) {
		$file = new SplFileObject($this->logfile, "a");
		$template = "%level[%date %time] %mes\n";
		$out = str_replace("%mes", $message, $template);
		$out = str_replace("%level", $level, $out);
		$out = str_replace("%date", date("Y-m-d"), $out);
		$out = str_replace("%time", date("H:i:s"), $out);
		$file->fwrite($out);
	}
}
