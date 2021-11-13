<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/head.php'); ?>

<?php
//ini_set("display_errors", On);
//error_reporting(E_ALL);

#初期変数（POSTデータ）
$uid=$_REQUEST['uid'];
$name=$_REQUEST['name'];
$email=$_REQUEST['email'];
$zip=$_REQUEST['zip11'];
$address=$_REQUEST['addr11'];
$tel=$_REQUEST['tel'];
$belonging=$_REQUEST['belonging'];
$belonging2=$_REQUEST['belonging2'];
$forceflag=$_REQUEST['forceflag'];
#判定
/*
modify(uid有)
forceflag
newで重複なし
  →スルーして登録　（１）

既存と電話等重複あり(uid無し)
  →表示＆リンク「これを編集してください」　（２）
    →OKならuserへ戻す（uid付与したリンク）
    →NOならorder（forceflag付与したリンク）
*/


### 既存と電話等重複あり(uid無し)かどうかを判断
//強制なら重複あっても関係なし＆uid有であれば既存修正で一意に特定できるのでチェック不要
if(isset($forceflag) || !empty($_REQUEST['uid']))
{
  #重複あり分岐フラグ　false
  $check_duplicate = false;
}
//uid無では重複を調べる
elseif(empty($_REQUEST['uid']))
{
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

  //空白だと引っかかるからNULL以外なら重複にする
  $sql = "select * from t_user where (tel='$tel' and tel <> '') or (name='$name' and name <> '') or (email='$email' and email <>'') limit 100";
  $result = mysqli_query($con ,$sql);
  while($row = mysqli_fetch_array($result)){
  	$rows[] = $row;
  }

  $con = mysqli_close($con);
  if (!$con) {
    exit('データベースとの接続を閉じられませんでした。');
  }

  if (mysqli_num_rows($result) > 0) {
    #重複あり分岐フラグ　true
    $check_duplicate = true;
  }
  else
  {
    #重複あり分岐フラグ　false
    $check_duplicate = false;
  }
}
//それ以外はそのまま　ここはありえない？
else {
  #重複あり分岐フラグ　false
  $check_duplicate = false;
}

?>

<?php
###分岐　重複エラー＆戻し
if($check_duplicate)
{
/*  既存と電話等重複あり(uid無し)
    →表示＆リンク「これを編集してください」　（２）
      →OKならuserへ戻す（uid付与したリンク）
      →NOならorder（forceflag付与したリンク）
*/
?>

  重複する顧客が見つかりました。この顧客は存在していますので、そちらを再編集することをお勧めします。メールアドレスや電話番号は重複できません</br>
  <table class=\"table\"><thead><tr><th>id</th><th>情報</th><th>リンク</th></tr></thead><tbody>

  <?php
  // check_duplicate = true ならチェック用としてSQLしてあるのでそれを利用
  //print($row_cnt);
  //print_r($rows);
  foreach($rows as $row){
  ?>

    <tr><td><?php echo $row['uid']; ?></td>
    <td>
    <b><?php echo $row['name']; ?></b><br>
    <?php echo $row['email']; ?><br>
    <?php echo $row['tel']; ?><br>
    <?php echo $row['address']; ?><br>
    <?php echo $row['belonging']; ?>    <?php echo $row['belonging2']; ?>
  </td><td><a href="./user.php?uid=<?php echo $row['uid']; ?>">既存顧客を編集</a></td></tr>
  <?php
  }
  ?>
  </table>
  <p>
  <form action="./user2.php?forceflag=true" method="post">
    <input type="hidden" name="name" value="<?php echo $_REQUEST['name']; ?>">
    <input type="hidden" name="email" value="<?php echo $_REQUEST['email']; ?>">
    <input type="hidden" name="zip11" value="<?php echo $_REQUEST['zip11']; ?>">
    <input type="hidden" name="addr11" value="<?php echo $_REQUEST['addr11']; ?>">
    <input type="hidden" name="tel" value="<?php echo $_REQUEST['tel']; ?>">
    <input type="hidden" name="belonging" value="<?php echo $_REQUEST['belonging']; ?>">
    <input type="hidden" name="belonging2" value="<?php echo $_REQUEST['belonging2']; ?>">
    <input type="submit" value="（重複覚悟で）このまま新規登録する" />
  </form>
  </p>

<?php
}
?>

