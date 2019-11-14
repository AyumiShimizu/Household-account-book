<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　問い合わせページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//=====================================
// 画面処理
//=====================================
// POST送信されていた場合
if (!empty($_POST)) {
  debug('POST送信がありました。');
//  debug('POST情報:' . print_r($_POST, true));

  // 変数にユーザー情報を代入
  $email = $_POST['email'];
  $name = $_POST['name'];
  $contact_comment = $_POST['comment'];

  // Emailの形式と最大文字数をチェック
  validEmail($email, 'email');
  validMaxLen($email, 'email');

  //未入力チェック
  validRequired($email, 'email');
  validRequired($name, 'name');
  validRequired($contact_comment, 'comment');

  if (empty($err_msg)) {
//    debug('バリデーションOKです。');

    // メール送信
    $from = 'ayumi.shimizu.0220@gmail.com';
    $to = $email;
    $subject = '【お問い合わせをいただきありがとうございます。】｜Household account book';
    $comment = <<<EOT

{$name}様

※こちらのメールは自動返信となっております。

お問い合わせいただきました内容への返答は改めて連絡をさせていただきます。
少々お時間をいただきますが、よろしくお願いいたします。

---------------------------------------------------------------------
お問い合わせ内容
{$contact_comment}

/////////////////////////////////////////////////////////////////////
Household account book by Ayumi Shimizu
URL：http://ayumis.sakura.ne.jp/index.php
E-mail：ayumi.shimizu.0220@gmail.com
/////////////////////////////////////////////////////////////////////
EOT;
    sendMail($from, $to, $subject, $comment);

    header("Location:index.php");
  }
}
?>
<?php
$siteTitle = '問い合わせ';
require('head.php');
?>

<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

  <!-- メインコンテンツ -->
  <div id="main" class="site-width">


    <!-- 一覧 -->
    <section class="page-login">
      <h2 class="page-1colum-title">問い合わせ</h2>

      <form action="" method="post">

        <div class="msg-common-area <?php if (!empty($err_msg['common'])) echo 'err'; ?>"><?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?></div>

        <label for="email">メールアドレス</label><span class="js-required notes-err">※必須</span>
        <input type="email" name="email" value="<?php echo getFormData('email'); ?>" class="js-email">
        <div class="msg-area <?php echo setClassErr('email'); ?>"><?php echo getErrMsg('email'); ?></div>

        <label for="name">お名前 </label><span class="js-required notes-err">※必須</span>
        <input type="text" name="name" value="<?php if (!empty($_POST['name'])) echo $_POST['name']; ?>" class="js-contact-name">
        <div class="msg-area <?php echo setClassErr('name'); ?>"><?php echo getErrMsg('name'); ?></div>

        <label for="comment">問い合わせ内容</label><span class="js-required notes-err">※必須</span>
        <textarea name="comment" class="slip-comment js-comment js-count" cols="30" rows="10" placeholder="お問い合わせ内容をご記入ください"><?php echo getFormData('comment'); ?></textarea>
        <div class="js-text-count text-count">
          <p><span class="js-show-count"> 0 </span>/100文字</p>
        </div>
        <div class="msg-area">
          <p class="js-count-err"><?php getErrMsg('comment'); ?></p>
        </div>

        <input type="submit" class="btn btn-regist" value="送信">


      </form>

    </section>



  </div>
  </div>

  <!-- footer -->
  <?php
  require('footer.php');
