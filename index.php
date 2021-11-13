<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/head.php'); ?>

<ul>
  <li>
  <b>既存顧客/既存オーダーを探す</b>
  <form id="searchorder">
    <input type="hidden" name="search" value="true">
    <label>
      <input type="name" name="name" value="" placeholder="名前" id="searchorder_name">
    </label>
    <label>
      <input type="email" name="email" autocomplete="email" value="" placeholder="メールアドレス">
    </label>
    <label>
      <input type="tel" name="tel" value="" placeholder="電話番号">
    </label>

    <button class="submit" data-action="searchorder.php">オーダーを検索</button>
    <button class="submit" data-action="searchuser.php">顧客リストを検索</button>

  </form>
  </li>
  <li>
    <b>新規顧客を登録</b>
    <form action="user.php" name="user" method="POST">
      <button class="submit" data-action="user.php">新規登録（顧客登録→オーダー登録）</button>
    </form>
  </li>

  <li>
    <B>配達予定</b>
      <?php if(isset($_REQUEST['limitrows'])){$limitrows = $_REQUEST['limitrows'];} ?>
      <?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/deliv_list.php'); ?>

  </li>


  <li>
    <B>日別予定検索（配達予定・必要台数・引取予定）</b>

  <form action="orderdaily.php" name="orderdaily" method="POST">
    <input type="text" id="datepicker" name="date" value="">
    <input type="submit" value="オーダー 日別集計">
  </form>
  </li>

  <li>
  <B>データ閲覧・ダウンロード</b>
    <br>
  <a href="unpaid_view.php">料金未収顧客の一覧</a> | <a href="csv_all.php">契約全員ダウンロード（CSV）</a>
  </li>

</ul>


<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/foot.php'); ?>
