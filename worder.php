<?php

include(dirname(__FILE__) . '/mysql-connect.php');

$lastIdCacheFile = dirname(__FILE__) . '/data/worder-last-id.tmp';
$lastId = intval(file_get_contents($lastIdCacheFile));

$query = 'select * from data where network_userid is not null and id > ' . $lastId . ' order by id asc limit 1;';
$result = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

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

                //print_r($words);
                $collection = array();

                // берём первые 5 слов (с самым большим вхождением)
                $collection = array_slice($words, 0, 5);

                // добавляем слова от 5 символов и длиннее
                foreach($words as $word => $c) {
                    if (strlen($word) >= 5) {
                        $collection[$word] = $c;
                    }
                }

                $collection = array_unique($collection);

                //print_r($collection);

                $url_parts = parse_url($url);
                $host = $url_parts['host'];

                $canonical_url = $url;
                if (!empty($json['canonical_url'])) {
                    $canonical_url = $json['canonical_url'];
                }

                $query = "insert IGNORE into urls (url, host) values ('". mysql_escape_string($canonical_url) ."', '" . mysql_escape_string($host) . "') on duplicate key update c = c+0;";
                mysql_query($query) or die('Запрос не удался: ' . mysql_error());
                $url_id = mysql_insert_id();

                foreach($collection as $word => $c) {

                    $query = "insert into words (word) values ('". mysql_escape_string($word) ."') on duplicate key update c = c+1;";
                    mysql_query($query) or die('Запрос не удался: ' . mysql_error());
                    $word_id = mysql_insert_id();

                    $query = "insert into nuids_words (network_userid, word_id) values ('" . mysql_escape_string($network_userid) . "', " . $word_id . ") on duplicate key update c = c+1;";
                    mysql_query($query) or die('Запрос не удался: ' . mysql_error());

                    if ($url_id) {
                        $query = "insert into urls_words (url_id, word_id, c) values (" .$url_id . ", " . $word_id . ", " . intval($c) . ") on duplicate key update c = c + " . intval($c) . ";";
                        mysql_query($query) or die('Запрос не удался: ' . mysql_error());
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
