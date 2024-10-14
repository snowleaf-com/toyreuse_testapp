<?php
//---------------お決まり---------------------
require 'function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
require 'auth.php';

//-------------------変数の定義-------------
$err_msg = array();
$token = '';
$sideName = 'ログインページ';
$mail = '';
$password = '';
$checklogin = '';

//DB用
$dbh = '';
$sql = '';
$data = '';
$stmt = '';


//----------------------------セキュリティ系-----------------------
//クロスサイトリクエストフォージェリ（CSRF）対策
if (!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
}
$token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');


//------------------POSTがある場合----------------
if (!empty($_POST)) {

  //クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
  if ($_POST['token'] !== $_SESSION['token']) {
    echo "不正アクセスの可能性あり";
    exit();
  }

  //未入力のバリデーション
  validRequired(h($_POST['mail']), 'mail');
  validRequired(h($_POST['password']), 'password');

  //エラーがなかった場合
  if (empty($err_msg)) {

    //変数に格納
    $mail = h($_POST['mail']);
    $password = h($_POST['password']);
    if (empty($_POST['checklogin'])) {
      $checklogin = false;
    } else {
      $checklogin = true;
    }

    try {
      $dbh = dbConnect();
      $sql = 'SELECT password,id FROM members WHERE mail=:mail AND delete_flg = 0';
      $data = array(
        'mail' => $mail,
      );
      $stmt = queryPost($dbh, $sql, $data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      //とってきたパスワード（ハッシュ化を戻す）と、入力されたのがあってたら、以降の処理をする
      if (!empty($result) && password_verify($password, $result['password'])) {
        $sesLimit = 60 * 60;
        $_SESSION['login_date'] = time();
        $_SESSION['user_id'] = $result['id'];

        if ($checklogin) {
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        } else {
          $_SESSION['login_limit'] = $sesLimit;
        }

        debug('セッション変数の中身：' . print_r($_SESSION, true));

        $_SESSION['msg_success'] = 'ログインしました。';
        header('Location: ./mypage/');
        return;
      } else {
        $err_msg['password'] = 'パスワードが違います。';
      }
    } catch (PDOException $e) {
      error_log('SQLエラーです' . $e->getMessage());
      debug('SQLでPDOExceptionが作動しました。');
      $err_msg['common'] = 'エラーが発生いたしました';
    }
  }
}

?>

<?php require 'registration_head.php'; ?>

<body class="r_m_f">

  <?php require 'registration_header.php'; ?>


  <!-- メイン部分 -->
  <!-- パンくず -->
  <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li itemprop="itemListElement" itemscope
      itemtype="https://schema.org/ListItem">
      <a itemprop="item" href="index.php">
        <span itemprop="name">ホーム</span>
      </a>
      <meta itemprop="position" content="1" />
    </li>

    <li itemprop="itemListElement" itemscope
      itemtype="https://schema.org/ListItem">
      <a itemprop="item" href="login.php">
        <span itemprop="name">ログイン</span>
      </a>
      <meta itemprop="position" content="2" />
    </li>
  </ol>



  <main class="r_m_f_container">
    <div class="r_m_f_main">
      <h1>ログイン</h1>
    </div>
    <div class="r_m_f_main2">
      <p class="center">アカウントをお持ちでない方はこちら<br><br>
        <button class="btn" onclick="location.href='registration_mail_form.php'" style="background-color:azure">新規登録する</button>
      </p>

    </div>
    <div class="r_m_f_main2">
      <!-- フォーム -->
      <form method="post" action="">
        <div class="cp_iptxt">
          <label class="ef">メールアドレス
            <br>
            <input type="text" name="mail" class="<?php if (!empty($err_msg['mail'])) echo 'form_warning' ?>" value="<?php if (!empty($_POST['mail'])) echo $_POST['mail'] ?>">
            <?php if (!empty($err_msg['mail'])): ?>
              <span class="err_warning"><?php getErrMsg('mail') ?></span>
            <?php endif; ?>
          </label>
        </div>

        <div class="cp_iptxt">
          <label class="ef">パスワード
            <br>
            <input type="password" name="password" id="js-password" class="<?php if (!empty($err_msg['password'])) echo 'form_warning' ?>">
            <?php if (!empty($err_msg['password'])): ?>
              <span class="err_warning"><?php getErrMsg('password') ?></span>
            <?php endif; ?>
          </label>
          <p>
            <input type="checkbox" id="js-passcheck">
            <label for="js-passcheck">パスワードを表示する</label>
          </p>
        </div>

        <p class="center">
          <input type="hidden" name="token" value="<?= $token ?>">
          <button class="btn" type="submit" name="submit">ログイン</button><br><br>
          <input type="checkbox" id="checklogin" name="checklogin" <?php if (!empty($_POST['checklogin'])) echo ' checked' ?>>
          <label for="checklogin">次回から自動ログインする</label><br><br>
          <a href="pass_reminder.php" class="cp_link">パスワードをお忘れの方はこちら</a>
        </p>
      </form>
    </div>




  </main>


  <?php require 'registration_footer.php'; ?>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script>
    $(function() {

      var password = '#js-password';
      var passcheck = '#js-passcheck';

      //----------パスワードの可視化
      $(passcheck).change(function() {
        if ($(this).prop('checked')) {
          $(password).attr('type', 'text');
        } else {
          $(password).attr('type', 'password');
        }
      });
    });
  </script>
</body>

</html>