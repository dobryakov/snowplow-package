<?php

if (empty($_COOKIE['sp'])) {
  setcookie('sp', uniqid(), time()+60*60*24*30, '/');
}

$r = $_SERVER['REQUEST_URI'] . '&nuid=' . $_COOKIE['sp'];

file_put_contents(dirname(__FILE__) . '/sp.log', $r . "\n", FILE_APPEND);

header('P3P: policyref="/w3c/p3p.xml", CP="NOI DSP COR NID PSA OUR IND COM NAV STA"');
header("Content-type: image/gif");
readfile(dirname(__FILE__) . "/i");
