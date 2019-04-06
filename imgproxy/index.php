<?php


if( isset($_GET['url']) && strpos($_GET['url'],'autoopt.ru')!==false ){
    $url=$_GET['url'];
    $fp = fopen($url, 'r');
    header("Content-Type: image/jpg");
    //header("Content-Length: " . filesize($name));
    fpassthru($fp);
}