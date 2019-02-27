<?php
/**
 * Created by PhpStorm.
 * User: ullone
 * Date: 2019/2/18
 * Time: 16:02
 */

namespace console\extend\chat_im\model;

use common\models\user\User;

class ChatUser
{
    public $user_id;
    public $user_name;
    public $user_head_img;
    public $room_id;

    private $fd;

    /**
     * 身份校验
     */
    static function checkIdentity($request){
        if(!isset($request->header['sec-websocket-protocol'])){
            echo '前端没定义子协议：sec-websocket-protocol！'.PHP_EOL;
            return false;
        }
        $authArr = explode('&',$request->header['sec-websocket-protocol']);
        if(count($authArr)!=3){
            echo '子协议sec-websocket-protocol格式错误！'.PHP_EOL;
            return false;
        }

        $user = User::findOne($authArr[1]);
        if(!$user)
        {
            echo '用户不存在！'.PHP_EOL;;
            return false;
        }
        if(!$user->validToken($authArr[0], $authArr[2]))
        {
            echo 'token验证失败！'.PHP_EOL;;
            return false;
        }

        if(!self::saveToRedis($request->fd,$user)){
            echo 'redis保存失败！'.PHP_EOL;;
            return false;
        }

        return true;
    }

    /**
     * 获取用户
     */
    public static function get($fd){
        $userInfo = self::getChatUser($fd);
        if(!$userInfo){
            return null;
        }
        $user = new self();
        foreach ($userInfo as $k=>$v){
            $user->$k = $v;
        }
        $user->fd = $fd;
        return $user;
    }

    /**
     * 保存聊天用户
     * @param $fd
     * @return mixed
     */
    private static function saveToRedis($fd,$user){
        $userInfo = [
            'user_id'=>$user->id,
            'user_name'=>$user->username?$user->username:$user->nickname,
            'user_head_img'=>$user->headimgurl,
        ];
        return \Yii::$app->redis->set('swoole_chat_user_'.$fd,json_encode($userInfo));
    }

    /**
     * 更新聊天用户
     * @return mixed
     */
    public function update(){
        return \Yii::$app->redis->set('swoole_chat_user_'.$this->fd,json_encode($this));
    }

    /**
     * 删除聊天用户
     */
    public function delete(){
        return \Yii::$app->redis->del('swoole_chat_user_'.$this->fd);
    }

    /**
     * 获取聊天用户
     * @param $fd
     * @return mixed
     */
    public static function getChatUser($fd){

        return json_decode(\Yii::$app->redis->get('swoole_chat_user_'.$fd),true);
    }
}