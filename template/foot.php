

<?php
//メニューへのボタンをメニュー以外で出す
$url = $_SERVER['REQUEST_URI'];
 ?>
<?php if($url == "/works/index.php"){ ?>


<?php }else{ ?>
  <p>
  <a href="./index.php">メニューに戻る</a>
  </p>
<?php } ?>

</body>
</html>
