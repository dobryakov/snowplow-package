<?php

include(dirname(__FILE__) . '/mysql-connect.php');
include(dirname(__FILE__) . '/postgresql-connect.php');
include(dirname(__FILE__) . '/mongo-connect.php');

$lastIdCacheFile = dirname(__FILE__) . '/data/worder-last-id.tmp';
$lastId = intval(file_get_contents($lastIdCacheFile));

$query = 'select * from data where network_userid is not null and id > ' . $lastId . ' order by id asc limit 1;';
$result = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

$hosts_whitelistfile = dirname(__FILE__) . '/hosts-whitelist.txt';
$hosts_whitelist = file($hosts_whitelistfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {

    $dataId = intval($line['id']);
    $network_userid = $line['network_userid'];

    $urls = array();

    if (!filter_var($line['page_url'], FILTER_VALIDATE_URL) === false) {
        $urls['page_url'] = trim($line['page_url']);
    }

    if (!filter_var($line['page_referrer'], FILTER_VALIDATE_URL) === false) {
        $urls['page_referrer'] = trim($line['page_referrer']);
    }

    if (strlen($line['se_value']) > 0 && $line['se_property'] == 'url' && $line['se_action'] == 'click' && !filter_var($line['se_value'], FILTER_VALIDATE_URL) === false) {
        $urls['se_value'] = trim($line['se_value']);
    }

    if (strlen($line['unstruct_event']) > 0) {
        $event = json_decode(base64_decode($line['unstruct_event']), true);
        $targetUrl = mysql_escape_string(trim($event['data']['data']['targetUrl']));
        if (!filter_var($targetUrl, FILTER_VALIDATE_URL) === false) {
            $urls['targetUrl'] = trim($targetUrl);
        }
    }

    //print_r($urls);

    foreach($urls as $url) {
        $_data = file_get_contents('http://t.adaliska.com/s/api/pagegrab.json?url=' . urlencode($url));
        if ($_data) {
            $json = json_decode($_data, true);
            if ($json) {
                $words = $json['words'];
                $opengraph = $json['opengraph'];

                //print_r($words);
                $collection = array();

                // берём 5 слов с самым большим вхождением
                //$collection = array_slice($words, 0, 5);
                foreach($words as $word => $word_stats) {
                    if ($word_stats['p'] > 50) {
                        $collection[$word] = $word_stats;
                    }
                }

                // добавляем слова от 5 символов и длиннее
                foreach($words as $word => $word_stats) {
                    if (strlen($word) >= 5) {
                        $collection[$word] = $word_stats;
                    }
                }

                //$collection = array_unique($collection);

                //print_r($collection);

                $url_parts = parse_url($url);
                $host = $url_parts['host'];

                // @TODO: parse og tags
                $title = null;
                $description = null;
                $image_url = null;

                $canonical_url = $url;
                if (!empty($json['canonical_url'])) {
                    $canonical_url = $json['canonical_url'];
                }

                $query = "insert IGNORE into urls (url, host) values ('". mysql_escape_string($canonical_url) ."', '" . mysql_escape_string($host) . "') on duplicate key update c = c+0;";
                mysql_query($query) or die('Запрос не удался: ' . mysql_error());
                $url_id = mysql_insert_id();

                $word_ids = array();

                foreach($collection as $word => $word_stats) {

                    $c = $word_stats['c'];
                    $p = $word_stats['p'];
                    $r = $word_stats['r'];

                    $query = "insert into words (word) values ('". mysql_escape_string($word) ."') on duplicate key update c = c+1;";
                    mysql_query($query) or die('Запрос не удался: ' . mysql_error());
                    $word_id = mysql_insert_id();
                    $word_ids[] = intval($word_id);

                    $query = "insert into nuids_words (network_userid, word_id) values ('" . mysql_escape_string($network_userid) . "', " . $word_id . ") on duplicate key update c = c+1;";
                    mysql_query($query) or die('Запрос не удался: ' . mysql_error());

                    if ($url_id) {

                        // insert to mysql

                        $query = "insert into urls_words (url_id, word_id, c) values (" .$url_id . ", " . $word_id . ", " . intval($c) . ") on duplicate key update c = c + " . intval($c) . ";";
                        mysql_query($query) or die('Запрос не удался: ' . mysql_error());

                    }

                }

                if ($url_id) {

                    // insert to postgresql

                    $pgquery = "INSERT into urls (url, host, word_ids, title, description, image_url) VALUES ('" . pg_escape_string($url) . "', '" . pg_escape_string($host) . "', '{" . join(',', array_unique($word_ids)) . "}', '" . pg_escape_string($title) . "', '" . pg_escape_string($description) . "', '" . pg_escape_string($image_url) . "')";
                    //echo $pgquery . "\n";
                    $pgresult = pg_query($pgquery) or die('Ошибка запроса: ' . pg_last_error());

                    // insert to mongo

                    if (in_array($host, $hosts_whitelist)) {

                        $item = array(
                            'url' => array(
                                'id'   => $url_id,
                                'url'  => $url,
                                'host' => $host
                            ),
                            'words' => array(),
                            'words_array' => array()
                        );

                        foreach($collection as $word => $word_stats) {
                            $item['words'][] = array(
                                $word => $word_stats['p']
                            );
                            $item['words_array'][] = $word;
                        }

                        $item['opengraph'] = $opengraph;

                        //print_r($item);
                        print_r(json_encode($item));
                        $mongocollection->insert($item);

                        // search example:
                        // use snowplow
                        // db.urls.find({'words.babe':{$gt:59}, 'words.hosed':{$gt:29}})

                    }

                }

            }
        }
    }

    //$query = "insert into events (data_id, targetUrl) values ({$dataId}, '{$targetUrl}');";
    //mysql_query($query) or die('Запрос не удался: ' . mysql_error());

    file_put_contents($lastIdCacheFile, $dataId);

}

include(dirname(__FILE__) . '/mysql-close.php');
include(dirname(__FILE__) . '/postgresql-close.php');
include(dirname(__FILE__) . '/mongo-close.php');
