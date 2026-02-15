<?php
require_once 'config.php';
require_once 'includes/Backend.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
$d=['session_id'=>$_POST['session_id']??'','activity_type'=>$_POST['activity_type']??'','device_category'=>$_POST['device_category']??'','screen_width'=>(int)($_POST['screen_width']??0),'screen_height'=>(int)($_POST['screen_height']??0),'duration'=>(int)($_POST['duration']??0)];
Analytics::record($d);
}
