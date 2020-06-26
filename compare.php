<?php

$num_rows=10000;
$host = 'mysql';
$user = getenv('MYSQL_USER');
$pass = getenv('MYSQL_PASSWORD');
$db   = getenv('MYSQL_DATABASE');

$mysqli = new mysqli($host, $user, $pass, $db);

$redis = new Redis();
$redis->connect('redis', 6379);

$res = $mysqli->query("SELECT id FROM user WHERE 1 ORDER BY rand() LIMIT ".$num_rows);
while ($row=$res->fetch_object())
{
  $array[]=$row->id;
}
echo '<br /><br />Start! '.$res->num_rows.' IDs have been picked by random<br /><br />';

$start=microtime(true);
foreach ($array as $id) {
    $output=$id.": ".$redis->hget($id,"birthday").'<br />';
//  echo output;
}
$duration_redis=microtime(true)-$start;
echo 'Redis: '.$duration_redis.' Seconds<br />';



$start=microtime(true);
foreach ($array AS $id) {
    $res = $mysqli->query("SELECT birthday FROM user WHERE id='".$id."' LIMIT 1");
    while ($row=$res->fetch_object())
    {
        $output=$id.": ".$row->brithday.'<br />';
        //  echo output;
    }
}
$duration_mysql=microtime(true)-$start;
echo 'MySQL: '.$duration_mysql.' Seconds<br />';

$result=((100/$duration_mysql*$duration_redis)-100);

if ($result<0) {echo 'Redis has been '.number_format(($result*-1),0).'% faster than MySQL';}
elseif ($result>0) {echo 'MySQL has been '.number_format($result,0).'% faster than Redis';}
else {echo 'No difference between Redis and MySQL';}
