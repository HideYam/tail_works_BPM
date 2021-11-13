<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/head.php'); ?>

<?php
//ini_set("display_errors", On);
//error_reporting(E_ALL);

###  初期変数（このファイル動作の前提） ###
$uid=$_REQUEST['uid'];
$oid=$_REQUEST['oid'];
if(!isset($uid) || !isset($oid)){
  exit('エラーです。');
}
//ほか、$_REQUEST['hash']有無で分岐（後述）

###  DB接続  ###
// 接続共通 //
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

### データ取得 ###
///  SQL :orderとuserを取得する///
if(isset($_REQUEST['hash'])){
  //過去履歴を表示する場合（URLにハッシュあり）　t_order_accumをクエリーする
  $hash = $_REQUEST['hash'];
  $sql = "select case when a.update_date is null then a.ins_date else a.update_date end as sortdate,a.*,b.name,b.email,b.tel,b.address,b.zip,b.belonging,b.belonging2,c.s_memo,c.s_code,c.s_name from t_order_accum a left join t_user b on a.uid=b.uid left join m_staff c on a.deliv_staff=c.s_code where a.uid=$uid+0 and a.oid=$oid+0 and a.hash='$hash'";
}else{
  //通常　t_orderをクエリーする
  $sql = "select case when a.update_date is null then a.ins_date else a.update_date end as sortdate,a.*,b.name,b.email,b.tel,b.address,b.zip,b.belonging,b.belonging2,c.s_memo,c.s_code,c.s_name from t_order a left join t_user b on a.uid=b.uid left join m_staff c on a.deliv_staff=c.s_code where a.uid=$uid+0 and a.oid=$oid+0";
}
$result = mysqli_query($con ,$sql);
while($row = mysqli_fetch_array($result)){
  $rows_order[] = $row;
}

///  SQL :order_detailを取得する ///
if(isset($_REQUEST['hash'])){
  //過去履歴を表示する場合（URLにハッシュあり）　t_order_detail_accumをクエリーする
  $sql = "select * from t_order_detail_accum c left join m_order_detail d on d.code=c.code where c.oid=$oid+0 and c.amount > 0 and c.hash= '$hash' order by c.code;";
}else{
  //通常　t_order_detailをクエリーする
  $sql = "select * from t_order_detail c left join m_order_detail d on d.code=c.code where c.oid=$oid+0 and c.amount > 0 order by c.code;";
}
$result = mysqli_query($con ,$sql);
while($row = mysqli_fetch_array($result)){
  $rows_orderdetail[] = $row;
}
$roaccum = mysqli_num_rows($result);


///  SQL :order_accumから過去履歴を引く
$sql = "select case when a.update_date is null then a.ins_date else a.update_date end as sortdate,a.*,b.name,b.email,b.tel,b.address,b.zip,b.belonging,b.belonging2,c.s_memo,c.s_code,c.s_name from t_order_accum a left join t_user b on a.uid=b.uid left join m_staff c on a.deliv_staff=c.s_code where a.uid=$uid+0 and a.oid=$oid+0 order by sortdate desc";
$result = mysqli_query($con ,$sql);
while($row = mysqli_fetch_array($result)){
	$rows_orderaccum[] = $row;
}
$rcaccum = mysqli_num_rows($result);

// DB接続解除共通 //
$con = mysqli_close($con);
if (!$con) {
  exit('データベースとの接続を閉じられませんでした。');
}
?>

