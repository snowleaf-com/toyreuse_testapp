<?php
//---------------お決まり---------------------
require 'function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　会員情報登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//-------------------変数の定義-------------
$err_msg = array();
$access_err_msg = array();
$page_flg = 0;
$sideName = '会員情報登録ページ';
//セレクトボックス用
$selected = '';
$selected2 = '';
$selected3 = '';
//登録用
$nickname = ''; 
$username = ''; 
$userkananame = ''; 
$password = ''; 
$bornyear = ''; 
$bornmonth = ''; 
$bornday = ''; 
$zip = ''; 
$address = ''; 
$number = ''; 
$mail = ''; 

//DB用
$dbh = '';
$sql = '';
$data = '';
$stmt = '';
$result = '';

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
	header("Location: registration_mail_form.php");
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
      $sql = 'SELECT count(pre_mail) AS cnt FROM pre_members WHERE urltoken = :urltoken AND flg = 0 AND date > now() -interval 1 hour';
      $data = array(
        ':urltoken' => $urltoken,
      );
      $stmt = queryPost($dbh, $sql, $data);
      $cnt = $stmt->fetchColumn();
  
      if($cnt > 0) {//０より大きい時に登録してあり条件を通ることになるので、メールアドレスを取得する。
        $sql = 'SELECT pre_mail FROM pre_members WHERE urltoken = :urltoken AND flg = 0';
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

//-------------------------POST-------------
//（確認するボタン）
if($page_flg === 0 && !empty($_POST)) {

  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']){
    echo "不正アクセスの可能性あり";
    exit();
  }
  

  $nickname = h($_POST['nickname']);
  $username = h($_POST['username']);
  $userkananame = h($_POST['userkananame']);
  $password = h($_POST['password']);
  $bornyear = h($_POST['bornyear']);
  $bornmonth = h($_POST['bornmonth']);
  $bornday = h($_POST['bornday']);
  $zip = h($_POST['zip11']);
  $address = h($_POST['addr11']);
  $number = h($_POST['number']);
  $mail = h($_POST['mail']);


  debug('１');

  //未入力チェック
  validRequired($nickname, 'nickname');
  validRequired($username, 'username');
  validRequired($userkananame, 'userkananame');
  validRequired($password, 'password');
  validRequired($bornyear, 'bornyear');
  validRequired($bornmonth, 'bornmonth');
  validRequired($bornday, 'bornday');
  validRequired($zip, 'zip');
  validRequired($address, 'addr');
  validRequired($number, 'number');
  validRequired($mail, 'mail');

  debug('２');

  if(empty($err_msg)) {
    // その他のバリデーション
    validMaxLen($nickname, 'nickname');
    validMaxLen($username, 'username');
    validMaxLen($userkananame, 'userkananame');
    validKana($userkananame, 'userkananame');
    validMinLen($password, 'password', $min = 4);


    debug('３');

    if(empty($err_msg)) {
      
      debug('４');

      //ページを変える
      $page_flg = 1;

      debug('５');

    }


  }
}

//(修正するボタン)
if($page_flg === 1 && !empty($_POST['back'])) {
  $page_flg = 0;
}

