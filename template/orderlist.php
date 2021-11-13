<?php
if ($rc > 0) {
  echo "<table class=\"table\"><thead><tr><th>OrderNO</th><th>個人情報</th><th>更新日</th><th>オーダー詳細</th><th>変更・修正</th></tr></thead><tbody>";


foreach($rows as $row){
?>
<tr>
  <td>
    <?php echo $row['uid']; ?>
    -<?php echo $row['oid']; ?>
  </td>
  <td>
    <strong><?php echo htmlspecialchars($row['name'],ENT_QUOTES,'UTF-8'); ?></strong><br>
    <?php echo $row['tel']; ?><br>
    <?php echo $row['belonging']; ?><?php echo $row['belonging2']; ?><br>
    <?php echo $row['email']; ?>
  </td>
  <td>
    <?php echo date('Y/m/d G:i', strtotime($row['sortdate'])); ?>
  </td>
  <td>
    <a href="./orderdetail.php?uid=<?php echo $row['uid']; ?>&oid=<?php echo $row['oid']; ?>">申込書表示</a>
    <BR>
    <a href="./keiyaku.php?uid=<?php echo $row['uid']; ?>&oid=<?php echo $row['oid']; ?>">契約書表示</a>
    <BR>
    <a href="./keiyaku2.php?uid=<?php echo $row['uid']; ?>&oid=<?php echo $row['oid']; ?>">契約書(更新用)表示</a>
  </td>
  <td>
    <a href="./order.php?uid=<?php echo $row['uid']; ?>&oid=<?php echo $row['oid']; ?>">【オーダー】修正・変更</a>|<a href="javascript:void(0);" onclick="MoveCheck('orderdelete.php?uid=<?php echo $row['uid']; ?>&oid=<?php echo $row['oid']; ?>')">削除</a>
    <BR>
    <a href="./order.php?uid=<?php echo $row['uid']; ?>&oid=<?php echo $row['oid']; ?>&refresh_contract=true">【オーダー】契約更新（期間満了時）</a>
    <BR>

    <a href="./user.php?uid=<?php echo $row['uid']; ?>">【顧客】情報変更</a>
  </td>
<?php
}

echo "</tbody></table>";
}
?>
