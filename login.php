<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//=====================================
// ログイン画面処理
//=====================================
// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信がありました。');
//  debug('POST情報:'.print_r($_POST, true));

  // 変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  // ログイン保持にチェックがあるか
  if(!empty($_POST['pass_save'])){
    $pass_save = true;
  }else{
    $pass_save = false;
  }
  
  // Emailの形式と最大文字数をチェック
  validEmail($email, 'email');
  validMaxLen($email, 'email');

  // パスワードチェック
  validPass($pass, 'pass');

  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');

  if(empty($err_msg)){
    debug('バリデーションOKです。');

    // 例外処理
    try{
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'SELECT pass, `user_id` FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(':email' => $email);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      // クエリ結果の値を取得
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      // パスワード照合
      if(!empty($result) && password_verify($pass, array_shift($result))){
        debug('パスワードがマッチしました。');

        // ログイン有効期限の書換(デフォルトを１時間とする)
        $sesLimit = 60*60;
        // 最終ログイン日時を現在日時に変更
        $_SESSION['login_data'] = time();

        // ログイン保持にチェックがある場合
        if($pass_save){
          debug('ログイン保持にチェックがあります。');
          // ログイン有効期限を30日に書換
          $_SESSION['login_limit'] = $sesLimit*24*30;
        }else{
          debug('ログイン保持にチェックはありません。');
          // ログイン有効期限をデフォルトのまま設定
          $_SESSION['login_limit'] = $sesLimit;
        }
        // ユーザーIDを格納
        $_SESSION['user_id'] = $result['user_id'];

        // DBのログイン日時を更新
        try {
          // DBへ接続
          $dnh = dbConnect();
          // SQL文作成
          $sql = 'UPDATE users SET login_data = :login_data WHERE `user_id` = :u_id';
          $data = array(':login_data' => date('Y-m-d H:i:s'), ':u_id' => $_SESSION['user_id']);
          // クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          // クエリ成功の場合
          if ($stmt) {
            debug('クエリ成功。DBのログイン日時を更新しました。');
          }
        } catch (Exception $e) {
          error_log('エラー発生:'.$e->getMessage());
          $err_msg['common'] = MSG01;
          throw $e;
        }
        // トップページへ遷移
        header("Location:index.php");
      }else{
        debug('パスワードがマッチしません。');
        $err_msg['common'] = MSG09;
      }
    }catch(Exception $e){
      error_log('エラー発生:'.$e->getMessage());
      $err_msg['common'] = MSG01;
    }
  }
}
?>
<?php
  $siteTitle = 'ログイン';
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

        <div class="msg-common-area <?php if(!empty($err_msg['common'])) echo 'err'; ?>"><?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?></div>

        <label for="email">メールアドレス</label><span class="js-required notes-err">※必須</span>
        <input type="email" name="email" value="<?php echo getFormData('email'); ?>" class="js-email">
        <div class="msg-area <?php echo setClassErr('email'); ?>"><?php echo getErrMsg('email'); ?></div>

        <label for="pass">パスワード </label><span class="js-required notes-err">※半角英数字のみ6文字以上で入力してください</span>
        <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>" autocomplete="off" class="js-pass">
        <input type="checkbox" name="pass" class="js-show-pass">パスワードを表示する
        <div class="msg-area <?php echo setClassErr('pass'); ?>"><?php echo getErrMsg('pass'); ?></div>

        <input type="checkbox" name="pass_save">ログイン状態を保持する場合はチェック!!


        <input type="submit" class="btn btn-regist" value="ログイン">

        <p class="page-link"><a href="signup.php">新規登録はコチラ</a></p>
        <p class="page-link"><a href="passRemindSend.php">パスワードを忘れた場合はコチラ</a></p>


      </form>

    </section>



  </div>
</div>

<!-- footer -->
<?php
  require('footer.php');
