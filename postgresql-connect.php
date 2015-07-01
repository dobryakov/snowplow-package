<?php

$dbconn = pg_connect("host=127.0.0.1 dbname=snowplow user=user")
    or die('Could not connect: ' . pg_last_error());

// Выполнение SQL запроса
//$query = 'SELECT * FROM urls';
//$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

