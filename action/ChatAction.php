<?php
/**
 * Created by PhpStorm.
 * Author: Wanzhou Chen
 * Email:  295124540@qq.com
 * Date:   2019/2/21
 * Time:   14:43
 */

namespace console\extend\chat_im\action;

use console\extend\chat_im\model\ChatMsg;
use console\extend\chat_im\model\ChatRoom;

class ChatAction extends BaseAction
{
    /**
     * 群组聊天
     */
    public function byGroup(){
        $room = ChatRoom::getRoomByFd($this->fd);
        if(!$room){
            return $this->error(-1,'你还没有加入任何聊天室哦！');
        }
        $chatMsg = ChatMsg::create($this->fd,$this->data);
        if(!$chatMsg){
            return $this->error(-1,'系统异常，消息创建失败！');
        }
        $this->responseFds = $room->getFds();// 这步很重要
        return $this->success($chatMsg);
    }

    /**
     * 单聊
     */
    public function bySingle(){
        // 单聊，得判断对方是否在线，不在线时，需要把消息放入队列，对方上线后推送给对方
        return $this->success($this->data);
    }
}