<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　新規登録ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// SESSIONにEmail情報があるか確認、なければリダイレクト
if(empty($_SESSION['auth_email'])){
  header("Location:signupMailSend.php"); //登録申請メール送信ページへ遷移
}

//=====================================
// 定数
//=====================================
//ページ固有メッセージを定数に設定
define('MSG20', 'メールアドレスが違うか、有効期限が切れています。');
define('msg21', '認証キーが違うか、有効期限が切れています。');

//======================================
// 画面処理
//=====================================-
// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
//  debug('POST情報:'.print_r($_POST, true));

  // 変数にユーザー情報を代入
  $email = $_POST['email'];

  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  // Email関連のバリデーションチェックは新規登録申請メールページにて完了しているので、このページではチェックをしない
  // 入力されたEmailと申請に使われたEmailが同じかチェック
  if($_SESSION['auth_email'] !== $email){
    $err_msg['common'] = MSG20;
  }
  // 有効期限内かチェック
  if(time() > $_SESSION['auth_limit']){
    $err_msg['common'] = MSG20;
  }
  if(empty($err_msg)){
    debug('Email認証OK。');
  }

  // 未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');
  if(empty($err_msg)){
//    debug('未入力チェックOK。');

    // パスワードとパスワード再入力が合っているかチェック
    validMatch($pass, $pass_re, 'pass_re');
//    debug('パスワードチェックOK。');

    if(empty($err_msg)){

      // 例外処理
      try{
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'INSERT INTO users (email, pass, regist_data, login_data) VALUES (:email, :pass, :regist_data, :login_data)';
        $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':regist_data' => date('Y-m-d H:i:s'), ':login_data' => date('Y-m-d H:i:s'));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功の場合
        if($stmt){
          // ログイン有効期限(デフォルトを1時間とする)
          $sesLimit = 60*60;
          // 最終ログイン日時を現在時間に
          $_SESSION['login_time'] = time();
          $_SESSION['login_limit'] = $sesLimit;
          // ユーザーIDを格納
          $_SESSION['user_id'] = $dbh->lastInsertId();

          // 今後は必要ないため、$_SESSION['auth_email']と$_SESSION['auth_limit']を破棄
          unset($_SESSION['auth_email'], $_SESSION['auth_limit']);

//          debug('セッション変数の中身:'.print_r($_SESSION, true));
          header("Location:index.php"); //トップページヘ
        }
      }catch(Exception $e){
        error_log('【エラー発生】:'.$e->getMessage());
        $err_msg['common'] = MSG01;
      }
    }
  }
}
?>
<?php
$siteTitle = '新規登録';
require('head.php');
?>
<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

  <!-- メインコンテンツ -->
  <div id="main" class="site-width">

    <!-- メインコンテンツ -->
    <div id="main">

      <!-- 一覧 -->
      <section class="page-login">
        <h2 class="page-1colum-title"><?php echo $siteTitle; ?></h2>

        <form action="" method="post">

          <div class="msg-common-area <?php echo setClassErr('common'); ?>"><?php echo getErrMsg('common'); ?></div>

          <label for="email">メールアドレス</label><span class="js-required"></span>
          <input type="email" name="email" value="<?php echo getFormData('auth_email'); ?>" class="js-email" placeholder="Email">
          <div class="msg-area <?php echo setClassErr('email'); ?>"><?php echo getErrMsg('email'); ?></div>

          <label for="pass">パスワード </label><span class="js-required notes-err">※半角英数字のみ6文字以上で入力してください</span>
          <input type="password" name="pass" value="<?php echo getFormData('pass'); ?>" autocomplete="off" placeholder="半角英数字6文字以上" class="js-pass">
          <input type="checkbox" name="pass" class="js-show-pass">パスワードを表示する
          <div class="msg-area <?php echo setClassErr('pass'); ?>"><?php echo getErrMsg('pass'); ?></div>

          <label for="pass_re">パスワード（再入力）</label><span class="js-required notes-err"></span>
          <input type="password" name="pass_re" value="<?php echo getFormData('pass_re'); ?>" autocomplete="off" placeholder="パスワード欄と同じものを再入力" class="js-pass-re">
          <input type="checkbox" name="pass" class="js-show-pass-re">パスワードを表示する
          <div class="msg-area <?php echo setClassErr('pass_re'); ?>"><?php echo getErrMsg('pass_re'); ?></div>

          <input type="submit" class="btn btn-regist" value="登録する">

          <p class="page-link"><a href="login.php">ログインはコチラ</a></p>
          <p class="page-link"><a href="">パスワードを忘れた場合はコチラ</a></p>


        </form>

      </section>



    </div>
  </div>

  <!-- footer -->
  <?php
  require('footer.php');
