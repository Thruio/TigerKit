<?php

require_once("bootstrap.php");

if(!file_exists("build/logs")){
  @mkdir("build");
  @mkdir("build/logs");
}
