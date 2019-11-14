<?php

// 共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　退会ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//=====================================
// 画面処理
//=====================================
// POSTされていた場合
if(!empty($_POST)){
  debug('POST送信があります。');

  // 例外処理
  try{
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql1 = 'UPDATE users SET derete_flg = 1 WHERE `user_id` = :user_id';
    $sql2 = 'UPDATE bank SET delete_flg = 1 WHERE `user_id` = :user_id';
    $sql3 = 'UPDATE sub_cate SET delete_flg = 1 WHERE `user_id` = :user_id';
    $sql4 = 'UPDATE deposit SET delete_flg = 1 WHERE `uer_id` = :user_id';
    $data = array(':user_id' => $_SESSION['user_id']);
    // クエリ実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);
    $stmt4 = queryPost($dbh, $sql4, $data);

    // クエリ成功の場合(最悪、usersテーブルのみ削除が成功していれば成功とする)
    if($stmt1){
      debug('クエリ成功。セッションを削除します。');
      // セッション削除
      session_destroy();
      debug('セッション情報が削除されたか確認。:'.print_r($_SESSION, true));
      header('Location:signupMailSend.php');
    }else{
      debug('クエリが失敗しました。');
      $err_msg['common'] = MSG01;
    }
  }catch(Exception $e){
    error_log('エラー発生。:'.$e->getMessage());
    $err_msg['common'] = NSG01;
  }
}
?>
<?php
  $siteTitle = '退会';
  require('head.php');
?>
<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="main" class="site-width">

    <!-- 一覧 -->
    <section class="page-login page-draw">
      <h2 class="page-1colum-title"><?php echo $siteTitle; ?></h2>

      <form action="" method="post">

      <div class="msg-common-area <?php echo setClassErr('common'); ?>"><?php echo getErrMsg('common'); ?></div>

        <input type="submit" class="btn btn-send" value="退会する">

        <p class="page-link"><a href="profEdit.php">プロフィール編集はコチラ</a></p>
        <p class="page-link"><a href="passEdit.php">パスワード変更はコチラ</a></p>

      </form>

    </section>

  </div>
</div>

<!-- footer -->
<?php
  require('footer.php');
