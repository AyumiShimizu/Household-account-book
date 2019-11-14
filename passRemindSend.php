<?php

// 共通変数・関数ファイルを読込
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行メール送信ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証必要なし

//=====================================
// 画面処理
//=====================================
// POST送信されていた場合
if(!empty($_POST)){
//  debug('POST送信があります。passRemindSend.php');
//  debug('POST情報:'.print_r($_POST, true));

  // 変数にPOST情報を代入
  $email = $_POST['email'];

  // 未入力チェック
  validRequired($email, 'email');

  if(empty($err_msg)){
//    debug('未入力チェックOK。');

    // Emailチェック
    validEmail($email, 'email');
    validMaxLen($email, 'email');

    if(empty($err_msg)){
//      debug('バリデーションOK。');

      // 例外処理
      try{
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // EmailがDBに登録されている場合
        if($stmt && array_shift($result)){
//          debug('クエリ成功。DB登録あり。');
//          debug('認証キーメールを送信します。');

          // 認証キーを生成
          $auth_key = makeRanKey();

          // メール送信
          $from = 'household-account-book@ayumis.sakura.ne.jp';
          $to = $email;
          $subject = '【パスワード再発行認証】｜Household account book';
          $comment = <<<EOT
{$email} 様

パスワードの再発行のご依頼がありました。
下記URLにて認証キーをご入力頂くと、新しいパスワードが発行されます。

パスワード再発行認証キー入力ページ：http://ayumis.sakura.ne.jp/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります。

認証キーを再発行されたい場合は、下記URLより改めて再発行の手続きをお願い致します。
hhttp://ayumis.sakura.ne.jp/passRemindSend.php

/////////////////////////////////////////////////////////////////////
Household account book
URL：http://ayumis.sakura.ne.jp/index.php
E-mail：household-account-book@ayumis.sakura.ne.jp
/////////////////////////////////////////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);

          // 認証に必要な情報をセッションへ保存保存
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_key_limit'] = time()+(60*30);

//          debug('セッション変数:'.print_r($_SESSION, true));

          // 認証キー入力ページへ遷移
          header("Location:passRemindRecieve.php");

        }else{
//          debug('クエリに失敗したかDB未登録のEmailが入力されました。');
          $err_msg['common'] = MSG01;
        }
      }catch(Exception $e){
        error_log('エラー発生passRemindSend.php:'.$e->getMessage());
        $err_msg['common'] = MSG01;
      }
    }
  }
}
?>
<?php
  $siteTitle = 'パスワード再発行メール送信';
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

        <div class="msg-common-area <?php if(!empty($err_msg['common'])) echo 'err'; ?>"><?php echo getErrMsg('common'); ?></div>

        <label for="email"> 登録したEmailを入力してください</label>
        <input type="email" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>" class="js-email">
        <div class="msg-area <?php echo setClassErr('email'); ?>"><?php echo getErrMsg('email'); ?></div>

        <input type="submit" class="btn btn-regist" value="送信する">

        <p class="page-link"><a href="passRemindRecieve.php">認証キー入力ページはコチラ</a></p>

      </form>

    </section>



  </div>
</div>

<!-- footer -->
<?php
  require('footer.php');
