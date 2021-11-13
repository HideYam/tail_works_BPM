<?php
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

//今日の配達予定



if(!isset($limitrows))
{
  $limitrows = 7;
}

if(isset($_REQUEST['nextmonth']))
{
  $limitdays = "DATE_FORMAT(CURRENT_DATE + interval 1 MONTH,'%Y-%m-01')";
}elseif(isset($_REQUEST['thirdmonth'])){
  $limitdays = "DATE_FORMAT(CURRENT_DATE + interval 2 MONTH,'%Y-%m-01')";

}elseif(!isset($limitdays)){
  $limitdays = "current_date";
  //$limitdays = "DATE_FORMAT(CURRENT_DATE + interval 1 MONTH,'%Y-%m-01')";
}

$sql = "select
date
,case when deliv is null then 0 else deliv end as deliv
,case when pickup is null then 0 else pickup end as pickup
,case when delivAM is null then 0 else delivAM end as delivAM
,case when pickupAM is null then 0 else pickupAM end as pickupAM
,case when delivPM is null then 0 else delivPM end as delivPM
,case when pickupPM is null then 0 else pickupPM end as pickupPM
from(
    SELECT
    DATE_FORMAT(DATE_ADD($limitdays, INTERVAL @i := @i + 1 DAY),'%Y/%m/%d')  AS date
    FROM
    ( SELECT @i:=-1 ) AS dummy,
    m_order_detail a
    limit 0,$limitrows
) as t
left join (select DATE_FORMAT(deliv_date,'%Y/%m/%d') as dt,count(distinct oid) as deliv,count(distinct case when DATE_FORMAT(deliv_date,'%p') ='AM' then oid end) as delivAM,count(distinct case when DATE_FORMAT(deliv_date,'%p') ='PM' then oid end) as delivPM from t_order where deliv_date <> '0000-00-00 00:00:00' group by dt) as b on t.date=b.dt
left join (select DATE_FORMAT(pickup_date,'%Y/%m/%d') as dt,count(distinct oid) as pickup,count(distinct case when DATE_FORMAT(pickup_date,'%p') ='AM' then oid end) as pickupAM,count(distinct case when DATE_FORMAT(pickup_date,'%p') ='PM' then oid end) as pickupPM from t_order where pickup_date <> '0000-00-00 00:00:00' group by dt) as c on t.date=c.dt";
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
$week=array('日','月','火','水','木','金','土');
?>
<?php
if ($rc > 0) {

    echo "<a href='./'>初期値</a> | <a href='./?nextmonth=true&limitrows=31'>来月を表示</a> | <a href='./?thirdmonth=true&limitrows=31'>再来月を表示</a>| <a href='./?limitrows=31'>今日から31日分</a><br>";

  echo "<table class=\"table\"><thead><tr><th>日付</th><th>配達午前</th><th>配達午後</th><th>引取午前</th><th>引取午後</th></tr></thead><tbody>";

  foreach($rows as $row){
  ?>
  <tr>
    <td>
      <strong>
        <a href="./orderdaily.php?date=<?php echo date('Y/m/d', strtotime($row['date'])); ?>">
        <?php echo date('Y/m/d', strtotime($row['date'])); ?>
        <?php echo '('.$week[date('w', strtotime($row['date']))].')'; ?>
        </a>
      </strong>
    </td>
    <td>
    <?php echo $row['delivAM']; ?>
    </td>
    <td>
    <?php echo $row['delivPM']; ?>
    </td>
    <td>
      <?php echo $row['pickupAM']; ?>
    </td>
    <td>
      <?php echo $row['pickupPM']; ?>
    </td>
  </tr>

  <?php
  }
  echo "</tbody></table>";
}
else{
  echo "予定はありません。";
}
?>
