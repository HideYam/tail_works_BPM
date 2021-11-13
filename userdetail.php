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


if(isset($_REQUEST['uid'])){
  $uid = $_REQUEST['uid'];
}else{
  exit('uidがありませんでした。');
}

### 検索 ###
$sql = "select *,case when update_date is null then ins_date else update_date end as sortdate from t_user_accum a where uid=$uid order by sortdate desc";

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

<!-- 表示-->

<?php
if ($rc > 0) {
?>

  <a href="./searchorder.php?search=true&uid=<?php echo $rows[0]['uid']; ?>">既存オーダー一覧</a>
  ｜
  <a href="./order.php?uid=<?php echo $rows[0]['uid']; ?>">【新規】オーダー追加</a>
  ｜
  <a href="./user.php?uid=<?php echo $rows[0]['uid']; ?>">顧客情報変更</a>｜
  <a href="javascript:void(0);" onclick="MoveCheck('userdelete.php?uid=<?php echo $rows[0]['uid']; ?>')">削除</a>

  <?php
  echo "<table class=\"table\"><thead><tr><th>No</th><th>名前</th><th>郵便番号</th><th>住所</th><th>電話</th><th>メール</th><th>所属</th><th>所属２</th><th>更新日</th></tr></thead><tbody>";

  $counter = 0;
  foreach($rows as $row){
  $counter = $counter + 1;
  ?>

  <?php if($counter == 2){echo '<tr><td colspan=8>↓更新履歴</td></tr>';} ?>
  <tr>
    <td>
      <?php echo $row['uid']; ?>
    </td>
    <td>
      <strong><?php echo htmlspecialchars($row['name'],ENT_QUOTES,'UTF-8'); ?></strong><br>
    </td>
    <td>
      <?php echo $row['zip']; ?>
    </td>
    <td>
      <?php echo $row['address']; ?>
    </td>
    <td>
      <?php echo $row['tel']; ?>
    </td>
    <td>
      <?php echo $row['email']; ?>
    </td>
    <td>
      <?php echo $row['belonging']; ?>
    </td>
    <td>
      <?php echo $row['belonging2']; ?>
    </td>
    <td>
      <?php echo date('Y/m/d G:i', strtotime($row['sortdate'])); ?>
    </td>
  </tr>
  <?php
  }

echo "</tbody></table>";
}
?>
<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
