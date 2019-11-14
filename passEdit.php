<?php

// 共通変数・関数ファイルを読込
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード変更ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//=====================================
// 画面処理
//=====================================
// DBからユーザー情報を取得
$userData = getUser($_SESSION['user_id']);
//debug('取得したユーザー情報:'.print_r($userData, true));

// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
//  debug('POST情報:'.print_r($_POST, true));

  // 変数にユーザー情報を代入
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];

  // 未入力チェック
  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');

  if(empty($err_msg)){
    debug('未入力チェックOK。');

    // 古いパスワードチェック
    validPass($pass_old, 'pass_old');
    // 新しいパスワードチェック
    validPass($pass_new, 'pass_new');

    // 古いパスワードとDBのパスワードを照合
    if(!password_verify($pass_old, $userData['pass'])){
      $err_msg['pass_old'] = MSG12;
    }

    // 古いパスワードと新しいパスワードが同じかチェック
    if($pass_old === $pass_new){
      $err_msg['pass_new'] = MSG13;
    }

    // 新しいパスワードと再入力パスワードが同じかチェック
    validMatch($pass_new, $pass_new_re, 'pass_new_re');

    if(empty($err_msg)){
      debug('バリデーションOK。');

      // 例外処理
      try{
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'UPDATE users SET pass = :pass WHERE `user_id` = :u_id';
        $data = array(':u_id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功の場合
        if($stmt){
          debug('クエリ成功。');
          debug('パスワード変更完了メールを送信します。');

          // メール送信
          if($userData['username']){
            $username = $userData['username'];
          }else{
            $username = '名無し';
          }
          $from = 'household-account-book@ayumis.sakura.ne.jp';
          $to = $userData['email'];
          $subject = '【パスワード変更完了のお知らせ】｜Household account book';
          $comment = <<<EOT
{$username}様

パスワードの変更が完了しました。

/////////////////////////////////////////////////////////////////////
Household account book
URL：http://ayumis.sakura.ne.jp/index.php
E-mail：household-account-book@ayumis.sakura.ne.jp
/////////////////////////////////////////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);

          header("Location:index.php");
        }
      }catch(Exception $e){
        error_log('エラー発生。passEdit.php@$stmt:'.$e->getMessage());
        $err_msg['common'] = MSG01;
      }
    }
  }
}
?>
<?php
  $siteTitle = 'パスワード変更';
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

        <div class="msg-common-area <?php echo setClassErr('common'); ?>"><?php echo getErrMsg('common'); ?></div>

        <label for="pass_old">古いパスワード</label><span class="js-required notes-err">　※入力必須です</span>
        <input type="password" name="pass_old" value="<?php echo getFormData('pass_old'); ?>"  class="pass_old js-pass-old" autocomplete="off" placeholder="古いパスワードを入力">
        <input type="checkbox" name="pass" class="js-show-pass">古いパスワードを表示する
        <div class="msg-area <?php echo setClassErr('pass_old'); ?>"><?php echo getErrMsg('pass_old'); ?></div>

        <label for="pass_new">新しいパスワード </label><span class="js-required notes-err">※半角英数字6文字以上で入力してください</span>
        <input type="password" name="pass_new" value="<?php echo getFormData('pass_new'); ?>" class="pass_new js-pass-new" autocomplete="off" placeholder="新しいパスワードを入力">
        <input type="checkbox" name="pass" class="js-show-pass">新しいパスワードを表示する
        <div class="msg-area <?php echo setClassErr('pass_ner'); ?>"><?php echo getErrMsg('pass_new'); ?></div>

        <label for="pass_new_re">新しいパスワードパスワード（再入力）</label>
        <span class="js-required notes-err" style="display:block; left:0;"></span>
        <input type="password" name="pass_new_re" value="<?php echo getFormData('pass_new_re'); ?>" class="pass-new-re js-pass-new-re" autocomplete="off" placeholder="新しいパスワードを再入力">
        <input type="checkbox" name="pass" class="js-show-pass">新しいパスワード(再入力)を表示する
        <div class="msg-area <?php echo setClassErr('pass_new_re'); ?>"><?php echo getErrMsg('pass_new_re'); ?></div>

        <input type="submit" class="btn btn-regist" value="変更する">

        <p class="page-link"><a href="login.php">ログインはコチラ</a></p>
        <p class="page-link"><a href="">パスワードを忘れた場合はコチラ</a></p>


      </form>

    </section>



  </div>
</div>

<!-- footer -->
<?php
  require('footer.php');
