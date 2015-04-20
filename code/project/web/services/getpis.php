<?php
require_once "config.php";
ini_set('display_errors', 1);

$db = new mysqli("localhost", $config->user, $config->password, $config->db);

$sql = "SELECT * FROM el_pi";
$rs = $db->query($sql);
$rs->data_seek(0);
$data = array();
while($row = $rs->fetch_assoc()){
    $d = new stdClass();
    $d->serial = $row["serial"];
    $d->alias = $row["alias"];
    $data[] = $d;
}

echo json_encode($data);
