<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

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

class Zalo
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

    public function __construct($input)
    {
        $this->data = $input;
        $this->sender = $input['sender'];
        $this->message = new Message($input['message'], $input['event_name']);
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
    public function hear($text, $callback)
    {
        if ($this->message->text ==$text) {
            call_user_func($callback, $this);
        }
    }
}
