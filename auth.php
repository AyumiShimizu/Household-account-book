<?php

//=====================================
// ログイン認証・自動ログアウト
//=====================================
// ログインしている場合
if(!empty($_SESSION['login_data'])){
  debug('ログイン済みユーザーです');

  // 現在日時が最終ログイン日時＋有効期限を超えていた場合
  if($_SESSION['login_data'] + $_SESSION['login_limit'] < time()){
    debug('ログイン有効期限オーバーです。');

    // セッションを削除
    session_destroy();
    // ログインページへ遷移
    header("Location:login.php");
  }else{
    debug('ログイン有効期限内です。');
    // 最終ログイン日時を現在日時に更新
    $_SESSION['login_data'] = time();

    // 最終ログイン日時をDBに保存
    try{
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users SET login_data = :login_data WHERE `user_id` = :u_id';
      $data = array(':login_data' => date('Y-m-d H:i:s'), ':u_id' => $_SESSION['user_id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if($stmt){
//        debug('クエリ成功。');
      }
    }catch(Exception $e){
      error_log('エラー発生。auth.php@$stmt:'.$e->getMessage());
    }

    //　現在実行中のスクリプトファイルがlogin.phpの場合はトップページへ遷移する
    if(basename($_SERVER['PHP_SELF']) === 'login.php'){
      header("Location:index.php");
    }
  }
}else{
  debug('未ログインユーザーです。');
  if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
    // ログインページへ遷移
    header("Location:login.php");
  }
}