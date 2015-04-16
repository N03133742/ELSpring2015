<?php
require_once "config.php";
ini_set('display_errors', 1);

function register_pi($db, $serial){
  $sql = "INSERT INTO `el_pi` VALUES($serial, 'PI_$serial')";
  $rs = $db->query($sql);
}
function register_sensor($db, $serial, $type, $pi_id){
  $sql = "INSERT INTO `el_sensor` VALUES('$serial', $type, $pi_id)";
  $rs = $db->query($sql);
}
$db = new mysqli("localhost", $config->user, $config->password, $config->db);

$dataJSON = file_get_contents("php://input");

$data = json_decode($dataJSON);

//echo var_dump($data);

$new = false;
$id = $data->id;
$sql = "SELECT * FROM `el_pi` WHERE `serial`=$id";
$rs = $db->query($sql);
if($rs->num_rows == 0){
  $new = true;
  $db->autocommit(false);
  register_pi($db, $id);
}
$rs->close();

foreach($data->sensors as $sensorData){
  $serial = $sensorData->serial;
  $sensorType = 1;
  if(!$new){
    $sql = "SELECT * FROM `el_sensor` WHERE `serial`='$serial'";
    $rs = $db->query($sql);
    $sensorType = 1;
    if($rs->num_rows == 0){
      $new = true;
      $db->autocommit(false);
      register_sensor($db, $serial, $sensorType, $id);
    }
    $rs->close();
  }else{
    register_sensor($db, $serial, $sensorType, $id);
  }
  foreach($sensorData->data as $read){
    if(!$new) $db->autocommit(false);
    $depth = $read->depth;
    $time = $read->time;
    $sql = "INSERT INTO `el_reading`(`time`, `depth`, `el_sensor_serial`) VALUES('$time', $depth, '$serial')";
    $db->query($sql);
    $id = $db->insert_id;
    foreach($read->value as $value){
      $sql = "INSERT INTO `el_reading_data`(`value`, `el_reading_id`) VALUES($value, $id)";
      $db->query($sql);
    }
  }
}

if($db->errno == 0){
  echo "{'success': true}";
  $db->commit();
}else{
  echo "{'success': false, 'message': '$db->error'}";
  $db->rollback();
}

$db->close();

?>
