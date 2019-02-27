<?php
/**
 * Created by PhpStorm.
 * User: Wanzhou Chen
 * Date: 2019/2/18
 * Time: 10:21
 */

namespace console\controllers;

use console\extend\chat_im\ChatServer;
use yii\console\Controller;

/**
 * 基于Swoole聊天室服务
 * Class ChartServerController
 * @package console\controllers
 */
class ChatServerController extends Controller
{
    private $server;

    /**
     * 启动服务
     */
    function actionStart(){

        $this->server = new ChatServer();

        $this->server->handshake();  // 握手处理，监听

        $this->server->open();// 连接打开，监听

        $this->server->message();// 接收到消息，监听

        $this->server->close();// 链接关闭，监听

        $this->server->request();// http请求，监听

        $this->server->start();// 开始
    }

    /**
     * 重启服务
     */
    function actionRestart(){
        $this->sendSignal(SIGTERM);
        $time = 0;
        while (posix_getpgid($this->getPid()) && $time <= 10)
        {
            usleep(100000);
            $time++;
        }
        if ($time > 100)
        {
            $this->stderr("服务停止超时..." . PHP_EOL);
            exit(1);
        }
        if( $this->getPid() === false )
        {
            $this->stdout("服务重启成功..." . PHP_EOL);
        }
        else
        {
            $this->stderr("服务停止错误, 请手动处理杀死进程..." . PHP_EOL);
        }
        $this->actionStart();
    }

    /**
     * 关闭服务
     */
    function actionStop(){
        $this->sendSignal(SIGTERM);
        $this->stdout("服务已经停止, 停止监听 {$this->host}:{$this->port}" . PHP_EOL);
    }

    /**
     * 发送信号
     *
     * @param $sig
     */
    private function sendSignal($sig)
    {
        if ($pid = $this->getPid())
        {
            posix_kill($pid, $sig);
        }
        else
        {
            $this->stdout("服务未运行..." . PHP_EOL);
            exit(1);
        }
    }
    /**
     * 获取pid进程
     *
     * @return bool|string
     */
    private function getPid()
    {
        $pid_file = $this->config['pid_file'];
        if (file_exists($pid_file))
        {
            $pid = file_get_contents($pid_file);
            if (posix_getpgid($pid))
            {
                return $pid;
            }
            else
            {
                unlink($pid_file);
            }
        }
        return false;
    }
    /**
     * 写入pid进程
     *
     * @throws \yii\base\Exception
     */
    private function setPid()
    {
        $parentPid = getmypid();
        $pidDir = dirname($this->config['pid_file']);
        if(!file_exists($pidDir)) FileHelper::createDirectory($pidDir);
        file_put_contents($this->config['pid_file'], $parentPid + 1);
    }

}