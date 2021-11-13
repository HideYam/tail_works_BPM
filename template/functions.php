<?php

/**
* ダイジェスト認証をかける
*
* @param array $auth_list ユーザー情報(複数ユーザー可) array("ユーザ名" => "パスワード") の形式
* @param string $realm レルム文字列
* @param string $failed_text 認証失敗時のエラーメッセージ
*/
function digest_auth($auth_list,$realm="Restricted Area",$failed_text="認証に失敗しました"){
    if (!$_SERVER['PHP_AUTH_DIGEST']){
        $headers = getallheaders();
        if ($headers['Authorization']){
            $_SERVER['PHP_AUTH_DIGEST'] = $headers['Authorization'];
        }
    }

    if ($_SERVER['PHP_AUTH_DIGEST']){
        // PHP_AUTH_DIGEST 変数を精査する
        // データが失われている場合への対応
        $needed_parts = array(
                    'nonce' => true,
                    'nc' => true,
                    'cnonce' => true,
                    'qop' => true,
                    'username' => true,
                    'uri' => true,
                    'response' => true
                    );
        $data = array();

        $matches = array();
        preg_match_all('/(\w+)=("([^"]+)"|([a-zA-Z0-9=.\/\_-]+))/',$_SERVER['PHP_AUTH_DIGEST'],$matches,PREG_SET_ORDER);

        foreach ($matches as $m){
            if ($m[3]){
                $data[$m[1]] = $m[3];
            }else{
                $data[$m[1]] = $m[4];
            }
            unset($needed_parts[$m[1]]);
        }

        if ($needed_parts){
            $data = array();
        }

        if ($auth_list[$data['username']]){
            // 有効なレスポンスを生成する
            $A1 = md5($data['username'].':'.$realm.':'.$auth_list[$data['username']]);
            $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
            $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

            if ($data['response'] != $valid_response){
                unset($_SERVER['PHP_AUTH_DIGEST']);
            }else{
                return $data['username'];
            }
        }
    }

    //認証データが送信されているか
    header('HTTP/1.1 401 Authorization Required');
    header('WWW-Authenticate: Digest realm="'.$realm.'", nonce="'.uniqid(rand(),true).'", algorithm=MD5, qop="auth"');
    header('Content-type: text/html; charset='.mb_internal_encoding());

    die($failed_text);
}
?>
