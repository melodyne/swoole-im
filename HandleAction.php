<?php
/**
 * Created by PhpStorm.
 * User: ullone
 * Date: 2019/2/19
 * Time: 14:31
 */

namespace console\extend\chat_im;

use console\extend\chat_im\model\ChatRoom;
use console\extend\chat_im\model\ChatUser;

class HandleAction
{

    static public $instance;

    private function __construct(){
        // 私有化
    }

    static public function getInstance(){
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function handle($frame){

        $param = json_decode($frame->data,true);

        if(empty($param['action'])||empty($param['data'])){
            echo '无效数据格式！'.PHP_EOL;
            return false;
        }

        $wordArr = explode('/',trim($param['action'],'/'));
        $className = 'MainAction';
        $functionName = 'index';
        foreach ($wordArr as $key=>$value){
            $wordArr = explode('_',$value);
            $str = '';
            foreach ($wordArr as $v){
                $str .= ucwords(strtolower($v));
            }
            if($key==0){
                $className = 'console\\extend\\chat_im\\action\\'.$str.'Action';
            }
            if($key==1){
                $functionName = lcfirst($str);
            }
        }

        // 实例化对应的类
        $action = new $className();
        $action->fd = $frame->fd;
        $action->responseFds = [$frame->fd];
        $action->data = $param['data'];
        $data = $action->$functionName();
        return new Response($action->responseFds,$param['action'],$data);

    }

    /**
     * 销毁资源
     */
    public function destroy($fd){

        $chatUser = ChatUser::get($fd);
        if(!$chatUser){
            return true;
        }
        $chatRoom = ChatRoom::getRoom($chatUser->room_id);
        if(!$chatRoom){
            return true;
        }

        // 销毁聊天用户信息，聊天室移除该成员
        if($chatUser->delete()||$chatRoom->removeMember($fd)){
            echo '销毁了成员:'.$fd.'相关资源！'.PHP_EOL;
            return true;
        }else{
            echo '成员:'.$fd.'的相关资源销毁失败！'.PHP_EOL;
            return false;
        }

    }
}