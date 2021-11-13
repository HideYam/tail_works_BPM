<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/head.php'); ?>

<?php
ini_set("display_errors", On);
error_reporting(E_ALL);

//$mysqli = new mysqli($server, $userName, $password,$dbName);
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

### 検索値のセット ###
if ($_REQUEST['name'] == "") {
  $name = "";
}else{
  $name = $_REQUEST['name'];
}

if ($_REQUEST['email'] == "") {
  $email = "";
}else{
  $email = $_REQUEST['email'];
}

if ($_REQUEST['tel'] == "") {
  $tel = "";
}else{
  $tel = $_REQUEST['tel'];
}

### 検索結果か初期表示か ###
if ($_REQUEST['search']) {
  //検索Flagがあれば検索する
  //検索して表示
  $sql = "select case when a.update_date is null then a.ins_date else a.update_date end as sortdate,a.*,b.name,b.email,b.tel,b.address,b.zip from t_order a
   left join t_user b on a.uid=b.uid where email like '%$email%' and name like '%$name%' and tel like '%$tel%' order by sortdate desc limit 10000";
} else {
  //検索でなければ直近表示
  //直近の更新データ
  $sql = "select case when a.update_date is null then a.ins_date else a.update_date end as sortdate,a.*,b.name,b.email,b.tel,b.address,b.zip from t_order a
   left join t_user b on a.uid=b.uid order by sortdate desc limit 10";
}

### データ取得 ###
//$result = $mysqli -> query($sql);
$result = mysqli_query($con ,$sql);
$rc = mysqli_num_rows($result);

//while($row = $result->fetch_array(MYSQLI_ASSOC)){
while($row = mysqli_fetch_array($result)){
	$rows[] = $row;
}

$con = mysqli_close($con);
if (!$con) {
  exit('データベースとの接続を閉じられませんでした。');
}
?>

<!-- 画面 -->


<!-- ボックス -->
<form action="search.php">
  <input type="hidden" name="search" value="true">
  <label>名前：
    <input type="name" name="name" value="<?php echo $name; ?>">
  </label>
  <label>メールアドレス：
    <input type="email" name="email" autocomplete="email" value="<?php echo $email; ?>">
  </label>
  <label>電話番号（ハイフンなし）：
    <input type="tel" name="tel" value="<?php echo $tel; ?>">
  </label>
  <input type="submit" value="検索" />
</form>

<!-- 表示-->
<?php if ($_REQUEST['search']) { //検索結果表示 ?>
<h4>検索結果</h4>
<p>件数：<?php echo $rc; ?></p>
<?php }else{ //初期表示?>
<h4>直近の更新オーダー10件を表示</h4>
<?php } ?>

<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/orderlist.php'); ?>

<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
