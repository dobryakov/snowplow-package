<?php

$query = 'INSERT INTO raw (request, created_at) VALUES ("' . mysql_escape_string($r) . '", ' . time() . ')';

$result = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

