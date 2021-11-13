<?php
/** address2zip.php
 * 住所から郵便番号を求める（PHP5対応）
 *
 * @copyright	(c)studio pahoo
 * @author		パパぱふぅ
 * @参考URL		http://www.pahoo.org/e-soul/webtech/php06/php06-36-01.shtm
 *
*/
// 初期化処理 ================================================================
define('INTERNAL_ENCODING', 'UTF-8');
mb_internal_encoding(INTERNAL_ENCODING);
mb_regex_encoding(INTERNAL_ENCODING);
define('MYSELF', basename($_SERVER['SCRIPT_NAME']));
define('REFERENCE', 'http://www.pahoo.org/e-soul/webtech/php06/php06-36-01.shtm');

//プログラム・タイトル
define('TITLE', '住所から郵便番号を求める');

//リリース・フラグ（公開時にはTRUEにすること）
define('FLAG_RELEASE', FALSE);

/**
 * 共通HTMLヘッダ
 * @global string $HtmlHeader
*/
$encode = INTERNAL_ENCODING;
$title = TITLE;
$HtmlHeader =<<< EOT
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="{$encode}">
<title>{$title}</title>
<meta name="author" content="studio pahoo" />
<meta name="copyright" content="studio pahoo" />
<meta name="ROBOTS" content="NOINDEX,NOFOLLOW" />
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<script type="text/javascript">
<!--
function exec(mode) {
	document.myform.mode.value = mode;
	document.myform.submit();
}
-->
</script>
</head>

EOT;

/**
 * 共通HTMLフッタ
 * @global string $HtmlFooter
*/
$HtmlFooter =<<< EOT
</html>

EOT;

// サブルーチン ==============================================================
/**
 * エラー処理ハンドラ
*/
function myErrorHandler ($errno, $errmsg, $filename, $linenum, $vars) {
	echo 'Sory, system error occured !';
	exit(1);
}
error_reporting(E_ALL);
if (FLAG_RELEASE)	$old_error_handler = set_error_handler('myErrorHandler');

/**
 * PHP5以上かどうか検査する
 * @return	bool TRUE：PHP5以上／FALSE:PHP5未満
*/
function isphp5over() {
	$version = explode('.', phpversion());

	return $version[0] >= 5 ? TRUE : FALSE;
}

/**
 * 指定したパラメータを取り出す
 * @param	string $key  パラメータ名（省略不可）
 * @param	bool   $auto TRUE＝自動コード変換あり／FALSE＝なし（省略時：TRUE）
 * @param	mixed  $def  初期値（省略時：空文字）
 * @return	string パラメータ／NULL＝パラメータ無し
*/
function getParam($key, $auto=TRUE, $def='') {
	if (isset($_GET[$key]))			$param = $_GET[$key];
	else if (isset($_POST[$key]))	$param = $_POST[$key];
	else							$param = $def;
	if ($auto)	$param = mb_convert_encoding($param, INTERNAL_ENCODING, 'auto');
	return $param;
}

/**
 * 都道府県リストを求める WebAPI URL
 * @return	string WebAPI URL / FALSE
*/
function getURL_state() {
	return 'http://api.thni.net/jzip/X0401/JSON/J/state_index.js';
}

/**
 * 都道府県リストを求める
 * @param	array  $items 情報を格納する配列
 * @return	bool TRUE/FALSE
*/
function get_state(&$items) {
	$url = getURL_state();			//リクエストURL
	if ($url == FALSE)	return FALSE;

	$json = @file_get_contents($url);
	if ($json == FALSE)	return FALSE;

	$arr = json_decode($json);
	if (count($arr) < 1)	return FALSE;

	foreach ($arr as $val) {
		$items[] = (string)$val->name;
	}

	return TRUE;
}

/**
 * 都道府県から市町村リストを求める WebAPI URL
 * @param	string $state 都道府県
 * @return	string WebAPI URL / FALSE
*/
function getURL_city($state) {
	$state = urlencode($state);
	return "http://api.thni.net/jzip/X0401/JSON/J/{$state}/city_index.js";
}

/**
 * 都道府県から市町村リストを求める
 * @param	string $state 都道府県
 * @param	array  $items 情報を格納する配列
 * @return	bool TRUE/FALSE
*/
function get_city($state, &$items) {
	$url = getURL_city($state);			//リクエストURL
	if ($url == FALSE)	return FALSE;

	$json = @file_get_contents($url);
	if ($json == FALSE)	return FALSE;

	$arr = json_decode($json);
	if (count($arr) < 1)	return FALSE;

	foreach ($arr as $val) {
		$items[] = (string)$val->name;
	}

	return TRUE;
}

/**
 * 都道府県から町域リストを求める WebAPI URL
 * @param	string $state 都道府県
 * @param	string $city  市町村
 * @return	string WebAPI URL / FALSE
*/
function getURL_street($state, $city) {
	$state = urlencode($state);
	$city  = urlencode($city);
	return "http://api.thni.net/jzip/X0401/JSON/J/{$state}/{$city}/street_index.js";
}

/**
 * 都道府県から町域リストを求める
 * @param	string $state 都道府県
 * @param	string $city  市町村
 * @param	array  $items 情報を格納する配列
 * @return	bool TRUE/FALSE
*/
function get_street($state, $city, &$items) {
	$url = getURL_street($state, $city);			//リクエストURL
	if ($url == FALSE)	return FALSE;

	$json = @file_get_contents($url);
	if ($json == FALSE)	return FALSE;

	$arr = json_decode($json);
	if (count($arr) < 1)	return FALSE;

	foreach ($arr as $val) {
		$items[] = (string)$val->name;
	}

	return TRUE;
}

