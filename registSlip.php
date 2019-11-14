<?php 

// 共通変数・関数を読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　入出金登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//=====================================
// 画面処理
//=====================================
// ページエラー表示変更
// Noticeレベルを除外しないと、未定義の場合の処理の際にエラーが出て正しく表示されないため
error_reporting(E_ALL & ~E_NOTICE);

// GETパラメータを習得
//-------------------------------------
// 伝票データのGETデータを格納
$deposit_flg = (isset($_GET['sort'])) ? $_GET['sort'] : 0;
$deposit_id = ($deposit_flg == 0 || $deposit_flg == 1) ? $_GET['s_id'] : '';
$trans_id = ($deposit_flg == 2) ? $_GET['s_id'] : '';

if ($deposit_id || $trans_id) {
  if ($deposit_id && $deposit_flg == 0) {
//    debug('支出伝票情報があります。収支伝票ID：' . print_r($deposit_id, true));
  } elseif ($deposit_id && $deposit_flg == 1) {
//    debug('収入伝票情報があります。収支伝票ID：' . print_r($deposit_id, true));
  } elseif ($trans_id && $deposit_flg == 2) {
//    debug('振替伝票情報があります。振替伝票ID：' . print_r($trans_id, true));
  }
}else{
//  debug('伝票情報はありません。');
}

// DBから伝票データを取得
$dbFormData = (!empty($deposit_id)) ? getDepositData($_SESSION['user_id'], $deposit_id) : (!empty($trans_id) ? getTransDataOne($_SESSION['user_id'], $trans_id) : '');
//debug('フォーム用DBデータ：'.print_r($dbFormData, true));
// 伝票データがあった場合、データ対象口座の情報を取得し、連想配列に変換
global $targetBank;
$targetBank = array();
global $targetOutBank;
$targetOutBank = array();
global $targetInBank;
$targetInBank = array();
if($deposit_id){
  $dbTargetBank = ($dbFormData) ? getBankOne($_SESSION['user_id'], $dbFormData['bank_id']) : '';
  foreach ($dbTargetBank as $key => $val) {
    $targetBank = $val;
  }
//  debug('伝票対象口座情報$targetBank：'.print_r($targetBank, true));
}elseif($trans_id){
  $dbTargetOutBank = ($dbFormData) ? getBankOne($_SESSION['user_id'], $dbFormData['outbank_id']) : '';
  foreach ($dbTargetOutBank as $key => $val) {
    $targetOutBank = $val;
//    debug('伝票対象振替元口座情報：'.print_r($targetOutBank, true));
  }
  $dbTargetInBank = ($dbFormData) ? getBankOne($_SESSION['user_id'], $dbFormData['inbank_id']) : '';
  foreach ($dbTargetInBank as $key => $val) {
    $targetInBank = $val;
//    debug('伝票対象振替先口座情報：'.print_r($targetInBank, true));
  }
}

// 新規登録画面か編集画面か判定用フラグ
$edit_deposit_flg = (!isset($dbFormData['deposit_id'])) ? false : true;
debug('$edit_deposit_flg確認：'.print_r($edit_deposit_flg, true));
$edit_trans_flg = (!isset($dbFormData['trans_id'])) ? false : true;
debug('$edit_trans_flgの確認：'.print_r($edit_trans_flg, true));

// DBからメインカテゴリ情報を取得
$dbMainCategoryData = getMainCategory();
// DBからサブカテゴリ情報を取得
$dbSubCategoryData = getSubCategory($_SESSION['user_id']);
// DBから口座情報を取得
$dbBank = getBankAll($_SESSION['user_id']);

// パラメータ改ざんチェック
//-------------------------------------
if (!empty($deposit_id) && empty($dbFormData)) {
  debug('GETパラメータの伝票IDが違います。トップページへ遷移します。');
  header("Location:index.php");
}

