<?php

// 共通変数・関数ファイルを読込
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　カテゴリー月別入出金一覧ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//=====================================
// 画面処理
//=====================================
// ページエラー表示変更
// Noticeレベルを除外しないと、未定義の場合の処理の際にエラーが出て正しく表示されないため
error_reporting(E_ALL & ~E_NOTICE);

// 画面表示データ取得
//=====================================
// GETパラメータを取得
//-------------------------------------
$user_id = $_SESSION['user_id'];
// ページの取得期間の指定
$days = (!empty($_GET['data'])) ? $_GET['data'] : date('Y-m');
debug('取得期間の指定結果：'.print_r($days,true));
// 現在の年月の初日を取得
$firstDay = date('Y-m-d', strtotime('first day of '. $days));
debug('今月の初日を取得：' . print_r($firstDay, true));
// 現在の年月の末日を取得
$lastDay = date('Y-m-d', strtotime('last day of '. $days));
debug('今月の末日を取得：' . print_r($lastDay, true));

// 期間の表示をフォーマット
$reDays = date('Y年n月', strtotime($days));

// 前月リンク用期間指定
$lastMonth = date('Y-m', strtotime($firstDay.'-1 month'));
debug('前月の指定結果：'.print_r($lastMonth, true));
// 来月リンク用期間指定
$nextMonth = date('Y-m', strtotime($firstDay.'+1 month'));
debug('前月の指定結果：'.print_r($nextMonth, true));

// 年一覧リンク作成
$year = date('Y', strtotime($days));
debug('年取得:'.print_r($year, true));

// DB情報を取得
//-------------------------------------
// DBから指定機関の収入データをサブカテゴリ毎に集計して取得
$dbScateInTotal = getDepositScateInTotal($user_id, $firstDay, $lastDay);
// DBから指定機関の支出データをサブカテゴリ毎に集計して取得
$dbScateOutTotal = getDepositScateOutTotal($user_id, $firstDay, $lastDay);

?>
<?php
$siteTitle = $reDays . 'カテゴリー集計一覧';
require('head.php');
?>

<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

  <!-- メインコンテンツ -->
  <div id="main" class="site-width">

    <!-- メインコンテンツ -->
    <div id="main">

      <div class="area-title">
        <p class="page-prev"><a href="totalMonth.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$lastMonth : '?data='.$lastMonth; ?>">＜＜先月へ</a></p>
        <p class="page-next"><a href="totalMonth.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$nextMonth : '?data='.$nextMonth; ?>">来月へ＞＞</a></p>
        <h1 class="page-1colum-title"><?php echo $siteTitle; ?></h1>
      </div>

      <div class="page-1colum page-total">

        <!-- 収入一覧 -->
        <section class="page-total-left">
          <h2 class="item-title">収入一覧</h2>
          <table class="tabel-total">
            <tbody>

              <?php foreach ($dbScateInTotal as $key => $val) : ?>

                <tr>
                  <th><?php echo sanitize(getMcateName($val['mainc_id'])); ?></th>
                  <th><?php echo sanitize(getScateName($val['subc_id'])); ?></th>
                  <td>¥ <?php echo sanitize(!empty($val['price_total'])) ? number_format($val['price_total']) : 0; ?></td>
                </tr>

              <?php endforeach; ?>

            </tbody>
          </table>

        </section>

        <!-- 支出一覧 -->
        <section class="page-total-right">
          <h2 class="item-title">支出一覧</h2>
          <table class="tabel-total">
            <tbody>

              <?php foreach ($dbScateOutTotal as $key => $val): ?>

                <tr>
                  <th><?php echo sanitize(getMcateName($val['mainc_id'])); ?></th>
                  <th><?php echo sanitize(getScateName($val['subc_id'])); ?></th>
                  <td>¥ <?php echo sanitize(!empty($val['price_total'])) ? number_format($val['price_total']) : 0; ?></td>
                </tr>
              <?php endforeach; ?>

            </tbody>
          </table>

        </section>

        <p class="page-link"><a href="totalYear.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$year : '?data='.$year; ?>">年間カテゴリー別集計表はコチラ</a></p>
        <p class="page-link"><a href="slipList.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$days : '?data='.$days; ?>">入出金一覧はコチラ</a></p>


      </div>
    </div>
    <!-- footer -->
    <?php
    require('footer.php');
    ?>