//(送信するボタン）
if($page_flg === 1 && !empty($_POST['submit'])) {

  //ページ遷移前のトークンと同じかどうかを比べる
  if ($_POST['token'] !== $_SESSION['token']){
    echo "不正アクセスの可能性あり";
    exit();
  }
  // 重複防止のため、tokenを削除→更新しても不正表示が出る。
  unset($_SESSION['token']);




  debug('６');
  $nickname = h($_POST['nickname']);
  $username = h($_POST['username']);
  $userkananame = h($_POST['userkananame']);
  $password = h($_POST['password']);
  $bornyear = h($_POST['bornyear']);
  $bornmonth = h($_POST['bornmonth']);
  $bornday = h($_POST['bornday']);
  $zip = h($_POST['zip11']);
  $address = h($_POST['addr11']);
  $number = h($_POST['number']);
  $mail = h($_POST['mail']);

  try {
    $dbh = dbConnect();

    $dbh->beginTransaction();

    $sql = 'INSERT INTO members 
            SET nickname = :nickname, username = :username, userkananame = :userkananame, password = :password, bornyear = :bornyear, bornmonth = :bornmonth, bornday = :bornday,
            zip = :zip, address = :address, number = :number, mail = :mail, login_time = now(), create_date = now()';
    $data = array(
      ':nickname' => $nickname,
      ':username' => $username,
      ':userkananame' => $userkananame,
      ':password' => password_hash($password, PASSWORD_DEFAULT),
      ':bornyear' => $bornyear,
      ':bornmonth' => $bornmonth,
      ':bornday' => $bornday,
      ':zip' => $zip,
      ':address' => $address,
      ':number' => $number,
      ':mail' => $mail,
    );
    $stmt = queryPost($dbh, $sql, $data);
    $_SESSION['user_id'] = $dbh->lastInsertId();
    if($stmt) {
      debug('ユーザー登録完了');
    }

    $sql = 'UPDATE pre_members SET flg = 1 WHERE pre_mail = :pre_mail';
    $data = array(
      ':pre_mail' => $mail
    );

    $stmt = queryPost($dbh, $sql, $data);

    if($stmt) {
      debug('プレメンバーの情報も論理削除しました');
    }

    $sesLimit = 60*60;
    $_SESSION['login_date'] = time();
    $_SESSION['login_limit'] = $sesLimit;
    $dbh->commit();
    
    //ページを変える（最終）
    $page_flg = 2;

  } catch(PDOException $e) {
    $dbh->rollBack();
    error_log('SQLエラーです' . $e->getMessage());
    debug('SQLでPDOExceptionが作動しました。');
    $err_msg['common'] = "エラーが発生いたしました。管理者に問い合わせてください。";
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

  <li itemprop="itemListElement" itemscope
      itemtype="https://schema.org/ListItem">
        <span itemprop="name">会員情報登録</span>
    <meta itemprop="position" content="2" />
  </li>
</ol>


<!-- ページフラグが０のとき -->
<?php if($page_flg === 0) : ?>

  <main class="r_m_f_container">
    <div class="r_m_f_main">
      <h1>
        会員情報登録
      </h1>
    </div>
    <div class="r_m_f_main2">
    <!------------ エラーメッセージがない時以下を表示する -->
      <?php if(count($access_err_msg) === 0) : ?>
        <span class="err_warning"><?php getErrMsg('common'); ?></span>
        <!-- フォーム -->
        <form method="post" action="registration_form.php?urltoken=<?php echo $urltoken ?>">

          <div class="cp_iptxt">
            <label class="ef">メールアドレス
              <br>
              <!-- pre_memberカラムから持ってくる -->
              <b><?php echo $result['pre_mail'] ?></b>
              <input type="hidden" name="mail" value="<?php echo $result['pre_mail'] ?>">
            </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">ニックネーム<span class="required">必須</span>
              <br>
              <input type="text" name="nickname" placeholder="例)赤ちゃん太郎" class="<?php if(!empty($err_msg['nickname'])) echo 'form_warning' ?>" value="<?php if(!empty($_POST['nickname'])) echo $_POST['nickname'] ?>">
              <?php if(!empty($err_msg['nickname'])): ?>
                <span class="err_warning"><?php getErrMsg('nickname') ?></span>
              <?php endif; ?>
            </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">パスワード<span class="required">必須</span>
              <br>
              <input type="password" name="password" id="js-password" placeholder="" class="<?php if(!empty($err_msg['password'])) echo 'form_warning' ?>">
              <?php if(!empty($err_msg['password'])): ?>
                <span class="err_warning"><?php getErrMsg('password') ?></span>
              <?php endif; ?>
            </label>
            <p>
              <input type="checkbox" id="js-passcheck"/>
              <label for="js-passcheck">パスワードを表示する</label>
            </p>
          </div>

          <div class="cp_iptxt">
            <label class="ef">お名前（全角）<span class="required">必須</span>
              <br>
              <input type="text" name="username" placeholder="例)山田太郎" class="<?php if(!empty($err_msg['username'])) echo 'form_warning' ?>" value="<?php if(!empty($_POST['username'])) echo $_POST['username'] ?>">
              <?php if(!empty($err_msg['username'])): ?>
                <span class="err_warning"><?php getErrMsg('username') ?></span>
              <?php endif; ?>
            </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">お名前（カナ）<span class="required">必須</span>
              <br>
              <input type="text" name="userkananame" placeholder="例)ヤマダタロウ" class="<?php if(!empty($err_msg['userkananame'])) echo 'form_warning' ?>" value="<?php if(!empty($_POST['userkananame'])) echo $_POST['userkananame'] ?>">
              <?php if(!empty($err_msg['userkananame'])): ?>
                <span class="err_warning"><?php getErrMsg('userkananame') ?></span>
              <?php endif; ?>
            </label>
          </div>
          <div class="selectwrap">
            <label class="ef">生年月日<span class="required">必須</span><br>
              <div class="cp_ipselect cp_sl01 cp_ipselect_ib" style="width:30%">
                <select name="bornyear" class="<?php if(!empty($err_msg['bornyear'])) echo 'form_warning' ?>">
                  <option value="" hidden>-</option>
                  <?php for($i=1900; $i<=2020; $i++) :
                    if(!empty($_POST['bornyear'])) {
                      if($i == $_POST['bornyear']) {
                        $selected = 'selected';
                      } else {
                        $selected = '';
                      }
                    }
                    echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
                  endfor; ?>
                </select>
              </div>
              年

              <div class="cp_ipselect cp_sl01 cp_ipselect_ib" style="width:20%">
                <select name="bornmonth" class="<?php if(!empty($err_msg['bornmonth'])) echo 'form_warning' ?>">
                  <option value="" hidden>-</option>
                  <?php for($i=1; $i<=12; $i++) {
                    if(!empty($_POST['bornmonth'])) {
                      if($i == $_POST['bornmonth']) {
                        $selected2 = 'selected';
                      } else {
                        $selected2 = '';
                      }
                    }
                    echo '<option value="' . $i . '" ' . $selected2 . '>' . $i . '</option>';
                  } ?>
                </select>
              </div>
              月
              
              <div class="cp_ipselect cp_sl01 cp_ipselect_ib" style="width:20%">
                <select name="bornday" class="<?php if(!empty($err_msg['bornday'])) echo 'form_warning' ?>">
                  <option value="" hidden>-</option>
                  <?php for($i=1; $i<=31; $i++) {
                    if(!empty($_POST['bornday'])) {
                      if($i == $_POST['bornday']) {
                        $selected3 = 'selected';
                      } else {
                        $selected3 = '';
                      }
                    }
                    echo '<option value="' . $i . '" ' . $selected3 . '>' . $i . '</option>';
                  } ?>
                </select>
              </div>
              日
            </label>
            <?php if(!empty($err_msg['bornyear']) || !empty($err_msg['bornmonth'])|| !empty($err_msg['bornday'])): ?>
                <br><span class="err_warning">入力してください。</span>
              <?php endif; ?>
          </div>
          <div class="cp_iptxt">
            <label class="ef">郵便番号<span class="required">必須</span>
              <br>
              <input type="text" name="zip11" placeholder="例)1234567 ハイフン無し半角" class="<?php if(!empty($err_msg['zip'])) echo 'form_warning ' ?>henkan" onKeyUp="AjaxZip3.zip2addr(this,'','addr11','addr11');" maxlength="7" value="<?php if(!empty($_POST['zip11'])) echo $_POST['zip11'] ?>">
              <?php if(!empty($err_msg['zip'])): ?>
                <span class="err_warning"><?php getErrMsg('zip') ?></span>
              <?php endif; ?>
            </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">住所<span class="required">必須</span>
              <br>
              <input type="text" name="addr11" placeholder="例)東京都江戸川区湖南１丁目２−１５" class="<?php if(!empty($err_msg['addr'])) echo 'form_warning' ?>" value="<?php if(!empty($_POST['addr11'])) echo $_POST['addr11'] ?>">
              <?php if(!empty($err_msg['addr'])): ?>
                <span class="err_warning"><?php getErrMsg('addr') ?></span>
              <?php endif; ?>
            </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">電話番号<span class="required">必須</span>
              <br>
              <input type="text" name="number" placeholder="例)01234567890 ハイフン無し半角" class="<?php if(!empty($err_msg['number'])) echo 'form_warning ' ?>henkan" maxlength="12" value="<?php if(!empty($_POST['number'])) echo $_POST['number'] ?>">
              <?php if(!empty($err_msg['number'])): ?>
                <span class="err_warning"><?php getErrMsg('number') ?></span>
              <?php endif; ?>
            </label>
          </div>


            <input type="hidden" name="token" value="<?=$token?>">
            <p class="center pt-30">
            <button class="btn" type="submit" name="confirm">確認する</button>
            </p>
        </form>
        <!------------ アクセスエラーメッセージがある時以下を表示する -->
      <?php elseif(count($access_err_msg) > 0) : ?>
        <?php
          foreach($access_err_msg as $value){
          echo "<p class='center'>".$value."</p>";
        }; ?>
      <?php endif; ?><!-- アクセスエラーメッセージ -->
    </div>
  </main>

<!-- ページフラグが１のとき -->
<?php elseif($page_flg === 1) : ?>

  <main class="r_m_f_container">
    <div class="r_m_f_main">
      <h1>会員情報登録
        </h1>
      </div>
      <div class="r_m_f_main2">
        <h3 class="center"><span class="err_warning"><?php getErrMsg('common'); ?></span></h3>
        <p class="center">以下でよろしいですか？</p>
        <form method="post" action="registration_form.php?urltoken=<?php echo $urltoken ?>">

          <div class="cp_iptxt">
            <label class="ef">メールアドレス
            <p class="confirm_p"><b><?php echo $mail ?></b></p>
            <input type="hidden" name="mail" value="<?php echo $mail ?>">
          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">ニックネーム
            <p class="confirm_p">
            <b><?php echo $nickname ?></b></p>
            <input type="hidden" name="nickname" value="<?php echo $nickname ?>">
          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">パスワード
            <p class="confirm_p">
            <b>表示しません</b></p>
            <input type="hidden" name="password" value="<?php echo $password ?>">
          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">お名前（全角）
            <p class="confirm_p">
            <b><?php echo $username ?></b></p>
            <input type="hidden" name="username" value="<?php echo $username ?>">
          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">お名前（カナ）
            <p class="confirm_p">
            <b><?php echo $userkananame ?></b></p>
            <input type="hidden" name="userkananame" value="<?php echo $userkananame ?>">
          </label>
          </div>
          
          <div class="selectwrap">

          <label class="ef">生年月日<p class="confirm_p">
          <b><?php echo $bornyear ?></b>
          年
          <input type="hidden" name="bornyear" value="<?php echo $bornyear ?>">


          <b><?php echo $bornmonth ?></b>
          月
          <input type="hidden" name="bornmonth" value="<?php echo $bornmonth ?>">

          
          <b><?php echo $bornday ?></b>
          日</p>
          <input type="hidden" name="bornday" value="<?php echo $bornday ?>">

          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">郵便番号
            <p class="confirm_p">
            <b><?php echo $zip ?></b></p>
            <input type="hidden" name="zip11" value="<?php echo $zip ?>">
          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">住所
            <p class="confirm_p">
            <b><?php echo $address ?></b></p>
            <input type="hidden" name="addr11" value="<?php echo $address ?>">
          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">電話番号
            <p class="confirm_p">
            <b><?php echo $number ?></b></p>
            <input type="hidden" name="number" value="<?php echo $number ?>">
          </label>
          </div>


            <input type="hidden" name="token" value="<?=$token?>">
            <p class="center pt-30">
            <button class="btn_s" type="submit" name="back" style="background-color:azure" value="修正する">修正する</button>
            <button class="btn_s" type="submit" name="submit" value="登録する">登録する</button>
            </p>
        </form>
        </p>
      </div>
  </main>
<!--ページフラグが2の時 -->
<?php elseif($page_flg === 2): ?>

  <main class="r_m_f_container">
    <div class="r_m_f_main">
      <h1>会員情報登録
        </h1>
    </div>
    <div class="r_m_f_main2">
      <p class="center pt-20 pb-20">登録が完了しました。<br><a href="mypage/" class="cp_link">マイページ</a>へお進みください。</p>
    </div>
  </main>
<?php endif; ?><!--ページフラグ -->


<?php require 'registration_footer.php'; ?>


<script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script><!-- 郵便番号を住所に変換する -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
  $(function() {

    var password  = '#js-password';
    var passcheck = '#js-passcheck';
    
    //----------パスワードの可視化
    $(passcheck).change(function() {
        if ($(this).prop('checked')) {
            $(password).attr('type','text');
        } else {
            $(password).attr('type','password');
        }
    });

    //-----------全角を半角に、スペース・ハイフンを無しする
    //changeイベントはinput要素が変更を完了した時に実行
    $('.henkan').change(function(){
      //要素のvalue属性を変数に代入。
      var text  = $(this).val();
      //全角英数字、スペース、ハイフンを対象に置き換え
      var hen = text.replace(/[Ａ-Ｚａ-ｚ０-９]/g,function(s){
                return String.fromCharCode(s.charCodeAt(0)-0xFEE0);
                })
                .replace(/\x20/g, '')
                .replace(/\u3000/g, '')
                .replace(/ー/g, '')
                .replace(/–/g, '')
                .replace(/−/g, '')
                .replace(/-/g, '')
                .replace(/–/g, '')
                .replace(/➖/g, '');
      //要素のvalue属性に変換した hen を入れる。
      $(this).val(hen);
    });

  });
</script>
</body>
</html>