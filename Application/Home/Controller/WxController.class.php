<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/1/8
 * Time: 17:08
 */

namespace Home\Controller;

use Think\Controller;

class WxController extends Controller{

    public function checkSignature(){
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce     = $_GET['nonce'];
        $echostr   = $_GET['echostr'];
        $token = 'weixin';
        $tmp = array($timestamp,$nonce,$token);
        sort($tmp);
        $tmp = sha1(implode('',$tmp));
        if($tmp == $signature && $echostr){
            echo $echostr;
        }

    }
}