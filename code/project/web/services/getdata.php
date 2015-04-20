<?php
require_once "config.php";
ini_set('display_errors', 1);


if(!isset($_GET["serial"])){
    exit;
}

$piSerial = $_GET["serial"];

$db = new mysqli("localhost", $config->user, $config->password, $config->db);
$data = array();

$sql = "SELECT * FROM el_sensor WHERE `el_pi_serial` = $piSerial";
$rs = $db->query($sql);
$rs->data_seek(0);
while($row = $rs->fetch_assoc()){
    $d = new stdClass();
    $d->sensorType = $row["el_sensor_type_type_id"];
    $d->serial = $row["serial"];
    $data[] = $d;
}
$rs->close();

foreach($data as $sensor){
    $sql = "SELECT * FROM el_reading WHERE `el_sensor_serial` = '$sensor->serial'";
    $rs = $db->query($sql);
    $rs->data_seek(0);
    $sensor->data = array();
    while($row = $rs->fetch_assoc()){
        $r = new stdClass();
        $r->id = $row["id"];
        $r->time = $row["time"];
        $r->depth = $row["depth"];
        $r->values = array();

        $sql = "SELECT * FROM el_reading_data WHERE `el_reading_id` = $r->id";
        $rs2 = $db->query($sql);
        $rs2->data_seek(0);
        while($row = $rs2->fetch_assoc()){
            $r->values[] = $row["value"];
        }
        $rs2->close();
        $sensor->data[] = $r;
    }
    $rs->close();
}
$db->close();

echo json_encode($data);
/* */
