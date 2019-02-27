<?php
/**
 * Created by PhpStorm.
 * User: ullone
 * Date: 2019/2/18
 * Time: 18:44
 */

namespace console\extend\chat_im;

use console\extend\chat_im\model\ChatUser;
use \Swoole\WebSocket\Server as SwooleSocketServer;
use Yii;

class ChatServer
{
    private $port = '9501';//聊天室服务端口
    private $server;

    public function __construct()
    {
        $this->server = new SwooleSocketServer("0.0.0.0",$this->port, SWOOLE_PROCESS , SWOOLE_SOCK_TCP | SWOOLE_SSL);
        $this->server->set([
            'reactor_num' => Yii::$app->params['swoole']['reactor_num'],
            'worker_num' => Yii::$app->params['swoole']['worker_num'],
            'max_request' => Yii::$app->params['swoole']['max_request'],
            //'chroot' => '/var/www/yiia/console', 
            'user' => Yii::$app->params['swoole']['user'], 
            'group' => Yii::$app->params['swoole']['group'],
            'ssl_cert_file' => Yii::$app->params['swoole']['ssl_cert_file'],
            'ssl_key_file' => Yii::$app->params['swoole']['ssl_key_file'],
        ]);
    }

    /**
     * 监听握手处理
     */
    public function handshake()
    {
        $this->server->on('handshake', function (\swoole_http_request $request, \swoole_http_response $response) {
            echo '接收到一条连接握手请求.' . PHP_EOL;
            // 验证身份,如果验证未通过end输出，返回false，握手失败，拒绝连接
            if(!ChatUser::checkIdentity($request)){
                $response->end();
                return false;
            }

            // websocket握手连接算法验证
            $secWebSocketKey = $request->header['sec-websocket-key'];
            $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
            if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
                $response->end();
                return false;
            }
            $key = base64_encode(sha1(
                $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
                true
            ));

            $headers = [
                'Upgrade' => 'websocket',
                'Connection' => 'Upgrade',
                'Sec-WebSocket-Accept' => $key,
                'Sec-WebSocket-Version' => '13',
            ];

            // WebSocket connection to 'ws://127.0.0.1:9502/'
            // failed: Error during WebSocket handshake:
            // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
            if (isset($request->header['sec-websocket-protocol'])) {
                $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
            }

            foreach ($headers as $key => $val) {
                $response->header($key, $val);
            }

            $response->status(101);
            $response->end();
        });
    }

    /**
     * 开启服务器
     */
    public function start(){
        $this->server->start();
    }

    /**
     * 监听WebSocket连接打开事件
     */
    public function open()
    {
        echo '监听连接...'.PHP_EOL;
        $this->server->on('open', function (SwooleSocketServer $server, $request) {
//            if($server->disconnect($request->fd, 1000,'非法连接！')){
//                echo "非法接入，已断开连接：{$request->fd}".PHP_EOL;
//                return false;
//            }
            echo "成功连接到聊天室 fd：{$request->fd}".PHP_EOL;
        });
    }

    /**
     * 监听WebSocket消息事件
     */
    public function message()
    {
        $this->server->on('message', function (SwooleSocketServer $server, $frame) {

            // $this->server->connections 所有websocket连接用户的fd
            // 消息处理，返回相关用户的fds和返回信息
            $response = HandleAction::getInstance()->handle($frame);
            foreach ($this->server->connections as $fd) {
                if(in_array($fd,$response->fds)){
                    echo "推送消息给fd：{$fd}".PHP_EOL;
                    $this->server->push($fd,json_encode($response->msg,JSON_UNESCAPED_UNICODE));
                }
            }
        });
    }

    /**
     * 监听WebSocket连接关闭事件
     */
    public function close()
    {
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
            HandleAction::getInstance()->destroy($fd);
        });
    }

    /**
     * 监听WebSocket http请求事件
     */
    public function request(){
        echo '监听请求...'.PHP_EOL;
        $this->server->on('request', function ($request, $response) {
            echo '接收到一个请求.'.PHP_EOL;
            // 接收http请求从get获取message参数的值，给用户推送
            // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
//            foreach ($this->server->connections as $fd) {
//                echo $request->get['message'].' to fd:'.$fd;
//                $this->server->push($fd, $request->get['message']);
//            }
        });
    }

}