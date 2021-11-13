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

if(!isset($_REQUEST['uid']))
{
  //uidは必須
  exit('顧客コードが存在しません。');
}

if(!isset($_REQUEST['oid']))
{
  //uidは必須
  exit('オーダーが存在しません。');
}


$uid = $_REQUEST['uid'];
$oid = $_REQUEST['oid'];

if(isset($_REQUEST['hash'])){
  //過去履歴を表示する場合（URLにハッシュあり）はaccumをクエリー
  $hash = $_REQUEST['hash'];
  $sql = "select a.*,b.name,b.email,b.tel,b.address,b.zip,b.belonging,b.belonging2,c.* from (select *,case when update_date is null then ins_date else update_date end as sortdate from t_order_accum) as a left join (select *,case when update_date is null then ins_date else update_date end as sortdate_user from t_user_accum) as b on a.uid=b.uid and b.sortdate_user < a.sortdate left join m_staff c on a.deliv_staff=c.s_code where a.uid=$uid+0 and a.oid=$oid+0 and a.hash= '$hash' order by sortdate_user desc limit 1";

  $sql_order = "select * from t_order_detail_accum c left join m_order_detail d on d.code=c.code where c.oid=$oid+0 and c.amount > 0 and c.hash= '$hash' order by c.code;";
}else{
  //通常
  $sql = "select case when a.update_date is null then a.ins_date else a.update_date end as sortdate,a.*,b.name,b.email,b.tel,b.address,b.zip,b.belonging,b.belonging2,c.* from t_order a left join t_user b on a.uid=b.uid left join m_staff c on a.deliv_staff=c.s_code where a.uid=$uid+0 and a.oid=$oid+0";
  $sql_order = "select * from t_order_detail c left join m_order_detail d on d.code=c.code where c.oid=$oid+0 and c.amount > 0 order by c.code;";
}


$result = mysqli_query($con ,$sql_order);
if (mysqli_num_rows($result) <= 0) {
  exit('オーダーが存在しません。');
}
//上書きするので初期化
while($row = mysqli_fetch_array($result)){
  $rows_ordermaster[] = $row;
}

$result = mysqli_query($con ,$sql);
if (mysqli_num_rows($result) <= 0) {
  exit('顧客またはオーダーが存在しません。');
}
while($row = mysqli_fetch_array($result)){
	$rows[] = $row;
}
?>


<!doctype html>

<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>契約書</title>
  <link rel="stylesheet" href="css/keiyaku.css?<?php echo rand(); ?>">
  <style>
    body { padding: 1mm; line-height: 1.2em; max-width: 100em; margin: 0 auto }
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

    }

  </style>
</head>

<body>

