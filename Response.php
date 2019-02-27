<?php
/**
 * Created by PhpStorm.
 * User: ullone
 * Date: 2019/2/19
 * Time: 15:53
 */

namespace console\extend\chat_im;


class Response
{
    public $fds = [];//要发送消息给用户
    public $msg = '';//返回消息

    function __construct($fds,$action,$data)
    {
        if(!is_array($fds)){
            $fds = explode(',',$fds);
        }
        $this->fds = $fds;
        $this->msg = array_merge([ 'action'=>$action],$data);
    }
}