<?php

// 共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　カテゴリー編集ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//=====================================
// 画面処理
//=====================================
// DBから大カテゴリー情報を取得
$dbMainCategoryData = getMainCategory();
debug('メインカテゴリー情報:'.print_r($dbMainCategoryData, true));

// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
//  debug('POST情報：'.print_r($_POST, true));

  // 変数にユーザー情報を代入
  $subc_name = $_POST['subc_name'];
  $user_id = $_SESSION['user_id'];
  $mainc_id = $_POST['mainc_id'];

  // バリデーションチェック
  // selectbosチェック
  validSelect($mainc_id, 'mainc_id');
  // 未入力チェック
  validRequired($subc_name, 'subc_name');
  // 最大文字数チェック
  validMaxLen($subc_name, 'subc_name');

  if(empty($err_msg)){
    debug('バリデーションチェックOK。');

    // 例外処理
    try{
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'INSERT INTO sub_cate (subc_name, `user_id`, mainc_id, regist_data) VALUE (:subc_name, :user_id, :mainc_id, :regist_data)';
      $data = array(':subc_name' => $subc_name, ':user_id' => $user_id, ':mainc_id' => $mainc_id, ':regist_data' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if($stmt){
        debug('クエリ成功。');
      }else{
        debug('クエリ失敗しました。');
        $err_msg['common'] = MSG01;
      }
    }catch(Exception $e){
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = MSG01;
    }
  }
}

?>
<?php
  $siteTitle = 'カテゴリー編集';
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

        <label for="">メインカテゴリ</label>
        <span class="js-required notes-err">※選択してください</span>
        <select name="mainc_id" value="" class="main-cate js-main-cate">
          <option value="0 <?php if(getFormData('mainc_id', true) == 0) echo 'selected'; ?>">メインカテゴリ</option>
          <?php foreach($dbMainCategoryData as $key => $val){ 
          ?>
            <option value="<?php echo $val['mainc_id'] ?>" <?php if(getFormData('mainc_id', true) == $val['mainc_id']) echo 'selected'; ?>>
              <?php echo $val['mainc_name']; ?>
            </option>
          <?php } ?>
        </select>
        <div class="msg-area <?php echo setClassErr('mainc_id'); ?>"><?php echo getErrMsg('mainc_id'); ?></div>

        <label for="">登録したいサブカテゴリ名</label>
        <span class="js-required notes-err">※入力してください</span>
          <input type="text" name="subc_name" value="" class="edit-sab-cate js-sub-cate">
        </select>
        <div class="msg-area <?php echo setClassErr('sub_cate'); ?>"><?php echo getErrMsg('sub_cate'); ?></div>

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
