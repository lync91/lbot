<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Zalo\Builder\MessageBuilder;

class Message
{
    public $msg_id;
    public $text;
    public $attachments;
    public function __construct($mes, $type)
    {
        $this->msg_id = $mes['msg_id'];
        $this->text = $mes['text'];
        if ($type == 'user_send_image') {
            $this->attachments = $mes['attachments'];
        }
    }
}

class LBot
{
    CONST TOKKEN = 'ek6DD1NtP4MYvuWx5FPdIg7bYZGIw6OwqwVMEHdpHWlKaQi0PTmh9PAEnGnYuLuQoOR3BJRFLrw1sT9mG9XFRvBNnar1dd1CjVZYULcJ15UqXPLO2iDpHzYqodGaprO1rAMy2o7LEddmmjLU5wGwNjZehdidWZzVpysvHWw2Kp3vszau2A9_JV_RupWXj7PCxjxDKrgINcosoEbXPxjVIhFIorD2aqLddeFRSblOT0s9f9WdRi4gLwdHb5f2fn5obi-QKc2P56UUoub3RhXlPwAfmKLGwrDtEKg68zz65-nWGW';
    CONST URL = 'https://openapi.zalo.me/v2.0/oa/message?access_token=';
    public $client;
    public $data;
    public $sender;
    public $app_id;
    public $user_id_by_app;
    public $event_name;
    public $timestamp;
    public $message;
    private $request;

    public function __construct($request)
    {
        $this->sender = $request->input('sender');
        $this->message = $request->input('message.text');
        Storage::put('file.json', $this->message);
    }

    public function send_text($id, $text)
    {
        $client = new Client();
        $res = $client->request('POST', self::URL.self::TOKKEN, [
            'json' => [
                'recipient' => [
                    'user_id' => $this->sender['id']
                ],
                'message' => [
                    'text' => $text
                ]
            ]
        ]);
    
        if ($res->getStatusCode() == 200) { // 200 OK
            $response_data = $res->getBody()->getContents();
            return $response_data;
        }
    }
    public function reply($text)
    {
        return $this->send_text($this->sender['id'], $text);
    }

    public function send($mes)
    {
        $client = new Client();
        $res = $client->request('POST', self::URL.self::TOKKEN, [
            'json' => $mes
        ]);
    
        if ($res->getStatusCode() == 200) { // 200 OK
            $response_data = $res->getBody()->getContents();
            return $response_data;
        }
    }

    public function test()
    {
        // build data
        $msgBuilder = new MessageBuilder('text');
        $msgBuilder->withUserId('2036513421776710115');
        $msgBuilder->withText('Message Text');

        // add buttons (only support 5 buttons - optional)
        $actionOpenUrl = $msgBuilder->buildActionOpenURL('https://wwww.google.com'); // build action open link
        $msgBuilder->withButton('Open Link', $actionOpenUrl);

        $actionQueryShow = $msgBuilder->buildActionQueryShow('query_show'); // build action query show
        $msgBuilder->withButton('Query Show', $actionQueryShow);

        $actionQueryHide = $msgBuilder->buildActionQueryHide('query_hide'); // build action query hide
        $msgBuilder->withButton('Query Hide', $actionQueryHide);

        $actionOpenPhone = $msgBuilder->buildActionOpenPhone('0919018791'); // build action open phone
        $msgBuilder->withButton('Open Phone', $actionOpenPhone);

        $actionOpenSMS = $msgBuilder->buildActionOpenSMS('0919018791', 'sms text'); // build action open sms
        $msgBuilder->withButton('Open SMS', $actionOpenSMS);

        $msgText = $msgBuilder->build();
        return $this->send($msgText);
        // return $msgText;
        // send request
        // $response = $zalo->post(ZaloEndpoint::API_OA_SEND_MESSAGE, $accessToken, $msgText);
        // $result = $response->getDecodedBody(); // result
    }

    public function hear($text, $callback)
    {
        if ($this->message ==$text) {
            call_user_func($callback, $this);
        }
    }
}
