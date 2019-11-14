<?php

// 共通変数・関数ファイルを読込
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　トップページ ');
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
// 直近の伝票表示件数
$listSpan = 8;
// 現在の年月を習得
$nowDay = date('Y-m');
// 年月の表示をフォーマット
$day = date('Y年n月', strtotime($nowDay));

// 現在の年月の初日を取得
$firstDay = date('Y-m-d', strtotime('first day of this month'));
debug('今月の初日を取得：' . print_r($firstDay, true));
// 現在の年月の末日を取得
$lastDay = date('Y-m-d', strtotime('last day of this month'));
debug('今月の末日を取得：' . print_r($lastDay, true));

// DB情報を取得
//-------------------------------------
// DBから全ての収支データを取得
$dbDepositData = getDepositDataAll($_SESSION['user_id']);
// DBから全ての振替データを取得
$dbTransData = getTransDataAll($_SESSION['user_id']);
// 習得した全ての収支データと振替データを結合する
$dbSlipAllData = array_merge($dbDepositData, $dbTransData);

// 収支合計エリア
//-------------------------------------
// 今月の収入合計をDBから習得し、変数に格納
$dbDepositInTotal = getPeriodInTotal($_SESSION['user_id'], $firstDay, $lastDay);
foreach ($dbDepositInTotal as $key => $val);
$depositInTotal = $val['total_price'];
// 今月の支出合計をDBから習得し、変数に格納
$dbDepositOutTotal = getPeriodOutTotal($_SESSION['user_id'], $firstDay, $lastDay);
foreach ($dbDepositOutTotal as $key => $val);
$depositOutTotal = $val['total_price'];
// 今月の収支合計
$total = $depositInTotal - $depositOutTotal;


// 口座表示エリア
//-------------------------------------
// DBから口座情報を取得
$dbBank = getBankAll($_SESSION['user_id']);

// 直近入出金一覧エリア
//-------------------------------------
// 習得したDB伝票データを各データを日付順に並び替える
$updata = array();
foreach ($dbSlipAllData as $key => $val) {
  $updata[$key] = $val['regist_data'];
}
// 登録日時[regist_data]にて新しい順に並び替え
array_multisort($updata, SORT_DESC, $dbSlipAllData);
// 表示に必要な数だけ先頭から取り出す
$slipData = array_slice($dbSlipAllData, 0, $listSpan);
?>
<?php
require('head.php');
?>

<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

  <!-- メインコンテンツ -->
  <div id="main" class="site-width">

    <!-- 左エリア -->
    <div class="index-box">
      <!-- 収支合計 -->
      <section class="box-item item-area1">
        <h2 class="item-title">今月の収支合計</h2>
        <table>
          <tbody>
            <tr>
              <th>収入</th>
              <td>¥ <?php echo sanitize(number_format($depositInTotal)); ?></td>
            </tr>
            <tr>
              <th>支出</th>
              <td>¥ <?php echo sanitize(number_format($depositOutTotal)); ?></td>
            </tr>
            <tr>
              <th>収支合計</th>
              <td>¥ <?php echo sanitize(number_format($total)); ?></td>
            </tr>
          </tbody>
        </table>


        <p class="page-link"><a href="totalMonth.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&data=' . $nowDay : '?data=' . $nowDay; ?>"><?php echo $day; ?>カテゴリ別集計一覧ページはコチラ</a></p>
      </section>

      <!-- 口座残高一覧 -->
      <section class="box-item item-area2">
        <h2 class="item-title">口座残高一覧</h2>
        <table>
          <tbody>

            <?php foreach ($dbBank as $key => $val) : ?>
              <tr>
                <th><?php echo sanitize($val['bank_name']); ?></th>
                <td>¥ <?php echo sanitize(number_format($val['current_price'])); ?></td>
              </tr>
            <?php endforeach; ?>

          </tbody>
        </table>

        <p class="page-link"><a href="bankEdit.php">新しい口座の登録はコチラ</a></p>

      </section>
    </div>

    <!-- 右エリア -->
    <div class="index-box">
      <!-- 入出金一覧 -->
      <section class="box-item item-area3">
        <h2 class="item-title">直近の入出金</h2>


        <?php foreach ($slipData as $key => $val) : ?>

          <div class="<?php echo sanitize(setClassName($val['deposit_flg'])); ?>">
            <p><a href="registSlip.php<?php echo setLink($val); ?>"><?php echo sanitize(date('n/d', strtotime($val['regist_data']))); ?></p>
            <p><a href="registSlip.php<?php echo setLink($val); ?>"><?php echo sanitize(getMcateName($val['mainc_id'])); ?></p>
            <p><a href="registSlip.php<?php echo setLink($val); ?>"><span>¥ </span><?php echo sanitize(number_format($val['price'])); ?></p>
          </div>

        <?php endforeach; ?>

        <p class="page-link"><a href="slipList.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&data=' . $nowDay : '?data=' . $nowDay; ?>"><?php echo $day; ?>入出金一覧はコチラ</a></p>

      </section>
    </div>

  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>