<?php

include(dirname(__FILE__) . '/mysql-connect.php');

$query = 'select * from data where unstruct_event is not null and id > (select max(data_id) from events) limit 100;';
$result = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {

  $data_id = intval($line['id']);
  $event = json_decode(base64_decode($line['unstruct_event']), true);

  $targetUrl = mysql_escape_string(trim($event['data']['data']['targetUrl']));

  $query = "insert into events (data_id, targetUrl) values ({$data_id}, '{$targetUrl}');";
  mysql_query($query) or die('Запрос не удался: ' . mysql_error());

}

include(dirname(__FILE__) . '/mysql-close.php');
