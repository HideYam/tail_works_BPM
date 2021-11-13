<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/head.php'); ?>
<?php
#初期変数（GETデータ）
$dt=$_REQUEST['date'];
if(empty($dt)){
  $dt=date('Y/m/d');
  $sdt=date('Y-m-d 00:00:00');
  $edt=date('Y-m-d 23:59:59');
}else{
  $sdt=date('Y-m-d 00:00:00',strtotime($dt));
  $edt=date('Y-m-d 23:59:59',strtotime($dt));
}

#query
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

//配達予定
$sql = "select * from t_order a
left join t_user b on a.uid=b.uid
where deliv_date >= '$sdt' and deliv_date <= '$edt'  limit 100";
$result = mysqli_query($con ,$sql);
$rc = mysqli_num_rows($result);
while($row = mysqli_fetch_array($result)){
  $rows[] = $row;
}

//引取予定
$sql = "select * from t_order a
left join t_user b on a.uid=b.uid
where pickup_date >= '$sdt' and pickup_date <= '$edt'  limit 100";
$result = mysqli_query($con ,$sql);
$rc_p = mysqli_num_rows($result);
while($row = mysqli_fetch_array($result)){
  $rows_p[] = $row;
}

//配達物件数
$sql = "select
b.code,c.name,b.status,b.model_name,b.model_type,d.name as username
,sum(amount) as amount
from t_order a
left join t_order_detail b on a.oid=b.oid
left join m_order_detail c on b.code=c.code
left join t_user d on a.uid=d.uid
where deliv_date >= '$sdt' and deliv_date <= '$edt'  and amount > 0 group by b.code,name,status,model_name,model_type,username limit 1000";
$result = mysqli_query($con ,$sql);
$rc_b = mysqli_num_rows($result);
while($row = mysqli_fetch_array($result)){
  $rows_b[] = $row;
}

//引取物件数
$sql = "select
b.code,c.name,b.status,b.model_name,b.model_type,d.name as username
,sum(amount) as amount
from t_order a
left join t_order_detail b on a.oid=b.oid
left join m_order_detail c on b.code=c.code
left join t_user d on a.uid=d.uid
where pickup_date >= '$sdt' and pickup_date <= '$edt'  and amount > 0 group by b.code,name,status,model_name,model_type,username limit 1000";
$result = mysqli_query($con ,$sql);
$rc_p2 = mysqli_num_rows($result);
while($row = mysqli_fetch_array($result)){
  $rows_p2[] = $row;
}



$con = mysqli_close($con);
if (!$con) {
  exit('データベースとの接続を閉じられませんでした。');
}
?>

<!-- ボックス -->
<form action="orderdaily.php" name="orderdaily">
  <input type="text" id="datepicker" name="date" value="">
  <input type="submit" value="オーダー 日別集計">
</form>

<?php echo $dt;?>の予定<br><br>

<b>■配達</b><br>
<?php
if ($rc > 0) {
  echo "<table class=\"table\"><thead><tr><th>個人情報</th><th>配達日時</th><th>詳細表示</th><th>変更・修正</th></tr></thead><tbody>";

  foreach($rows as $row){
  ?>
  <tr>
    <td>
      <strong><?php echo htmlspecialchars($row['name'],ENT_QUOTES,'UTF-8'); ?></strong><br>
      <?php echo $row['tel']; ?><br>
      <a href="https://maps.google.co.jp/maps?q=<?php echo $row['address']; ?>" target="_blank"><?php echo $row['address']; ?></a><br>
      <?php echo $row['email']; ?>
    </td>
    <td>
      <?php echo date('Y/m/d G:i', strtotime($row['deliv_date'])); ?>
    </td>
    <td>
      <a href="./orderdetail.php?uid=<?php echo $row['uid']; ?>&oid=<?php echo $row['oid']; ?>">詳細内容表示</a>
    </td>
    <td>
      <a href="./order.php?uid=<?php echo $row['uid']; ?>&oid=<?php echo $row['oid']; ?>">オーダー変更</a>
      <BR>
      <a href="./user.php?uid=<?php echo $row['uid']; ?>">顧客属性</a>
    </td>
  </tr>

  <?php
  }
  echo "</tbody></table>";
}
else{
  echo "<br>予定はありません。<br>";
}
?>

<?php
if ($rc_b > 0) {
  echo "<br>（納品物件）<table class=\"table\"><thead><tr><th>物件名</th><th>製品</th><th>型番</th><th>数</th><th>納品先</th></tr></thead><tbody>";
  foreach($rows_b as $row){
  ?>
  <tr>
    <td>
      <?php echo $row['name']; ?>
    </td>
    <td>
      <?php echo $row['model_name']; ?>
    </td>
    <td>
      <?php echo $row['model_type']; ?>
    </td>
    <td>
      <?php echo $row['amount']; ?>
    </td>
    <td>
      <?php echo $row['username']; ?>
    </td>
  </tr>
  <?php
  }
  echo "</tbody></table>";
}
?>

<br>
<b>■引き取り</b><br>
<br>
<?php
if ($rc_p > 0) {
  echo "<table class=\"table\"><thead><tr><th>個人情報</th><th>引取日時</th><th>詳細表示</th><th>変更・修正</th></tr></thead><tbody>";

  foreach($rows_p as $row){
  ?>
  <tr>
    <td>
      <strong><?php echo htmlspecialchars($row['name'],ENT_QUOTES,'UTF-8'); ?></strong><br>

      <?php echo $row['tel']; ?><br>
      <a href="https://maps.google.co.jp/maps?q=<?php echo $row['address']; ?>" target="_blank"><?php echo $row['address']; ?></a><br>
      <?php echo $row['email']; ?>
    </td>
    <td>
      <?php echo date('Y/m/d G:i', strtotime($row['pickup_date'])); ?>
    </td>
    <td>
      <a href="./orderdetail.php?uid=<?php echo $row['uid']; ?>&oid=<?php echo $row['oid']; ?>">詳細内容表示</a>
    </td>
    <td>
      <a href="./order.php?uid=<?php echo $row['uid']; ?>&oid=<?php echo $row['oid']; ?>">オーダー変更</a>
      <BR>
      <a href="./user.php?uid=<?php echo $row['uid']; ?>">顧客属性</a>
    </td>
  </tr>

  <?php
  }
  echo "</tbody></table>";
}
else{
  echo "予定はありません。<br>";
}
?>

<?php
if ($rc_p2 > 0) {
  echo "<br>（引き取り物件）<table class=\"table\"><thead><tr><th>物件名</th><th>製品</th><th>型番</th><th>数</th><th>引き取り先</th></tr></thead><tbody>";
  foreach($rows_p2 as $row){
  ?>
  <tr>
    <td>
      <?php echo $row['name']; ?>
    </td>
    <td>
      <?php echo $row['model_name']; ?>
    </td>
    <td>
      <?php echo $row['model_type']; ?>
    </td>
    <td>
      <?php echo $row['amount']; ?>
    </td>
    <td>
      <?php echo $row['username']; ?>
    </td>
  </tr>
  <?php
  }
  echo "</tbody></table>";
}
?>


<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
