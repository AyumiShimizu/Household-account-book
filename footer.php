  <footer id="footer">

    Copyright Household account book</a>. All Rights Reserved.
    <a href="contact.php">お問合せはこちらからお願いします。</a>

  </footer>

  <script src="js/vendor/jquery-3.4.1.min.js"></script>
  <!-- 郵便番号から住所を自動入力 -->
  <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
  <script>
    
    $(function() {
      // フッターを最下部に固定
      var $ftr = $('#footer');
      if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
        $ftr.attr({
          'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'
        });
      }

      //=====================================
      // 定数
      //=====================================
      const MSG01 = "※入力必須です。";
      const MSG02 = "　入力されました!!";
      const MSG03 = "※選択必須です。";
      const MSG04 = "　選択されました!!";
      
      //=====================================
      // 正規表現を変数に定義
      //=====================================
      // 全角英数字記号
      var RegEx01 = /[Ａ-Ｚａ-ｚ０-９！＂＃＄％＆＇（）＊＋，－．／：；＜＝＞？＠［＼］＾＿｀｛｜｝]/g;
      // 半角英数字記号
      var RegEx02 = /[a-zA-Z0-9!#$%&()*+,.:;=?@\[\]^_{}-]/g;
      // 数字以外
      var RegEx03 = /[^0-9０-９]/g;
      //=====================================
      // 共通項目を関数化
      //=====================================
      // 入力確認メッセージ表示
      function checkInputMsg(ele) {
        // 入力欄が空だった場合
        if (!ele.val()) {
          ele.prev('.js-required').text(MSG01).removeClass('notes-success').addClass('notes-err');
          return false;
        } else {
          ele.prev('.js-required').text(MSG02).removeClass('err').addClass('notes-success');
          return true;
        }
      }
      // 選択確認メッセージ表示(
      function checkSelectMsg(ele) {
        // 入力欄が空だった場合
        if (!ele.val()) {
          ele.prev('.js-required').text(MSG03).removeClass('notes-success').addClass('notes-err');
          return false;
        } else {
          ele.prev('.js-required').text(MSG04).removeClass('notes-err').addClass('notes-success');
          return true;
        }
      }
      // 全角から半角への入力整形
      function convertHalf(ele) {
        // もし入力された文字が半角でなかった場合
        if (!ele.val().match(RegEx02)) {
          var $str = ele.val();
          var half = $str.replace(RegEx01, function(s) {
            return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
          });
          ele.val(half);
          return false;
        }
        return true;
      }
      // 数字以外の記号などを削除
      function convertNum(ele) {
        // もし入力された文字に数字以外の文字が入っていた場合
        if (ele.val().match(RegEx03)) {
          var str = ele.val();
          var hyphen_after = str.replace(RegEx03, "");
          ele.val(hyphen_after);
          return false;
        } else {
          return true;
        }
      }

      //=====================================
      // 入力部分バリデーション
      //=====================================
      // 新規登録、プロフィール編集、問い合わせ共通
      //-------------------------------------
      // ユーザーネーム入力フォーム
      $('js-name').on('change', function() {
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // Email入力フォーム
      $('.js-email').on('change', function() {
        // 入力された文字が半角でなかった場合
        convertHalf($(this));
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // 郵便番号入力フォーム
      $('.js-zip').on('change', function() {
        // 入力された文字に数字以外が入っていた場合
        convertNum($(this));
        // 入力された文字が半角でなかった場合
        convertHalf($(this));
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // TEL入力フォーム
      $('.js-tel').on('change', function() {
        // 入力された文字に数字以外が入っていた場合
        convertNum($(this));
        // 入力された文字が半角でなかった場合
        convertHalf($(this));
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // 生年月日入力フォーム
      $('.js-birth').on('change', function() {
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // 新規登録パスワード
      //-------------------------------------
      // パスワード入力フォーム
      $('.js-pass').on('change', function() {
        // 入力された文字が半角でなかった場合
        convertHalf($(this));
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // パスワード再入力フォーム
      $('.js-pass-re').on('change', function() {
        // 入力された文字が半角でなかった場合
        convertHalf($(this));
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // パスワード編集
      //-------------------------------------
      // 古いパスワード入力フォーム
      $('.js-pass-old').on('change', function() {
        // 入力された文字が半角でなかった場合
        convertHalf($(this));
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // 新しいパスワードフォーム
      $('.js-pass-new').on('change', function() {
        // 入力された文字が半角でなかった場合
        convertHalf($(this));
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // 新しいパスワード再入力フォーム
      $('.js-pass-new-re').on('change', function() {
        // 入力された文字が半角でなかった場合
        convertHalf($(this));
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // カテゴリ編集
      //-------------------------------------
      // 大カテゴリセレクトフォーム
      $('.js-main-cate').on('change', function() {
        // 入力確認メッセージ表示
        checkSelectMsg($(this));
      });

      // 小カテゴリ名入力フォーム
      $('.js-sub-cate').on('change', function() {
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // 口座編集
      //-------------------------------------
      // 口座名入力フォーム
      $('.js-bank-name').on('change', function() {
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // 口座初期設定残高入力フォーム(レシート振替入力ページの金額入力フォームと併用)
      $('.js-start_price').on('change', function() {
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // レシート入力画面共通部分
      //-------------------------------------
      // 日付選択フォーム
      $('.js-slip-date').on('change', function() {
        // 選択確認メッセージ表示
        checkSelectMsg($(this));
      });

      // メインカテゴリ選択フォーム
      $('.js-slip-mcate').on('change', function() {
        // サブカテゴリの値を要素に格納
        var sCateVal = $('.js-slip-scate').val();

        if(sCateVal === "0 selected"){
          // 注釈表示変更
          // メインカテゴリが選択されて、かつ、サブカテゴリが選択されていない場合
          if ($(this).val() !== "0 selected" && sCateVal === "0 selected") {
            $(this).prev('.js-required').text("※サブカテゴリーを選択してください　　　　").removeClass('notes-success').addClass('notes-err');
            return false;

          } else if ($(this).val() === "0 selected" && sCateVal === "0 selected") { //メインカテゴリーが未選択で、かつ、サブカテゴリが未選択の場合
            $(this).prev('.js-required').text("※メインカテゴリーから選択してください").removeClass('notes-success').addClass('notes-err');
            return false;

          } else { //メインカテゴリが選択されて、かつ、サブカテゴリが選択されている場合
            if ($(this).val() !== "0 selected" && sCateVal !== "0 selected") {
              $(this).prev('.js-required').text("カテゴリーがすべて選択されました!!").removeClass('notes-err').addClass('notes-success');
              return true;
            }
          }
        }  
      });

      // サブカテゴリ選択フォーム
      $('.js-slip-scate').on('change', function() {
        // メインカテゴリの値を変数に代入
        var mCateVal = $('.js-slip-mcate').val();

        // サブカテゴリが選択されて、かつ、メインカテゴリが選択されている場合
        if ($(this).val() !== "0 selected" && mCateVal !== "0 selected") {
          $(this).siblings('.js-required').text("カテゴリーがすべて選択されました!!").removeClass('notes-err').addClass('notes-success');
          return true;

        } else if ($(this).val() !== "0 selected" && mCateVal === "0 selected") { // サブカテゴリが選択されて、かつ、メインカテゴリが未選択の場合
          $(this).siblings('.js-required').text("※メインカテゴリを先に選択してください").removeClass('notes-success').addClass('notes-err');
          return false;

        } else if ($(this).val() === "0 selected" && mCateVal !== "0 selected") { // サブカテゴリが未選択で、かつ、メインカテゴリが選択選択の場合
          $(this).siblings('.js-required').text("※サブカテゴリを選択してください　　　").removeClass('notes-success').addClass('notes-err');
          return false;

        } else { // 上記以外の場合
          $(this).siblings('.js-required').text("※メインカテゴリから選択してください").removeClass('notes-success').addClass('notes-err');
          setDisabled(mCateVal);
          return false;
        }
      });

      // 口座選択フォーム
      $('.js-bank-select').on('change', function() {
        // 金額入力フォームの値を変数に格納
        var priceVal = $('.js-price').val();
        // 口座が選択されていて、かつ、金額が入力されていない場合
        if ($(this).val() !== "0 selected" && priceVal === "") {
          $(this).prev('.js-required').text("※金額を半角数字のみで入力してください").removeClass('notes-success').addClass('notes-err');
          return false;
        } else if ($(this).val() !== "0" && priceVal !== "") { //口座が選択されて、かつ、金額が入力されている場合
          $(this).prev('.js-required').text("口座が選択され、金額が入力されました!!").removeClass('notes-err').addClass('notes-success');
          return true;
        } else { //上記以外の場合
          $(this).prev('.js-required').text("※口座を選択し、金額を入力してください").removeClass('notes-success').addClass('notes-err');
          return false;
        }
      });

      // 金額入力フォーム
      $('.js-price').on('focusout', function() {
        // 入力された文字に数字以外が入っていた場合
        convertNum($(this));
        // 入力された文字が半角でなかった場合
        convertHalf($(this));
        // 口座選択フォームの値を変数に格納
        var bankVal = $('.js-bank-select').val();
        // 金額が入力されて、かつ、口座が選択されている場合
        if ($(this).val() !== "" && bankVal !== "0 selected") {
          $(this).siblings('.js-required').text("口座が選択され、金額が入力されました!!").removeClass('notes-err').addClass('notes-success');
          return true;
        } else if ($(this).val() !== "" && bankVal === "0 selected") { //金額が入力されて、かつ、口座が選択されていない場合
          $(this).siblings('.js-required').text("※支払いをした口座を選択してください").removeClass('notes-success').addClass('notes-err');
          return false;
        }
      });

      // 振替ページ口座選択フォーム
      //------------------------------------
      // 振替元口座選択フォーム
      $('.js-outbank-select').on('change', function() {
        // 振替先口座の値を変数に格納
        var inBank = $('.js-inbank-select').val();
        // 振替元口座が選択されて、かつ、振替先口座が選択されていない場合
        if ($(this).val() !== "0 selected" && inBank === "0 selected") {
          $(this).prev('.js-required').text("※入金した口座を選択してください").removeClass('notes-success').addClass('notes-err');
          return false;
          // 振替元口座が未選択で、かつ、振替先口座が選択されている場合
        } else if ($(this).val() === "0 selected" && inBank !== "0 selected") {
          $(this).prev('.js-required').text("※出金した口座を選択してください").removeClass('notes-success').addClass('notes-err');
          return false;
          // 振替元口座が未選択で、かつ、振替先口座も未選択の場合
        } else if ($(this).val() === "0 selected" && inBank === "0 selected") {
          $(this).prev('.js-required').text("※出金した口座と入金した口座を選択してください").removeClass('notes-success').addClass('notes-err');
        } else {
          $(this).prev('.js-required').text("出金、入金された口座の共に選択されました!!").removeClass('notes-err').addClass('notes-success');
          return true;
        }
      });

      // 振替先口座選択フォーム
      $('.js-inbank-select').on('change', function() {
        // 振替先口座の値を変数に格納
        var outBank = $('.js-outbank-select').val();
        // 振替先口座が選択されて、かつ、振替元口座が選択されていない場合
        if ($(this).val() !== "0 selected" && outBank === "0 selected") {
          $(this).siblings('.js-required').text("※出金した口座を選択してください").removeClass('notes-success').addClass('notes-err');
          return false;
          // 振替先口座が未選択で、かつ、振替元口座が選択されている場合
        } else if ($(this).val() === "0 selected" && outBank !== "0 selected") {
          $(this).siblings('.js-required').text("※入金した口座を選択してください").removeClass('notes-success').addClass('notes-err');
          return false;
          // 振替先口座が未選択で、かつ、振替元口座が未選択の場合
        } else if ($(this).val() === "0 selected" && outBank === "0 selected") {
          $(this).siblings('.js-required').text("※出金した口座と入金した口座を選択してください").removeClass('notes-success').addClass('notes-err');
        } else {
          $(this).siblings('.js-required').text("出金、入金された口座の共に選択されました!!").removeClass('notes-err').addClass('notes-success');
          return true;
        }
      });

      // 金額入力フォーム
      $('.js-trans-price').on('focusout', function() {
        // 入力された文字に数字以外が入っていた場合
        convertNum($(this));
        // 入力された文字が半角でなかった場合
        convertHalf($(this));
        // 金額が入力されている場合
        if ($(this).val !== "") {
          $(this).siblings('.js-required').text("金額が入力されました!!").removeClass('notes-err').addClass('notes-success');
          return true;
        } else { //金額が入力されていない場合
          $(this).siblings('.js-required').text("※金額を入力してください").removeClass('notes-success').addClass('notes-err');
          return false;
        }
      });

      // 問い合わせページ
      //------------------------------------
      // 名前
      $('.js-contact-name').on('change', function() {
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      // 問い合わせ内容
      $('.js-comment').on('change', function() {
        // 入力確認メッセージ表示
        checkInputMsg($(this));
      });

      //===============================
      // 画面表示処理
      //===============================
      // パスワード可視化
      $('.js-show-pass').on('change', function() {
        if ($(this).prop('checked')) {
          $('.js-pass').attr('type', 'text');
        } else {
          $('.js-pass').attr('type', 'password');
        }
      });
      // パスワード(再入力)可視化
      $('.js-show-pass-re').on('change', function() {
        if ($(this).prop('checked')) {
          $('.js-pass-re').attr('type', 'text');
        } else {
          $('.js-pass-re').attr('type', 'password');
        }
      });

      // レシート入力画面(収支入力部分)
      //-------------------------------------
      // 収支入力ページ表示切替処理
      // メインカテゴリの要素を変数に格納
      var $mCate = $('.js-slip-mcate');
      // 後述処理でメインカテゴリの不要なoption要素を削除するため、オリジナルを取得しておく
      var mCateOriginal = $mCate.html();

      // 収支入力ページの表示を選択したタブで切り替え
      $('.js-tab-change').on('click', function() {
        // class=tab-successを付与されている要素から削除
        $('.tab-success').removeClass('tab-success');
        // クリックされた要素にclass=tab-successを付与
        $(this).addClass('tab-success');
        // class=js_panel_showを付与されている要素から削除
        $('.js_panel_show').removeClass('js_panel_show');
        // クリックしたタブからインデックス番号を習得し、変数に格納
        var index = $(this).index();
        // クリックしたタブのインデックスが「2(振替)」の時
        if (index === 2) {
          // class名「slip-trans」に「js_panel_show'」を付与
          $('.slip-trans').addClass('js_panel_show');
        } else {
          // それ以外(インデックスが「0(収入)」または「1(支出)])の時はclass名「slip-deposit'」に「js_panel_show」を付与
          $('.slip-deposit').addClass('js_panel_show');
        }

        // 選択したタブに応じて収支フラグ(deposit_flg)を設定
        // 選択タブのdata-val値を取得
        var tabVal = $(this).data('val');
        // inputタブ.js-deposit-flgにvalue属性を追加
        $('.js-deposit-flg').val(tabVal);

        // 選択したタブに連動してメインカテゴリ表示内容操作
        $mCate.html(mCateOriginal).find('option').each(function() {
          //メインカテゴリのdata-val値を取得
          var mCateVal = $(this).data('val');
          //valueと異なるdata-valを持つ要素を削除
          if (tabVal != mCateVal) {
            $(this).not(':first-child').remove();
            return true;
          }
        });
        // メインカテゴリが未選択の場合、サブカテゴリselectタグにdisabled属性を付与
        if ($('.js-slip-mcate').val() === "0 selected") {
          // サブカテゴリに
          $('.js-slip-scate').attr('disabled', 'disabled');
        } else { //メインカテゴリ選択時は、サブカテゴリselectタグにdisabled属性を削除
          $('.js-slip-scate').removeAttr('disabled');
        }
      });

      // メインカテゴリの選択に応じて、サブカテゴリの表示内容を連動(メインカテゴリ基準)
      // サブカテゴリの要素を変数に格納
      var $sCate = $('.js-slip-scate');
      // 後述処理でサブカテゴリの不要なoption要素を削除するため、オリジナルを取得しておく
      var sCateOriginal = $sCate.html();

      $('.js-slip-mcate').on('change', function() {
        //選択されたメインカテゴリのvalueを取得し変数に入れる
        var mCateVal = $(this).val();

        //サブカテゴリの削除された要素をもとに戻すため.html(original)を入れておく
        $sCate.html(sCateOriginal).find('option').each(function() {
          //サブカテゴリのdata-val値を取得
          var sCateVal = $(this).data('val');

          //valueと異なるdata-valを持つ要素を削除
          if (mCateVal != sCateVal) {
            $(this).not(':first-child').remove();
            return true;
          }
        });
        // メインカテゴリが未選択の場合、サブカテゴリselectタグにdisabled属性を付与
        if ($(this).val() === "0 selected") {
          // サブカテゴリに
          $('.js-slip-scate').attr('disabled', 'disabled');
        } else { //メインカテゴリ選択時は、サブカテゴリselectタグにdisabled属性を削除
          $('.js-slip-scate').removeAttr('disabled');
        }
      });

      // 口座が選択された時に現在の残高(data-val値)を設定
      $('.js-bank-select').on('change', function() {
        // 選択された口座のdata属性(現在の残高)を取得
        var cPrice = $(this).find("option:selected").data('val');
        // input.current_priceのvalue属性値をセット
        $('.js-current-price').val(cPrice);
      });

      // 振替ページ
      // 口座が選択された時に現在の残高(data-val値)を設定
      // 出金口座部分
      $('.js-outbank-select').on('change', function() {
        // 選択された口座のdata属性(現在の残高)を取得
        var cPrice = $(this).find("option:selected").data('val');
        // input.out_current_priceのvalue属性値をセット
        $('.js-out-current-price').val(cPrice);
      });
      // 入金口座部分
      $('.js-inbank-select').on('change', function() {
        // 選択された口座のdata属性(現在の残高)を取得
        var cPrice = $(this).find("option:selected").data('val');
        // input.in_current_priceのvalue属性値をセット
        $('.js-in-current-price').val(cPrice);
      });

      // テキストエリア文字カウント
      $('.js-count').on('keyup', function() {
        var count = $(this).val().length;
        $('.js-show-count').text(count);
        if (count >= 50) {
          $('.js-count-err').text("50文字以内で入力してください").css('color', 'red');
        }
      });

    });

    //=============================
    //ページ読み込み時処理
    //=============================
      
    // 収支入力ページ表示切替処理
    $(window).on('load', function() {
      // class=tab-successが付与されているタブのdata-val値を取得
      var tabVal = $('.tab-success').data('val');
      // class=js_panel_showを付与されている要素から削除
      $('.js_panel_show').removeClass('js_panel_show');
      // tabValが「2(振替)」の時
      if (tabVal === 2) {
        // class名「slip-trans」に「js_panel_show'」を付与
        $('.slip-trans').addClass('js_panel_show');
      } else {
        // それ以外(「0(収入)」または「1(支出)])の時はclass名「slip-deposit'」に「js_panel_show」を付与
        $('.slip-deposit').addClass('js_panel_show');
      }
    });

    // 収支メインカテゴリ表示切替処理
    $(window).on('load', function() {
      // class=tab-successが付与されているタブのdata-val値を取得
      var tabVal = $('.tab-success').data('val');
      // メインカテゴリの要素を変数に格納
      var $mCate = $('.js-slip-mcate');
      // 後述処理でメインカテゴリの不要なoption要素を削除するため、オリジナルを取得しておく
      var mCateOriginal = $mCate.html();

      // 選択したタブに連動してメインカテゴリ表示内容操作
      $mCate.html(mCateOriginal).find('option').each(function() {
        //メインカテゴリのdata-val値を取得
        var mCateVal = $(this).data('val');
        //valueと異なるdata-valを持つ要素を削除
        if (tabVal != mCateVal) {
          $(this).not(':first-child').remove();
          return true;
        }
      });
    });

    // メインカテゴリの選択に応じて、サブカテゴリの表示内容を連動(メインカテゴリ基準)
    $(window).on('load', function() {
      // サブカテゴリの要素を変数に格納
      var $sCate = $('.js-slip-scate');
      // 後述処理でサブカテゴリの不要なoption要素を削除するため、オリジナルを取得しておく
      var sCateOriginal = $sCate.html();
      //選択されたメインカテゴリのvalueを取得し変数に入れる
      var mCateVal = $('.js-slip-mcate').val();

      //サブカテゴリの削除された要素をもとに戻すため.html(original)を入れておく
      $sCate.html(sCateOriginal).find('option').each(function() {
        //サブカテゴリのdata-val値を取得
        var sCateVal = $(this).data('val');
        //valueと異なるdata-valを持つ要素を削除
        if (mCateVal != sCateVal) {
          $(this).not(':first-child').remove();
          return true;
        }
      });
    });

    // 口座が選択された時に現在の残高(data-val値)を設定
    $(window).on('load', function() {
      // 選択された口座のdata属性(現在の残高)を取得
      var cPrice = $('.js-bank-select').find("option:selected").data('val');
      // input.current_priceのvalue属性値をセット
      $('.js-current-price').val(cPrice);
    });


      // 伝票入力ページ(振替)
      //-------------------------------------
      // 口座が選択された時に現在の残高(data-val値)を設定
      // 出金口座部分
      $(window).on('load', function() {
        // 選択された口座のdata属性(現在の残高)を取得
        var cPrice = $('.js-outbank-select').find("option:selected").data('val');
        // input.out_current_priceのvalue属性値をセット
        $('.js-out-current-price').val(cPrice);
      });
      // 入金口座部分
      $(window).on('load', function() {
        // 選択された口座のdata属性(現在の残高)を取得
        var cPrice = $('.js-inbank-select').find("option:selected").data('val');
        // input.in_current_priceのvalue属性値をセット
        $('.js-in-current-price').val(cPrice);
      });


  </script>
</body>

</html>