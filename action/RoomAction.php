<?php
/**
 * Created by PhpStorm.
 * Author: Wanzhou Chen
 * Email:  295124540@qq.com
 * Date:   2019/2/21
 * Time:   14:26
 */

namespace console\extend\chat_im\action;

use console\extend\chat_im\model\ChatRoom;

class RoomAction extends BaseAction
{
    /**
     * 创建房间
     * @return array
     */
    public function create(){
        $room = ChatRoom::createRoom($this->fd,$this->data['live_id']);
        if(!$room){
            return $this->error(-1,ChatRoom::$error);
        }
       return $this->success($room);
    }

    /**
     * 加入房间
     */
    public function join(){
        $room = ChatRoom::joinRoom($this->fd,$this->data['room_id']);
        if(!$room){
            return $this->error(-1,ChatRoom::$error);
        }
        return $this->success($room);
    }

    /**
     * 退出房间
     */
    public function out(){

    }
}