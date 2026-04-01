<?php
//function accountcreate($username, $password, $ipaddress, $macaddress, $connection, $bandwidth, $disabledate, $disabletime)
//{
//    $adminpassword='852456Qwe';
//    $adminport=88;
//    $proxyaddress='89.252.174.162';
//
//    $fp = fsockopen($proxyaddress, $adminport, $errno, $errstr, 1000);
//    if(!$fp)
//    {
//        echo "$errstr ($errno)<br>\n";
//    }
//    else
//    {
//        $url_ = "/account";
//        $url = "add=1"."&";
//        $url = $url."autodisable=1"."&";
//        $url = $url."enable=1"."&";
//        if(strlen($password) > 0)
//            $url = $url."usepassword=1"."&";
//        else
//            $url = $url."usepassword=0"."&";
//
//        $url = $url."enablesocks=1"."&";
//        $url = $url."enablewww=0"."&";
//        $url = $url."enabletelnet=0"."&";
//        $url = $url."enabledial=0"."&";
//        $url = $url."enableftp=0"."&";
//        $url = $url."enableothers=0"."&";
//        $url = $url."enablemail=0"."&";
//        $url = $url."username=".$username."&";
//        $url = $url."password=".$password."&";
//        $url = $url."connection=".$connection."&";
//        $url = $url."bandwidth=".$bandwidth."&";
//        $url = $url."disabledate=".$disabledate."&";
//        $url = $url."disabletime=".$disabletime."&";
//        $url = $url."userid=-1";
//        $len = "Content-Length: ".strlen($url);
//        $auth = "Authorization: Basic ".base64_encode("admin:".$adminpassword);
//        $msg = "POST ".$url_." HTTP/1.0\r\nHost: ".$proxyaddress."\r\n".$auth."\r\n".$len."\r\n"."\r\n".$url;
//        fputs($fp,$msg);
//        //echo $msg;
//        while(!feof($fp))
//        {
//            $s = fgets($fp,4096);
//            //echo $s;
//        }
//        fclose($fp);
//    }
//}
//function accountedit($username, $password, $connection, $bandwidth, $disabledate, $disabletime)
//{
//    $adminpassword = '852456Qwe';
//    $adminport = 88;
//    $proxyaddress = '89.252.174.162';
//
//    $fp = fsockopen($proxyaddress, $adminport, $errno, $errstr, 1000);
//    if(!$fp)
//    {
//        echo "$errstr ($errno)<br>\n";
//    }
//    else
//    {
//        $url_ = "/account";
//        $url = "edit=1"."&";
//        $url = $url."autodisable=1"."&";
//        $url = $url."enable=1"."&";
//        $url = $url."usepassword=1"."&";
//        $url = $url."enablesocks=1"."&";
//        $url = $url."enablewww=0"."&";
//        $url = $url."enabletelnet=0"."&";
//        $url = $url."enabledial=0"."&";
//        $url = $url."enableftp=0"."&";
//        $url = $url."enableothers=0"."&";
//        $url = $url."enablemail=0"."&";
//        $url = $url."username=".$username."&";
//        $url = $url."password=".$password."&";
//        $url = $url."connection=".$connection."&";
//        $url = $url."bandwidth=".$bandwidth."&";
//        $url = $url."disabledate=".$disabledate."&";
//        $url = $url."disabletime=".$disabletime."&";
//        $url = $url."userid=".$username;
//        $len = "Content-Length: ".strlen($url);
//        $auth = "Authorization: Basic ".base64_encode("admin:".$adminpassword);
//        $msg = "POST ".$url_." HTTP/1.0\r\nHost: ".$proxyaddress."\r\n".$auth."\r\n".$len."\r\n"."\r\n".$url;
//        fputs($fp,$msg);
//        //echo $msg;
//        while(!feof($fp))
//        {
//            $s = fgets($fp,4096);
//            //echo $s;
//        }
//        fclose($fp);
//    }
//
//}
//
//accountedit('nhbYLN580','lngXXV608','-1','-1','2024-08-03','23:59:58');
//exit;
//try {
//    print_r(accountcreate('testahmet2','denemeahmet2','','','-1','-1','2024-07-23','15:00:00'));
//echo 123;
//}catch (Exception $e){
//    print_r($e);
//}
//exit;

