<?php
//---------------お決まり---------------------
require 'function.php';
require 'MailSend.php'; //メール送信用
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　新規会員登録メール送信ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//-------------------変数の定義-------------
$err_msg = array();
$page_flg = 0;
$pre_mail = '';
$token = '';
$urltoken = '';
$url = '';
$sideName = '新規会員登録ページ';

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

  //未入力のバリデーション
  validRequired(h($_POST['pre_mail']), 'mail');

  //エラーがなかった場合
  if (empty($err_msg)) {

    //変数に格納
    $pre_mail = h($_POST['pre_mail']);

    //メール形式のバリデーション
    validEmail($pre_mail, 'mail');
    //重複のバリデーション
    validEmailDup($pre_mail);

    //更にエラーがなかった場合
    if (empty($err_msg)) {
      //URLトークン生成
      $urltoken = bin2hex(random_bytes(64));
      $url = 'http://localhost:8888/akachan/registration_form.php?urltoken=' . $urltoken;

      try {
        $dbh = dbConnect();
        //pre_membersテーブルにINSERT
        $sql = 'INSERT INTO pre_members SET urltoken = :urltoken, pre_mail = :pre_mail, date = NOW()';
        $data = array(
          ':urltoken' => $urltoken,
          ':pre_mail' => $pre_mail,
        );
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
          debug('登録完了：' . print_r($stmt, true));
        }
      } catch (PDOException $e) {
        error_log('SQLエラーです' . $e->getMessage());
        debug('SQLでPDOExceptionが作動しました。');
        $err_msg['common'] = 'エラーが発生いたしました';
      }

      //メール送信準備
      $to = $pre_mail;
      $subject = '【TOY REUSE】会員登録用URLのお知らせ';
      $comment = <<< EOM
1時間以内に下記のURLからご登録下さい。
{$url}
EOM;

      $send = new MailSend();
      $send->sendMail($subject, $comment, $to);

      //セッション変数を全て解除
      $_SESSION = array();

      //クッキーの削除
      if (isset($_COOKIE["PHPSESSID"])) {
        setcookie("PHPSESSID", '', time() - 1800, '/');
      }

      //セッションを破棄する
      session_destroy();

      //ページフラッグを１にして、ページ内変更する。
      $page_flg = 1;
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
      <a itemprop="item" href="registration_mail_form.php">
        <span itemprop="name">新規会員登録</span>
      </a>
      <meta itemprop="position" content="2" />
    </li>
  </ol>


  <!-- ページフラグが０のとき -->
  <?php if ($page_flg === 0) : ?>
    <main class="r_m_f_container">
      <div class="r_m_f_main">
        <h1>新規会員登録</h1>
      </div>
      <div class="r_m_f_main2">
        <span class="err_warning"><?php getErrMsg('common'); ?></span>
        <!-- フォーム -->
        <form method="post" action="">
          <div class="cp_iptxt">
            <label class="ef">メールアドレス<span class="required">必須</span>
              <br>
              <input type="text" name="pre_mail" placeholder="PC携帯どちらでも可" value="<?php if (!empty($_POST)) echo $_POST['pre_mail'] ?>" class="<?php if (!empty($err_msg['mail'])) echo 'form_warning' ?>">
              <?php if (!empty($err_msg['mail'])): ?>
                <span class="err_warning"><?php getErrMsg('mail') ?></span>
              <?php endif; ?>
            </label>
          </div>
          <p class="center pt-20">メールアドレス宛に登録ページのURLをお送りします。<br><br><br>
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
        <h1>新規会員登録</h1>
      </div>
      <div class="r_m_f_main2">
        <p class="center pt-20 pb-20">送信しました。有効期限は1時間です。<br>メールをご確認ください。</p>
      </div>
    </main>
  <?php endif; ?>


  <?php require 'registration_footer.php'; ?>
</body>

</html>