//-------------------------------------
// POST送信されている場合
//-------------------------------------
if (!empty($_POST)) {
  debug('POST送信があります。');
//  debug('POST情報：' . print_r($_POST, true));

  // 収支フラグ(deposit_flg)を変数に代入し判定
  $deposit_flg = $_POST['sort'];
  if ($deposit_flg == 0) {
    debug('支出処理です。');
  } else if ($deposit_flg == 1) { 
    debug('収入処理です。');
  } else if ($deposit_flg == 2) {
    debug('振替処理です。');
  }

  // 支出、収入
  //-----------------------------------
  if ($deposit_flg == 0 || $deposit_flg == 1) {
    // 変数にユーザー情報を代入
    $user_id = $_SESSION['user_id'];
    $regist_data = $_POST['regist_data'];
    $price = (!empty($_POST['price'])) ? $_POST['price'] : 0;
    $comment = $_POST['comment'];
    $mainc = $_POST['mainc_id'];
    $subc = $_POST['subc_id'];
    $bank = $_POST['bank_id'];
    $current_price = $_POST['current_price'];
      
    // 削除ボタンか登録(編集)ボタンか
    if (isset($_POST['delete'])) {
      debug('削除ボタンが押されました');

      // 削除時の口座編集残高
      $deleteBankCurrentPrice = ($deposit_flg == 0) ? $targetBank['current_price'] + $price : (($deposit_flg == 1) ? $targetBank['current_price'] - $price : '');
//      debug('削除時の口座登録残高:'.print_r($deleteBankCurrentPrice, true));
      
      // データベースから削除
      // 例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        // 収支伝票処理
        $sql1 = 'UPDATE deposit SET delete_flg = 1 WHERE deposit_id = :deposit_id';
        $data1 = array('deposit_id' => $deposit_id);
        // 口座処理
        $sql2 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :bank_id';
        $data2 = array('user_id' => $_SESSION['user_id'], ':bank_id' => $bank, ':current_price' => $deleteBankCurrentPrice);
        // クエリ実行
        $stmt1 = queryPost($dbh, $sql1, $data1);
        $stmt2 = queryPost($dbh, $sql2, $data2);

        // クエリ成功の場合
        if ($stmt1 && $stmt2) {
//          debug('クエリ成功。削除されました。');
          header('Location:sliplComplete.php');
        } else {
//          debug('エラー発生。');
          $err_msg['common'] = MSG01;
        }
      } catch (Exception $e) {
        error_log('【エラー発生】：' . $e->getMessage());
        $err_msg['common'] = MSG01;
      }

    } else if (isset($_POST['regist'])) {
//      debug('登録(編集)ボタンが押されました');

      // バリデーションチェック
      // 更新の場合はDB情報と入力情報が異なる場合にバリデーションチェック
      if (empty($dbFormData)) { //$dbFormData=空なら新規登録
        // 未入力チェック
        validRequired($regist_data, 'regist_data');
        validRequired($bank, 'bank_id');
        validRequired($price, 'price');
        // セレクトボックスチェック
        validSelect($mainc, 'mainc_id');
        validSelect($subc, 'subc_id');
        validSelect($bank, 'bank_id');
        // 最大文字数チェック
        validMaxLen($price, 'price');
        validMaxLen($comment, 'comment');
        // 半角文字チェック
        validHalf($price, 'price');
      } else {
        if ($dbFormData['regist_data'] !== $regist_data) {
          // 未入力チェック
          validRequired($regist_data, 'regiust_data');
          // 最大文字数チェック
          validMaxLen($regist_data, 'regist_data');
        }
        if ($dbFormData['mainc_id'] !== $mainc) {
          // selectboxチェック
          validSelect($mainc, 'mainc_id');
        }
        if ($dbFormData['subc_id'] !== $subc) {
          // selectboxチェック
          validSelect($subc, 'subc_id');
        }
        if ($dbFormData['bank_id'] !== $bank) {
          // selectboxチェック
          validSelect($bank, 'bank_id');
        }
        if ($dbFormData['price'] != $price) {
          // 未入力チェック
          validRequired($price, 'price');
          // 最大文字数チェック
          validMaxLen($price, 'price');
        }
        if ($dbFormData['comment'] !== $comment) {
          // 最大文字数チェック
          validMaxLen($comment, 'comment');
        }
      }

      if (empty($err_msg)) {
        debug('バリデーションOK。');

        // 口座残高調整
        if($edit_deposit_flg){

          // 更新時の口座変更がされた場合
          if($bank !== $dbFormData['bank_id']){
          debug('口座が変更されました');
            // 支出の場合
            if($deposit_flg == 0){
              debug('支出');
              // 変更前の口座残高がプラスの場合
              if($targetBank['current_price'] >= 0){
                $updataBeforBankCurrentPrice = $targetBank['current_price'] + $dbFormData['price'];
                debug('変更前口座の残高調整データ1：'.print_r($updataBeforBankCurrentPrice, true));
              }elseif($targetBank['current_price'] < 0){
                $updataBeforBankCurrentPrice = 0 - abs($dbFormData['price'] - abs($targetBank['current_price']));
                debug('変更前口座の残高調整データ2：'.print_r($updataBeforBankCurrentPrice, true));
              }
              // 変更後口座の残高がプラスの場合
              if($current_price >= 0){
                $updataAfterBankCurrentPrice = $current_price - $price;
                debug('変更後口座の残高調整データ1：'.print_r($updataAfterBankCurrentPrice, true));
              }elseif($current_price < 0){
                $updataAfterBankCurrentPrice = 0 - abs($price + (abs($current_price)));
                debug('変更後口座の残高調整データ2：'.print_r($updataAfterBankCurrentPrice, true));
              }
            // 収入の場合
            }elseif($deposit_flg == 1){
              debug('収入');
              // 変更前の口座残高がプラスの場合
              if($targetBank['current_price'] >= 0){
                $updataBeforBankCurrentPrice = $targetBank['current_price'] - $dbFormData['price'];
                debug('変更前口座の残高調整データ1：'.print_r($updataBeforBankCurrentPrice, true));
              }elseif($targetBank['current_price'] < 0){
                $updataBeforBankCurrentPrice = 0 - abs($dbFormData['price'] + (abs($targetBank['current_price'])));
                debug('変更前口座の残高調整データ2：'.print_r($updataBeforBankCurrentPrice, true));
              }
              // 変更後口座の残高がプラスの場合
              if($current_price >= 0){
                $updataAfterBankCurrentPrice = $current_price + $price;
                debug('変更後口座の残高調整データ1：'.print_r($updataAfterBankCurrentPrice, true));
              }elseif($current_price < 0){
                $updataAfterBankCurrentPrice = 0 - abs($price - (abs($current_price)));
                debug('変更後口座の残高調整データ2：'.print_r($updataAfterBankCurrentPrice, true));
              }
            }
            // 口座は変更されていないが、金額が変更された場合
          }elseif($bank === $dbFormData['bank_id'] && $price !== $dbFormData['price']){
            debug('口座は変更されていないが、金額が変更されました。');
            // 金額変更前の口座残高調整
            // 支出の場合
            if($deposit_flg == 0){
            debug('支出');
              // 対象口座の残高がプラスの場合
              if($targetBank['current_price'] >= 0){
                $updataBeforBankCurrentPrice = $targetBank['current_price'] + $dbFormData['price'];
                debug('金額変更前口座の残高調整データ1：'.print_r($updataBeforBankCurrentPrice, true));
              }elseif($targetBank['current_price'] < 0){
                $updataBeforBankCurrentPrice = 0 - abs($dbFormData['price'] - (abs($targetBank['current_price'])));
                debug('金額変更前口座の残高調整データ2：'.print_r($updataBeforBankCurrentPrice, true));
              }
              // 調整後残高がプラスの場合
              if($updataBeforBankCurrentPrice >= 0){
                $updataAfterBankCurrentPrice = $updataBeforBankCurrentPrice - $price;
                debug('金額変更後口座の残高調整データ1：'.print_r($updataAfterBankCurrentPrice, true));
              }elseif($updataBeforBankCurrentPrice < 0){
                $updataAfterBankCurrentPrice = 0 - abs($price + (abs($updataBeforBankCurrentPrice)));
                debug('金額変更後口座の残高調整データ2：'.print_r($updataBeforBankCurrentPrice, true));
              }
            // 収入の場合
            }elseif($deposit_flg == 1){
              debug('収入');
              // 対象講座の残高がプラスの場合
              if($targetBank['current_price'] >= 0){
                $updataBeforBankCurrentPrice = $targetBank['current_price'] - $dbFormData['price'];
                debug('金額変更前口座の残高調整データ1：'.print_r($updataBeforBankCurrentPrice, true));
              }elseif($targetBank['current_price'] < 0){
                $updataBeforBankCurrentPrice = 0 - abs($dbFormData['price'] + (abs($targetBank['current_price'])));
                debug('金額変更前口座の残高調整データ2：'.print_r($updataBeforBankCurrentPrice, true));
              }
              // 調整後残高がプラスの場合
              if($updataBeforBankCurrentPrice >= 0){
                $updataAfterBankCurrentPrice = $updataBeforBankCurrentPrice + $price;
                debug('金額変更後口座の残高調整データ：'.print_r($updataAfterBankCurrentPrice, true));
              }elseif($updataBeforBankCurrentPrice < 0){
                $updataAfterBankCurrentPrice = 0 - abs($price - (abs($updataBeforBankCurrentPrice)));
                debug('金額変更後口座の残高調整データ：'.print_r($updataBeforBankCurrentPrice, true));
              }
            }
          
          // 口座も金額も変更されていない場合
          }elseif($bank === $dbFormData['bank_id'] && $price === $dbFormData['price']){
            debug('口座、金額ともに変更されていません');
          }

        }else{
          // 新規登録時の口座登録残高
          if($deposit_flg == 0){
            debug('支出');
            // 調整後残高がプラスの場合
            if($current_price >= 0){
              $registBankCurrentPrice = $current_price - $price;
              debug('新規登録時口座の残高登録用データ1：'.print_r($registBankCurrentPrice, true));
            }elseif($current_price < 0){
              $registBankCurrentPrice = 0 - abs($price + (abs($current_price)));
              debug('新規登録時口座の残高登録用データ2：'.print_r($registBankCurrentPrice, true));
            }
          // 収入の場合
          }elseif($deposit_flg == 1){
            debug('収入');
            // 調整後残高がプラスの場合
            if($current_price >= 0){
              $registBankCurrentPrice = $current_price + $price;
              debug('新規登録時口座の残高登録用データ1：'.print_r($registBankCurrentPrice, true));
            }elseif($current_price < 0){
              $registBankCurrentPrice = 0 - abs($price - (abs($current_price)));
              debug('新規登録時口座の残高登録用データ2：'.print_r($registBankCurrentPrice, true));
            }
          }
        }

        // 例外処理
        try {
          // DBへ接続
          $dbh = dbConnect();
          // SQL文作成
          if ($edit_deposit_flg) {
            debug('更新です');
            // 伝票(deposit)テーブルの更新
            $sql1 = 'UPDATE deposit SET deposit_flg = :deposit_flg, price = :price, bank_id = :bank_id, mainc_id = :mainc, subc_id = :subc, comment = :comment, regist_data = :regist_data WHERE `user_id` = :user_id AND deposit_id = :deposit_id';
            $data1 = array(':user_id' => $user_id, ':deposit_id' => $deposit_id, ':deposit_flg' => $deposit_flg, ':price' => $price, ':bank_id' => $bank, ':mainc' => $mainc, ':subc' => $subc, 'comment' => $comment, ':regist_data' => $regist_data);
            // クエリ実行
            $stmt1 = queryPost($dbh, $sql1, $data1);

            // 口座変更、または金額変更時の口座情報更新
            if ($bank !== $dbFormData['bank_id'] || $price !== $dbFormData['price']) {
              // 変更前口座(bank)テーブルの更新
              $sql2 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :bank_id';
              $data2 = array(':user_id' => $user_id, ':bank_id' => $dbFormData['bank_id'], ':current_price' => $updataBeforBankCurrentPrice);
              // 変更後口座(bank)テーブルの更新
              $sql3 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :bank_id';
              $data3 = array(':user_id' => $user_id, ':bank_id' => $bank, ':current_price' => $updataAfterBankCurrentPrice);
              // クエリ実行
              $stmt2 = queryPost($dbh, $sql2, $data2);
              $stmt3 = queryPost($dbh, $sql3, $data3);
            }

            // 口座、または金額変更されていた場合
            if ($bank !== $dbFormData['bank_id'] || $price !== $dbFormData['price']) {
              if ($stmt1 && $stmt2 && $stmt3) {
                debug('クエリ成功。');
                header('Location:sliplComplete.php');
              } else {
                debug('クエリ失敗。');
                $err_msg['common'] = MSG01;
              }
            // 口座変更と金額変更がない場合
            }elseif($bank === $dbFormData['bank_id'] && $price === $dbFormData['price']){
              if ($stmt1) {
                debug('クエリ成功。');
                header('Location:sliplComplete.php');
              } else {
                debug('クエリ失敗。');
                $err_msg['common'] = MSG01;
              }
            }

          } else {
            debug('新規登録です。');
            // 伝票(deposit)テーブルの新規登録
            $sql1 = 'INSERT INTO deposit (`user_id`, deposit_flg, price, bank_id, mainc_id, subc_id, comment, regist_data) VALUE (:user_id, :deposit_flg, :price, :bank, :mainc, :subc, :comment, :regist_data)';
            $data1 = array(':user_id' => $user_id, ':deposit_flg' => $deposit_flg, ':price' => $price, ':bank' => $bank, ':mainc' => $mainc, ':subc' => $subc, ':comment' => $comment, ':regist_data' => $regist_data);
            // 口座(bank)テーブルの残高更新
            $sql2 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :bank';
            $data2 = array(':current_price' => $registBankCurrentPrice, ':user_id' => $user_id, ':bank' => $bank);

            // クエリ実行
            $stmt1 = queryPost($dbh, $sql1, $data1);
            $stmt2 = queryPost($dbh, $sql2, $data2);

            if ($stmt1 && $stmt2) {
              debug('クエリ成功。');
              header('Location:sliplComplete.php');
            } else {
              debug('クエリ失敗。');
              $err_msg['common'] = MSG01;
            }
          }
          
        } catch (Exception $e) {
          error_log('エラー発生：' . $e->getMessage());
          $err_msg['common'] = MSG01;
        }
      }
    }

  // 振替
  //-------------------------------------
  }elseif ($deposit_flg == 2) {
    // 変数にユーザー情報を代入
    $user_id = $_SESSION['user_id'];
    $regist_data = $_POST['regist_data'];
    $price = (!empty($_POST['price'])) ? $_POST['price'] : 0;
    $outBank = $_POST['outbank_id'];
    $out_current_price = $_POST['out_current_price'];
    $inBank = $_POST['inbank_id'];
    $in_current_price = $_POST['in_current_price'];
    $comment = $_POST['comment'];

    // 削除ボタンか登録(編集)ボタンか
    if (isset($_POST['delete'])) {
      debug('削除ボタンが押されました');
    
      // 削除時の残高調整
      // 振替元口座
      $deleteOutBankCurrentPrice = $targetOutBank['current_price'] + $price;
//      debug('削除時の振替元口座残高調整結果:'.print_r($deleteOutBankCurrentPrice, true));
      // 振替先口座
      $deleteInBankCurrentPrice = $targetInBank['current_price'] - $price;
//      debug('削除時の振替先口座残高調整結果:'.print_r($deleteInBankCurrentPrice, true));
      
      // データベースから削除
      // 例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        // 振替伝票テーブルの更新
        $sql1 = 'UPDATE trans SET delete_flg = 1 WHERE trans_id = :trans_id';
        $data1 = array(':trans_id' => $trans_id);
        // 振替元口座(bank)テーブルの更新
        $sql2 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :outbank_id';
        $data2 = array(':user_id' => $user_id, ':outbank_id' => $outBank, ':current_price' => $deleteOutBankCurrentPrice);
        // 振替先口座(bank)テーブルの更新
        $sql3 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :inbank_id';
        $data3 = array(':user_id' => $user_id, ':inbank_id' => $inBank, ':current_price' => $deleteInBankCurrentPrice);
        
        // クエリ実行
        $stmt1 = queryPost($dbh, $sql1, $data1);
        $stmt2 = queryPost($dbh, $sql2, $data2);
        $stmt3 = queryPost($dbh, $sql3, $data3);

        // クエリ成功の場合
        if ($stmt) {
          debug('クエリ成功。削除されました。');
          header('Location:sliplComplete.php');
        } else {
          debug('エラー発生。');
          $err_msg['common'] = MSG01;
        }
      } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG01;
      }

    } else if (isset($_POST['regist'])) {
      debug('登録(編集)ボタンが押されました');

      // バリデーションチェック
      // 更新の場合はDB情報と入力情報が異なる場合にバリデーションチェック
      if (empty($dbFormData)) {
        // 未入力チェック
        validRequired($regist_data, 'regist_data');
        validRequired($price, 'price');
        // セレクトボックスチェック
        validSelect($outBank, 'outbank_id');
        validSelect($inBank, 'inbank_id');
        // 最大文字数チェック
        validMaxLen($price, 'price');
        validMaxLen($comment, 'comment');
        // 半角文字チェック
        validHalf($price, 'price');
      } else {
        if ($dbFormData['regist_data'] !== $regist_data) {
          // 未入力チェック
          validRequired($regist_data, 'regiust_data');
          // 最大文字数チェック
          validMaxLen($regist_data, 'regist_data');
        }
        if ($dbFormData['outbank_id'] !== $outBank) {
          // selectboxチェック
          validSelect($outBank, 'outbank_id');
        }
        if ($dbFormData['inbank_id'] !== $inBank) {
          // selectboxチェック
          validSelect($inBank, 'inbank_id');
        }
        if ($dbFormData['price'] != $price) {
          // 未入力チェック
          validRequired($price, 'price');
          // 最大文字数チェック
          validMaxLen($price, 'price');
        }
        if ($dbFormData['comment'] !== $comment) {
          // 最大文字数チェック
          validMaxLen($comment, 'comment');
        }
      }

      if (empty($err_msg)) {
        debug('バリデーションOK。');
        
        // 更新、新規登録時の残高編集
        if ($edit_trans_flg) {

          // 更新前口座の残高調整
          // 振替元口座
          // 更新後振替元口座と更新前振替口座が違う、または、振込元口座変更はないが金額が変更された場合
          if($outBank !== $dbFormData['outbank_id']){

            // 更新前振替元口座情報取得時残高がプラスの場合
            if($targetOutBank['current_price'] >= 0){
              $beforOutBankCurrentPrice = $targetOutBank['current_price'] + $dbFormData['price'];
              debug('更新前振替元口座の残高調整データ1:'.print_r($beforOutBankCurrentPrice, true));
            // 更新前振替元口座情報取得時残高がマイナスの場合
            }elseif($targetOutBank['current_price'] < 0){
              $beforOutBankCurrentPrice = 0 - abs($dbFormData['price'] - (abs($targetOutBank['current_price'])));
              debug('更新前振替元口座の残高調整データ2:'.print_r($beforOutBankCurrentPrice, true));
            }
          // 更新後振替元口座と更新前振替元口座、金額が同じ場合
          }elseif($outBank === $dbFormData['outbank_id'] && $price === $dbFormData['price']){
            debug('振替元口座、金額ともには変更がありません。');
          }

          // 振替先口座
          // 更新後振替先口座と更新前振替先口座が違う、または、振込先講座の変更はないが金額が変更されていた場合
          if ($inBank !== $dbFormData['inbank_id']) {

            // 更新前振替先口座の残高調整
            // 更新前振替先口座情報取得時残高がプラスの場合
            if ($targetInBank['current_price'] >= 0) {
              $beforInBankCurrentPrice = $targetInBank['current_price'] - $dbFormData['price'];
              debug('更新前振替先口座の残高調整データ1:'.print_r($beforInBankCurrentPrice, true));
            } elseif ($targetInBank['current_id'] < 0) {
              $beforInBankCurrentPrice = 0 - abs($dbFormData['price'] + (abs($targetInBank['current_price'])));
              debug('更新前振替先口座の残高調整データ2:'.print_r($beforInBankCurrentPrice, true));
            }

          // 更新後振替先口座と更新前振替先口座、金額が同じ場合
          }elseif($inbank === $dbFormData['inbank_id'] && $price === $dbFormData['price']){
            debug('振替先口座、金額の変更がありません');
          }

          // 更新後口座の調整
          // 振替元口座
          if($outBank !== $dbFormData['outbank_id']){
            // 更新後振替元口座と更新前振替先口座が同じ場合
            if($outBank === $dbFormData['inbank_id']){
              // 更新前振替先口座の調整残高がプラスの場合
              if($beforInBankCurrentPrice >= 0){
                $registOutCurrentPrice = $beforInBankCurrentPrice - $price;
                debug('更新後振替元口座の残高登録データ1:'.print_r($registOutCurrentPrice, true));
              // 更新前振替先口座の調整残高がマイナスの場合
              }elseif($beforInBankCurrentPrice < 0){
                $registOutCurrentPrice = 0 - abs($price + (abs($beforInBankCurrentPrice)));
                debug('更新後振替元口座の残高登録データ2:'.print_r($registOutCurrentPrice, true));
              }
            // 更新後振替元口座と更新前振替先口座が違う場合
            }elseif($outBank !== $dbFormData['inbank_id']){
              // 更新後振替先口座の残高がプラスの場合
              if($out_current_price >= 0){
                $registOutCurrentPrice = $out_current_price - $price;
                debug('更新後振替元口座の残高登録データ3:'.print_r($registOutCurrentPrice, true));
              }elseif($out_current_price < 0){
                $registOutCurrentPrice = 0 - abs($price + (abs($out_current_price)));
                debug('更新後振替元口座の残高登録データ4:'.print_r($registOutCurrentPrice, true));
              }
            }
          // 更新前後で振替元口座は変更されていないが、金額が変更されている場合
          }elseif($outBank === $dbFormData['outbank_id'] && $price !== $dbFormData['price']){
            // 振替元口座の残高調整
            if($out_current_price >= 0){
              $beforOutBankCurrentPrice = $out_current_price + $dbFormData['price'];
              debug('金額変更振替元口座の残高調整データ1:'.print_r($beforOutBankCurrentPrice, true));
            }elseif($out_current_price < 0){
              $beforOutBankCurrentPrice = 0 - abs($dbFormData['price'] - (abs($out_current_price)));
              debug('金額変更振替元口座の残高調整データ2:'.print_r($beforOutBankCurrentPrice, true));
            }
            // 振替元口座の調整残高がプラスの場合
            if($beforOutBankCurrentPrice >= 0){
              $registOutCurrentPrice = $beforOutBankCurrentPrice - $price;
              debug('金額変更後振替元口座の残高登録データ1:'.print_r($registOutCurrentPrice, true));
            }elseif($beforOutBankCurrentPrice < 0){
              $registOutCurrentPrice = 0 - abs($price + (abs($beforOutBankCurrentPrice))); 
              debug('金額変更後振替元口座の残高登録データ2:'.print_r($registOutCurrentPrice, true));
            }
          }

          // 振替先口座
          if($inBank !== $dbFormData['inbank_id']){
            // 更新後の振替先口座が更新前振替元口座と同じ場合
            if($inBank === $dbFormData['outbank_id']){
              // 更新前振替元口座の調整残高がプラスの場合
              if($beforOutBankCurrentPrice >= 0){
                $registInCurrentPrice = $beforOutBankCurrentPrice + $price;
                debug('更新後振替先口座の残高登録データ1:'.print_r($registInCurrentPrice, true));
              }elseif($beforOutBankCurrentPrice < 0){
                $registInCurrentPrice = 0 - abs($price - (abs($beforOutBankCurrentPrice)));
                debug('更新後振替先口座の残高登録データ2:'.print_r($registInCurrentPrice, true));
              }
            }elseif($inBank !== $dbFormData['outbank_id']){
              // 登録振替先口座の取得残高がプラスの場合
              if($in_current_price >= 0){
                $registInCurrentPrice = $in_current_price + $price;
                debug('更新後振替先口座の残高登録データ3:'.print_r($registInCurrentPrice, true));
              // 登録振替先口座の残高がマイナスの場合
              }elseif($in_current_price < 0){
                $registInCurrentPrice = 0 - abs($price + (abs($in_current_price)));
                debug('更新後振替先口座の残高登録データ4:'.print_r($registInCurrentPrice, true));
              }
            }

          // 更新前後で振替先口座の変更はないが、金額変更があった場合
          }elseif($inBank === $dbFormData['inbank_id'] && $price !== $dbFormData['prince']){
            // 振替先口座の残高調整
            if($in_current_price >= 0){
              $beforInBankCurrentPrice = $in_current_price - $dbFormData['price'];
              debug('金額変更振替元口座の残高調整データ1:'.print_r($beforInBankCurrentPrice, true));
            }elseif($in_current_price < 0){
              $beforInBankCurrentPrice = 0 - abs($dbFormData['price'] + (abs($in_current_price)));
              debug('金額変更振替元口座の残高調整データ2:'.prine_r($beforInBankCurrentPrice, true));
            }
            // 振替先口座の調整残高がプラスの場合
            if($beforInBankCurrentPrice >= 0){
              $registInCurrentPrice = $beforInBankCurrentPrice + $price;
              debug('更新後振替先口座の登録残高データ1：'.print_r($registInCurrentPrice, true));
            }elseif($beforInBankCurrentPrice < 0){
              $registInCurrentPrice = 0 - abs($price - (abs($beforInBankCurrentPrice)));
              debug('更新後振替先口座の登録残高データ2：'.print_r($registInCurrentPrice, true));
            }
          }

          // 新規登録時の残高調整
        }else{
          // 振替元口座の残高がプラスの場合
          if($out_current_price >= 0){
            $registOutCurrentPrice = $out_current_price - $price;
            debug('新規振替元口座の残高登録データ1:'.print_r($registOutCurrentPrice, true));
          }elseif($out_current_price < 0){
            $registOutCurrentPrice = 0 - abs($price + (abs($out_current_price)));
            debug('新規振替元口座の残高登録データ2:'.print_r($registOutCurrentPrice, true));
          }
          // 振替先口座の残高がプラスの場合
          if($in_current_price >= 0){
            $registInCurrentPrice = $in_current_price + $price;
            debug('新規振替先口座の残高登録データ1:'.print_r($registInCurrentPrice, true));
          }elseif($in_current_price < 0){
            $registInCurrentPrice = 0 - abs($price - (abs($in_current_price)));
            debug('新規振替先口座の残高登録データ2:'.print_r($registInCurrentPrice, true));
          }
        }

        // 例外処理
        try {
          // DBへ接続
          $dbh = dbConnect();
          // SQL文作成
          if ($edit_trans_flg) {
            debug('更新です');
            // 伝票(trans)テーブルの更新
            $sql1 = 'UPDATE trans SET regist_data = :regist_data, price = :price, inbank_id = :inbank, outbank_id = :outbank, comment = :comment WHERE `user_id` = :user_id AND trans_id = :trans_id';
            $data1 = array(':user_id' => $user_id, ':trans_id' => $trans_id, ':regist_data' => $regist_data, ':price' => $price, ':inbank' => $inBank, ':outbank' => $outBank, 'comment' => $comment);
            // クエリ実行
            $stmt1 = queryPost($dbh, $sql1, $data1);

            // 更新後振替元口座と更新前振替元口座が違う、または金額が変更された場合
            if($dbFormData['outbank_id'] !== $outBank || $price !== $dbFormData['price']){
              // 変更前振替元口座(bank)テーブルの更新
              $sql2 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :bank_id';
              $data2 = array(':user_id' => $user_id, ':bank_id' => $dbFormData['outbank_id'], ':current_price' => $beforOutBankCurrentPrice);
              $stmt2 = queryPost($dbh, $sql2, $data2);
            }

            // 更新前振替先口座と更新後振替先口座が違う場合
            if($dbFormData['inbank_id'] !== $inBank || $price !== $dbFormData['price']){
              // 更新前振替先口座(bank)テーブルの更新
              $sql3 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :bank_id';
              $data3 = array(':user_id' => $user_id, ':bank_id' => $dbFormData['inbank_id'], ':current_price' => $beforInBankCurrentPrice);
              // クエリ実行
              $stmt3 = queryPost($dbh, $sql3, $data3);
              
            }

            // 変更後振替元口座と変更前振替元口座が違う場合
            if($outBank !== $dbFormData['outbank_id'] || $price !== $dbFormData['price']){
              // 更新後振替元口座(bank)テーブルの更新
              $sql4 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :bank_id';
              $data4 = array(':user_id' => $user_id, ':bank_id' => $outBank, ':current_price' => $registOutCurrentPrice);
              // クエリ実行
              $stmt4 = queryPost($dbh, $sql4, $data4);
            }
              
            // 変更後振替先口座と変更前振替先口座が違う場合
            if($inBank !== $dbFormData['inbank_id'] || $price !== $dbFormData['price']){
              // 更新後振替先口座(bank)テーブルの更新
              $sql5 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :bank_id';
              $data5 = array(':user_id' => $user_id, ':bank_id' => $inBank, ':current_price' => $registInCurrentPrice);
              $stmt5 = queryPost($dbh, $sql5, $data5);
            }
              
            // 登録日時、もしくはコメントだけが変わっていた場合
            if($regist_data !== $dbFormData['regist_data'] || $comment !== $dbFormData['comment']){
              if ($stmt1){
                debug('クエリ1成功。');
                header('Location:sliplComplete.php');
              } else {
                debug('クエリ1失敗。');
                $err_msg['common'] = MSG01;
              }
            // 金額が変わっていた、または振替元、振替先口座がともに変わっていた場合
            }elseif($price !== $dbFormData['price'] || $outBank !== $dbFormData['outbank_id'] && $inBank !== $dbFormData['inbank_id']){
              if ($stmt1 && $stmt2 && $stmt3 && $stmt4 && $stmt5)  {
                debug('クエリ2成功。');
                header('Location:sliplComplete.php');
              } else {
                debug('クエリ2失敗。');
                $err_msg['common'] = MSG01;
              }
            // 振替元口座のみが変わっていた場合
            }elseif($outBank !== $dbFormData['outbank_id']){
              if ($stmt1 && $stmt2 && $stmt4)  {
                debug('クエリ3成功。');
                header('Location:sliplComplete.php');
              } else {
                debug('クエリ3失敗。');
                $err_msg['common'] = MSG01;
              }
            // 振替先口座のみが変わっていた場合
            }elseif($inBank !== $dbFormData['inbankid']){
              if ($stmt1 && $stmt3 && $stmt5)  {
                debug('クエリ4成功。');
                header('Location:sliplComplete.php');
              } else {
                debug('クエリ4失敗。');
                $err_msg['common'] = MSG01;
              }
            }

          } else {
            debug('新規登録です。');
            // 伝票(trans)テーブルの新規登録
            $sql1 = 'INSERT INTO trans (`user_id`, price, inbank_id, outbank_id, comment, regist_data) VALUE (:user_id, :price, :inbank, :outbank, :comment, :regist_data)';
            $data1 = array(':user_id' => $user_id, ':price' => $price, ':inbank' => $inBank, ':outbank' => $outBank, ':comment' => $comment, ':regist_data' => $regist_data);
            // 出金口座(bank)テーブルの更新
            $sql2 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :bank_id';
            $data2 = array(':current_price' => $registOutCurrentPrice, ':user_id' => $user_id, ':bank_id' => $outBank);
            // 入金口座(bank)テーブルの更新
            $sql3 = 'UPDATE bank SET current_price = :current_price WHERE `user_id` = :user_id AND bank_id = :bank_id';
            $data3 = array(':current_price' => $registInCurrentPrice, ':user_id' => $user_id, ':bank_id' => $inBank);

            // クエリ実行
            $stmt1 = queryPost($dbh, $sql1, $data1);
            $stmt2 = queryPost($dbh, $sql2, $data2);
            $stmt3 = queryPost($dbh, $sql3, $data3);

            if ($stmt1 && $stmt2 && $stmt3) {
              debug('クエリ成功。');
              header('Location:sliplComplete.php');
            } else {
              debug('クエリ失敗。');
              $err_msg['common'] = MSG01;
            }
          }

        } catch (Exception $e) {
          error_log('エラー発生：' . $e->getMessage());
          $err_msg['common'] = MSG01;
        }
      }
    }
  }
}
?>
<?php
$siteTitle = "レシート入力";
require('head.php');
?>

