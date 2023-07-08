<?php

use GuzzleHttp\Client;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Facades\FCM;

use Carbon\Carbon;

function endecryption($action, $string, $key = '', $iv = ''){
	$output = false;

	$encrypt_method = "AES-256-CBC";
	$secret_key = env('APP_KEY', 'secret key');
	$secret_iv  = 'secret iv';

	$key = hash('sha256', $secret_key);

	$iv = substr(hash('sha256', $secret_iv), 0, 16);

	//encryption
	if( $action == 1) {
		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = base64_encode($output);
	}
	//decryption
	else if( $action == 2){
		$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	}
	return $output;
}

function getRandomValue($type, $length = 10){
	$value = '';

	if(!is_numeric($length)){
		$length = 10;
	}

	switch(strtolower($type)){
		case 'numeric':
			$characters = '0123456789';
			break;
		case 'string':
		default:
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
	}

	$charactersLength = strlen($characters);

	for ($i = 0; $i < $length; $i++) {
		if($i == 0){
			$start = 1;
		}else{
			$start = 0;
		}

		$value .= $characters[rand($start, $charactersLength - 1)];
	}

	return $value;
}

function sendTelegram($bot_key, $chat_id, $data){
	$data['chat_id'] = $chat_id;

	$client = new Client();

	$response = $client->request('GET', 'https://api.telegram.org/bot'.$bot_key.'/sendMessage?'.http_build_query($data));
	$result = $response->getBody()->getContents();

	return json_decode($result);
}


function fcmSendData($params){
	$optionBuilder = new OptionsBuilder();
	$optionBuilder->setTimeToLive(60);

	$dataBuilder = new PayloadDataBuilder();
	$dataBuilder->addData($params['data']);

	$option = $optionBuilder->build();
	$data = $dataBuilder->build();

	$token = $params['token'];

	$downstreamResponse = FCM::sendTo($token, $option, null, $data);

	return ['success'=> $downstreamResponse->numberSuccess(), 'failure' => $downstreamResponse->numberFailure()];
}

function fcmSendNotification($params){
	$optionBuilder = new OptionsBuilder();
	$optionBuilder->setTimeToLive(60*20);

	$notificationBuilder = new PayloadNotificationBuilder($params['title']);
	$notificationBuilder->setBody($params['content']);

	if(isset($params['sound']) && !empty($params['sound'])){
		$notificationBuilder->setSound($params['sound']);
	}else{
		$notificationBuilder->setSound('default');
	}

	$dataBuilder = new PayloadDataBuilder();
	$dataBuilder->addData($params['data']);

	$option = $optionBuilder->build();
	$notification = $notificationBuilder->build();
	$data = $params['data'] ? $dataBuilder->build() : null;

	$token = $params['token'];

	$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

	return ['success'=> $downstreamResponse->numberSuccess(), 'failure' => $downstreamResponse->numberFailure()];
}
?>
