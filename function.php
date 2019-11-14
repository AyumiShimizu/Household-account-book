<?php

//=====================================
// エラー表示
//=====================================
// ログを取るか
ini_set('log_errors', 'on');
// ログの出力ファイルを指定
ini_set('error_log', 'php.log');
//ログをディスプレイに表示する
ini_set('display_errors', 'off');
//全てのエラーを出力する
error_reporting(E_ALL);

//=====================================
// 時間管理
//=====================================
// タイムゾーンを東京に指定
date_default_timezone_set("Asia/Tokyo");
//=====================================
// デバッグ
//=====================================
// !デバッグフラグ(デバッグをするか)
$debug_flg = true;
// デバッグログ関数
function debug($str)
{
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバッグ：' . $str);
  }
}

//====================================
// セッション準備・セッション有効期限を延ばす
//====================================
// !セッションファイルの置き場を変更する(/var/tmp/以下に置くと30日は削除されない)
session_save_path("/var/tmp/");
// !ガーベージコレクションが削除するセッションの有効期限を設定(30日以上経っているものに対してだけ100分の1の確率で消去)
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
// !ブラウザを閉じても削除されないようにCookie自体の有効期限を伸ばす
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
// !セッションを使う
session_start();
// !現在のセッションIDを新しく生成したものと置き換える(なりすましのセキュリティ対策)
session_regenerate_id();

