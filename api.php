<?php

//print_r($_REQUEST);

$resource = $_REQUEST['resource'];
$id       = $_REQUEST['id'];
$format   = $_REQUEST['format'];

$data     = array('visited' => array('pages' => array(), 'hosts' => array() ));

include(dirname(__FILE__) . '/mysql-connect.php');

if ($resource == 'user') {

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

include(dirname(__FILE__) . '/mysql-close.php');

if ($format == 'json') {
  header("Content-type: application/json");
  echo json_encode($data);
}

