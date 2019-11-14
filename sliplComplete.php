<?php
// 共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　伝票登録完了ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
?>
<?php
  $siteTitle = "レシート入力完了";
  require('head.php');
?>
<body>

  <style>
    p{
      margin: 0 auto;
      text-align: center;
    }

    .btn-slip-comp{
    width: 340px;
    height: 50px;
    margin: 20px auto;
    border-radius: 25px;
    border: 2px solid #5281B3;
    background-color: white;
    }

    .btn-link{
      display: block;
      line-height: 50px;
    }
  </style>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="main" class="site-width">
      
    <!-- 一覧 -->
    <section class="page-edit page-2colum">
      <h2 class="page-2colum-title">レシート入力完了</h2>

        <p>レシートが入力されました。<br/>
        続けて入力されますか？
        </p>

        <p class="btn-slip-comp"><a class="btn-link" href="registSlip.php">続けて入力する</a></p>

        <p class="btn-slip-comp"><a class="btn-link" href="index.php">トップページへ</a></p>

    </section>

    <!-- サイドメニュー -->
    <?php
    require('sidebar.php');
    ?>

  </div>
</div>

<!-- footer -->
<?php
  require('footer.php');