//====================================
// 画面表示処理開始ログ
//====================================
function debugLogStart()
{
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理開始');
  debug('現在日時タイムスタンプ：' . time());
  if (!empty($_SESSION['login_data']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ：' . ($_SESSION['login_data'] + $_SESSION['login_limit']));
  }
}
//====================================
// 定数
//====================================
define('MSG01', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG02', '入力必須です。');
define('MSG03', 'Emailの形式ではありません。');
define('MSG04', 'このEmailアドレスは登録出来ません');
define('MSG05', '文字以内で入力してください。');
define('MSG06', '文字以上で入力してください。');
define('MSG07', 'パスワード再入力が合っていません。');
define('MSG08', '半角英数字で入力してください。');
define('MSG09', 'Emailもしくはパスワードが違います。');
define('MSG10', '郵便番号の形式ではありません。');
define('MSG11', '電話番号の形式ではありません。');
define('MSG12', '古いパスワードが違います。');
define('MSG13', '古いパスワードと同じです。');
define('MSG14', '文字で入力してください。');
define('MSG15', '選択してください。');


//====================================
// グローバル変数
//====================================
// エラーメッセージ格納用の配列
$err_msg = array();

//====================================
// バリデーションチェック関数
//====================================
// 未入力チェック
function validRequired($str, $key)
{
  if ($str === '') {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

// Emailバリデーション関数
function validEmail($str, $key)
{
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

// Email重複チェック後メール送信
function validEmailDup($email){
  global $err_msg;
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // 入力したアドレスがすでに登録されていた場合
    if ($stmt && (array_shift($result))) {
//      debug('クエリ成功。DB登録あり。:' . print_r(array($result), true));
      // ログインページとパスワード再設定用キーを表示した上でパスワード再設定ページを添付

      // 認証キーを作成
      $auth_key = makeRanKey();

      // メール送信
      $from = 'household-account-book@ayumis.sakura.ne.jp';
      $to = $email;
      $subject = '【ご入力いただいたメールアドレスについて】｜Household account book';
      $comment = <<<EOT
本メールアドレス宛に新規登録のご依頼がありましたが、ご入力されたメールアドレスはすでにご登録が完了しております。

パスワードをお忘れの場合は、下記URLより記載させていただきました認証キーをご入力頂くとパスワードが再発行されます。
パスワード再発行認証キー入力ページ：http://ayumis.sakura.ne.jp/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります。

パスワードを覚えていらっしゃる場合は、下記URLのログインページよりログインをしてください。
ログインページ：http://ayumis.sakura.ne.jp/login.php

認証キーを再発行されたい場合は下記ページより再度、再発行のお手続きをお願い致します。
http://ayumis.sakura.ne.jp/passRemindSend.php

/////////////////////////////////////////////////////////////////////
Household account book
URL：http://ayumis.sakura.ne.jp/index.php
E-mail：household-account-book@ayumis.sakura.ne.jp
/////////////////////////////////////////////////////////////////////
EOT;
      sendMail($from, $to, $subject, $comment);

      // 認証に必要な情報をセッションへ保存
      $_SESSION['auth_key'] = $auth_key;
      $_SESSION['auth_email'] = $email;
      $_SESSION['auth_key_limit'] = time() + (60 * 30); //現在時刻より30分後にUNIXタイムスタンプを入れる
      header("Location:passRemindRecieve.php"); //パスワード再設定認証キー入力ページへ遷移

    } elseif ($stmt && empty(array_shift($result))) {

      // メール送信
      $from = 'household-account-book@ayumis.sakura.ne.jp';
      $to = $email;
      $subject = '【新規登録用ページのお知らせ】｜Household account book';
      $comment = <<<EOT
本メールアドレス宛に新規登録のご依頼がありましたので、お知らせ致します。
下記URLより登録の完了をお願い致します。

新規登録ページ：http://ayumis.sakura.ne.jp/signup.php

なお、お知らせいただきましたメールアドレスでの登録可能時間は1時間となります。
1時間を過ぎてしまった場合は、再度、新規登録申請ページよりメールアドレスをご入力の上、申請をお願い致します。
新規登録申請メール送信ページ：http://ayumis.sakura.ne.jp/signupMailSend.php

/////////////////////////////////////////////////////////////////////
Household account book
URL：http://ayumis.sakura.ne.jp/index.php
E-mail：household-account-book@ayumis.sakura.ne.jp
/////////////////////////////////////////////////////////////////////
EOT;
      sendMail($from, $to, $subject, $comment);

      // 登録に必要な情報をセッションへ保存
      $_SESSION['auth_email'] = $email;
      $_SESSION['auth_limit'] = time() + (60 * 60);
      header("Location:signupMailComplete.php"); //メール送信完了ページへ遷移
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}

// 最大文字数チェック
function validMaxLen($str, $key, $max = 256){
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = $max . MSG04;
  }
}
// 最小文字数チェック
function validMinLen($str, $key, $min = 6){
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = $min . MSG05;
  }
}
// 同値チェック
function validMatch($str1, $str2, $key){
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg['$key'] = MSG07;
  }
}
//半角文字チェック
function validHalf($str, $key){
  if (!preg_match("/^[!-)+->@-~]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG08;
  }
}
// パスワードチェック
function validPass($str, $key){
  // 半角文字チェック
  validHalf($str, $key);
  // 最大文字数チェック
  validMaxLen($str, $key);
  // 最小文字数チェック
  validMinLen($str, $key);
}
// 郵便番号形式チェック
function validZip($str, $key){
  // 半角数字のみで7桁か判定
  if (!preg_match("/^([0-9]{7})$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}
// 電話番号形式チェック
function validTel($str, $key){
  // 桁数が10桁か11桁か判定
  if (!preg_match("/^(0{1}\d{9,10})$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG11;
  }
}
// 固定長チェック
function validLength($str, $key, $len = 10){
  if (mb_strlen($str) !== $len) {
    global $err_msg;
    $err_msg[$key] = $len . MSG14;
  }
}
// selectboxチェック
function validSelect($str, $key){
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}
// エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if (!empty($err_msg[$key])) {
    return $err_msg[$key];
  }
}
// エラーメッセージ表示のclass名追加
function setClassErr($key){
  global $err_msg;
  if (!empty($err_msg[$key])) {
    return 'err';
  }
}
//====================================
// データベース
//====================================
// DB接続関数
function dbConnect(){
  // DBへの接続準備
  // 記載情報はローカル環境時の情報
  $dsn = 'mysql:dbname=サーバー名;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    // SQL実行失敗時にはエラーコードを表示
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成(DBへ接続)
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}
// クエリ実行関数
function queryPost($dbh, $sql, $data){
  // クエリ作成
  $stmt = $dbh->prepare($sql);
  // プレースホルダに値をセットし、SQL文を実行
  if (!$stmt->execute($data)) {
    debug('クエリに失敗しました。');
    global $err_msg;
    $err_msg['common'] = MSG01;
    return 0;
  }
  return $stmt;
}
// ユーザー情報取得関数
function getUser($user_id){
//  debug('ユーザー情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT username, email, pass, zip, addr_pref, addr_city, addr_num, tel, birth FROM users WHERE `user_id` = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $user_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果のデータを1レコード返却
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
// メインカテゴリー情報取得関数
function getMainCategory(){
//  debug('メインカテゴリー情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT mainc_id, mainc_name, deposit_flg FROM main_cate WHERE delete_flg = 0';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生' . $e->getMessage());
  }
}
// サブカテゴリー情報取得関数
function getSubCategory($user_id)
{
//  debug('サブカテゴリー情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT subc_id, subc_name, mainc_id FROM sub_cate WHERE `user_id` = :user_id AND delete_flg = 0';
    $data = array(':user_id' => $user_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
// 全口座情報取得関数
function getBankAll($user_id)
{
//  debug('全口座情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT bank_id, bank_name, current_price FROM bank WHERE `user_id` = :user_id AND delete_flg = 0';
    $data = array(':user_id' => $user_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = MSG01;
  }
}
// 個別口座情報取得関数
function getBankOne($user_id, $bank_id)
{
//  debug('指定口座情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT bank_id, bank_name, current_price FROM bank WHERE `user_id` = :user_id AND bank_id = :bank_id AND delete_flg = 0';
    $data = array(':user_id' => $user_id, ':bank_id' => $bank_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = MSG01;
  }
}
// 収支伝票情報取得関数(伝票指定)
function getDepositData($user_id, $deposit_id)
{
//  debug('指定した収支伝票情報を習得します。');
//  debug('ユーザーID：' . print_r($user_id, true));
//  debug('収支伝票ID：' . print_r($deposit_id, true));
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT deposit_id, deposit_flg, price, bank_id, mainc_id, subc_id, comment, regist_data FROM deposit WHERE `user_id` = :user_id AND deposit_id = :deposit_id AND delete_flg = 0';
    $data = array(':user_id' => $user_id, ':deposit_id' => $deposit_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のデータを1レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}
// 収支伝票情報取得関数(全伝票)
function getDepositDataAll($user_id)
{
//  debug('全ての収支伝票情報を習得します。');
//  debug('ユーザーID：' . print_r($user_id, true));
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT deposit_id, deposit_flg, price, mainc_id, regist_data FROM deposit WHERE `user_id` = :user_id AND delete_flg = 0';
    $data = array(':user_id' => $user_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のデータを全レコード返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}
// 収支伝票情報取得関数(期間指定)
function getDepositPeriodData($user_id, $firstDay, $lastDay)
{
  debug($firstDay . 'から' . $lastDay . '間の収入伝票情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT deposit_id, deposit_flg, price, mainc_id, subc_id, regist_data FROM deposit WHERE `user_id` = :user_id AND delete_flg = 0 AND regist_data BETWEEN :startday AND :lastday';
    $data = array(':user_id' => $user_id, ':startday' => $firstDay, ':lastday' => $lastDay);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のdataを全レコード返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}
// 収入伝票期間指定合計取得関数
function getPeriodInTotal($user_id, $firstDay, $lastDay)
{
  debug($firstDay . 'から' . $lastDay . '間の収入伝票合計情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT SUM(price) AS total_price FROM deposit WHERE `user_id` = :user_id AND delete_flg =  0 AND deposit_flg = 1 AND regist_data BETWEEN :startday AND :lastday';
    $data = array(':user_id' => $user_id, ':startday' => $firstDay, ':lastday' => $lastDay);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のdataを全レコード返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}
// 収入伝票情報サブカテゴリ合計期間指定取得関数
function getDepositScateInTotal($user_id, $firstDay, $lastDay)
{
//  debug('サブカテゴリ毎の収入伝票情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    // 収入データの指定期間の合計をサブカテゴリ毎に集計して取得
    $sql = 'SELECT s.subc_id, s.subc_name, s.mainc_id, m.mainc_id, m.mainc_name, IFNULL(d.subc_id, s.subc_id), SUM(d.price) AS price_total FROM sub_cate AS s LEFT OUTER JOIN main_cate AS m ON s.mainc_id = m.mainc_id LEFT OUTER JOIN deposit AS d ON s.subc_id = d.subc_id AND d.delete_flg = 0 AND d.regist_data BETWEEN :startday AND :lastday WHERE s.user_id = :user_id AND s.delete_flg = 0 AND m.deposit_flg = 1 GROUP BY s.subc_id, s.mainc_id ORDER BY s.mainc_id ASC';
    $data = array(':user_id' => $user_id, ':startday' => $firstDay, ':lastday' => $lastDay);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のdataを全レコード返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}
// 支出伝票期間指定合計取得関数
function getPeriodOutTotal($user_id, $firstDay, $lastDay)
{
  debug($firstDay . 'から' . $lastDay . '間の支出伝票合計情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT SUM(price) AS total_price FROM deposit WHERE `user_id` = :user_id AND delete_flg =  0 AND deposit_flg = 0 AND regist_data BETWEEN :startday AND :lastday';
    $data = array(':user_id' => $user_id, ':startday' => $firstDay, ':lastday' => $lastDay);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のdataを全レコード返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}

// 支出伝票情報サブカテゴリ合計期間指定取得関数
function getDepositScateOutTotal($user_id, $firstDay, $lastDay)
{
//  debug('サブカテゴリ毎の支出伝票情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    // 支出データの指定期間の合計をサブカテゴリ毎に集計して取得
    $sql = 'SELECT s.subc_id, s.subc_name, s.mainc_id, m.mainc_id, m.mainc_name, IFNULL(d.subc_id, s.subc_id), SUM(d.price) AS price_total FROM sub_cate AS s LEFT OUTER JOIN main_cate AS m ON s.mainc_id = m.mainc_id LEFT OUTER JOIN deposit AS d ON s.subc_id = d.subc_id AND d.delete_flg = 0 AND d.regist_data BETWEEN :startday AND :lastday WHERE s.user_id = :user_id AND s.delete_flg = 0 AND m.deposit_flg = 0 GROUP BY s.subc_id, s.mainc_id ORDER BY s.mainc_id ASC';
    $data = array(':user_id' => $user_id, ':startday' => $firstDay, ':lastday' => $lastDay);

    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のdataを全レコード返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}
// 振替伝票情報取得関数
function getTransDataOne($user_id, $trans_id)
{
//  debug('指定振替伝票情報を習得します。');
//  debug('ユーザーID：' . print_r($user_id, true));
//  debug('振替伝票ID：' . print_r($trans_id, true));
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT trans_id, price, inbank_id, outbank_id, comment, regist_data FROM trans WHERE `user_id` = :user_id AND trans_id = :trans_id AND delete_flg = 0';
    $data = array(':user_id' => $user_id, ':trans_id' => $trans_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のデータを1レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}
// 振替伝票情報取得関数(全伝票)
function getTransDataAll($user_id)
{
//  debug('振替伝票情報を全て習得します。');
//  debug('ユーザーID：' . print_r($user_id, true));
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT trans_id, price, regist_data FROM trans WHERE `user_id` = :user_id AND delete_flg = 0';
    $data = array(':user_id' => $user_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のデータを全レコード返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
// 振替伝票情報今月分取得関数
function getTransPeriodData($user_id, $firstDay, $lastDay)
{
  debug($firstDay . 'から' . $lastDay . '間の振替データを取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT trans_id, price, regist_data FROM trans WHERE `user_id` = :user_id AND delete_flg = 0 AND regist_data BETWEEN :startday AND :lastday';
    $data = array(':user_id' => $user_id, ':startday' => $firstDay, ':lastday' => $lastDay);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のデータを全レコード返却
      return $stmt->fetchAll();
    } else {
      return false;
      global $err_msg;
      $err_msg['common'] = MSG01;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}
// 



//=====================================
// メール送信
//=====================================
function sendMail($from, $to, $subject, $comment)
{
  if (!empty($to) && !empty($subject) && !empty($comment)) {
    //文字化けしないように設定
    mb_language("Japanese"); //現在使っている言語
    mb_internal_encoding("UTF-8"); //内部の日本語のエンコーディング

    // メールを送信
    $result = mb_send_mail($to, $subject, $comment, "From: " . $from);
    // 送信結果の判定
    if ($result) {
      debug('メールを送信しました。');
    } else {
      debug('【エラー発生】メールの送信に失敗しました。');
    }
  }
}

//=====================================
// その他
//=====================================
// サニタイズ
function sanitize($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}
// フォーム入力保持
function getFormData($str, $flg = false)
{
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $dbFormData;
  global $err_msg;
  // ユーザーデータがある場合
  if (!empty($dbFormData)) {
    // フォームにエラーがある場合
    if (!empty($err_msg[$str])) {
      // POSTにデータがある場合
      if (isset($method[$str])) {
        return sanitize($method[$str]);
      } else {
        // ない場合
        return sanitize(($dbFormData[$str]));
      }
    } else {
      // POSTにデータが有り、DBの情報と違う場合
      if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if (isset($method[$str])) {
      return sanitize($method[$str]);
    }
  }
}

// 認証キー、パスワード生成
function makeRanKey($length = 10)
{
  static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $length; ++$i) {
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}

// GETパラメータ付与
function appendGetParam($arr_del_key = array())
{
  if (!empty($_GET)) {
    $str = '?';
    foreach ($_GET as $key => $val) {
      if (!in_array($key, $arr_del_key, true)) {
        $str .= $key . '=' . $val . '&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}

// 編集ページへのリンク生成
function setLink($val)
{
  // 収支データの場合
  if (isset($val['deposit_id'])) {
    // 支出データの場合
    if ($val['deposit_flg'] == 0) {
      if (!empty(appendGetParam())) {
        return appendGetParam() . '&s_id=' . $val['deposit_id'] . '&sort=' . $val['deposit_flg'];
      } else {
        return '?s_id=' . $val['deposit_id'] . '&sort=' . $val['deposit_flg'];
      }
      // 収入データの場合
    } elseif ($val['deposit_flg'] == 1) {
      if (!empty(appendGetParam())) {
        return appendGetParam() . '&s_id=' . $val['deposit_id'] . '&sort=' . $val['deposit_flg'];
      } else {
        return '?s_id=' . $val['deposit_id'] . '&sort=' . $val['deposit_flg'];
      }
    }
    // 振替データの場合
  } elseif (isset($val['trans_id'])) {
    if (!empty(appendGetParam())) {
      return appendGetParam() . '&s_id=' . $val['trans_id'] . '&sort=2';
    } else {
      return '?s_id=' . $val['trans_id'] . '&sort=2';
    }
  }
}

// メインカテゴリ名表示
function getMcateName($key)
{
  // メインカテゴリ情報を取得
  $dbMainCategory = getMainCategory();
  // メインカテゴリ情報を検索
  $mCateData = array_search($key, array_column($dbMainCategory, 'mainc_id'));
  $mCate = $dbMainCategory[$mCateData];
  if (!isset($key)) {
    return '振替';
  } else {
    return $mCate['mainc_name'];
  }
}
// サブカテゴリ名表示
function getScateName($key)
{
  // サブカテゴリ情報を習得
  $dbSubCategory = getSubCategory($_SESSION['user_id']);
  // サブカテゴリ情報を検索
  $sCateData = array_search($key, array_column($dbSubCategory, 'subc_id'));
  $sCate = $dbSubCategory[$sCateData];
  if (!isset($key)) {
    return false;
  } else {
    return $sCate['subc_name'];
  }
}

// クラス名呼び出し関数
function setClassName($key)
{
  if (isset($key)) {
    if ($key == 0) {
      return 'item-out';
    } elseif ($key == 1) {
      return 'item-in';
    }
  } else {
    return 'item-tra';
  }
}
