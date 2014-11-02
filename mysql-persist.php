<?php

$query = 'INSERT INTO raw (request) VALUES ("' . mysql_escape_string($r) . '")';

$result = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

