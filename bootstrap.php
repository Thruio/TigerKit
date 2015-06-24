<?php

require_once("vendor/autoload.php");

define("APP_ROOT", rtrim(__DIR__,"/"));

echo "getenv(\"HOST\") = " . getenv("HOST") . "\n";
echo "\n";

if(getenv("HOST") == "Travis"){
  $database = new \Thru\ActiveRecord\DatabaseLayer(array(
    'db_type'     => 'Mysql',
    'db_hostname' => 'localhost',
    'db_port'     => '3306',
    'db_username' => 'travis',
    'db_password' => 'travis',
    'db_database' => 'tigerkit'
  ));
}