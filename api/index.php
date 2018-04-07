<?php

  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");

  require('FireWorks.php');
  require('UUID.php');

  $RESULT         = new \stdClass;
  $_key           = key($_GET);
  $CMD            = strtoupper($_key);
  $CMD_VALUE      = $FW->_inj($_GET[$_key]);
  $DISPLAY_IDLE   = false;
  $IP = isset($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];

  //$iotf_api_key    = $FW->_inj($_GET['key'],null);
  //$iotf_api_secret = $FW->_inj($_GET['secret'],null);

  // Get TIMESTAMP
  if ( $CMD == "TIMESTAMP") {
    $RESULT = time();

  // Signe IN
  }else if ( $CMD == "SIGNIN" || $CMD == "LOGIN" || $CMD == "UUID" ){
    if (UUID::is_valid($CMD_VALUE)){
      $device = $FW->fetch("SELECT * FROM devices WHERE `uuid`='$CMD_VALUE'")['data'];
      //print_r($device);
      if ($device){
        $_SESSION['UUID'] = $device->uuid;
        $_SESSION['ID']   = $device->id;
        $RESULT->SIGNIN   = "OK";
        write_event($CMD_VALUE,'SIGNIN');
      }else{
        $_SESSION['UUID'] = null;
        $_SESSION['ID']   = null;
        $RESULT->ERROR    = "NOT REGISTRED";
      }
    }else{
      $_SESSION['UUID']   = null;
      $RESULT->ERROR      = "UUID";
      $RESULT->UUID       = $CMD_VALUE;
    }
  
  // Register new device or renew ID
  }else if ( $CMD == "SIGNUP" || $CMD == "REGISTER"  || $CMD == "REG" ){
    $RESULT->UUID = UUID::v4();
    $insert = $FW->fetch("INSERT INTO devices (`uuid`,`ip`) VALUE ('$RESULT->UUID','$IP')");
    $FW->fetch("INSERT INTO events (`id`,`ip`,`cmd`,`msg`) VALUE ('$insert[lastInsertId]','$IP','REG','$IP|$CMD_VALUE')");
    $_SESSION['UUID'] = $RESULT->UUID;
    $_SESSION['ID']   = $insert['lastInsertId'];
  
  // if SESSION is OPEN then RUN
  }else if ( isset($_SESSION['ID']) && $_SESSION['ID'] ){
    
    // Display IDLE OK
    if ( $CMD == "IDLE" ){
      $DISPLAY_IDLE = true;

    // Close SESSION
    }else if ( $CMD == "SIGNOUT" || $CMD == "LOGOUT" ){
      write_event('SIGNOUT');
      session_destroy();
      $RESULT->SIGNOUT = "OK";

    // PUBLISH Msessage
    }else if ( $CMD == "PUB" || $CMD == "PUBLISH" ){
      write_event($CMD_VALUE,'PUB');
      $RESULT->PUB = "OK";

    // List published message
    }else if ( $CMD == "LS" || $CMD == "LIST"){
      $list = $FW->fetch("SELECT ts AS TIMESTAMP, ip AS IP, msg AS MESSAGE FROM events WHERE `id`='$_SESSION[ID]' AND `cmd`='PUB'",true)['data'];
      write_event('LIST','CMD');
      $RESULT->LIST = $list;

    // List command
    }else if ( $CMD == "LSCMD" || $CMD == "LISTCMD"){
      $list = $FW->fetch("SELECT ts AS TIMESTAMP, ip AS IP, msg AS MESSAGE FROM events WHERE `id`='$_SESSION[ID]' AND `cmd`='CMD'",true)['data'];
      write_event('LIST_CMD','CMD');
      $RESULT->LIST_CMD = $list;

    // List command
    }else if ( $CMD == "LOG"){
      $list = $FW->fetch("SELECT ts AS TIMESTAMP, ip AS IP, msg AS MESSAGE FROM events WHERE `id`='$_SESSION[ID]'",true)['data'];
      write_event('LOG','CMD');
      $RESULT->LIST_CMD = $list;

    // no CMD specified idle
    }else if ( $CMD == ""){

    // CMD Not Reconised
    }else{
      $RESULT->ERROR = "NOT RECONISED";
    }
  
  // ERROR Not signed todo any think
  }else{
    $RESULT->ERROR = "NOT SIGNED";

  }




  /*
  COMMAND LIST:
  =============
    TIMESTAMP                   get cutent time linux
    SIGNUP | REGISTER | REG     get new UUID 
    SIGNIN | LOGIN | UUID       log in 
    SIGNOUT | LOGOUT            log out
    IDLE                        idle session
    PUB | PUBLISH               Pulish MSG
    LS | LIST                   List all PUB event
    LSCMD | LISTCMD             List all CMD event
    LOG                         List all event
    A2F
    AUTH
  */
    
  idle();
  echo json_encode($RESULT, JSON_PRETTY_PRINT);
  session_commit();
  die();
/////////////////////////////////////////////////////////////////////////////////////////////////////////

function write_event($message = null, $type = null){
  global $FW, $_SESSION, $IP;
  $type = $type ? "'".$type."'" : null ;
  if ( isset($_SESSION['ID']) && $_SESSION['ID'] && $message ){
    $FW->fetch("INSERT INTO events (`id`,`ip`,`cmd`,`msg`) VALUE ('$_SESSION[ID]', '$IP', $type, '$message')");
  }
}
function idle(){
  global $FW, $RESULT, $_SESSION, $IP, $DISPLAY_IDLE;
  if (isset($_SESSION['ID']) && $_SESSION['ID']){
    $FW->fetch("UPDATE devices SET `ip`='$IP' WHERE `id`='$_SESSION[ID]'");
    if ($DISPLAY_IDLE){
      $RESULT->IDLE = "OK";
      $RESULT->UUID = $_SESSION['UUID'];
    }
  }else{
    if ($DISPLAY_IDLE){
      $RESULT->IDLE = "FAILED";
    }    
  }
}
?>