<body>

  <!-- ヘッダー -->
  <?php require('header.php'); ?>

  <!-- メインコンテンツ -->
  <div id="main" class="site-width">

    <!-- 入力エリア -->
    <section class="page-slip page-2colum">
      <nav>
        <ul class="slip-tab-area">
          <li class="tab-item js-tab-change <?php if($deposit_flg == 0) echo 'tab-success'; ?>" data-val="0">支出</li>
          <li class="tab-item js-tab-change <?php if($deposit_flg == 1) echo 'tab-success'; ?>" data-val="1">収入</li>
          <li class="tab-item js-tab-change <?php if($deposit_flg == 2) echo 'tab-success'; ?>" data-val="2">振替</li>
        </ul>
      </nav>

      <div class="panel-group">

        <!-- 支出、収入エリア -->
        <div class="slip-deposit js_panel_show">
          <form action="" method="post">

            <input type="hidden" name="sort" value="<?php echo $deposit_flg; ?>" class="js-deposit-flg">

            <div class="msg-common-area <?php echo setClassErr('common'); ?>"><?php echo getErrMsg('common'); ?></div>

            <span class="js-required notes-err">
              <?php if(empty($dbFormData)) echo '※選択または入力してください'; ?>
            </span>
            <input type="date" name="regist_data" value="<?php echo getFormData('regist_data'); ?>" class="js-slip-date">
            <div class="msg-area <?php echo setClassErr('regist_data'); ?>">
              <?php echo getErrMsg('regist_data'); ?>
            </div>

            <div class="slip-category-area">
              <span class="js-required notes-err">
                <?php if(empty($dbFormData)) echo '※メインカテゴリから選択してください'; ?>
              </span>
              <select name="mainc_id" class="main-cate js-slip-mcate">
                <option value="0 <?php if(getFormData('mainc_id') == 0) echo 'selected'; ?>">メインカテゴリ</option>
                <?php foreach ($dbMainCategoryData as $key => $val) { ?>
                  <option value="<?php echo $val['mainc_id'] ?>" data-val="<?php echo $val['deposit_flg']; ?>" <?php if (getFormData('mainc_id') == $val['mainc_id']) echo 'selected'; ?>>
                    <?php echo $val['mainc_name']; ?>
                  </option>
                <?php } ?>
              </select>

              <select name="subc_id" class="sub-cate js-slip-scate" <?php if(getFormData('mainc_id') == 0) echo 'disabled'; ?>>
                <option value="0 <?php if (getFormData('subc_id') == 0) echo 'selected'; ?>">サブカテゴリ</option>
                <?php foreach ($dbSubCategoryData as $key => $val) { ?>
                  <option value="<?php echo $val['subc_id'] ?>" data-val="<?php echo $val['mainc_id']; ?>" <?php if (getFormData('subc_id') == $val['subc_id']) echo 'selected'; ?>>
                    <?php echo $val['subc_name']; ?>
                  </option>
                <?php } ?>
              </select>
            </div>
            <div class="msg-area <?php echo setClassErr('mainc_id', 'subc_id'); ?>"><?php echo getErrMsg('mainc_id', 'subc_id'); ?></div>

            <div class="slip-price-area">
              <span class="js-required notes-err area-bank-notes">
                <?php if(empty($dbFormData)) echo '※口座を選択し、金額を入力してください'; ?>
              </span>
              <select name="bank_id" class="bank js-bank-select">
                <option value="0 <?php if (getFormData('bank_id') == 0) echo 'selected'; ?>">口座名</option>
                <?php foreach ($dbBank as $key => $val) { ?>
                  <option value="<?php echo $val['bank_id'] ?>" <?php if (getFormData('bank_id') == $val['bank_id']) echo 'selected'; ?> data-val="<?php echo $val['current_price']; ?>">
                    <?php echo $val['bank_name']; ?>
                  </option>
                <?php } ?>
              </select>
              <input type="hidden" name="current_price" class="js-current-price">
              <input type="text" name="price" value="<?php echo getFormData('price'); ?>" class="price js-price" placeholder="金額">
            </div>
            <div class="msg-area <?php echo setClassErr('bank_id', 'price'); ?>">
              <?php echo getErrMsg('bank_id', 'price'); ?>
            </div>

            <textarea name="comment" class="slip-comment js-count" cols="30" rows="10" placeholder="メモ欄ですよ"><?php echo getFormData('comment'); ?></textarea>
            <div class="text-count">
              <p><span class="js-show-count"> 0 </span>/50文字</p>
            </div>
            <div class="msg-area">
              <p class="js-count-err"><?php echo getErrMsg('comment'); ?></p>
            </div>

            <div class="btn-container">
              <?php if (!empty($dbFormData)) { ?>
                <input type="submit" name="delete" class="btn btn-delete" value="削除する">
              <?php } ?>
              <input type="submit" name="regist" class="btn btn-regist" value="<?php if (!empty($dbFormData)) echo '変更する'; else echo '登録する' ?>">
            </div>
          </form>
        </div>

        <!-- 振替エリア -->
        <div class="slip-trans">
          <form action="" method="post">

            <input type="hidden" name="sort" value="<?php echo $deposit_flg; ?>" class="js-deposit-flg">

            <div class="msg-common-area <?php echo setClassErr('common'); ?>">
              <?php getErrMsg('common'); ?>
            </div>

            <span class="js-required notes-err">
              <?php if(empty($dbFormData)) echo '※選択または入力してください'; ?>
            </span>
            <input type="date" name="regist_data" value="<?php echo getFormData('regist_data'); ?>" class="js-slip-date">
            <div class="msg-area <?php echo setClassErr('data'); ?>">
              <?php echo getErrMsg('data'); ?>
            </div>

            <div class="slip-category-area">
              <span class="js-required notes-err">
                <?php if(empty($dbFormData)) echo '※出金した口座と入金した口座を選択してください'; ?>
              </span>
              <select name="outbank_id" class="main-cate js-outbank-select">
                <option value="0 <?php if (getFormData('outbank_id') == 0) echo 'selected'; ?>">振替元口座</option>
                <?php foreach ($dbBank as $key => $val) { ?>
                  <option value="<?php echo $val['bank_id'] ?>" <?php if (getFormData('outbank_id') == $val['bank_id']) echo 'selected'; ?> data-val="<?php echo $val['current_price']; ?>">
                    <?php echo $val['bank_name']; ?>
                  </option>
                <?php } ?>
              </select>
              <input type="hidden" name="out_current_price" class="js-out-current-price">

              <select name="inbank_id" class="sub-cate js-inbank-select">
                <option value="0 <?php if (getFormData('inbank_id') == 0) echo 'selected'; ?>">振替先口座</option>
                <?php foreach ($dbBank as $key => $val) { ?>
                  <option value="<?php echo $val['bank_id'] ?>" <?php if (getFormData('inbank_id') == $val['bank_id']) echo 'selected'; ?> data-val="<?php echo $val['current_price']; ?>">
                    <?php echo $val['bank_name']; ?>
                  </option>
                <?php } ?>
              </select>
              <input type="hidden" name="in_current_price" class="js-in-current-price">
            </div>

            <div class="msg-area <?php setClassErr('outbank_id', 'inbank_id'); ?>">
              <?php echo getErrMsg('outbank_id', 'inbank_id'); ?>
            </div>

            <div class="slip-price-area">
              <span class="js-required notes-err">
                <?php if(empty($dbFormData)) echo '※金額を入力してください'; ?>
              </span>
              <input type="text" name="price" value="<?php echo getFormData('price'); ?>" class="price js-trans-price">
            </div>

            <div class="msg-area <?php echo setClassErr('price'); ?>">
              <?php echo getErrMsg('price'); ?>
            </div>

            <textarea name="comment" class="slip-comment js-count" cols="30" rows="10" placeholder="メモ欄ですよ"><?php echo getFormData('comment'); ?></textarea>
            <div class="js-text-count text-count">
              <p><span class="js-show-count"> 0 </span>/50文字</p>
            </div>
            <div class="msg-area">
              <p class="js-count-err"><?php getErrMsg('comment'); ?></p>
            </div>

            <div class="btn-container">
                <?php if (!empty($dbFormData)) { ?>
                <input type="submit" name="delete" class="btn btn-delete" value="削除する">
              <?php } ?>
              <input type="submit" name="regist" class="btn btn-regist" value="<?php if (!empty($dbFormData)) echo '変更する'; else echo '登録する' ?>">
            </div>
          </form>
        </div>

      </div>
    </section>

    <!-- サイドメニュー -->
    <?php
    require('sidebar.php');
    ?>

  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>
