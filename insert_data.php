<?php

$host = 'mysql';
$user = getenv('MYSQL_USER');
$pass = getenv('MYSQL_PASSWORD');
$db   = getenv('MYSQL_DATABASE');

$mysqli = new mysqli($host, $user, $pass, $db);

$temp_line = '';
$import = file('./import/import.sql');

foreach ($import as $line)
{
    if (substr($line,0,2)=='--' || $line=='') {continue;}

    $temp_line.=$line;
    if (substr(trim($line),-1,1)==';')
    {
        $mysqli->query($temp_line);
        $temp_line='';
    }
}

$redis = new Redis();
$redis->connect('redis', 6379);
$redis->flushDB();

$res = $mysqli->query("SELECT * FROM user WHERE 1 ");
while ($row=$res->fetch_object())
{
  $redis->hSet($row->id,'user_name',$row->user_name);
  $redis->hSet($row->id,'first_name',$row->first_name);
  $redis->hSet($row->id,'last_name',$row->last_name);
  $redis->hSet($row->id,'email',$row->email);
  $redis->hSet($row->id,'birthday',$row->birthday);
}

echo 'Import successful!<br>MySQL: '.$res->num_rows.' records<br>Redis: '.$redis->dbSize().' hash keys';