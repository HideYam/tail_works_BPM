
<?php
###### 特殊(header/footer非共通) ######
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
  $sql = "select case when a.update_date is null then a.ins_date else a.update_date end as sortdate,a.*,b.name,b.email,b.tel,b.address,b.zip,b.belonging,b.belonging2,c.* from t_order_accum a left join t_user b on a.uid=b.uid left join m_staff c on a.deliv_staff=c.s_code where a.uid=$uid+0 and a.oid=$oid+0 and a.hash='$hash'";
}else{
  //通常　t_orderをクエリーする
  $sql = "select case when a.update_date is null then a.ins_date else a.update_date end as sortdate,a.*,b.name,b.email,b.tel,b.address,b.zip,b.belonging,b.belonging2,c.* from t_order a left join t_user b on a.uid=b.uid left join m_staff c on a.deliv_staff=c.s_code where a.uid=$uid+0 and a.oid=$oid+0";
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


// DB接続解除共通 //
$con = mysqli_close($con);
if (!$con) {
  exit('データベースとの接続を閉じられませんでした。');
}
?>




<!doctype html>

<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>オーダーシート（NO：<?php echo $uid; ?>-<?php echo $oid; ?>）</title>
  <style>
  @page { size: A4 landscape; }

    body { padding: 1mm; line-height: 1.2em; max-width: 100em; margin: 0 auto;  font-family: "游明朝", YuMincho, "ヒラギノ明朝 ProN W3", "Hiragino Mincho ProN", "HG明朝E", "ＭＳ Ｐ明朝", "ＭＳ 明朝", serif;font-size: 1.2em}
    h1 { margin-bottom: 1em }
    h2 { margin-top: 1em }
    pre, code { background: #f7f7f7 }
    svg {
      fill: black;
      color: #fff;
      position: absolute;
      top: 0;
      border: 0;
      right: 0;
    }

    table {
      width: 95%;
      border-collapse: collapse;
      border: solid 2px gray;
      display: table;
margin: 0 auto;
    }
    table th, table td {
      border: dashed 1px gray;
      padding-top: 1em;
      padding-bottom: 1em;
    }

  </style>
</head>

<body>


<!-- 表示-->
<table class="table">



<?php
foreach($rows_order as $row){
?>
<tr>
  <td>氏名（所属）</td>
  <td>
　<?php echo htmlspecialchars($row['name'],ENT_QUOTES,'UTF-8'); ?> （<?php echo htmlspecialchars($row['belonging'],ENT_QUOTES,'UTF-8'); ?>）
  </td>
</tr>
<tr>
  <td>現所属</td>
  <td>
　　　　　　　　　本部・署　　　　　　　　　　　課
  </td>
</tr>
<tr>
  <td>移動後所属</td>
  <td>
　　　　　　　　　本部・署　　　　　　　　　　　課
  </td>
</tr>
<tr>
  <td>連絡先電話番号</td>
  <td>
　<?php echo $row['tel']; ?>
  </td>
</tr>

<tr>
  <td>住所</td>
  <td>
　　〒<?php
if(strlen($row['zip']) == 7){
  $zip7 = substr($row['zip'], 0, 3).'-'.substr($row['zip'], 3, 4);
}else{$zip7 = $row['zip'];}

echo $zip7;

?>


　<?php echo $row['address']; ?>
  </td>
</tr>


<tr>
  <td>配達日時</td>
  <td>
　　<?php if($row['deliv_date'] <> '0000-00-00 00:00:00' && isset($row['deliv_date'])){echo date('Y/m/d G:i',strtotime($row['deliv_date']));}else{echo '（未定）';}?>
  </td>
</tr>

<tr>
  <td>担当者</td>
  <td>
　　<?php if(isset($row['s_name'])){echo $row['s_name'];}else{echo '（未確定）';}?>
  </td>
</tr>

<?php
}
?>
</tbody></table>

<table class="table">
<thead><tr><th colspan=2>ID</th><th>商品</th><th>新品/中古</th><th>品目・内容</th><th>型番</th><th>年式</th><th>管理番号</th><th>契約金額</th><th>数量</th></tr></thead><tbody>

<?php
foreach($rows_orderdetail as $row){
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

</tr>
<?php
}
?>
</tbody></table>









</body></html>
