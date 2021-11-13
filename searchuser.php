<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/head.php'); ?>

<?php
//ini_set("display_errors", On);
//error_reporting(E_ALL);

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

### 検索 ###
$sql = "select *,case when a.update_date is null then a.ins_date else a.update_date end as sortdate from t_user a where name like '%$name%' and tel like '%$tel%' and email like '%$email%' order by a.ins_date desc limit 100";

### データ取得 ###
//$result = $mysqli -> query($sql);
$result = mysqli_query($con ,$sql);
if (!$result) {
  exit('取得できませんでした');
}
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





<!-- 表示-->
<?php if ($_REQUEST['search']){?>
<h4>検索結果</h4>
<?php if ($rc > 0) {?>
<p>件数：<?php echo $rc; ?></p>
<?php }else{ echo '見つかりませんでした。';} }?>

<?php
if ($rc > 0) {
  echo "<table class=\"table\"><thead><tr><th>No</th><th>個人情報</th><th>更新日</th><th>変更・修正</th></tr></thead><tbody>";
  foreach($rows as $row){
  ?>
  <tr>
    <td>
      <?php echo $row['uid']; ?>
    </td>
    <td>
      <strong><?php echo htmlspecialchars($row['name'],ENT_QUOTES,'UTF-8'); ?></strong><br>

      <?php echo $row['tel']; ?><br>
      <?php echo $row['belonging']; ?><?php echo $row['belonging2']; ?><br>
      <?php echo $row['email']; ?>

    </td>
    <td>
      <?php echo date('Y/m/d G:i', strtotime($row['sortdate'])); ?>
    </td>
    <td>
      <a href="./searchorder.php?search=true&name=<?php echo $row['name']; ?>&email=<?php echo $row['email']; ?>&tel=<?php echo $row['tel']; ?>">【既存】オーダー一覧</a>
      <BR>
      <a href="./order.php?uid=<?php echo $row['uid']; ?>">【新規】オーダー追加</a>
      <BR>
      <a href="./user.php?uid=<?php echo $row['uid']; ?>">顧客情報変更</a>｜
      <a href="javascript:void(0);" onclick="MoveCheck('userdelete.php?uid=<?php echo $row['uid']; ?>')">削除</a>
    </td>
  <?php
  }

echo "</tbody></table>";
}
?>

<!-- ボックス -->
<form id="searchorder">
  <input type="hidden" name="search" value="true">
  <label>
    <input type="name" name="name" value="<?php echo $name; ?>" placeholder="名前">
  </label>
  <label>
    <input type="email" name="email" autocomplete="email" value="<?php echo $email; ?>" placeholder="メールアドレス">
  </label>
  <label>
    <input type="tel" name="tel" value="<?php echo $tel; ?>" placeholder="電話番号">
  </label>
  <!-- button class="submit" data-action="searchorder.php">オーダーを検索</button-->
  <button class="submit" data-action="searchuser.php">顧客リストを検索</button>
</form>

<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
