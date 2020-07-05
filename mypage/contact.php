<?php
//---------------お決まり---------------------
require '../function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　お問い合わせページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
require 'auth.php';

//-------------------変数の定義-------------
$page_flg = 1;
$sideName = 'お問い合わせ' . ' - ';

$userData = getUser($_SESSION['user_id']);

//----------------------------セキュリティ系-----------------------
//クロスサイトリクエストフォージェリ（CSRF）対策
if(!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
}
$token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');


//-------------------------POST関係-----------------------
//１ページ目の投稿ボタン押下
if(!empty($_POST['confirm'])) {
  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']){
    echo "不正アクセスの可能性あり";
    exit();
  }

  $comment = h($_POST['comment']);

  validMinLen($comment, 'comment', 10);
  validRequired($comment, 'comment');

  if(empty($err_msg)) {
    $page_flg = 2;
  }
}

//修正するボタン押下
if(!empty($_POST['back'])) {
  $page_flg = 1;
}

//２ページ目のボタン
if(!empty($_POST['submit'])) {
    //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
    if ($_POST['token'] !== $_SESSION['token']){
      echo "不正アクセスの可能性あり";
      exit();
    }
    unset($_SESSION['token']);

    $comment = h($_POST['comment']);

    //メール送信準備
    $to = 'yy.dec5@gmail.com';
    $from = $userData['mail'];
    $subject = '【TOY REUSE】お問い合わせ';
    $comment = <<< EOM
{$userData['username']}様よりお問い合わせがありました。
{$userData['mail']}からです。

{$comment}
EOM;

    sendMail($from, $to, $subject, $comment);

    $page_flg = 3;
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
              <a itemprop="item"  style="cursor:default; text-decoration:none;color:#444">
              <span itemprop="name">お問い合わせ</span>
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
  お問い合わせ
  </div>
  <div class="main_wrap3">
    <?php if($page_flg === 1) : ?><!-- ページフラグ１のとき -->
    <span class="err_warning"><?php getErrMsg('common'); ?></span>
    <form method="post" action="">
    <div class="cp_iptxt">
      <label class="ef">メールアドレス
        <br>
        <b><?php echo $userData['mail'] ?></b>
      </label>
    </div>

    <div class="cp_iptxtarea">
      <label class="ef">お問い合わせ内容<span class="required">必須</span>
        <br>
        <textarea name="comment" class="<?php if(!empty($err_msg['comment'])) echo 'form_warning' ?>"><?php if(!empty($_POST['comment'])) echo $_POST['comment'] ?></textarea>
        <?php if(!empty($err_msg['comment'])): ?>
          <span class="err_warning"><?php getErrMsg('comment') ?></span>
        <?php endif; ?>
      </label>
    </div>







    <input type="hidden" name="token" value="<?=$token?>">
    <p class="center pt-30">
    <button class="btn" type="submit" name="confirm" style="background-color:pink" value="確認する">確認する</button>
    </p>
    </form>

    <?php elseif($page_flg === 2) : ?><!-- ページフラグ２のとき -->
      <span class="err_warning"><?php getErrMsg('common'); ?></span>
      <p class="center pt-20">以下でよろしいですか？</p>
    <form method="post" action="">
    <div class="cp_iptxt">
      <label class="ef">メールアドレス
        <br>
        <b><?php echo $userData['mail'] ?></b>
      </label>
    </div>

    <div class="cp_iptxtarea">
      <label class="ef">お問い合わせ内容<span class="required">必須</span>
        <br>
        <b><p style="white-space:pre"><?php echo $comment ?></p></b>
        <input type="hidden" name="comment" value="<?php echo $comment ?>">
      </label>
    </div>

      <input type="hidden" name="token" value="<?=$token?>">
      <p class="center pt-30">
        <button class="btn_s" type="submit" name="back" style="background-color:azure" value="修正する">修正する</button>
        <button class="btn_s" type="submit" name="submit" value="登録する">登録する</button>
      </p>
    </form>

    <?php elseif($page_flg === 3) : ?><!-- ページフラグ３のとき -->
      <p class="center pt-20 pb-20">メールを送信しました。<br>
      ２、３日経っても返信が無い場合は恐れ入りますが、再度お問い合わせください。<br>
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

</script>
</body>
</html>