/**
 * 都道府県から郵便番号を求める WebAPI URL
 * @param	string $state  都道府県
 * @param	string $city   市町村
 * @param	string $street 町域
 * @return	string WebAPI URL / FALSE
*/
function getURL_zip($state, $city, $street) {
	$state  = urlencode($state);
	$city   = urlencode($city);
	$street = urlencode($street);
	return "http://api.thni.net/jzip/X0401/JSON/J/{$state}/{$city}/{$street}.js";
}

/**
 * 都道府県から郵便番号を求める
 * @param	string $state 都道府県
 * @param	string $city  市町村
 * @param	string $street 町域
 * @return	string 郵便番号
*/
function get_zip($state, $city, $street) {
	$url = getURL_zip($state, $city, $street);		//リクエストURL
	if ($url == FALSE)	return FALSE;

	$json = @file_get_contents($url);
	if ($json == FALSE)	return FALSE;

	$arr = json_decode($json);
	if (count($arr) < 1)	return FALSE;

	return $arr->zipcode;
}

/**
 * HTML BODYを作成する
 * @param	array  $items  住所（都道府県，市町村，町域）
 * @param	string $state  選択中の都道府県
 * @param	string $city   選択中の市町村
 * @param	string $street 選択中の町域
 * @param	string $zip    郵便番号（7桁数字，ハイフン無し）
 * @param	string $url    WebAPIのURL
 * @param	string $message メッセージ
 * @return	string HTML BODY
*/
function makeCommonBody($items, $state, $city, $street, $zip, $url, $message) {
	$myself = MYSELF;
	$refere = REFERENCE;

	$p_title = TITLE;
	$version = '<span style="font-size:small;">' . date('Y/m/d版', filemtime(__FILE__)) . '</span>';

	if (! FLAG_RELEASE) {
		$phpver = phpversion();
		$msg =<<< EOT
PHPver : {$phpver}<br />
WebAPI : <a href="{$url}">{$url}</a>
<dl>

EOT;
	} else {
		$msg = '';
	}

	$outstr = '';
//--プルダウンメニュー
//--都道府県
	$outstr .=<<< EOT
<select name="state" id="state" onChange="exec('state');">

EOT;
	foreach ($items['state'] as $val) {
		$selected = ($val == $state) ? 'selected' : '';
		$outstr .=<<< EOT
<option value="{$val}" {$selected}>{$val}</option>

EOT;
	}
	$outstr .=<<< EOT
</select>

EOT;

//--市町村
	$outstr .=<<< EOT
<select name="city" id="city" onChange="exec('city');">

EOT;
	foreach ($items['city'] as $val) {
		$selected = ($val == $city) ? 'selected' : '';
		$outstr .=<<< EOT
<option value="{$val}" {$selected}>{$val}</option>

EOT;
	}
	$outstr .=<<< EOT
</select>

EOT;

//--町域
	$outstr .=<<< EOT
<select name="street" id="street" onChange="exec('street');">

EOT;
	foreach ($items['street'] as $val) {
		$selected = ($val == $street) ? 'selected' : '';
		$outstr .=<<< EOT
<option value="{$val}" {$selected}>{$val}</option>

EOT;
	}
	$outstr .=<<< EOT
</select>

EOT;

	$body =<<< EOT
<body>
<h2>{$p_title} {$version}</h2>
<form name="myform" method="POST" action="{$myself}" enctype="multipart/form-data">
{$outstr}
<br />
郵便番号：
<input tyle="text" name="zip" id="zip" size="10" value="{$zip}" />
<input type="hidden" name="mode" id="mode" />
</form>
{$message}

</body>

EOT;
	return $body;
}

// メイン・プログラム =======================================================
$state  = getParam('state', TRUE, '');
$city   = getParam('city', TRUE, '');
$street = getParam('street', TRUE, '');
$zip    = getParam('zip', FALSE, '');
$mode   = getParam('mode', FALSE, '');

switch ($mode) {
	case 'state':
		$city = '';
		$street = '';
		break;
	case 'city':
		$street = '';
		break;
}

$url = '';
$message = '';
$items = array();
$items['state']  = array('');
$items['city']   = array('');
$items['street'] = array('');

if (isphp5over()) {
	$url = getURL_state();
	$res = get_state($items['state']);
	if (! $res) {
		$message = 'error > 都道府県が検索できません．';
	} else if ($state != '') {
		$url = getURL_city($state);
		$res = get_city($state, $items['city']);
		if (! $res) {
			$message = 'error > この都道府県では検索できません．';
		} else if ($city != '') {
			$url = getURL_street($state, $city);
			$res = get_street($state, $city, $items['street']);
			if (! $res) {
				$message = 'error > この市町村では検索できません．';
			} else if ($street != '') {
				$url = getURL_zip($state, $city, $street);
				$zip = get_zip($state, $city, $street);
				if (! $res) {
					$message = 'error > 郵便番号が検索できません．';
				}
			}
		}
	}
} else {
	$message = '<p style="color:red">error > 実行には PHP5 以上が必要です．</p>';
}

$HtmlBody = makeCommonBody($items, $state, $city, $street, $zip, $url, $message);

// 表示処理
echo $HtmlHeader;
echo $HtmlBody;
echo $HtmlFooter;

/*
** バージョンアップ履歴 ===================================================
 *
 * @version  1.1  2017/03/15  PHP7対応
 * @version  1.0  2014/08/05
*/
?>
