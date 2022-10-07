<?php


$today =  date('Y-m-d');
$from = date('Y-m-d', strtotime($today."+ 7 days"));
$to = date('Y-m-d', strtotime($today."+ 28 days"));

$employeesId = YOUR_EMPLOYEES_ID;
$authCode = DIC_AUTH_CODE;

echo $from.' -> '.$to. PHP_EOL;

// get cURL resource
$ch = curl_init();

// set url
curl_setopt($ch, CURLOPT_URL, 'https://api.dipendentincloud.it/timesheet?employees='.$employeesId.'&version=2&date_from='.$from.'&date_to='.$to);

// set method
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

// return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// set headers
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: '.$authCode.'',
    'Content-Type: application/json; charset=utf-8',
]);

// json body
$json_array = [

];
$body = json_encode($json_array);

// set body
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

// send the request and save response to $response
$response = curl_exec($ch);

// stop if fails
if (!$response) {
    die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}

// close curl resource to free up system resources
curl_close($ch);

$data = json_decode($response);
$days = get_object_vars($data->data->timesheet);

$msg = '';
foreach ($days[array_key_first($days)] as $key => $day){

    $reasons = $day->reasons;

    if(!empty($reasons)) {
        $code = $reasons[0]->reason->code;

        if ($code != "SW") {
            $msg .= $key . ' -> Ferie' . PHP_EOL;
        }
    }
}

if($msg != ''){
    telegram($msg);
}



/*
 * https://www.html.it/pag/394635/creare-telegram-bot/
 */
function telegram($msg) {
    echo 'Telegram sending'.PHP_EOL;
    $telegrambot = BOT_TOKEN;
    $telegramchatid = CHAT_ID;
    $url = 'https://api.telegram.org/bot'.$telegrambot.'/sendMessage';

    $data = [
        'chat_id' => $telegramchatid,
        'text' => $msg
    ];
    $options=[
        'http' => [
            'method'=>'POST',
            'header' => "Content-Type:application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url,false,$context);
    echo 'Telegram: sent' . PHP_EOL;
}


