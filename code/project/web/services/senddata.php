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
  // echo $sql."\n";
  // echo $db->error."\n";
}
$db = new mysqli("localhost", $config->user, $config->password, $config->db);

$dataJSON = file_get_contents("php://input");

file_put_contents("received.json", $dataJSON);

$data = json_decode($dataJSON);
$db->autocommit(false);

//echo var_dump($data);

$id = $data->id;
$sql = "SELECT * FROM `el_pi` WHERE `serial`=$id";
// echo $sql."\n";
// echo $db->error."\n";
$rs = $db->query($sql);
if($rs->num_rows == 0){
    register_pi($db, $id);
}
$rs->close();

foreach($data->sensors as $sensorData){
    $serial = $sensorData->serial;
    $sensorType = 1;
    $sql = "SELECT * FROM `el_sensor` WHERE `serial`='$serial'";
    // echo $sql."\n";
    // echo $db->error."\n";
    $rs = $db->query($sql);
    $sensorType = 1;
    if($rs->num_rows == 0){
        register_sensor($db, $serial, $sensorType, $data->id);
    }
    $rs->close();
    foreach($sensorData->data as $read){
        $depth = $read->depth;
        $time = $read->time;
        $sql = "INSERT INTO `el_reading`(`time`, `depth`, `el_sensor_serial`) VALUES('$time', $depth, '$serial')";
        // echo $sql."\n";
        // echo $db->error."\n";
        $db->query($sql);
        $id = $db->insert_id;
        foreach($read->value as $value){
          $sql = "INSERT INTO `el_reading_data`(`value`, `el_reading_id`) VALUES($value, $id)";
        //   echo $sql."\n";
        //   echo $db->error."\n";
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
