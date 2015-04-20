<?php
$config = new stdClass();
$config->user = "fernandi2";
$config->password = "s123194";
$config->db="fernandi2_db";
$config->sensorConfig = array();
//CONFIG TEMP SENSOR
$config->sensorConfig[1] = new stdClass();
$config->sensorConfig[1]->readNum = 1;


function dbConnect(){
  return new mysqli("localhost", $config->user, $config->password, $config->db);
}
