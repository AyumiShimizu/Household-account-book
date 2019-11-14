<header id="header">
  <div class="site-width">
    <h1><a href="index.php">Household account book</a></h1>

    <nav id="top-nav">
      <ul class="nav-menu">
        <?php if(empty($_SESSION['user_id'])){ ?>
          <li><a href="signupMailSend.php">ユーザー登録</a></li>
          <li><a href="login.php">ログイン</a></li>
        <?php }else{ ?>
          <li><a href="registSlip.php">入力する</a></li>
          <li><a href="logout.php">ログアウト</a></li>
        <?php } ?>
      </ul>
    </nav>

  </div>
</header>