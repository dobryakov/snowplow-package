<?php

//print_r($_REQUEST);

$resource = $_REQUEST['resource'];
$id       = $_REQUEST['id'];
$format   = $_REQUEST['format'];
$data     = array();

include(dirname(__FILE__) . '/mysql-connect.php');

if ($resource == 'user') {

    $data = array('visited' => array('pages' => array(), 'hosts' => array()));

    // find user by domain_userid (duid)

    $query = 'SELECT network_userid FROM data WHERE domain_userid = "' . mysql_escape_string($id) . '" ORDER BY id DESC LIMIT 1000;';
    $result = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

    while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {

        $network_userid = $line['network_userid'];

        // find visited pages

        $query = 'SELECT DISTINCT(page_url) FROM data WHERE network_userid = "' . mysql_escape_string($network_userid) . '";';
        $result2 = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

        while ($line2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {

            $url = $line2['page_url'];
            $data['visited']['pages'][] = $url;

            $p = parse_url($url);
            $host = $p['host'];
            if (!in_array($host, $data['visited']['hosts'])) {
                $data['visited']['hosts'][] = $host;
            }

        }

        // find words

        $data['words'] = array();

        $query = 'select words.word, nuids_words.c from nuids_words inner join words on words.id = nuids_words.word_id where network_userid = "' . mysql_escape_string($network_userid) .'" order by nuids_words.c desc';
        $result3 = mysql_query($query) or die('Запрос не удался: ' . mysql_error());

        while ($line3 = mysql_fetch_array($result3, MYSQL_ASSOC)) {

            $word = $line3['word'];
            $c    = $line3['c'];
            $data['words'][] = array('word' => $word, 'c' => $c);

        }

    }

}

if ($resource == 'pagegrab' && accessControl() && !filter_var($_REQUEST['url'], FILTER_VALIDATE_URL) === false) {

    $url = $_REQUEST['url'];

    $html = get_url($url);

    $canonical_url = $url;

    preg_match_all("/link(\\s+)rel=(\"|')canonical(\"|')(\\s+)href=(\"|')(.+)(\"|')/i", $html, $_matches);
    if (!empty($_matches[6][0]) && !filter_var($_matches[6][0], FILTER_VALIDATE_URL) === false) {
        $canonical_url = $_matches[6][0];
    }

    $text = trim(strip_tags(preg_replace('#<script(.*?)>(.*?)</script>#is', '', preg_replace('#<style(.*?)>(.*?)</style>#is', '', $html))));

    $filteredText = preg_replace("/\s+/i", ' ', preg_replace("/&#?[a-z0-9]+;/i","", preg_replace("#\r|\n|\t#", ' ', $text)));

    $words = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $filteredText, -1, PREG_SPLIT_NO_EMPTY);

    $words = array_map('strtolower', $words);

    //echo $filteredText;
    //print_r($words);

    //$uniq_words = array_unique($words);

    $stats = array_count_values($words);
    arsort($stats);

    $data = array('words' => $stats, 'canonical_url' => $canonical_url);

}

include(dirname(__FILE__) . '/mysql-close.php');

if ($format == 'json') {
    header("Content-type: application/json");
    echo json_encode($data);
}

function accessControl() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $allowed_ips = array('127.0.0.1', '91.151.204.101', '162.210.198.46');
    return in_array($ip, $allowed_ips);
}

function get_url($url) {

    $cacheDir = dirname(__FILE__) . '/cache';
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0755);

    $cacheFile = $cacheDir . '/' . md5($url);
    if (filemtime($cacheFile) < time() - 60*60*24*7) unlink($cacheFile);

    if (file_exists($cacheFile)) {
        //file_put_contents(dirname(__FILE__) . '/log/get_url.log', "Cache HIT: {$cacheFile} {$url}" . "\n", FILE_APPEND);
        return file_get_contents($cacheFile);
    }

    file_put_contents(dirname(__FILE__) . '/log/get_url.log', "Cache MISS: {$url}" . "\n", FILE_APPEND);

    $content = file_get_contents($url);
    file_put_contents($cacheFile, $content);

    //file_put_contents(dirname(__FILE__) . '/log/get_url.log', "Cached: {$cacheFile} {$url}" . "\n", FILE_APPEND);

    return $content;

}