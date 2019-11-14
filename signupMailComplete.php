<?php
// 共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　新規登録申請Email送信完了ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

?>
<?php
  $siteTitle = "新規登録申請メール送信完了";
  require('head.php');
?>
<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="main" class="site-width">
      
    <!-- 一覧 -->
    <section class="page-login">
      <h2 class="page-1colum-title">新規登録申請メール送信完了</h2>

        <p style="margin:0 auto;">ご指定のメールアドレス宛に新規登録ページのURLをお送り致しました。<br/>
        送信されたメールに記載されているURLより登録をお願い致します。
        </p>


    </section>



  </div>
</div>

<!-- footer -->
<?php
  require('footer.php');
