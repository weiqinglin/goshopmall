<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/1/8
 * Time: 17:08
 */

namespace Home\Controller;

use Think\Controller;
use Think\Exception;

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

    private function __getAccessToken(){
        if(isset(cookie('access_token'))){
            return cookie('access_token');
        }
        $appID = C('appID');
        $appsecret = C('appsecret');
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appID}&secret={$appsecret}";
        $result = __http_client($url);
        if(isset($result['access_token'])){
            cookie('access_token',$result['access_token'],$result['expires_in']);
            return $result['access_token'];
        }else{
            throw new Exception('access token获取失败');
        }
    }

    private function __http_client($url,$method='get',$data=null)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        if($method == 'post'){
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }
        $result = curl_exec($ch);
        $result = json_decode(json_encode($result),true);
        return $result;
    }

    public function setMenu(){
        $access_token = cookie('access_token');
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
        $data = ' {
     "button":[
     {    
          "type":"click",
          "name":"今日歌曲",
          "key":"V1001_TODAY_MUSIC"
      },
      {
           "name":"菜单",
           "sub_button":[
           {    
               "type":"view",
               "name":"搜索",
               "url":"http://www.soso.com/"
            },
            {
                 "type":"miniprogram",
                 "name":"wxa",
                 "url":"http://mp.weixin.qq.com",
                 "appid":"wx286b93c14bbf93aa",
                 "pagepath":"pages/lunar/index"
             },
            {
               "type":"click",
               "name":"赞一下我们",
               "key":"V1001_GOOD"
            }]
       }]
 }';
        $result = __http_client($url,'post',$data);
        echo "<pre>";print_r($result);
    }
}