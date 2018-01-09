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

    private $_msgTpl = array(
        'text'=>'<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content></xml>',
        'image'=>'<xml><ToUserName>< ![CDATA[%s] ]></ToUserName><FromUserName>< ![CDATA[%s] ]></FromUserName><CreateTime>%s</CreateTime><MsgType>< ![CDATA[image] ]></MsgType><Image><MediaId>< ![CDATA[%s] ]></MediaId></Image></xml>',
        'voice'=>'<xml><ToUserName>< ![CDATA[%s] ]></ToUserName><FromUserName>< ![CDATA[%s] ]></FromUserName><CreateTime>%s</CreateTime><MsgType>< ![CDATA[voice] ]></MsgType><Voice><MediaId>< ![CDATA[%s] ]></MediaId></Voice></xml>',
        'video'=>'<xml><ToUserName>< ![CDATA[%s] ]></ToUserName><FromUserName>< ![CDATA[%s] ]></FromUserName><CreateTime>%s</CreateTime><MsgType>< ![CDATA[video] ]></MsgType><Video><MediaId>< ![CDATA[%s] ]></MediaId><Title>< ![CDATA[%s] ]></Title><Description>< ![CDATA[%s] ]></Description></Video> </xml>',
        'music'=>'<xml><ToUserName>< ![CDATA[%s] ]></ToUserName><FromUserName>< ![CDATA[%s] ]></FromUserName><CreateTime>%s</CreateTime><MsgType>< ![CDATA[music] ]></MsgType><Music><Title>< ![CDATA[%s] ]></Title><Description>< ![CDATA[%s] ]></Description><MusicUrl>< ![CDATA[%s] ]></MusicUrl><HQMusicUrl>< ![CDATA[%s] ]></HQMusicUrl><ThumbMediaId>< ![CDATA[%s] ]></ThumbMediaId></Music></xml>',
    );

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
        }else{
            $this->responseMsg();
        }

    }

    public function responseMsg(){
        $postStr  = $GLOBALS["HTTP_RAW_POST_DATA"] == '' ? file_get_contents('php://input') : $GLOBALS["HTTP_RAW_POST_DATA"];
        $postStr = simplexml_load_string($postStr,SimpleXMLElement,LIBXML_NOCDATA);
        $this->_responseMsg($postStr);
    }

    protected function _responseMsg($obj){
        switch (C('responseType')){
            case 'image':
                $this->reponseImage($obj);
                break;
            case 'voice':
                $this->responseVoice($obj);
                break;
            case 'video':
                $this->responseVedio($obj);
                break;
            case 'music':
                $this->responseMusic($obj);
                break;
            default:
                $this->responseText($obj);
        }
    }

    public function reponseImage($obj){
        $image = $this->getImage();
        $image['MediaId'] = 'aLjKn8LWT4uAp2mzRPEwxr-E4pQV_zMY2cwq4mjrKTAuAdDkwqQtI8vkoptf3k-O';
        $msg = sprintf($this->_msgTpl['image'],$obj->FromUserName,$obj->ToUserName,time(),$image['MediaId']);
        echo $msg;
    }

    public function responseVoice($obj){
        $voice = $this->getVoice();
        $msg = sprintf($this->_msgTpl['voice'],$obj->FromUserName,$obj->ToUserName,time(),$voice['MediaId']);
        echo $msg;

    }
    public function responseVedio($obj){
        $vedio = $this->getVedio();
        $msg = sprintf($this->_msgTpl['video'],$obj->FromUserName,$obj->ToUserName,time(),$vedio['MediaId'],$vedio['Title'],$vedio['Description']);
        echo $msg;
    }
    public function responseMusic($obj){
        $music = $this->getMusic();
        $msg = sprintf($this->_msgTpl['music'],$obj->FromUserName,$obj->ToUserName,time(),$music['Title'],$music['Description'],$music['MusicUrl'],$music['HQMusicUrl'],$music['ThumbMediaId']);
        echo $msg;
    }
    public function responseText($obj){
        $content = '你还好?';
        $msg = sprintf($this->_msgTpl['text'],$obj->FromUserName,$obj->ToUserName,time(),$content);
        echo $msg;
    }

    private function __getAccessToken(){
        if(cookie('access_token') != ''){
            return cookie('access_token');
        }
        $appID = C('appID');
        $appsecret = C('appsecret');
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appID}&secret={$appsecret}";
        $result = $this->__http_client($url);
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_SAFE_UPLOAD,false);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        if($method == 'post'){
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }
        $result = curl_exec($ch);
        $result = json_decode($result,true);

        return $result;
    }

    public function setMenu(){
        $access_token = $this->__getAccessToken();
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
        $result = $this->__http_client($url,'post',$data);
        echo "<pre>";print_r($result);
    }

    public function upload(){
        $this->display('Wx/upload');
    }

    public function uploadFunction(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->savePath  =      'Public/Uploads/'; // 设置附件上传目录// 上传文件
        $info   =   $upload->uploadOne($_FILES['photo']);
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
            return false;
        }else{// 上传成功 获取上传文件信息
            $path =  $upload->rootPath.$info['savepath'].$info['savename'];
        }
        if($_POST['type'] == 'all'){
            $url = "https://api.weixin.qq.com/cgi-bin/material/add_news?access_token={$this->__getAccessToken()}";
        }else{
            $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$this->__getAccessToken()}&type=image";
        }
        $name = pathinfo($path,PATHINFO_FILENAME);
        $ext = pathinfo($path,PATHINFO_EXTENSION);
        $filesize = filesize($path);
        $data['media'] = "@{$this->getUrl()}{$path};type=image;filename={$name}.{$ext};filelength={$filesize};content-type=image/$ext;";
        $result = $this->__http_client($url,'post',$data);
        if(isset($result['media_id'])){
            $data = array(

            );
            $sql = M('wxsucai')->add();
        }
    }
    public function getUrl(){
        $script_file = $_SERVER['SCRIPT_FILENAME'];
        $script_file = substr($script_file,0,-9);
        return  $script_file;
    }

    public function getSucai(){
        $ACCESS_TOKEN = $this->__getAccessToken();
        $MEDIA_ID = 'aLjKn8LWT4uAp2mzRPEwxr-E4pQV_zMY2cwq4mjrKTAuAdDkwqQtI8vkoptf3k-O';
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$ACCESS_TOKEN}&media_id={$MEDIA_ID}";
        header('location:'.$url);
    }
}