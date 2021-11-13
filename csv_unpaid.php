<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/functions.php'); ?>
<?php
digest_auth(array(
        "hideki" => "hide0717",
        "shiho" => "Shippoda0",
        "works" => "chiba2018"
        ));
?>
<?php

UnpaidUserCsv();


function UnpaidUserCsv() {

	try {

		//CSV形式で情報をファイルに出力のための準備
		$csvFileName = '/home/www/html/works/files/' . time() . rand() . '.csv';
		$res = fopen($csvFileName, 'w');
		if ($res === FALSE) {
			throw new Exception('ファイルの書き込みに失敗しました。');
		}

		// データ
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

		$result_header = mysqli_query($con ,'SELECT "名前","電話番号","価格（税抜）","email","郵便番号","住所","所属","所属備考","契約日","契約開始日","契約終了日","備考"');
		while($row_header = mysqli_fetch_assoc($result_header)){
			$rows_header[] = $row_header;
		}

		$result = mysqli_query($con ,'select b.name as 名前,b.tel as 電話番号,c.order_price,b.email as email,b.zip as 郵便番号,b.address as 住所,b.belonging as 所属,b.belonging2 as 所属備考,a.contract_date as 契約日,a.contract_start as 契約開始日,a.contract_end as 契約終了日,a.etc as 備考 from t_order a left join t_user b on a.uid=b.uid left join (select oid,sum(order_price*amount) as order_price from t_order_detail where amount>0 group by oid) c on c.oid=a.oid where a.payflag = 1 and b.uid is not null  order by a.uid;');
		if (mysqli_num_rows($result) <= 0) {
			exit('データが存在しません。');
		}
		while($row = mysqli_fetch_assoc($result)){
			$rows[] = $row;
		}

		$rows_w = array_merge($rows_header,$rows);

		// ループしながら出力
		foreach($rows_w as $dataInfo) {

			// 文字コード変換。エクセルで開けるようにする
			mb_convert_variables('SJIS', 'UTF-8', $dataInfo);

			// ファイルに書き出しをする
			fputcsv($res, $dataInfo);
		}

		// ハンドル閉じる
		fclose($res);

		// ダウンロード開始
		header('Content-Type: application/octet-stream');

		// ここで渡されるファイルがダウンロード時のファイル名になる
		header('Content-Disposition: attachment; filename=unpaid.csv');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($csvFileName));
		readfile($csvFileName);

		//print_r($rows);


	} catch(Exception $e) {

		// 例外処理をここに書きます
		echo $e->getMessage();


	}
}
?>
