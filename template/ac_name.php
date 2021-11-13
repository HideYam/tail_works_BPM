<?php

$con = mysqli_connect('localhost', 'works', 'chiba2018');
if (!$con) {
  exit('データベースに接続できませんでした。');
}else{
  mysqli_set_charset($con, 'utf8');
}

$result = mysqli_select_db($con,'dw');
if (!$result) {
  exit('データベースを選択できませんでした。');
}

$term = strip_tags(substr($_REQUEST['term'],0, 100));
$term = mysqli_real_escape_string($con,$term);

$sql = "SELECT name FROM t_user where name like '%$term%' order by name asc";
$result = mysqli_query($con,$sql);

if(mysqli_num_rows($result) > 0){
  while($row = mysqli_fetch_assoc($result)){
    $string[] = $row['name'];
  }
}else{exit;}

$words = array();
foreach($string as $word){
  if(mb_stripos($word, $term) !== FALSE){
    $words[] = $word;
  }
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($words);
