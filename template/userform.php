
<input type="hidden" name="uid" value="<?php echo $row['uid']; ?>">
<label>名前：
  <input type="name" name="name" size="10" value="<?php echo $row['name']; ?>"<?php if(isset($user_read_only)){echo ' readonly';};?>>
</label>

<label>電話番号：
  <input type="tel" name="tel" value="<?php echo $row['tel']; ?>"<?php if(isset($user_read_only)){echo ' readonly';};?>>
</label>

<label>郵便番号（ハイフンなし）：
  <input type="text" id="zip_ac" name="zip11" size="10" maxlength="8" onKeyUp="AjaxZip3.zip2addr(this,'','addr11','addr11');" value="<?php echo $row['zip']; ?>"<?php if(isset($user_read_only)){echo ' readonly';};?>>
</label>
<label>住所：
  <input type="text" id="address_ac" name="addr11" size="60" value="<?php echo $row['address']; ?>"<?php if(isset($user_read_only)){echo ' readonly';};?>>
</label>



<label>所属：
<?php
//所属master
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
//所属マスターから項目選択候補を取る
$result = mysqli_query($con ,'select * from m_belonging order by code;');
if (mysqli_num_rows($result) <= 0) {
  exit('マスタが存在しません。');
}
while($rowbf = mysqli_fetch_array($result)){
	$rows_bmaster[] = $rowbf;
}
?>



<?php
foreach($rows_bmaster as $rowb){
?>
<input type="radio" name="belonging" value="<?php echo $rowb['name'];?>"<?php if(strcmp($rowb['name'], $row['belonging']) == 0){echo ' checked';}?><?php if(isset($user_read_only)){echo ' readonly';};?>><?php echo $rowb['name'];?>



<?php
}
?>


</label>
<label>所属備考（部署や注意事項など）：
  <input type="text" name="belonging2" size="30" value="<?php echo $row['belonging2']; ?>"<?php if(isset($user_read_only)){echo ' readonly';};?>>
</label>
<label>メールアドレス：
  <input type="email" name="email" size="30" autocomplete="email" value="<?php echo $row['email']; ?>"<?php if(isset($user_read_only)){echo ' readonly';};?>>
</label>
