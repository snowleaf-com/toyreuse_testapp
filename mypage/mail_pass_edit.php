<?php
//---------------お決まり---------------------
require '../function.php';
require '../MailSend.php'; //メール送信用
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　メールパスワード変更ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
require 'auth.php';

//-------------------変数の定義-------------
$page_flg = 1;
$sideName = 'メールアドレス/パスワード変更' . ' - ';

$userData = getUser($_SESSION['user_id']);


//----------------------------セキュリティ系-----------------------
//クロスサイトリクエストフォージェリ（CSRF）対策
if (!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
}
$token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');



//POST投稿があるが、パスワード欄２つの入力が空の場合
if (!empty($_POST) && empty($_POST['pass_old']) && empty($_POST['pass_new'])) {
  //--------------------
  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']) {
    echo "不正アクセスの可能性あり";
    exit();
  }
  //バリデーションした時のために作っておく。
  unset($_SESSION['token']);
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
  //--------------------


  debug('1');
  $mail = h($_POST['mail']);

  //元のデータと違っているか判別する
  if ($mail !== $userData['mail']) { //違っている場合にバリデーション
    validEmail($mail, 'mail'); //形式チェック
    validRequired($mail, 'mail'); //未入力チェック

    debug('2');
    if (empty($err_msg)) {
      debug('3');
      validEmailDup($mail, 'mail'); //重複チェック

      if (empty($err_msg)) {
        debug('4');
        //URLトークン生成
        $urltoken = bin2hex(random_bytes(64));
        $url = 'http://localhost:8888/akachan/mypage/mail_pass_submit.php?urltoken=' . $urltoken;

        //URLトークンをDBに登録（後の照合用）
        $dbh = dbConnect();
        $sql = 'INSERT INTO pre_passmail_edit SET urltoken = :urltoken, userid = :userid, mail=:mail, date = now()';
        $data = array(
          ':urltoken' => $urltoken,
          ':userid' => $userData['id'],
          ':mail' => $mail,
        );
        $stmt = queryPost($dbh, $sql, $data);

        //メール送信準備
        $to = $mail;
        $subject = '【TOY REUSE】メールアドレス再設定のお知らせ';
        $comment = <<< EOM
1時間以内に下記のURLにアクセスしてください。
{$url}
EOM;

        $send = new MailSend();
        $send->sendMail($subject, $comment, $to);

        $page_flg = 2;
      } else { //重複していたらそのままページを飛ばす
        debug('3');
        $page_flg = 2;
      }
    }
  } else { //元のデータと違わない
    debug('2');
    $err_msg['mail'] = '変更はありません。';
  }
}



//POST投稿があり、passかpass_newかpass_new_reに入力があった場合
//メールも変えているパターンを考慮する
if (!empty($_POST['pass_old']) || !empty($_POST['pass_new'])) {
  debug('5');
  //--------------------
  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']) {
    echo "不正アクセスの可能性あり";
    exit();
  }
  //バリデーションした時のために作っておく。
  unset($_SESSION['token']);
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
  //--------------------

  $mail = h($_POST['mail']);
  $pass_old = h($_POST['pass_old']);
  $pass_new = h($_POST['pass_new']);


  validMinLen($pass_new, 'pass_new', 4);

  if (!password_verify($pass_old, $userData['password'])) {
    $err_msg['pass_old'] = '登録と一致しません。';
  }
  if ($pass_old === $pass_new) {
    $err_msg['pass_new'] = '新旧が同じです。';
  }

  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');

  //元のデータと違っているか判別する
  if ($mail !== $userData['mail']) { //違っている場合にバリデーション
    validEmail($mail, 'mail'); //形式チェック
    validRequired($mail, 'mail'); //未入力チェック
  }

  if (empty($err_msg)) {
    debug('6');
    validEmailDup($mail, 'mail'); //重複チェック

    if (empty($err_msg)) { //両方の処理をする
      //URLトークン生成
      $urltoken = bin2hex(random_bytes(64));
      $url = 'http://localhost:8888/akachan/mypage/mail_pass_submit.php?urltoken=' . $urltoken;

      //URLトークンをDBに登録（後の照合用）
      $dbh = dbConnect();
      $sql = 'INSERT INTO pre_passmail_edit SET urltoken = :urltoken, userid = :userid, mail=:mail, date = now()';
      $data = array(
        ':urltoken' => $urltoken,
        ':userid' => $userData['id'],
        ':mail' => $mail,
      );
      $stmt = queryPost($dbh, $sql, $data);

      //メール送信準備
      $to = $mail;
      $from = 'info@info.com';
      $subject = '【TOY REUSE】メールアドレス再設定のお知らせ';
      $comment = <<< EOM
1時間以内に下記のURLにアクセスしてください。
{$url}
EOM;

      sendMail($from, $to, $subject, $comment);

      $sql = 'UPDATE members SET password=:password WHERE id=:id AND delete_flg = 0';
      $data = array(
        ':password' => password_hash($pass_new, PASSWORD_DEFAULT),
        ':id' => $userData['id']
      );
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
        $page_flg = 4;
      }
    } else { //パスワードだけ変える
      $dbh = dbConnect();
      $sql = 'UPDATE members SET password=:password WHERE id=:id AND delete_flg = 0';
      $data = array(
        ':password' => password_hash($pass_new, PASSWORD_DEFAULT),
        ':id' => $userData['id']
      );
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
        $page_flg = 3;
      }
    }
  }
}







