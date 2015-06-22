<?php

include(dirname(__FILE__) . '/map.php');
include(dirname(__FILE__) . '/mysql-connect.php');

$query = 'SELECT * FROM raw order by id asc limit 1000';
$result = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {

  $s = $line['request'];
  parse_str($s, $a);

  $d = array();

  foreach ($map as $k => $v) {

    if (!empty($a[$k])) {
      $d[$v] = mysql_escape_string(trim($a[$k]));
    }

  }

  if ($d) {
    $d['created_at'] = $line['created_at'];
    $query = 'INSERT INTO data (' . join(', ', array_keys($d)) . ') VALUES ("' . join('", "', array_values($d)) . '")';
    $result2 = mysql_query($query) or die('Запрос не удался: ' . mysql_error());
  }

  $query = 'DELETE FROM raw WHERE id = ' . intval($line['id']) . ' LIMIT 1';
  $result3 = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

}

include(dirname(__FILE__) . '/mysql-close.php');
