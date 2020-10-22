<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Zalo\Builder\MessageBuilder;
use Automattic\WooCommerce\Client as WClient;
use Illuminate\Support\Facades\DB;

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
    public $wc;
    public $step = 0;

    public function __construct($request)
    {
        $this->sender = $request->input('sender');
        $this->message = $request->input('message.text');
        Storage::put('file.json', $this->message);
        $this->wc = new WClient(
            'https://dogohanam.net', 
            'ck_0c4900ca45dbdacd91ccf33e7ec9504fb481aba4', 
            'cs_1d7d5d12b8682f7805612d975ef797877b3b86a2',
            [
                'version' => 'wc/v3',
            ]
        );
        $this->initTempl();
        if ($this->step == 1) {
            if (is_numeric($this->message)) {
                $lsp = $this->getLoaiSanPham($this->message);
                $this->reply('Bạn đã chọn nhập loại sản phẩm "'.$lsp->name.'"');
                $list = [[
                    "id" => "Sắt",
                    "name" => "Sắt"
                ]];
                $this->sendButtons($this->sender['id'], 'Hãy chọn loại vật liệu', $list);
            }
        }
    }
    public function initTempl()
    {
        $data = DB::table('templ')->where('step', '<', 5)->get();
        if (count($data) > 0) {
            $dt = $data[0];
            $this->step = $dt->step;
        } else {
            $this->step = $dt->step;
            $this->insertTpl();
        }
        return $this->step;
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

    public function sendButtons($id, $text, $buttons)
    {
        $msgBuilder = new MessageBuilder('text');
        $msgBuilder->withUserId($id);
        $msgBuilder->withText($text);
        foreach ($buttons as $item) {
            $actionQueryHide = $msgBuilder->buildActionQueryHide('#'.$item->id); // build action query hide
            $msgBuilder->withButton($item->name, $actionQueryHide);
        }
        $msgText = $msgBuilder->build();
        Storage::put('file1.json', var_dump($msgText));
        return $this->send($msgText);
    }
    public function sendButtons1($id, $text, $buttons)
    {
        $msgBuilder = new MessageBuilder('text');
        $msgBuilder->withUserId($id);
        $msgBuilder->withText($text);
        foreach ($buttons as $item) {
            $actionQueryHide = $msgBuilder->buildActionQueryHide($item['id']); // build action query hide
            $msgBuilder->withButton($item->name, $actionQueryHide);
        }
        $msgText = $msgBuilder->build();
        Storage::put('file1.json', var_dump($msgText));
        return $this->send($msgText);
    }

    public function hear($text, $callback)
    {
        if ($this->message ==$text) {
            call_user_func($callback, $this);
        }
    }
    public function testwc()
    {
        $list = $this->wc->get('products/categories', ['per_page' => 100]);
        $step = array_chunk($list, 5);
        return $list;
    }
    public function dsSanPham()
    {
        $this->reply('Mời chọn danh mục sản phẩm');
        $list = $this->wc->get('products/categories', ['per_page' => 100]);
        $step = array_chunk($list, 5);
        foreach ($step as $items) {
            $this->sendButtons($this->sender['id'], ' ', $items);
        }
        return $list;
    }
    public function getLoaiSanPham($id)
    {
        $lsp = $this->wc->get('products/categories/'.$id);
        return $lsp;
    }
    public function gettempl()
    {
        
    }
    public function insertTpl()
    {
        $data = DB::table('templ')->insert([
            ['step' => 1]
        ]);
        return '$data';
    }
}
