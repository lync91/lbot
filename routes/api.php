<?php

use Illuminate\Http\Request;
// use App\Http\Controllers\Zalo;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use App\LBot;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/', function (Request $request) {
    return '$request->user();';
});
Route::get('/test', function (Request $request) {
    $zalo = new LBot($request);
    return $zalo->test();
});
Route::post('/', function(Request $request) {
    $input = json_decode($request->getContent(), true);
    // Storage::put('file.json', $request->input('app_id'));
    $zalo = new LBot($request);
    $res = '';
    // $res = $zalo->reply('hello');
    $res = $zalo->reply('1');
    // $zalo->hear('hello', function ($bot)
    // {
    //     $res = $bot->reply('1');
    // });
    return $res;
});

// Route::get('/test', function (Request $request) {
//     $client = new GuzzleHttp\Client();
//     $test = 'Hello';
//     $res = $client->request('POST', $url.$token, [
//         'json' => [
//             'recipient' => [
//                 'user_id' => '9020839572338346018'
//             ],
//             'message' => [
//                 'text' => "hello"
//             ]
//         ]
//     ]);

//     if ($res->getStatusCode() == 200) { // 200 OK
//         $response_data = $res->getBody()->getContents();
        
//         return $response_data;
//     }
// });

