<?php

// 共通変数・関数ファイルの読込
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行認証キー入力ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証は必要ない

// SESSIONに認証キーがあるか確認、なければ認証キー送信ページへ遷移
if(empty($_SESSION)){
  header('Location:passRemindSend.php');
}

//=====================================
// 定数
//=====================================
// ページ固有メッセージを定数に設定
define('MSG21', '認証キーが違うか、有効期限が切れています。');

//=====================================
// 画面処理
//=====================================
// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
//  debug('POST送信:'.print_r($_POST, true));

  // 変数に認証キーを代入
  $auth_key = $_POST['token'];

  // 未入力チェック
  validRequired($auth_key, 'token');

  if(empty($err_msg)){
    debug('未入力チェックOK。');

    // 固定長チェック
    validLength($auth_key, 'token', $len = 10);
    // 半角チェック
    validHalf($auth_key, 'token');

    if(empty($err_msg)){
      debug('バリデーションチェックOK。');

      if($auth_key !== $_SESSION['auth_key']){
        $err_msg['common'] = MSG21;
      }
      if(time() > $_SESSION['auth_key_limit']){
        $err_msg['common'] = MSG21;
      }

      if(empty($err_msg)){
        debug('認証OK。');

        // パスワード生成
        $pass = makeRanKey();

        // 例外処理
        try {
          // DBへ接続
          $dbh = dbConnect();
          // SQL文作成
          $sql = 'UPDATE users SET pass = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
          // クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          // クエリ成功の場合
          if($stmt){
            debug('クエリ成功。');

            // メール送信
            $from = 'household-account-book@ayumis.sakura.ne.jp';
            $to = $_SESSION['auth_email'];
            $subject = '【新しいパスワードの発行が完了しました】｜Household account book';
            $comment = <<<EOT

新しいパスワードの発行が完了いたしました。
下記URLよりこのメールに記載されていますパスワードをご入力いただき、ログインをお願いいたします。

ログインページ：http://ayumis.sakura.ne.jp/login.php
発行された新しいパスワード：{$pass}

なお、ログイン後にパスワード変更ページより、パスワードの変更をお願いいたします。

/////////////////////////////////////////////////////////////////////
Househols account book
URL：http://ayumis.sakura.ne.jp/index.php
E-mail：household-account-book@ayumis.sakura.ne.jp
/////////////////////////////////////////////////////////////////////
EOT;
            sendMail($from, $to, $subject, $comment);

            // セッションを削除
            session_unset();

            header("Location:login.php");

          }else{
            debug('クエリに失敗しました。');
            $err_msg['common'] = MSG01;
          }

        } catch (Exception $e) {
          error_log('エラー発生:'.$e->getMessage());
          $err_msg['common'] = MSG01;
        }
      }
    }
  }
}
?>
<?php
  $siteTitle = 'パスワード再発行認証キー入力';
  require('head.php');
?>
<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="main" class="site-width">

    <!-- 一覧 -->
    <section class="page-login">
      <h2 class="page-1colum-title"><?php echo $siteTitle; ?></h2>

      <form action="" method="post">

        <div class="msg-common-area <?php echo setClassErr('commono'); ?>"><?php echo getErrMsg('common'); ?></div>

        <label for="token">お送りしました認証キーを入力してください</label>
        <input type="password" name="token" value="<?php echo getFormData('token'); ?>" id="auth_key">
        <div class="msg-area <?php echo setClassErr('token'); ?>"><?php echo getErrMsg('token'); ?></div>

        <input type="submit" class="btn btn-regist" value="送信する">

        <p class="page-link"><a href="passRemindSend.php">パスワード再発行メール画面はコチラ</a></p>

      </form>

    </section>



  </div>
</div>

<!-- footer -->
<?php
  require('footer.php');
