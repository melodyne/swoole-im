<?php
/**
 * Created by PhpStorm.
 * User: ullone
 * Date: 2019/2/18
 * Time: 16:04
 */

namespace console\extend\chat_im\model;


use common\models\broadcast\Broadcast;

class ChatRoom
{
    public $roomId;// 聊天室ID
    public $title;
    public $ownerUserId;// 群主用户ID
    public $userNum;
    public $userList = [];

    public static $error = '';

    /**
     * 创建房间
     */
    public static function createRoom($fd,$liveId){
        // 获取直播间信息
        $live = Broadcast::findOne($liveId);
        if(!$live){
            echo $liveId.'直播间不存在'.PHP_EOL;
            self::$error = '该直播间:'.$liveId.'不存在';
            return false;
        }
        $room = new self();
        $room->roomId = $live->id;
        $room->title = $live->title;
        $room->ownerUserId = '';
        $room->userNum = 1;
        $room->userList[] = '';

        // 把自己加进去
        if(!$room->addFd($fd)){
            echo 'Redis异常！'.PHP_EOL;
            self::$error = '系统异常！';
            return false;
        }

        if(!$room->saveToRedis()){
            return false;
        }

        return $room;
    }

    /**
     * 创建房间
     */
    public static function joinRoom($fd,$liveId){
        $room = self::getRoom($liveId);
        if(!$room){
            $room = self::createRoom($fd,$liveId);
        }
        if(!$room){
            return false;
        }
        if(!$room->addFd($fd)){
            echo '加入房间失败'.PHP_EOL;
            self::$error = '加入房间失败！';
            return false;
        }
        return $room;
    }

    /**
     * 移除房间的成员
     */
    public function removeMember($fd){
        return \Yii::$app->redis->srem('swoole_chat_group_'.$this->roomId,$fd);
    }

    /**
     * 获取房间
     */
    public static function getRoom($roomId){

        $roomInfo = json_decode(\Yii::$app->redis->get('swoole_chat_room_'.$roomId),true);
        if(!$roomInfo){
            return null;
        }
        $room = new self();
        foreach ($roomInfo as $k=>$v){
            $room->$k = $v;
        }
        return $room;

    }

    /**
     * 通过fd获取房间
     */
    public static function getRoomByFd($fd){

        $user = ChatUser::get($fd);
        if(!$user){
            return null;
        }
        $room = self::getRoom($user->room_id);
        return $room;

    }

    /**
     * 保存到Redis
     * @return bool
     */
    private function saveToRedis(){
        if($this->getRoom($this->roomId)){
           return true;
        }
        return \Yii::$app->redis->set('swoole_chat_room_'.$this->roomId,json_encode($this));
    }

    /**
     * 把fd加入聊天集合中
     * @return mixed
     */
    public function addFd($fd){

        echo 'fd:'.$fd.'加入了房间'.$this->roomId.PHP_EOL;
        $redis = \Yii::$app->redis;
        $redis->sadd('swoole_chat_group_'.$this->roomId,$fd);

        // 用户信息，更新房间id
        $user = ChatUser::get($fd);
        $user->room_id = $this->roomId;
        $user->update();

        return true;
    }

    /**
     * 获取房间的fd
     * @return mixed
     */
    public function getFds(){
       return \Yii::$app->redis->smembers('swoole_chat_group_'.$this->roomId);
    }
}