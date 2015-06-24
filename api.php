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

    $query = 'SELECT network_userid FROM data WHERE domain_userid = "' . mysql_escape_string($id) . '" ORDER BY id DESC LIMIT 1;';
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


    }

}

if ($resource == 'pagegrab' && accessControl()) {

    $url = $_REQUEST['url'];

    $html = file_get_contents($url);

    $text = trim(strip_tags(preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html)));

    $filteredText = preg_replace("/\s+/i", ' ', preg_replace("/&#?[a-z0-9]+;/i","", preg_replace("#\r|\n|\t#", ' ', $text)));

    $words = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $filteredText, -1, PREG_SPLIT_NO_EMPTY);

    $words = array_map('strtolower', $words);

    //echo $filteredText;
    //print_r($words);

    //$uniq_words = array_unique($words);

    $stats = array_count_values($words);
    arsort($stats);

    $data = array('words' => $stats);

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