<!-- 表示-->
<table class="table">
<thead><tr><th colspan=4>オーダー情報 （NO：<?php echo $uid; ?>-<?php echo $oid; ?>）</th></tr></thead><tbody>
<?php
foreach($rows_order as $row){
?>
<tr>
  <td colspan=3>
    <strong><?php echo htmlspecialchars($row['name'],ENT_QUOTES,'UTF-8'); ?></strong> ｜<a href="./user.php?uid=<?php echo $uid; ?>">顧客情報修正</a>
    <br>
    <?php echo $row['tel']; ?>
    <br>
    <?php echo $row['belonging']; ?><?php echo $row['belonging2']; ?>
    <br>
    <?php echo $row['email']; ?>

  </td>
  <td>
    <a href="./keiyaku.php?uid=<?php echo $uid; ?>&oid=<?php echo $oid; ?><?php if(isset($_REQUEST['hash'])){echo '&hash='.$hash;}?>">契約書表示</a>|<a href="./keiyaku2.php?uid=<?php echo $uid; ?>&oid=<?php echo $oid; ?><?php if(isset($_REQUEST['hash'])){echo '&hash='.$hash;}?>">契約書表示（更新契約用）</a>|<a href="./orderdetail_view.php?uid=<?php echo $uid; ?>&oid=<?php echo $oid; ?>">【印刷用】</a>

    <br>


    <?php
    if(!isset($_REQUEST['hash'])){
    //過去履歴の時は表示しない
    ?>
    <a href="./order.php?uid=<?php echo $uid; ?>&oid=<?php echo $oid; ?>">内容修正</a>
    <br>

    <a href="javascript:void(0);" onclick="MoveCheck('orderdelete.php?uid=<?php echo $uid; ?>&oid=<?php echo $oid; ?>')">削除</a>
    <br>
    <a href="./order.php?uid=<?php echo $uid; ?>&oid=<?php echo $oid; ?>&refresh_contract=true"><B>(契約満了後)契約更新する</B></a>
    <?php
    }
    ?>

  </td>
</tr>
<tr>
  <td colspan=2>
    代金回収：<?php if($row['payflag'] == 1){echo '未回収';}elseif($row['payflag'] == 2){echo '<b>代済</B>';}?>
    <br>
    契約日：<?php if($row['contract_date'] <> '0000-00-00' && isset($row['contract_date'])){echo date('Y/m/d',strtotime($row['contract_date']));}else{echo '（未確定）';}?>
    <br>
    契約開始日：<?php if($row['contract_start'] <> '0000-00-00' && isset($row['contract_start'])){echo date('Y/m/d',strtotime($row['contract_start']));}else{echo '（未確定）';}?>
    <br>
    契約終了日：<?php if($row['contract_end'] <> '0000-00-00' && isset($row['contract_end'])){echo date('Y/m/d',strtotime($row['contract_end']));}else{echo '（未確定）';}?>
    <br>
    担当者：<?php if(isset($row['s_name'])){echo $row['s_name'];}else{echo '（未確定）';}?>
    <br>
  </td>
  <td colspan=2>
    配達日時：<?php if($row['deliv_date'] <> '0000-00-00 00:00:00' && isset($row['deliv_date'])){echo date('Y/m/d G:i',strtotime($row['deliv_date']));}else{echo '（未定）';}?>
    <br>
    引取日時：<?php if($row['pickup_date'] <> '0000-00-00 00:00:00' && isset($row['pickup_date'])){echo date('Y/m/d G:i',strtotime($row['pickup_date']));}else{echo '（未定）';}?>
    <br>
    登録日時：<?php echo date('Y/m/d G:i', strtotime($row['ins_date'])); ?>
    <br>
    更新日時：<?php if(!empty($row['update_date'])){echo date('Y/m/d G:i', strtotime($row['update_date']));}else{echo '（未更新）';} ?>
  </td>
</tr>
<?php
}
?>
</tbody></table>

<?php if($roaccum > 0){?>

<table class="table">
<thead><tr><th colspan=2>ID</th><th>商品</th><th>新品/中古</th><th>品目・内容</th><th>型番</th><th>年式</th><th>管理番号</th><th>契約金額</th><th>数量</th><th>分類</th><th>ターゲット</th></tr></thead><tbody>


<?php
$sumyen = 0;
foreach($rows_orderdetail as $row){
$sumyen = $sumyen + $row['order_price'];
?>
<tr>
  <?php if($row['setmenu'] == 1){
    //setmenuの時は下げる
    echo '<td>>></td><td>'.$row['code'].'</td>';
  }else{
    echo '<td colspan=2>'.$row['code'].'</td>';
  }
   ?>

  <td>
    <?php if($row['setmenu'] <> 1){
      //setmenu子以外の時はU
      echo '<u>'.$row['name'].'</u>';
    }else{
       echo $row['name'];
    }
    ?>
  </td>
  <td>
    <?php
    if($row['status'] == 1){
      echo '新品';
    }else{
      echo '中古その他';
    }
    ?>
  </td>

  <td><?php echo $row['model_name']; ?></td>
  <td><?php echo $row['model_type']; ?></td>
  <td><?php echo $row['model_year']; ?></td>
  <td><?php echo $row['model_serialno']; ?></td>
  <td>
    <?php
    if($row['order_price'] == 0){
      echo ' ';
    }else{
      echo $row['order_price'];
    }
    ?>
  </td>

  <td><?php echo $row['amount']; ?></td>
  <td><?php echo $row['category']; ?></td>
  <td><?php echo $row['target']; ?></td>
</tr>
<?php
}
?>
<tr><td colspan=7><b>合計金額（税抜）</B></td><td colspan=4>￥<?php echo $sumyen;?></td></tr>

</tbody></table>

<?php
}
?>



<?php if($rcaccum > 0){?>

<table class="table">
<thead><tr><th>過去履歴を表示</th></tr></thead><tbody>

<?php
foreach($rows_orderaccum as $row){
?>
<tr>
  <td>
    <a href="orderdetail.php?uid=<?php echo $uid;?>&oid=<?php echo $oid;?>&hash=<?php echo $row['hash'];?>"><?php if(!empty($row['update_date'])){echo date('Y/m/d G:i', strtotime($row['update_date']));}else{echo date('Y/m/d G:i', strtotime($row['ins_date']));} ?></a>
  </td>
</tr>
<?php
}
?>
</tbody></table>
<?php
}
?>



<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
