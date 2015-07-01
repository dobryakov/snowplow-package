<?php

// open connection to MongoDB server
$mongoconn = new Mongo('localhost');

// access database
$mongodb = $mongoconn->snowplow;

// access collection
$mongocollection = $mongodb->urls;
