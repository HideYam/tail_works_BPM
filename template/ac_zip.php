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

$sql = "SELECT zip as code ,concat(ken_name,city_name,town_name) as name , concat(zip,':',ken_name,city_name,town_name) as value FROM ad_address where concat(ken_name,city_name,town_name) like '%$term%' limit 100";
$result = mysqli_query($con,$sql);

$string = array();
if(mysqli_num_rows($result) > 0){
  while($row = mysqli_fetch_assoc($result)){
    $string[] = array(
    'code'=>$row['code'],
    'name'=>$row['name'],
    'value'=>$row['value']
    );
  }
}else{exit;}


header("Content-Type: application/json; charset=utf-8");
echo json_encode($string);
