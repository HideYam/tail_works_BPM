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

//oidの有無で分岐　uidなしはダメなのでエラー
if(!isset($_REQUEST['uid']))
{
  //uidは必須
  exit('顧客コードが存在しません。');
}
elseif (!isset($_REQUEST['oid']))
{
  $uid = $_REQUEST['uid'];
  //oidがなければオーダー新規登録＝顧客リストのみ引く
  $sql = "select * from t_user where uid =$uid+0 limit 1";

  //オーダーマスターから商品項目選択候補を取る
  //件数が多いので、新料金フラグ（newprice code > 10000）で切り替える
  $ordersql = 'select *,0+0 as amount, new as status ,name as model_name,price as order_price from m_order_detail a where a.show = 1 order by sort_order;';
  if(isset($_REQUEST['newprice']) and $_REQUEST['newprice'] == 'false'){
      $ordersql = 'select *,0+0 as amount, new as status ,name as model_name,price as order_price from m_order_detail a where a.show = 1 and code < 10000 order by sort_order;';
  }else{
      $ordersql = 'select *,0+0 as amount, new as status ,name as model_name,price as order_price from m_order_detail a where a.show = 1  and code > 10000 order by sort_order;';
      $oldflg="true"; //便宜的に旧料金を表示したい時のボタンを出す判定のため
  }

  $result = mysqli_query($con ,$ordersql);
  if(mysqli_num_rows($result) <= 0) {
    exit('オーダーマスタが存在しません。');
  }
  while($row = mysqli_fetch_array($result)){
  	$rows_ordermaster[] = $row;
  }

}
else
{
  $uid = $_REQUEST['uid'];
  $oid = $_REQUEST['oid'];
  //oidがあればオーダー更新＝オーダー情報と、詳細情報付きマスターを引く
  $sql = "select a.uid,a.email,a.name,a.tel,a.zip,a.address,a.belonging,a.belonging2,b.* from t_user as a left join t_order as b on a.uid = b.uid where b.oid=$oid+0 and a.uid =$uid+0 limit 1";

  //オーダーマスターとオーダー選択済みを紐付け
  $sql_order = "select a.*,b.oid,b.amount,b.status,b.model_name,b.model_type,b.model_year,b.order_price from m_order_detail a left join t_order_detail b on a.code = b.code where b.oid=$oid+0 and (b.amount > 0 or a.show = 1) order by sort_order";
  $result = mysqli_query($con ,$sql_order);
  if (mysqli_num_rows($result) <= 0) {
    exit('オーダーが存在しません。');
  }
  //上書きするので初期化
  while($row = mysqli_fetch_array($result)){
  	$rows_ordermaster[] = $row;
  }

}

$result = mysqli_query($con ,$sql);
if (mysqli_num_rows($result) <= 0) {
  exit('顧客またはオーダーが存在しません。');
}
while($row = mysqli_fetch_array($result)){
	$rows[] = $row;
}

//従業員マスター
$sql_staff = "select * from m_staff;";
$result = mysqli_query($con ,$sql_staff);
if (mysqli_num_rows($result) <= 0) {
  exit('顧客またはオーダーが存在しません。');
}
while($row = mysqli_fetch_array($result)){
	$rows_staff[] = $row;
}

$con = mysqli_close($con);
if (!$con) {
  exit('データベースとの接続を閉じられませんでした。');
}
?>


<form action="order2.php" method="post">

