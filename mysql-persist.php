<?php

$link = mysql_connect('mysql.adaliska.com', 'snowplow', 'j8w64usj')
    or die('Не удалось соединиться: ' . mysql_error());

mysql_select_db('snowplow') or die('Не удалось выбрать базу данных');

$query = 'INSERT INTO raw (request) VALUES ("' . mysql_escape_string($r) . '")';

$result = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

mysql_free_result($result);

mysql_close($link);

