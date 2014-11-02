<?php

header("Connection: close");
ob_start();

if (empty($_COOKIE['sp'])) {
  $c = uniqid();
  setcookie('sp', $c, time()+60*60*24*30, '/');
} else {
  $c = $_COOKIE['sp'];
}

header('P3P: policyref="/w3c/p3p.xml", CP="NOI DSP COR NID PSA OUR IND COM NAV STA"');
header("Content-type: image/gif");
readfile(dirname(__FILE__) . "/i");

$size = ob_get_length();
header("Content-Length: $size");
ob_end_flush();
flush();

$r = $_SERVER['REQUEST_URI'] . '&nuid=' . $c;

file_put_contents(dirname(__FILE__) . '/sp.log', $r . "\n", FILE_APPEND);

include(dirname(__FILE__) . '/mysql-persist.php');

