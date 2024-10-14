<?php
//---------------お決まり---------------------
require 'function.php';
require 'MailSend.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワードリマインダーページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//-------------------変数の定義-------------
$err_msg = array();
$page_flg = 0;
$mail = '';
$token = '';
$urltoken = '';
$url = '';
$sideName = 'パスワードリマインダー';

//DB用
$dbh = '';
$sql = '';
$data = '';
$stmt = '';

//メール用
$to = '';
$from = '';
$subject = '';
$comment = '';

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

  $mail = h($_POST['mail']);

  //形式のバリデーション
  validEmail($mail, 'mail');
  //未入力のバリデーション
  validRequired($mail, 'mail');

  //エラーがなかった場合
  if (empty($err_msg)) {
    //DB接続    
    try {
      $dbh = dbConnect();
      //入力されたメールアドレスが登録されているかどうか調べる
      $sql = 'SELECT count(id) AS cnt FROM members WHERE mail = :mail AND delete_flg = 0';
      $data = array(
        ':mail' => $mail,
      );
      $stmt = queryPost($dbh, $sql, $data);
      $cnt = $stmt->fetchColumn();
      if ($cnt > 0) {
        debug('登録されています。');
        //URLトークン生成
        $urltoken = bin2hex(random_bytes(64));
        $url = 'http://localhost:8888/akachan/pass_reminder_recieve.php?urltoken=' . $urltoken;

        //URLトークンをDBに登録（後の照合用）
        $sql = 'INSERT INTO pre_pass_edit SET urltoken = :urltoken, pre_mail = :pre_mail, date = now()';
        $data = array(
          ':urltoken' => $urltoken,
          ':pre_mail' => $mail,
        );
        $stmt = queryPost($dbh, $sql, $data);

        //メール送信準備
        $to = $mail;
        $subject = '【TOY REUSE】パスワード再設定ページのお知らせ';
        $comment = <<< EOM
1時間以内に下記のURLから再設定をして下さい。
{$url}
EOM;

        $send = new MailSend();
        $send->sendMail($subject, $comment, $to);

        $page_flg = 1;
      } else {
        //登録されていなかったら、「送信しました」のみを表示
        debug('登録されていませんでした。');
        $page_flg = 1;
      }
    } catch (PDOException $e) {
      error_log('SQLエラーです' . $e->getMessage());
      debug('SQLでPDOExceptionが作動しました。');
      $err_msg['common'] = 'エラーが発生いたしました';
    }


    //セッション変数を全て解除
    $_SESSION = array();

    //クッキーの削除
    if (isset($_COOKIE["PHPSESSID"])) {
      setcookie("PHPSESSID", '', time() - 1800, '/');
    }

    //セッションを破棄する
    session_destroy();
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

    <li itemprop="itemListElement" itemscope
      itemtype="https://schema.org/ListItem">
      <a itemprop="item" href="pass_reminder.php">
        <span itemprop="name">パスワードリマインダー</span>
      </a>
      <meta itemprop="position" content="3" />
    </li>
  </ol>


  <!-- ページフラグが０のとき -->
  <?php if ($page_flg === 0) : ?>
    <main class="r_m_f_container">
      <div class="r_m_f_main">
        <h1>パスワードリマインダー</h1>
      </div>
      <div class="r_m_f_main2">
        <span class="err_warning"><?php getErrMsg('common'); ?></span>
        <!-- フォーム -->
        <form method="post" action="">
          <div class="cp_iptxt">
            <label class="ef">メールアドレス
              <br>
              <input type="text" name="mail" placeholder="" value="<?php if (!empty($_POST)) echo $_POST['mail'] ?>" class="<?php if (!empty($err_msg['mail'])) echo 'form_warning' ?>">
              <?php if (!empty($err_msg['mail'])): ?>
                <span class="err_warning"><?php getErrMsg('mail') ?></span>
              <?php endif; ?>
            </label>
          </div>
          <p class="center pt-20">メールアドレス宛にパスワード再設定のURLをお送りします。<br><br><br>
            <input type="hidden" name="token" value="<?= $token ?>">
            <button class="btn" type="submit" name="submit">送信する</button>
        </form>
        </p>
      </div>
    </main>
  <?php endif; ?>
  <!-- ページフラグが１のとき -->
  <?php if ($page_flg === 1) : ?>
    <main class="r_m_f_container">
      <div class="r_m_f_main">
        <h1>パスワードリマインダー</h1>
      </div>
      <div class="r_m_f_main2">
        <p class="center pt-20 pb-20">送信しました。有効期限は1時間です。<br>メールをご確認ください。</p>
      </div>
    </main>
  <?php endif; ?>


  <?php require 'registration_footer.php'; ?>
</body>

</html>