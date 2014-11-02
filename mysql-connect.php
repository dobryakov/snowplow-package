<?php

$link = mysql_connect('mysql.adaliska.com', 'snowplow', 'j8w64usj')
    or die('Не удалось соединиться: ' . mysql_error());

mysql_select_db('snowplow') or die('Не удалось выбрать базу данных');

