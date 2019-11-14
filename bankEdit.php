<?php

// 共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　口座編集ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//=====================================
// 画面処理
//=====================================
// POST送信されていた場合
if(!empty($_POST)){

//  debug('POST情報：'.print_r($_POST, true));

  // 変数にユーザー情報を代入
  $user_id = $_SESSION['user_id'];
  $bank_name = $_POST['bank_name'];
  $start_price = $_POST['start_price'];

  // 未入力チェック
  validRequired($bank_name, 'bank_name');
  validRequired($start_price, 'start_price');

  if(empty($err_msg)){
    debug('未入力チェックOK。');

    // 最大文字数チェック
    validMaxLen($bank_name, 'bank_name');
    validMaxLen($start_price, 'start_price');

    if(empty($err_msg)){
      debug('バリデーションチェックOK。');

      // 例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL分を作成
        $sql = 'INSERT INTO bank (bank_name, `user_id`, start_price, current_price, regist_data) VALUES (:bank_name, :user_id, :start_price, :current_price, :regist_data)';
        $data = array(':bank_name' => $bank_name, ':user_id' => $user_id, ':start_price' => $start_price, ':current_price' => $start_price, ':regist_data' => date('Y-m-d H:i:s'));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功の場合
        if($stmt){
//          debug('クエリ成功。');
          header("Location:index.php");
        }else{
//          debug('クエリ失敗しました。');
          $err_msg['common'] = MSG01;
        }
      } catch (Exception $e) {
        error_log('エラー発生。'.$e->getMessage());
        $err_msg['common'] = MSG01;
      }
    }
  }
}
?>
<?php
  $siteTitle = '口座編集ページ';
  require('head.php');
?>
<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="main" class="site-width">

    <!-- 一覧 -->
    <section class="page-2colum page-edit">
      <h2 class="page-2colum-title"><?php echo $siteTitle; ?></h2>

      <form action="" method="post">

        <div class="msg-common-area <?php echo setClassErr('common'); ?>"><?php echo getErrMsg('common'); ?></div>

        <label for="">銀行名</label>
        <span class="js-required notes-err">※入力必須です</span>
        <input type="text" name="bank_name" value="<?php echo getFormData('bank_name'); ?>" class="js-bank-name" placeholder="登録したい銀行名を入力">
        <div class="msg-area <?php echo setClassErr('bank_name'); ?>"><?php echo getErrMsg('bank_name'); ?></div>

        <label for="">初期設定残高</label>
        <span class="js-required notes-err">※半角数字で入力してください</span>
        <input type="number" name="start_price" value="<?php echo getFormData('start_price'); ?>" class="js-start_price" placeholder="入力時の残高を入力">
        </select>
        <div class="msg-area <?php echo setClassErr('start_price'); ?>"><?php echo getErrMsg('start_price'); ?></div>

        <input type="submit" class="btn btn-regist" value="登録する">

      </form>

    </section>

    <!-- sidebar -->
    <?php
      require('sidebar.php');
    ?>

  </div>
</div>

<!-- footer -->
<?php
  require('footer.php');
