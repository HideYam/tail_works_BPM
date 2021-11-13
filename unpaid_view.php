
<?php
###### 特殊(header/footer非共通) ######
//ini_set("display_errors", On);
//error_reporting(E_ALL);


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

///  SQL :未払い者を探す  ///
$result = mysqli_query($con ,'select b.name as 名前,b.tel as 電話番号,c.order_price,b.email as email,b.zip as 郵便番号,b.address as 住所,b.belonging as 所属,b.belonging2 as 所属備考,a.contract_date as 契約日,a.contract_start as 契約開始日,a.contract_end as 契約終了日,a.etc as 備考 from t_order a left join t_user b on a.uid=b.uid left join (select oid,sum(order_price*amount) as order_price from t_order_detail where amount>0 group by oid) c on c.oid=a.oid where a.payflag = 1 and b.uid is not null  order by a.uid;');
if (mysqli_num_rows($result) <= 0) {
  exit('データが存在しません。');
}
while($row = mysqli_fetch_assoc($result)){
  $rows[] = $row;
}
$rows_w = $rows;

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

<a href="csv_unpaid.php">CSVダウンロード</a><br>

<!-- 表示-->
<table class="table">
<tr>
<thead>
<th>名前</th><th>電話番号</th><th>価格（税抜）</th><th>email</th><th>郵便番号</th><th>住所</th><th>所属</th><th>所属備考</th><th>契約日</th><th>契約開始日</th><th>契約終了日</th><th>備考</th></thead><tbody>
</tr>

<?php
foreach($rows_w as $row){
?>
<tr>
  <td><?php echo $row['名前']; ?></td>
  <td><?php echo $row['電話番号']; ?></td>
  <td><?php echo $row['order_price']; ?></td>
  <td><?php echo $row['email']; ?></td>
  <td><?php echo $row['郵便番号']; ?></td>
  <td><?php echo $row['住所']; ?></td>
  <td><?php echo $row['所属']; ?></td>
  <td><?php echo $row['所属備考']; ?></td>
  <td><?php echo $row['契約日']; ?></td>
  <td><?php echo $row['契約開始日']; ?></td>
  <td><?php echo $row['契約終了日']; ?></td>
  <td><?php echo $row['備考']; ?></td>
</tr>

<?php
}
?>
</tbody></table>

</body></html>
