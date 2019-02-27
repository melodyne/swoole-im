<?php
/**
 * Created by PhpStorm.
 * Author: Wanzhou Chen
 * Email:  295124540@qq.com
 * Date:   2019/2/21
 * Time:   11:10
 */
namespace console\extend\chat_im\action;

class BaseAction
{

    public $fd;
    public $data;
    public $responseFds;//响应的fds

    protected function success($data){
        return $this->res(0,'success',$data);
    }

    protected function error($code,$msg,$data=[]){
        return $this->res($code,$msg,$data);
    }

    private function res($code,$msg,$data){
        return [
            'code'=>$code,
            'msg'=>$msg,
            'data'=>$data
        ];
    }
}