<?php
foreach($rows as $row){
?>

<section class="keiyaku">
  <h1 id="section">物品賃貸借契約書</h1>
<p>賃貸人　株式会社ワークス(以下「甲」という。)と賃借人　 <?php echo $row['name']; ?>(以下「乙」という。)は、下記の記載事項により、物品賃貸借契約を締結する。</p>

<ul>
 <li>１． 契約対象の物品・期間・賃料</li>
</ul>

<table border=1>
<tr><td>契約品番</td><td>品目</td><td>型番</td><!--td>状態</td--><td>期間</td><!--td>数量</td--><td>価格</td></tr>
<?php
$sumyen = 0;
foreach($rows_ordermaster as $row_order){
//echo ' checked="checked"';
?>

  <?php if($row_order['amount'] > 0){?>
    <tr>
      <td><?php echo $row_order['code']; ?></td>
      <td><?php echo str_replace('【新料金】', '', $row_order['model_name']); ?></td>
      <td><?php echo $row_order['model_type']; ?></td>
      <!---td>
        <?php
        if($row_order['status'] == 1){
          echo '新品';
        }else{
          echo '中古・その他';
        }
        ?>
      </td--->
      <td>
        <?php
        if($row['contract_start'] <> '0000-00-00'){
          echo date('Y年m月d日',strtotime($row['contract_start']));
        }else{
          echo '　年　月　日';
        }
        ?>
        <BR>〜
        <?php
        if($row['contract_end'] <> '0000-00-00'){
          echo date('Y年m月d日',strtotime($row['contract_end']));
        }else{
          echo '　年　月　日';
        }
        ?>
      </td>
      <!--td><?php echo $row_order['amount']; ?></td-->
      <td>
        <?php
        if($row_order['order_price'] == 0 && $row_order['setmenu'] == 1){
          //セット子のとき
          echo '（セットに含む）';
        }elseif($row_order['order_price'] == 0){
            echo ' ';
        }else{
          echo '￥'.number_format($row_order['amount']*$row_order['order_price']);
        }
        ?>
      </td></tr>
  <?php
  $sumyen=$sumyen+$row_order['amount']*$row_order['order_price'];
  }
  ?>

<?php
}
?>



<!--

<?php
$timestamp = strtotime($row['contract_start'].' 00:00:00');
echo date('Y年m月d日 H時i分s秒__', $timestamp);


echo $tmpt;

?>
-->


<?php

//税率計算
if(strtotime($row['contract_start'].' 00:00:00') >= strtotime('2019-10-01 00:00:00')){
    $contax = 0.1;
}else{
    $contax = 0.08;
}
//税率計算 強制
if(isset($_REQUEST['force20191001'])){
    $contax = 0.1;
}elseif(isset($_REQUEST['force20190930'])){
    $contax = 0.08;
}

?>
<tr><td colspan=7 align=right>小計： ￥<?php echo number_format(floor($sumyen)); ?></td></tr>
<tr><td colspan=7 align=right>消費税：　￥<?php echo number_format(floor($sumyen*$contax)); ?></td></tr>
<tr><td colspan=7 align=right>合計：　￥<?php echo number_format(floor($sumyen+ $sumyen*$contax)); ?></td></tr>
</table>


<ul>
 <li>２． 設置場所</li>
 <p>乙の指定する場所。</p>
</ul>

<ul>
 <li>３． 契約条項</li>
</ul>
<h2 id="section-1">目的</h2>
<p>甲は前記の表示物品を（以下「賃貸借物品」という。）を賃貸し、乙はこれを賃借することを約します。</p>
<h2 id="section-2">契約更新期間・中途解約</h2>
<p>契約更新期間は前記表示の1年間とし、乙の申し出により更新することができます。中途解約の場合は月割りで計算しご返金いたします。</p>
<h2 id="section-3">賃料</h2>
<p>賃料は前記表示の金額とし、契約期間開始時に一括払いとします。なお、乙は、賃料を次の銀行口座に振り込むものとします。<br/><br/>
<?php if($row['belonging'] == 'かすみ会'){echo '三井住友銀行　船橋北口支店 (株)ワークス  普通 １０８５７１０';}else{echo '千葉銀行　京成駅前支店 (株)ワークス 代表取締役 田村哲子 普通 ３６２６６７３';} ?>
</p>

<h2 id="section-4">損害賠償</h2>
<p>乙は、賃貸借物品を善良なる管理者の注意義務をもって使用管理しなければなりません。乙の故意又は過失により賃貸借物品が汚損、毀損、破損等した場合は、乙はその損害を賠償するものとします。ただし、乙の責に帰さない事由より賃貸借物品が汚損、毀損、破損した場合は、甲の負担により修理、交換するものとします。
</p>

<h2 id="section-5">賃貸借物品の管理</h2>
<p>乙は、本賃貸借物品の全部又は一部を譲渡・転貸又は第三者に使用させてはなりません。
</p>

<h2 id="section-6">特約</h2>
<p>本契約に定めなき事項については、甲乙協議して解決するものとします。

<br/><br/>
以上のとおり契約が成立しましたので、本契約書２通を作成し、各自記名押印のうえ、各1通を所持します。
</p>

<hr />
<p><?php
if($row['contract_date'] <> '0000-00-00'){
  echo date('契約日：Y年n月j日',strtotime($row['contract_date']));
}else{
  echo '契約日：　　年　月　日';
}
?></p>


<h4 id="section-8">甲</h4>
<ul>
 <li>住所:　千葉県千葉市中央区旭町１６−４</li>
 <li class="sig">氏名:　株式会社ワークス　代表取締役　田村　哲子</li>
</ul>

<h4 id="section-9">乙</h4>
<ul>
 <li>住所: 〒<!--<?php echo $row['address']; ?>--></li><BR><BR>
 <li class="sig">氏名:　<!--<?php echo $row['name']; ?>--></li><BR><BR>
 <li>所属:　</li><BR><BR>
 <li>携帯電話:　</li>
</ul>


 </section>

<?php
}
?>

</body>

</html>
