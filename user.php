<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/head.php'); ?>

<?php
//ini_set("display_errors", On);
//error_reporting(E_ALL);

//ポスト先指定
$posturl = 'user2.php';
?>

<?php
#他からの遷移＝新規ではなく修正の場合
if(isset($_REQUEST['uid']))
{

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

  $uid = $_REQUEST['uid'];
  $sql = "select * from t_user where uid =$uid+0 limit 1";

  $result = mysqli_query($con ,$sql);
  if (mysqli_num_rows($result) <= 0) {
    exit('顧客が存在しません');
  }

  while($row = mysqli_fetch_array($result)){
  	$rows[] = $row;
    //$row_cnt = mysqli_num_rows($result);
  }

  $con = mysqli_close($con);
  if (!$con) {
    exit('データベースとの接続を閉じられませんでした。');
  }

}
else {
  $rows[0]['uid'] = '';
  $rows[0]['name'] = '';
  $rows[0]['email'] = '';
  $rows[0]['zip'] = '';
  $rows[0]['tel'] = '';
  $rows[0]['address'] = '';
  $rows[0]['belonging'] = '';
  $rows[0]['belonging2'] = '';
}
?>

<form action="<?php echo $posturl; ?>" method="post">
<?php
//print($row_cnt);
//print_r($rows);
foreach($rows as $row){
?>
  <?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/userform.php'); ?>
<?php
}
?>

  <input type="submit" value="顧客を登録・更新する" />

</form>


<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
