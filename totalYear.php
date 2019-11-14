<?php

// 共通変数・関数ファイルを読込
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　カテゴリー年別入出金一覧ページ ');
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
$user_id = (!empty($_GET['user_id'])) ? $_GET['user_id'] : $_SESSION['user_id'];
// 取得期間の指定
$days = (!empty($_GET['data'])) ? $_GET['data'] : date('Y');
debug('取得期間の指定結果：'.print_r($days,true));
// 現在の年月の初日を取得
$firstDay = $days.'-01-01';
debug('今年の初日を取得：' . print_r($firstDay, true));
// 現在の年月の末日を取得
$lastDay = $days.'-12-31';
debug('今年の末日を取得：' . print_r($lastDay, true));

// 前年リンク用期間指定
$lastYear = date('Y', strtotime($firstDay.'-1 year'));
debug('前月の指定結果：'.print_r($lastYear, true));
// 来年リンク用期間指定
$nextYear = date('Y', strtotime($firstDay.'+1 year'));
debug('前月の指定結果：'.print_r($nextYear, true));

// 月間集計、週出金一覧リンク
// 今年
$nowYear = date('Y');
// 今年より表示年が少ない場合、または今年より表示年が多い場合はその年の1月、そうでなければ今月のデータを代入
$month = ($nowYear > $days || $nowYear < $days) ? $days.'-01' : date('Y-m');

// 表示年が今年でない場合は表年の1月に指定


// DB情報を取得
//-------------------------------------
// DBから指定機関の収入データをサブカテゴリ毎に集計して取得
$dbScateInTotal = getDepositScateInTotal($user_id, $firstDay, $lastDay);
// DBから指定機関の支出データをサブカテゴリ毎に集計して取得
$dbScateOutTotal = getdepositScateOutTotal($user_id, $firstDay, $lastDay);

?>
<?php
$siteTitle = $days . '年カテゴリー集計一覧';
require('head.php');
?>

<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

  <!-- メインコンテンツ -->
  <div id="main" class="site-width">

    <div class="area-title">
      <p class="page-prev"><a href="totalYear.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$lastYear : '?data='.$lastYear; ?>">＜＜前年へ</a></p>
      <p class="page-next"><a href="totalYear.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$nextYear : '?data='.$nextYear; ?>">来年へ＞＞</a></p>
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
                <td>¥ <?php echo sanitize(!empty($val['price_total']))? number_format($val['price_total']) : 0; ?></td>
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
                <td>¥ <?php echo sanitize(!empty($val['price_total']))? number_format($val['price_total']) : 0; ?></td>
              </tr>

            <?php endforeach; ?>

          </tbody>
        </table>

      </section>

      <p class="page-link"><a href="totalMonth.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$month : '?data='.$month; ?>">月間カテゴリー別集計表はコチラ</a></p>
      <p class="page-link"><a href="slipList.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$month : '?data='.$month; ?>">入出金一覧はコチラ</a></p>


    </div>
  </div>

  <?php
  require('footer.php');
  ?>