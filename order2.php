<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/head.php'); ?>

<?php
//ini_set("display_errors", On);
//error_reporting(E_ALL);

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

/* 自動でコミットしない */
mysqli_autocommit($con, FALSE);

######　postデータの整理 ######
$uid   = $_REQUEST['uid'];
$email   = $_REQUEST['email'];
$name = $_REQUEST['name'];
$tel  = $_REQUEST['tel'];
$zip = $_REQUEST['zip11'];
$address = $_REQUEST['addr11'];
$belonging = $_REQUEST['belonging'];
$belonging2 = $_REQUEST['belonging2'];

$etc  = $_REQUEST['etc'];
$payflag  = $_REQUEST['payflag'];


$delivdate  = $_REQUEST['delivdate'];
$delivtime  = $_REQUEST['delivtime'];
if(empty($delivdate)){
  $delivdate = '0000-00-00';
}
if(empty($delivtime)){
  $delivtime = '00:00:00';
}
$delivdt = $delivdate.' '.$delivtime;


$pickupdate  = $_REQUEST['pickupdate'];
$pickuptime  = $_REQUEST['pickuptime'];
if(empty($pickupdate)){
  $pickupdate = '0000-00-00';
}
if(empty($pickuptime)){
  $pickuptime = '00:00:00';
}
$pickupdt = $pickupdate.' '.$pickuptime;

$contractdt  = $_REQUEST['contractdate'];
if(empty($contractdt)){
  $contractdt = '0000-00-00';
}
$contractst  = $_REQUEST['contractstart'];
if(empty($contractst)){
  $contractst = '0000-00-00';
}
$contractet  = $_REQUEST['contractend'];
if(empty($contractet)){
  $contractet = '0000-00-00';
}
$deliv_staff = $_REQUEST['deliv_staff'];


######　t_order へ　データを挿入 ######
//時刻でHash
$now = date("Y/m/d H:i:s");
$hash = hash("sha256", $now);

//oid無し＝新規
if(empty($_REQUEST['oid']))
{
  //insert
  //照合用のHash符号を入れる

  $inssql = "INSERT INTO t_order(uid, etc ,deliv_date ,pickup_date, hash ,contract_date,contract_start,contract_end,deliv_staff,payflag) VALUES('$uid', '$etc', '$delivdt','$pickupdt','$hash','$contractdt','$contractst','$contractet','$deliv_staff','$payflag')";
  $result = mysqli_query($con ,$inssql);


  if (!$result) {
    exit('データを登録できませんでした。(INSERT)');
  }


  //insert後にoid確認　Hash符号で
  $result = mysqli_query($con ,"select oid from t_order where hash='$hash'");
  while($row = mysqli_fetch_array($result)){
    $rows[] = $row;
  }
  $oid = $rows[0]['oid'];

}
//oid有り＝更新
elseif(!empty($_REQUEST['oid']))
{
  //update
  $oid = $_REQUEST['oid'];
  $updatesql = "UPDATE t_order set etc = '$etc' ,deliv_date = '$delivdt',pickup_date = '$pickupdt',update_date = CURRENT_TIMESTAMP , hash = '$hash' , contract_date = '$contractdt', contract_start = '$contractst', contract_end = '$contractet' ,deliv_staff = '$deliv_staff',payflag = '$payflag' where uid =$uid+0 and oid=$oid+0";
  $result = mysqli_query($con ,$updatesql);

  if (!$result) {
    exit('データを更新できませんでした。(update)');
  }


  //t_order_detailはdelete（その後にinsert）
  $deletesql = "DELETE from t_order_detail where oid=$oid+0";
  $result = mysqli_query($con ,$deletesql);

  if (!$result) {
    exit('データを削除できませんでした。');
  }

}

//t_order_accumに過去データ山積み 今入れた行を取って入れる
$insaccusql = "INSERT INTO t_order_accum select * from t_order where uid =$uid+0 and oid=$oid+0";
$result = mysqli_query($con ,$insaccusql);

#########　order_detail処理　#########
$order = $_POST['order'];
//print_r($order);

foreach($order as $key => $value){
  $code = $key;
  //print($key.'|');

  //POST配列から取得　まず初期化しないと前のが残るかも
  $check = '';
  $amount = '';
  $status = '';
  $model_name = '';
  $model_type = '';
  $model_year = '';
  $model_serialno = '';
  $order_price = '';
  foreach ( $value as $key2=>$value2)
  {
      if($key2 == 'check'){$check = $value2;}
      if($key2 == 'amount'){$amount = $value2;}
      if($key2 == 'status'){$status = $value2;}
      if($key2 == 'model_name'){$model_name = $value2;}
      if($key2 == 'model_type'){$model_type = $value2;}
      if($key2 == 'model_year'){$model_year = $value2;}
      if($key2 == 'model_serialno'){$model_serialno = $value2;}
      if($key2 == 'order_price'){$order_price = $value2;}
      //if($key2 == 'name'){$name = $value2;}
      //print($key2.'_'.$value2.'|'.'<br>') ;
  }

  //チェックされてるけど数量が０なら、数量を１にする
  if(!empty($check) && $amount < 1){$amount = 1+0;}
  //チェック外れてるけど数量が１以上なら、数量を０にする
  if(empty($check) && $amount > 0){$amount = 0+0;}

  $inssql = "INSERT INTO t_order_detail(oid ,hash, code ,amount,status,model_name,model_type,model_year,model_serialno,order_price) VALUES('$oid','$hash', '$code', '$amount','$status','$model_name','$model_type','$model_year','$model_serialno','$order_price')";
  $result = mysqli_query($con ,$inssql);
  /*
  if (!$result) {
    exit('データを登録できませんでした。(INSERT detail)');
  }
  */

}

//t_order_detail_accumに過去データ山積み 今入れた行を取って入れる
$insaccusql = "INSERT INTO t_order_detail_accum select * from t_order_detail where oid=$oid+0";
$result = mysqli_query($con ,$insaccusql);
if (!$result) {
  exit('データを履歴保存できませんでした');
}

#########　commit　#########
/* トランザクションをコミットします */
$result = mysqli_commit($con);
if (!$result) {
  exit('データベースに書き込めませんでした。');
}

$con = mysqli_close($con);
if (!$con) {
  exit('データベースとの接続を閉じられませんでした。');
}

?>


登録しました。
<!--
<?php echo $contractdt;?>
-->

<br>
<a href='orderdetail.php?uid=<?php echo $uid; ?>&oid=<?php echo $oid; ?>'>確認する</a>


<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
