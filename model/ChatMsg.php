<?php
/**
 * Created by PhpStorm.
 * User: ullone
 * Date: 2019/2/18
 * Time: 16:06
 */

namespace console\extend\chat_im\model;


class ChatMsg
{
    public $type;//类型
    public $user_id;//用户id
    public $user_name;//用户姓名
    public $user_head_img;//用户头像
    public $content;//内容

   static function create($fd,$chat){
       // 根据fd，从Rides缓存中获取用户信息
       $user = ChatUser::getChatUser($fd);
       if(!$user){
           return null;
       }
       $chatMsg = new self();
       $chatMsg->type = $chat['type'];
       $chatMsg->user_id = $user['user_id'];
       $chatMsg->user_name = $user['user_name'];
       $chatMsg->user_head_img = $user['user_head_img'];
       $chatMsg->content = $chat['content'];
       return $chatMsg;
   }

}