<?php
###分岐　登録更新
if(!$check_duplicate)
{

  $con = mysqli_connect('localhost', 'works', 'chiba2018');
  if (!$con) {
    exit('データベースに接続できませんでした。');
  }

  $result = mysqli_select_db($con,'dw');
  if (!$result) {
    exit('データベースを選択できませんでした。');
  }

  //既存uid発番有なら更新
  if(!empty($_REQUEST['uid']))
  {
    //既存ありなのでupdate
    $result = mysqli_query($con ,"UPDATE t_user set belonging='$belonging',belonging2='$belonging2',address='$address', zip='$zip', email='$email', name='$name', tel='$tel', update_date = CURRENT_TIMESTAMP where uid=$uid");
    if (!$result) {
      exit('データを登録できませんでした。');
    }
  }
  //uid無しなら新規
  else {
    $result = mysqli_query($con ,"INSERT INTO t_user(zip, address, email, name, tel ,belonging ,belonging2) VALUES('$zip','$address','$email', '$name', '$tel', '$belonging', '$belonging2')");
    if (!$result) {
      exit('データを登録できませんでした。');
    }

    //登録・更新後のuid確認
    $result = mysqli_query($con ,"select uid from t_user where name='$name' and email ='$email' and tel = '$tel' and address='$address'");
    if (!$result) {
      exit('データを登録できていません');
    }
    while($row = mysqli_fetch_array($result)){
    	$rows[] = $row;
    }
    $uid = $rows[0]['uid'];

    /*
    $rows[0]['uid'] = '';
    $rows[0]['name'] = '';
    $rows[0]['email'] = '';
    $rows[0]['zip'] = '';
    $rows[0]['tel'] = '';
    $rows[0]['address'] = '';
    */
  }

  //t_user_accumにデータ履歴保存
  $result = mysqli_query($con ,"INSERT into t_user_accum select * from t_user where uid=$uid;");
  if (!$result) {
    exit('データ履歴を登録できていません');
  }

  //order を表示するためのデータ確保
  $sql = "select case when a.update_date is null then a.ins_date else a.update_date end as sortdate,a.*,b.name,b.email,b.tel,b.address,b.zip,b.belonging,b.belonging2 from t_order a
   left join t_user b on a.uid=b.uid where a.uid=$uid+0 order by sortdate desc limit 100";

  $result = mysqli_query($con ,$sql);
  $rc = mysqli_num_rows($result);

  while($row = mysqli_fetch_array($result)){
    $rows[] = $row;
  }

  $con = mysqli_close($con);
  if (!$con) {
    exit('データベースとの接続を閉じられませんでした。');
  }
  ?>

  <?php
  //ui部分 顧客登録完了＆オーダーへ進む
  ?>
  <B> 登録・更新しました</B><br>
  <?php echo $name ?>
  <br>
  <?php echo $tel ?>
  <br>
  <?php echo $email ?>
  <br>
  <?php echo $zip ?>
  <br>
  <?php echo $address ?>
  <br>
  <?php echo $belonging ?> <?php echo $belonging2 ?>
  <br>

  <?php if($rc >0){echo '<br>この顧客ですでに下記のオーダーがあります';}?>
  <?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/orderlist.php'); ?>

  <a href='order.php?uid=<?php echo $uid ?>'>新規オーダー登録する</a>

<?php
}

/*
echo 'uid:';
echo $uid;
echo '<BR>name:';
echo $name;
echo '<BR>mail:';
echo $email;
echo '<BR>zip:';
echo $zip;
echo '<BR>addr:';
echo $address;
echo '<BR>tel:';
echo $tel;
echo '<BR>b:';
echo $belonging;
echo '<BR>b2:';
echo $belonging2;
echo '<BR>force:';
echo $forceflag;
echo '<BR>check_duplicate:';
echo $check_duplicate;
echo '<BR>';
*/

?>

<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
