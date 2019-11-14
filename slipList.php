<?php

// 共通変数・関数ファイルを読込
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　入出金一覧ページ ');
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
// 取得期間の取得
$days = (!empty($_GET['data'])) ? $_GET['data'] : date('Y-m');
// 現在の年月の初日を取得
$firstDay = date('Y-m-d', strtotime('first day of '. $days));
debug('指定期間'.$days.'の初日を取得：'.print_r($firstDay, true));
// 現在の年月の末日を取得
$lastDay = date('Y-m-d', strtotime('last day of '. $days));
debug('指定期間'.$days.'を取得：'.print_r($lastDay, true));

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
// DBから指定月の収支データを取得
$dbDepositData = getDepositPeriodData($_SESSION['user_id'], $firstDay, $lastDay);
// DBから指定月の振替データを取得
$dbTransPeriodData = getTransPeriodData($_SESSION['user_id'], $firstDay, $lastDay);
// 取得した今月の収支データと振替データを結合する
$dbSlipPeriodData = array_merge($dbDepositData, $dbTransPeriodData);

// 各データを日付順に並び替える
$updata = array();
foreach($dbSlipPeriodData as $key => $val){
  $updata[$key] = $val['regist_data'];
}
  array_multisort($updata, SORT_DESC, $dbSlipPeriodData);

?>
<?php
  $siteTitle = $reDays.'入出金一覧';
  require('head.php');
?>
<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="main" class="site-width">
      

    <div class="area-title js-list-title">
      <p class="page-prev"><a href="slipList.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$lastMonth : '?data='.$lastMonth; ?>">＜＜前月へ</a></p>
      <p class="page-next"><a href="slipList.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$nextMonth : '?data='.$nextMonth; ?>">来月へ＞＞</a></p>
      <h1 class="page-1colum-title"><?php echo $siteTitle; ?></h1>
    </div>

    <p class="page-link"><a href="totalMonth.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&data='.$days : '?data='.$days; ?>">月間カテゴリー別集計表はコチラ</a></p>

    <!-- 一覧 -->
    <section class="page-list js-list-area">

      <?php foreach($dbSlipPeriodData as $key => $val): 
        debug('$valを確認：'. print_r($val, true));?>

        <div class="list-item <?php echo setClassName($val['deposit_flg']); ?>">
          <p><a href="registSlip.php<?php echo setLink($val); ?>"><?php echo sanitize(date('n/d', strtotime($val['regist_data']))); ?></a></p>
          <p><a href="registSlip.php<?php echo setLink($val); ?>"><?php echo sanitize(getMcateName($val['mainc_id'])); ?></a></p>
          <p><a href="registSlip.php<?php echo setLink($val); ?>"><?php echo sanitize(getScateName($val['subc_id'])); ?></a></p>
          <p><a href="registSlip.php<?php echo setLink($val); ?>"><span>¥ </span><?php echo sanitize(number_format($val['price'])); ?></a></p>
        </div>

      <?php endforeach; ?>

    </section>



  </div>
</div>

<!-- footer -->
<?php
  require('footer.php');