?>

<?php require 'mypage_head.php' ?>

<body>
  <?php require 'mypage_header.php' ?>

  <div class="index_breadcrumb_wrap" style="margin-bottom: 20px;"><!-- パンくず大枠 -->
    <div class="bc_container">
      <div class="bc_left">
        <ol class="index_breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
          <li itemprop="itemListElement" itemscope
            itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="../index.php">
              <span itemprop="name">ホーム</span>
            </a>
            <meta itemprop="position" content="1" />
          </li>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="./">
              <span itemprop="name">マイページ</span>
            </a>
            <meta itemprop="position" content="2" />
          </li>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" style="cursor:default; text-decoration:none;color:#444">
              <span itemprop="name">メールアドレス・パスワード変更</span>
            </a>
            <meta itemprop="position" content="3" />
          </li>
        </ol>
      </div>
      <div class="bc_right">
        <b><?php echo $userData['nickname'] ?></b> さんのマイページ
      </div>
    </div>
  </div>
  <main><!-- メイン大枠 -->

    <div class="main_title">
      メールアドレス・パスワード変更
    </div>
    <div class="main_wrap3">
      <?php if ($page_flg === 1) : ?>
        <form method="post" action="">
          <div class="cp_iptxt">
            <label class="ef">メールアドレス
              <br>
              <input type="text" name="mail" class="<?php if (!empty($err_msg['mail'])) echo 'form_warning' ?>" value="<?php echo getUserFormData('mail') ?>">
              <?php if (!empty($err_msg['mail'])): ?>
                <span class="err_warning"><?php getErrMsg('mail') ?></span>
              <?php endif; ?>
            </label>
          </div>

          <p class="pb-30 center">メールアドレスは変更すると確認メールが送信されます。<br>
            メール内のURLをクリックして変更完了してください。</p>



          <div class="cp_iptxt">
            <label class="ef">変更するパスワード
              <br>
              <input type="password" name="pass_old" id="js-password" class="<?php if (!empty($err_msg['pass_old'])) echo 'form_warning' ?>" value="">
              <?php if (!empty($err_msg['pass_old'])): ?>
                <span class="err_warning"><?php getErrMsg('pass_old') ?></span>
              <?php endif; ?>
            </label>
          </div>
          <div class="cp_iptxt">
            <label class="ef">新しいパスワード
              <br>
              <input type="password" name="pass_new" id="js-password2" class="<?php if (!empty($err_msg['pass_new'])) echo 'form_warning' ?>" value="">
              <?php if (!empty($err_msg['pass_new'])): ?>
                <span class="err_warning"><?php getErrMsg('pass_new') ?></span>
              <?php endif; ?>
            </label>
            <p>
              <input type="checkbox" id="js-passcheck" />
              <label for="js-passcheck">パスワードを表示する</label>
            </p>
          </div>







          <input type="hidden" name="token" value="<?= $token ?>">
          <p class="center pt-30">
            <button class="btn" type="submit" name="submit" style="background-color:pink">変更する</button>
          </p>
        </form>
      <?php elseif ($page_flg === 2) : ?><!-- メールアドレスのみ変更 -->
        <p class="center pt-20 pb-20">変更したメールアドレス宛にメール送信しました。<br>
          アクセス有効期限は1時間です。<br>

        <?php elseif ($page_flg === 3) : ?><!-- パスワードのみ変更-->
        <p class="center pt-20 pb-20">パスワードを変更しました。<br>

        <?php elseif ($page_flg === 4) : ?><!-- 両方変更 -->
        <p class="center pt-20 pb-20">変更したメールアドレス宛にメール送信しました。<br>
          アクセス有効期限は1時間です。<br><br>
          パスワードを変更しました。<br>

        <?php endif; ?>

    </div>

  </main>

  <footer>
    <div class="footer">
      ©︎ TOY REUSE
    </div>
  </footer>



  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

  <script>
    $(function() {

      var password = '#js-password';
      var password2 = '#js-password2';
      var passcheck = '#js-passcheck';

      //----------パスワードの可視化
      $(passcheck).change(function() {
        if ($(this).prop('checked')) {
          $(password).attr('type', 'text');
          $(password2).attr('type', 'text');
        } else {
          $(password).attr('type', 'password');
          $(password2).attr('type', 'password');
        }
      });
    });
  </script>
</body>

</html>