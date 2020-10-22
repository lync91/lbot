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
    public $id;
    public $loaihang;
    public $tenhang;
    public $chatlieu;
    public $quycach;
    public $gia;
    public $soluong;
    public $hinhanh;
    public $attachments;

    public function __construct($request)
    {

        $this->sender = $request->input('sender');
        $this->message = $request->input('message.text');
        $this->event_name = $request->input('event_name');
        if ($this->event_name == 'user_send_image') {
            $this->attachments = $request->input('message.attachments');
        }
        Storage::put('file11.json', $this->message);
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
                $this->updateTempl(['loaihang' => $lsp->id]);
                $this->updateTempl(['tenhang' => $lsp->name]);
                $this->reply('Bạn đã chọn nhập loại sản phẩm "'.$lsp->name.'"');
                $this->setStep(2);
                $this->sendSimpleButtons($this->sender['id'], 'Hãy chọn loại vật liệu hoặc nhập vật liệu khác', ['Sắt', 'Inox', 'Gỗ tự nhiên', 'Gỗ ghép', 'Ghỗ ép']);
            } elseif ($this->message == '#') {
                $this->dsSanPham();
            }
        } elseif ($this->step == 2) {
            $this->updateTempl(['chatlieu' => $this->message]);
            $this->sendSimpleButtons($this->sender['id'], 'Mời nhập quy cách', ['80cm', '1m', '1,2m', '1,4m', '1,6m']);
            $this->setStep(3);
        } elseif ($this->step == 3) {
            $this->updateTempl(['quycach' => $this->message]);
            $this->reply('Mời nhập giá sản phẩm');
            $this->setStep(4);
        } elseif ($this->step == 4) {
            if (is_numeric($this->message)) {
                $this->updateTempl(['gia' => $this->message]);
                $this->reply('Mời gửi ảnh sản phẩm');
                $this->setStep(5);
            }
        } elseif ($this->step == 5) {
            if ($this->event_name == 'user_send_image') {
                $list = $this->attachments;
                foreach ($list as &$value) {
                    $value = ['src' => $value['payload']['url']];
                    // Storage::put('file.json', serialize($value['payload']['url']));
                }
                // Storage::put('file.json', serialize($list));
                $this->setStep(6);
                $this->reply('Sản phẩm đã tạo thành công');
                $this->createProduct($this->tenhang.' '. $this->chatlieu. ' ' .$this->quycach, $this->gia, [['id' => $this->loaihang]], $list);
            }
        }
    }
    public function initTempl()
    {
        $data = DB::table('templ')->where('step', '<', 6)->get();
        if (count($data) > 0) {
            $dt = $data[0];
            $this->step = $dt->step;
            $this->id = $dt->id;
            $this->loaihang = $dt->loaihang;
            $this->tenhang = $dt->tenhang;
            $this->chatlieu = $dt->chatlieu;
            $this->quycach = $dt->quycach;
            $this->gia = $dt->gia;

        } else {
            $this->step = 1;
            $this->insertTpl();
        }
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
            $actionQueryShow = $msgBuilder->buildActionQueryShow($item->id); // build action query hide
            $msgBuilder->withButton($item->name, $actionQueryShow);
        }
        $msgText = $msgBuilder->build();
        Storage::put('file1.json', var_dump($msgText));
        return $this->send($msgText);
    }
    public function sendSimpleButtons($id, $text, $buttons)
    {
        $msgBuilder = new MessageBuilder('text');
        $msgBuilder->withUserId($id);
        $msgBuilder->withText($text);
        foreach ($buttons as $item) {
            $actionQueryShow = $msgBuilder->buildActionQueryShow($item); // build action query show
        $msgBuilder->withButton($item, $actionQueryShow);
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
            $actionQueryShow = $msgBuilder->buildActionQueryShow($item); // build action query show
            $msgBuilder->withButton($item);
        }
        $msgText = $msgBuilder->build();
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
        $this->id = $data;
        Storage::put('file.json', serialize($data));
    }
    public function setStep($step)
    {
        DB::table('templ')->where('id', $this->id)
        ->update(['step' => $step]);
    }
    public function updateTempl($items)
    {
        DB::table('templ')->where('id', $this->id)
        ->update($items);
    }
    public function createProduct($name, $price = null, $cats, $images)
    {
        $data = [
            'name' => $name,
            'type' => 'simple',
            'regular_price' => $price,
            'categories' => $cats,
            'images' => $images
        ];
        return $this->wc->post('products', $data);
    }
}
