<?php

if (empty($_COOKIE['sp'])) {
  setcookie('sp', uniqid(), time()+60*60*24*30, '/');
}

//header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
//header('P3P: CP="CAO PSA OUR"');
header('P3P: policyref="/w3c/p3p.xml", CP="NOI DSP COR NID PSA OUR IND COM NAV STA"');
header("Content-type: image/gif");
readfile(dirname(__FILE__) . "/i");
