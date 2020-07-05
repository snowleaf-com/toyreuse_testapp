<?php
//---------------お決まり---------------------
require 'function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワードリマインダー設定ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//-------------------変数の定義-------------
$err_msg = array();
$access_err_msg = array();
$page_flg = 0;
$mail = '';
$password = '';
$password_re = '';
$token = '';
$urltoken = '';
$url = '';
$sideName = 'パスワードリマインダー設定';

//DB用
$dbh = '';
$sql = '';
$data = '';
$stmt = '';
$cnt = '';

//----------------------------セキュリティ系-----------------------
//クロスサイトリクエストフォージェリ（CSRF）対策
if(!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
}
$token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

//--------------------------GETパラメータ--------------------
//空の場合、メールアドレス送信ページに移動する
if(empty($_GET)) {
	header("Location: pass_reminder.php");
  exit();
} else {
  //GETがあるが、urltokenがない場合、エラーを表示する
  $urltoken = isset($_GET['urltoken']) ? $_GET['urltoken'] : NULL;
  if ($urltoken == '') {
  $access_err_msg['urltoken'] = "もう一度登録をやりなおして下さい。";
  } else {
    try {
      $dbh = dbConnect();
      //先ほど登録したpre_membersテーブルからトークンと、時間を取得してくる（1時間以内指定）。
      $sql = 'SELECT count(id) AS cnt FROM pre_pass_edit WHERE urltoken = :urltoken AND flg = 0 AND date > now() -interval 1 hour';
      $data = array(
        ':urltoken' => $urltoken,
      );
      $stmt = queryPost($dbh, $sql, $data);
      $cnt = $stmt->fetchColumn();
  
      if($cnt > 0) {//０より大きい時に条件を通るので、メールアドレスをもってくる
        $sql = 'SELECT pre_mail FROM pre_pass_edit WHERE urltoken = :urltoken AND flg = 0';
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
      } else {
        $access_err_msg['urltoken'] = "このURLはご利用できません。有効期限が過ぎた等の問題があります。<br>もう一度登録をやりなおして下さい。";
      }
  
    } catch(PDOException $e) {
      error_log('SQLエラーです' . $e->getMessage());
      debug('SQLでPDOExceptionが作動しました。');
      $access_err_msg['common'] = 'エラーが発生いたしました';
    } 
  }
}



//------------------POSTがある場合----------------
if(!empty($_POST)) {

  //クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
  if ($_POST['token'] !== $_SESSION['token']){
    echo "不正アクセスの可能性あり";
    exit();
  }

  $mail = h($_POST['mail']);
  $password = h($_POST['password']);
  $password_re = h($_POST['password_re']);

  //文字数のバリデーション
  validMinLen($password, 'password', $min = 4);
  validMinLen($password_re, 'password_re', $min = 4);
  //未入力のバリデーション
  validRequired($password, 'password');
  validRequired($password_re, 'password_re');

  //エラーがなかった場合
  if(empty($err_msg)) {

    if($password !== $password_re) {
      $err_msg['password'] = 'パスワードと再入力があっていません。';
      $err_msg['password_re'] = ' ';//エラー表示出すためにスペース
    }

    if(empty($err_msg)) {
      //DB接続    
      try {
        $dbh = dbConnect();
        $sql = 'UPDATE members SET password = :password WHERE mail = :mail AND delete_flg = 0';
        $data = array(
          ':password' => password_hash($password, PASSWORD_DEFAULT),
          ':mail' => $mail,
        );
        $stmt = queryPost($dbh, $sql, $data);
  
        $page_flg = 1;
  

      } catch(PDOException $e) {
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

  <li itemprop="itemListElement" itemscope
      itemtype="https://schema.org/ListItem">
        <span itemprop="name">パスワード再設定</span>
    <meta itemprop="position" content="4" />
  </li>
</ol>


<!-- ページフラグが０のとき -->
<?php if($page_flg === 0) : ?>
  <main class="r_m_f_container">
    <div class="r_m_f_main">
      <h1>パスワード再設定</h1>
    </div>
    <div class="r_m_f_main2">
    <!------------ エラーメッセージがない時以下を表示する -->
    <?php if(count($access_err_msg) === 0) : ?>
    <span class="err_warning"><?php getErrMsg('common'); ?></span>
      <!-- フォーム -->
      <form method="post" action="">
        <!-- メールアドレス隠して送信 -->
        <input type="hidden" name="mail" value="<?php echo $result['pre_mail'] ?>">
        <!-- メールアドレス隠して送信 -->

        <div class="cp_iptxt">
          <label class="ef">新しいパスワード
          <br>
          <input type="password" name="password" id="js-password" placeholder="" class="<?php if(!empty($err_msg['password'])) echo 'form_warning' ?>">
          <?php if(!empty($err_msg['password'])): ?>
            <span class="err_warning"><?php getErrMsg('password') ?></span>
          <?php endif; ?>
        </label>
        </div>

        <div class="cp_iptxt">
            <label class="ef">新しいパスワード（再入力）
              <br>
              <input type="password" name="password_re" id="js-password2" placeholder="" class="<?php if(!empty($err_msg['password_re'])) echo 'form_warning' ?>">
              <?php if(!empty($err_msg['password_re'])): ?>
                <span class="err_warning"><?php getErrMsg('password_re') ?></span>
              <?php endif; ?>
            </label>
            <p>
              <input type="checkbox" id="js-passcheck"/>
              <label for="js-passcheck">パスワードを表示する</label>
            </p>
          </div>


        <p class="center pt-20">
          <input type="hidden" name="token" value="<?=$token?>">
          <button class="btn" type="submit" name="submit">登録する</button>
      </form>
      </p>
      <!------------ アクセスエラーメッセージがある時以下を表示する -->
        <?php elseif(count($access_err_msg) > 0) : ?>
        <?php
          foreach($access_err_msg as $value){
          echo "<p class='center'>".$value."</p>";
        }; ?>
      <?php endif; ?><!-- アクセスエラーメッセージ -->
    </div>
  </main>
<?php endif; ?>
<!-- ページフラグが１のとき -->
<?php if($page_flg === 1) : ?>
  <main class="r_m_f_container">
    <div class="r_m_f_main">
      <h1>パスワード再設定</h1>
    </div>
    <div class="r_m_f_main2">
        <p class="center pt-20 pb-20">再設定しました。<br><a href="login.php" class="cp_link">ログインはこちらから。</a></p>
    </div>
  </main>
<?php endif; ?>


<?php require 'registration_footer.php'; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
  $(function() {

    var password  = '#js-password';
    var password2  = '#js-password2';
    var passcheck = '#js-passcheck';
    
    //----------パスワードの可視化
    $(passcheck).change(function() {
        if ($(this).prop('checked')) {
            $(password).attr('type','text');
            $(password2).attr('type','text');
        } else {
            $(password).attr('type','password');
            $(password2).attr('type','password');
        }
    });
  });
</script>
</body>
</html>