<?php

// 共通変数・共通関数ファイルを読み込む
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール編集ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//=====================================
// 画面処理
//=====================================
// DBからユーザー情報を取得
$dbFormData = getUser($_SESSION['user_id']);
//debug('取得したユーザー情報:'.print_r($dbFormData, true));

// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
//  debug('POST情報:'.print_r($_POST, true));

  // 変数にユーザー情報を代入
  $username = $_POST['username'];
  $email =  $_POST['email'];
  // 郵便番号は空欄の場合、後続のバリデーションでエラーになるため、空で送信されたら0を入れる
  $zip = (!empty($_POST['zip'])) ? $_POST['zip'] : 0;
  $addr_pref = $_POST['addr_pref'];
  $addr_city = $_POST['addr_city'];
  $addr_num = $_POST['addr_num'];
  $tel = $_POST['tel'];
  $birth = $_POST['birth'];

  // DB情報と入力情報が異なる場合にバリデーションチェックを行う
  // ユーザーネーム
  if($dbFormData['username'] !== $username){
    // 最大文字数チェック
    validMaxLen($username, 'username');
  }
  // Email
  if($dbFormData['email'] !== $email){
    // 最大文字数チェック
    validEmail($email, 'email');
  }
  // 郵便番号
  if($dbFormData['zip'] !== $zip){
    // 郵便番号形式チェック
    validZip($zip, 'zip');
  }
  // 都道府県
  if($dbFormData['addr_pref'] !== $addr_pref){
    // 最大文字数チェック
    validMaxLen($addr_pref, 'addr_pref');
  }
  // 市区町村
  if($dbFormData['addr_city'] !== $addr_city){
    // 最大文字数チェック
    validMaxLen($addr_city, 'addr_city');
  }
  // その他住所
  if($dbFormData['addr_num'] !== $addr_num){
    // 最大文字数チェック
    validMaxLen($addr_num, 'addr_num');
  }
  // TEL
  if((int)$dbFormData['tel'] !== $tel){
    // 電話番号形式チェック
    validTel($tel, 'tel');
  }
  // 誕生日
  if($dbFormData['birth'] !== $birth){
    // 最大文字数チェック
    validMaxLen($birth, 'birth');
  }

  if(empty($err_msg)){
    debug('バリデーションチェックOK。');

    // 例外処理
    try{
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users SET username = :u_name, email = :email, zip = :zip, addr_pref = :addr_pref, addr_city = :addr_city, addr_num = :addr_num, tel = :tel, birth =:birth WHERE `user_id` = :u_id';
      $data = array(':u_name' => $username, ':email' => $email, ':zip' => $zip, ':addr_pref' => $addr_pref, ':addr_city' => $addr_city, ':addr_num' => $addr_num, ':tel' => $tel, ':birth' => $birth, ':u_id' => $_SESSION['user_id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      if($stmt){
        debug('クエリ成功。');
        header("Location:index.php");
      }
    }catch(Exception $e){
      error_log('【エラー発生】:'.$e->getMessage());
      $err_msg['common'] = MSG01;
    }
  }
}
?>
<?php
$siteTitle = "プロフィール編集";
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

          <label for="">ユーザーネーム</label><span class="js-required notes-err"></span>
          <input type="text" name="username" value="<?php echo getFormData('username'); ?>" class="js-name" placeholder="任意のユーザーネーム">
          <div class="msg-area <?php echo setClassErr('user_name'); ?>"><?php echo getErrMsg('use_name'); ?></div>

          <label for="">Email</label><span class="js-required notes-err"></span>
          <input type="email" name="email" value="<?php echo getFormData('email'); ?>" class="js-email" placeholder="Email" autocomplete="email">
          <div class="msg-area <?php echo setClassErr('email'); ?>"><?php echo getErrMsg('email'); ?></div>

          <div class="addr-area">
            <label for="" style="float:left;">住所</label>
            <span class="js-required notes-err"></span>
            <input type="text" name="zip" value="<?php echo getFormData('zip'); ?>" class="js-zip" placeholder="郵便番号" style="width:200px; float:right;" autocomplete="postal-code" onKeyUp="AjaxZip3.zip2addr(this,'','addr_pref','addr_city','addr_num');">
            <select name="addr_pref" value="<?php echo getFormData('addr_pref'); ?>" id="" class="addr" style="width: 150px;margin-right: 10px;">
              <option value="0">都道府県</option>
              <option value="1">北海道</option>
              <option value="2">青森県</option>
              <option value="3">岩手県</option>
              <option value="4">宮城県</option>
              <option value="5">秋田県</option>
              <option value="6">山形県</option>
              <option value="7">福島県</option>
              <option value="8">茨城県</option>
              <option value="9">栃木県</option>
              <option value="10">群馬県</option>
              <option value="11">埼玉県</option>
              <option value="12">千葉県</option>
              <option value="13">東京都</option>
              <option value="14">神奈川県</option>
              <option value="15">新潟県</option>
              <option value="16">富山県</option>
              <option value="17">石川県</option>
              <option value="18">福井県</option>
              <option value="19">山梨県</option>
              <option value="20">長野県</option>
              <option value="21">岐阜県</option>
              <option value="22">静岡県</option>
              <option value="23">愛知県</option>
              <option value="24">三重県</option>
              <option value="25">滋賀県</option>
              <option value="26">京都府</option>
              <option value="27">大阪府</option>
              <option value="28">兵庫県</option>
              <option value="29">奈良県</option>
              <option value="30">和歌山県</option>
              <option value="31">鳥取県</option>
              <option value="32">島根県</option>
              <option value="33">岡山県</option>
              <option value="34">広島県</option>
              <option value="35">山口県</option>
              <option value="36">徳島県</option>
              <option value="37">香川県</option>
              <option value="38">愛媛県</option>
              <option value="39">高知県</option>
              <option value="40">福岡県</option>
              <option value="41">佐賀県</option>
              <option value="42">長崎県</option>
              <option value="43">熊本県</option>
              <option value="44">大分県</option>
              <option value="45">宮崎県</option>
              <option value="46">鹿児島県</option>
              <option value="47">沖縄県</option>
            </select>
            <input type="text" name="addr_city" value="<?php echo getFormData('addr_city'); ?>" id="" class="addr" placeholder="市区町村" style="width: 180px;">
            <input type="text" name="addr_num" value="<?php echo getFormData('addr_num'); ?>" id="" placeholder="以降の住所">
          </div>
          <div class="msg-area <?php echo setClassErr('addr'); ?>"><?php echo getErrMsg('addr'); ?></div>

          <label for="">TEL</label><span class="js-required notes-err"></span>
          <input type="text" name="tel" value="<?php echo getFormData('tel'); ?>" class="js-tel" placeholder="TEL" autocomplete="tel-national">
          <div class="msg-area <?php echo setClassErr('tel'); ?>"><?php echo getErrMsg('tel'); ?></div>

          <label for="">生年月日</label><span class="js-required notes-err"></span>
          <input type="date" name="birth" value="<?php echo getFormData('birth'); ?>" class="js-birth">
          <div class="msg-area <?php echo setClassErr('birth'); ?>"><?php echo getErrMsg('birth'); ?></div>

          <input type="submit" class="btn btn-regist" value="変更する">

          <p><a href="passEdit.php">パスワード変更はコチラ</a></p>
          <p><a href="withdraw.php">退会はコチラ</a></p>


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
