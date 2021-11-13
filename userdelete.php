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

#初期変数（GETデータ）

if(!isset($_REQUEST['uid'])){
  exit('エラーです。');
}
$uid = $_REQUEST['uid'];


/* 自動でコミットしない */
mysqli_autocommit($con, FALSE);

$result = mysqli_query($con ,"delete from t_user WHERE uid=$uid");
if (!$result) {
  exit('データを更新できませんでした。');
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
<p>削除が完了しました。</p>


<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
