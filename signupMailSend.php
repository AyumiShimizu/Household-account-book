<?php
// 共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　新規登録申請Email送信ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//=====================================
// 画面処理
//=====================================
// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
//  debug('POST情報:'.print_r($_POST, true));

  // 変数にPOST情報を代入
  $email = $_POST['email'];

  // 未入力チェック
  validRequired($email, 'email');

  if(empty($err_msg)){
    debug('未入力チェックOK。');

    // Email形式チェック
    validEmail($email, 'email');
    // Email最大文字数チェック
    validMaxLen($email, 'email');

    if(empty($err_msg)){
      debug('バリデーションOK。');

      // Email重複チェック
      validEmailDup($email, 'email');
    }
  }
}
?>
<?php
  $siteTitle = "新規登録申請メール送信";
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
        <p>ご指定のメールアドレス宛に新規登録ページのURLをお送り致します。</p>

        <div class="msg-common-area <?php if(!empty($err_msg['common'])) echo 'err'; ?>">
        <?php echo getErrMsg('common'); ?>
      </div>

        <label for="email">登録したいEmailを入力してください</label>
        <span class="js-required notes-err">※入力必須です</span>
        <input type="email" name="email" value="<?php echo getFormData('email'); ?>" class="js-email">
        <div class="msg-area <?php echo setClassErr('email'); ?>">
          <?php echo getErrMsg('email'); ?>
        </div>

        <input type="submit" class="btn btn-regist" value="送信する ">

        <p class="page-link"><a href="login.php">ログインページはコチラ</a></p>

      </form>

    </section>



  </div>
</div>

<!-- footer -->
<?php
  require('footer.php');