<?php
foreach($rows as $row){
?>

  <?php
  //oidがあればセットする　ただし、契約更新処理の場合(refresh_contract)は新規に起こしたいので、oid入れない
  if(isset($row['oid']) && !isset($_REQUEST['refresh_contract'])) {
    echo '<input type="hidden" name="oid" value="'.$row['oid'].'">';
  }?>

  <?php
  //顧客リスト　この変数をセットするとreadonlyにする
  $user_read_only = 'true';
  ?>
  <?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/userform.php'); ?>

  <br />
  代金回収状況：<br />
  <?php if(isset($row['payflag'])){ $payflag = $row['payflag'];}else{$payflag = 1;}?>
  <input type="radio" name="payflag" value="1"<?php if($payflag == 1){echo ' checked';}?>>未完了
  <input type="radio" name="payflag" value="2"<?php if($payflag == 2){echo ' checked';}?>>代済
  <br />
  <br />
  オーダー：<br />

  <?php
  if(isset($oldflg)){
    echo '<a href="order.php?uid='.$row['uid'].'&newprice=false">旧料金表示</a>';
  }



  echo "<table class=\"table\"><thead><tr><th colspan=2>名称</th><!--th>数</th--><th>状態</th><th>品目（内容）</th><th>型番</th><th>年式</th><th>管理番号</th><th>価格</th></tr></thead><tbody>";
  ?>

  <?php
  $setcounter = 0+0;
  foreach($rows_ordermaster as $row_order){
  //echo ' checked="checked"';
  ?>

  <?php
  //末尾０で線引き
  if(substr($row_order['code'], -1, 1) == '0'){
    echo '<!--hr-->';
  }
  ?>

  <?php
  /////セット商品の行//////
  if($row_order['setmenu'] == '2'){
    $setcounter = $setcounter + 1;
  ?>
  <tr><td colspan=2>
    <script language="JavaScript" type="text/javascript">
    <!--
    $(function() {
      $('#category_all<?php echo $setcounter;?>').on('click', function() {
        $('.category<?php echo $setcounter;?>').prop('checked', this.checked);
      });

      $('.category<?php echo $setcounter;?>').on('click', function() {
        if ($('#categories<?php echo $setcounter;?> :checked').length == $('#categories<?php echo $setcounter;?> :input').length){
          $('#category_all<?php echo $setcounter;?>').prop('checked', 'checked');
        }else{
          $('#category_all<?php echo $setcounter;?>').prop('checked', false);
        }
      });
    });
    //-->
    </script>
    <input type="checkbox" id="category_all<?php echo $setcounter;?>" name="order[<?php echo $row_order['code']; ?>][check]"<?php if(isset($row_order['amount']) && $row_order['amount'] > 0){echo ' checked="　checked"';}?>>
    <b><?php echo $row_order['name']; ?></B>

  <?php
  }
  ?>


  <?php
  //それ以外 1==セットの付随 0==オプション
  if($row_order['setmenu'] < 2){
  ?>

    <?php
    //1==セットの付随
    if($row_order['setmenu'] == 1){
    ?>
    <tr><td>　</td><td>
    <div id="categories<?php echo $setcounter;?>">
    <input type="checkbox" name="order[<?php echo $row_order['code']; ?>][check]" id="category_01" value="<?php echo $row_order['code']; ?>" class="category<?php echo $setcounter;?>"<?php if(isset($row_order['amount']) && $row_order['amount'] > 0){echo ' checked="　checked"';}?>>
    <?php echo $row_order['name']; ?>
    </div>
    <?php
    }
    ?>

    <?php
    //0==オプション
    if($row_order['setmenu'] == 0){
    ?>
    <tr><td colspan=2>
    <input type="checkbox" name="order[<?php echo $row_order['code']; ?>][check]" value="<?php echo $row_order['code']; ?>"<?php if(isset($row_order['amount']) && $row_order['amount'] > 0){echo ' checked="checked"';}?>><?php echo $row_order['name']; ?>
    <?php
    }
    ?>

  <?php
  //それ以外　・・・ここまで
  }
  ?>

  </td><td>

    <input type="radio" name="order[<?php echo $row_order['code']; ?>][status]" value="1"<?php if(isset($row_order['status']) && $row_order['status'] == '1'){echo ' checked';}?>/>新品<br/>
    <input type="radio" name="order[<?php echo $row_order['code']; ?>][status]" value="0"<?php if(isset($row_order['status']) && $row_order['status'] <> '1'){echo ' checked';}?>/>中古
  </td><td>
    <input type="text" name="order[<?php echo $row_order['code']; ?>][model_name]" size="10" value="<?php if(isset($row_order['model_name']) && $row_order['model_name'] <> ''){echo $row_order['model_name'];}else{echo '0';} ?>">
  </td><td>
    <input type="text" name="order[<?php echo $row_order['code']; ?>][model_type]" size="10" value="<?php if(isset($row_order['model_type']) && $row_order['model_type'] <> ''){echo $row_order['model_type'];}else{echo '-';} ?>">
  </td><td>
    <input type="text" name="order[<?php echo $row_order['code']; ?>][model_year]" size="10" value="<?php if(isset($row_order['model_year']) && $row_order['model_year'] <> ''){echo $row_order['model_year'];}else{echo '';} ?>">
  </td><td>
    <input type="text" name="order[<?php echo $row_order['code']; ?>][model_serialno]" size="10" value="<?php if(isset($row_order['model_serialno']) && $row_order['model_serialno'] <> ''){echo $row_order['model_serialno'];}else{echo '';} ?>">
  </td><td>


    <?php
    //契約更新処理の場合(refresh_contract)は金額を中古価格にするので、price_rfを採用する
    //金額改定(newprice)は新規の場合に金額を新価格にする
    if(isset($_REQUEST['refresh_contract'])) {
      $price = $row_order['price_rf'];
    }elseif(isset($row_order['order_price']) && $row_order['order_price'] <> ''){
      $price = $row_order['order_price'];
    }else{
      $price = 0;
    }
    ?>




    <input type="text" name="order[<?php echo $row_order['code']; ?>][order_price]" size="10" value="<?php echo $price; ?>">



  <!--
  </td><td>
    <input type="text" name="order[<?php echo $row_order['code']; ?>][amount]" size="2" value="<?php if(isset($row_order['amount'])){echo $row_order['amount'];}else{echo '0';} ?>">
  -->
    <input type="hidden" name="order[<?php echo $row_order['code']; ?>][amount]" value="<?php if(isset($row_order['amount'])){echo $row_order['amount'];}else{echo '0';} ?>">
    <input type="hidden" name="order[<?php echo $row_order['code']; ?>][name]" value="<?php echo $row_order['name']; ?>">
  </td></tr>

  <?php
  }

  ?>
  </table>
  <hr>

  担当者（最初は空欄でもOK）：<br />
  <select name="deliv_staff">
  <?php
  foreach($rows_staff as $rowstaff){
    echo '<option value="'.$rowstaff['s_code'].'"';
    if($rowstaff['s_code'] == $row['deliv_staff']){echo ' selected';}
    echo '>'.$rowstaff['s_name'].'</option>';
  }
  ?>
  </select>
  <br />

  配達日（最初は空欄でもOK）：<br />
  <input type="text" id="datepicker" name="delivdate"<?php if($row['deliv_date'] <> '0000-00-00 00:00:00' && isset($row['deliv_date'])){echo ' value="'.date('Y/m/d',strtotime($row['deliv_date'])).'"';
  }?>>
  <br />

  配達時間（最初は空欄でもOK）：<br />
  <input type="text" id="timepicker" name="delivtime"<?php if($row['deliv_date'] <> '0000-00-00 00:00:00' && isset($row['deliv_date'])){echo ' value="'.date('H:i',strtotime($row['deliv_date'])).'"';
  }?>>
  <br />

  引き取り日（最初は空欄でもOK）：<br />
  <input type="text" id="datepicker2" name="pickupdate"<?php if($row['pickup_date'] <> '0000-00-00 00:00:00' && isset($row['deliv_date'])){echo ' value="'.date('Y/m/d',strtotime($row['pickup_date'])).'"';
  }?>>
  <br />

  引き取り時間（最初は空欄でもOK）：<br />
  <input type="text" id="timepicker2" name="pickuptime"<?php if($row['pickup_date'] <> '0000-00-00 00:00:00' && isset($row['deliv_date'])){echo ' value="'.date('H:i',strtotime($row['pickup_date'])).'"';
  }?>>
  <br />

  <script>
  $(function () {
    $("#cpButton").click( function() {
      // テキストボックスへ値を設定します
      var dt = $('#datepicker4').val();
      var dw = dt.replace(/\//g, '-') + "T00:00:00+0900";
      ds = new Date(dw);
      //１年ごの前の日
      ds = ds.setFullYear(ds.getFullYear() + 1);
      ds = new Date(ds);
      ds = ds.setDate(ds.getDate() - 1);
      ds = new Date(ds);
      var y = ds.getFullYear();
      var m = ("00" + (ds.getMonth()+1)).slice(-2);
      var d = ("00" + ds.getDate()).slice(-2);
      var result = y + "/" + m + "/" + d;
      $("#datepicker3").val(dt);
      $("#datepicker5").val(result);
    });
  });
  </script>



  契約開始日（最初は空欄でもOK）：<br />
  <input type="text" id="datepicker4" name="contractstart"<?php if($row['contract_start'] <> '0000-00-00' && isset($row['contract_start']) && !isset($_REQUEST['refresh_contract'])){echo ' value="'.date('Y/m/d',strtotime($row['contract_start'])).'"';}

  elseif($row['contract_start'] <> '0000-00-00' && isset($row['contract_start']) && isset($_REQUEST['refresh_contract'])){echo ' value="'.date('Y/m/d',strtotime($row['contract_start'].'+ 1 year')).'"';}?>>
  <input type="button" id="cpButton" value="終了日と契約締結日を自動入力"><?php if($row['contract_start'] <> '0000-00-00' && isset($row['contract_start']) && isset($_REQUEST['refresh_contract'])){echo '　元契約：'.date('Y/m/d',strtotime($row['contract_start']));}?>
  <br />

  契約終了日（最初は空欄でもOK）：<br />
  <input type="text" id="datepicker5" name="contractend"<?php if($row['contract_end'] <> '0000-00-00' && isset($row['contract_end']) && !isset($_REQUEST['refresh_contract'])){echo ' value="'.date('Y/m/d',strtotime($row['contract_end'])).'"';}
  elseif($row['contract_end'] <> '0000-00-00' && isset($row['contract_end']) && isset($_REQUEST['refresh_contract'])){echo ' value="'.date('Y/m/d',strtotime($row['contract_end'].'+ 1 year')).'"';}?>>
  <?php if($row['contract_end'] <> '0000-00-00' && isset($row['contract_end']) && isset($_REQUEST['refresh_contract'])){echo '　元契約：'.date('Y/m/d',strtotime($row['contract_end']));}?>
  <br />

  契約書上の契約締結日（最初は空欄でもOK）：<br />
  <input type="text" id="datepicker3" name="contractdate"<?php if($row['contract_date'] <> '0000-00-00' && isset($row['contract_date']) && !isset($_REQUEST['refresh_contract'])){echo ' value="'.date('Y/m/d',strtotime($row['contract_date'])).'"';}
  elseif($row['contract_date'] <> '0000-00-00' && isset($row['contract_date']) && isset($_REQUEST['refresh_contract'])){echo ' value="'.date('Y/m/d',strtotime($row['contract_date'].'+ 1 year')).'"';}?>>
  <?php if($row['contract_date'] <> '0000-00-00' && isset($row['contract_date']) && isset($_REQUEST['refresh_contract'])){echo '　元契約：'.date('Y/m/d',strtotime($row['contract_date']));}?>
  <br />

  備考（メモ、引き継ぎ事項など）：<br />
  <input type="text" name="etc" size="100" value="<?php if(!empty($row['etc'])){echo $row['etc'];}?>"><br />
  <br />

  <?php
}
  ?>

  <input type="submit" value="登録する" />

</form>

<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
