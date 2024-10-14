<?php
//---------------お決まり---------------------
require '../function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　会員情報変更ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
require 'auth.php';

//-------------------変数の定義-------------
$page_flg = 1;
$sideName = '会員情報変更' . ' - ';
$userData = getUser($_SESSION['user_id']);

//〒→住所自動入力の関係上定義
$userData['zip11'] = $userData['zip'];
$userData['addr11'] = $userData['address'];



//----------------------------セキュリティ系-----------------------
//クロスサイトリクエストフォージェリ（CSRF）対策
if (!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
}
$token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');


//-----------投稿されたとき------------------
if (!empty($_POST)) {
  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']) {
    echo "不正アクセスの可能性あり";
    exit();
  }
  //バリデーションした時のために作っておく。
  unset($_SESSION['token']);
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];


  $nickname = h($_POST['nickname']);
  $username = h($_POST['username']);
  $userkananame = h($_POST['userkananame']);
  $bornyear = h($_POST['bornyear']);
  $bornmonth = h($_POST['bornmonth']);
  $bornday = h($_POST['bornday']);
  $zip = h($_POST['zip11']);
  $address = h($_POST['addr11']);
  $number = h($_POST['number']);


  //セッションに画像のパスが無い時、
  //画像アップロード処理をかける
  if (empty($_SESSION['pic'])) {
    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
    $pic = (empty($pic) && !empty($userData['pic'])) ? $userData['pic'] : $pic;
  } else {
    //セッションの画像のパスがあり、
    //なおかつ動画投稿があったときは上書きする処理
    if (!empty($_FILES['pic']['name'])) {
      $pic = uploadImg($_FILES['pic'], 'pic');
    } else {
      //動画投稿が無い時は$picにセッションを入れておく
      $pic = $_SESSION['pic'];
    }
  }
  //バリデーションした時に保持されないので、セッションにパスを入れておく。
  $_SESSION['pic'] = $pic;


  validRequired($nickname, 'nickname');
  validRequired($username, 'username');
  validRequired($userkananame, 'userkananame');
  validRequired($bornyear, 'bornyear');
  validRequired($bornmonth, 'bornmonth');
  validRequired($bornday, 'bornday');
  validRequired($zip, 'zip');
  validRequired($address, 'address');
  validRequired($number, 'number');

  if (empty($err_msg)) {
    if ($userData['nickname'] !== $nickname) {
      validMaxLen($nickname, 'nickname', 30);
    }
    if ($userData['username'] !== $username) {
      validMaxLen($username, 'username', 30);
    }
    if ($userData['userkananame'] !== $userkananame) {
      validMaxLen($userkananame, 'userkananame', 40);
    }
    if ($userData['zip'] !== $zip) {
      validNumber($zip, 'zip');
    }
    if ($userData['address'] !== $address) {
      validMaxLen($address, 'address');
    }
    if ($userData['number'] !== $number) {
      validNumber($number, 'number');
    }

    if (empty($err_msg)) {

      try {

        $dbh = dbConnect();
        $sql = 'UPDATE members SET nickname=:nickname, username=:username, userkananame=:userkananame, bornyear=:bornyear, bornmonth=:bornmonth, bornday=:bornday, zip=:zip, address=:address, number=:number, pic=:pic WHERE id=:u_id';
        $data = array(
          ':nickname' => $nickname,
          ':username' => $username,
          ':userkananame' => $userkananame,
          ':bornyear' => $bornyear,
          ':bornmonth' => $bornmonth,
          ':bornday' => $bornday,
          ':zip' => $zip,
          ':address' => $address,
          ':number' => $number,
          ':pic' => $pic,
          ':u_id' => $_SESSION['user_id'],
        );
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
          debug('アップデートクエリ成功');
          //セッションの画像パスを削除
          unset($_SESSION['pic']);
        } else {
          throw new PDOException();
        }
      } catch (PDOException $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = 'エラーが発生しました';
      }

      $page_flg = 2;
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
              <span itemprop="name">会員情報変更</span>
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
      会員情報変更
    </div>
    <div class="main_wrap3">

      <?php if ($page_flg === 1) : ?>
        <form method="post" action="" enctype="multipart/form-data">
          <span class="err_warning"><?php getErrMsg('common'); ?></span>
          <div class="cp_iptxt">
            <label class="ef">ニックネーム<span class="required">必須</span>
              <br>
              <input type="text" name="nickname" class="<?php if (!empty($err_msg['nickname'])) echo 'form_warning' ?>" value="<?php echo getUserFormData('nickname') ?>">
              <?php if (!empty($err_msg['nickname'])): ?>
                <span class="err_warning"><?php getErrMsg('nickname') ?></span>
              <?php endif; ?>
            </label>
          </div>
          <div class="cp_iptxt">
            <label class="ef">お名前（全角）<span class="required">必須</span>
              <br>
              <input type="text" name="username" class="<?php if (!empty($err_msg['username'])) echo 'form_warning' ?>" value="<?php echo getUserFormData('username') ?>">
              <?php if (!empty($err_msg['username'])): ?>
                <span class="err_warning"><?php getErrMsg('username') ?></span>
              <?php endif; ?>
            </label>
          </div>
          <div class="cp_iptxt">
            <label class="ef">お名前（カナ）<span class="required">必須</span>
              <br>
              <input type="text" name="userkananame" class="<?php if (!empty($err_msg['userkananame'])) echo 'form_warning' ?>" value="<?php echo getUserFormData('userkananame') ?>">
              <?php if (!empty($err_msg['userkananame'])): ?>
                <span class="err_warning"><?php getErrMsg('userkananame') ?></span>
              <?php endif; ?>
            </label>
          </div>

          <div class="selectwrap">
            <label class="ef">生年月日<span class="required">必須</span><br>
              <div class="cp_ipselect cp_sl04" style="width:30%">
                <select name="bornyear" required>
                  <option value="" hidden>-</option>
                  <?php for ($i = 1900; $i <= 2020; $i++) {
                    if ($userData['bornyear'] == $i) {
                      echo '<option value="' . $i . '" selected>' . $i . '</option>';
                    } else {
                      echo '<option value="' . $i . '">' . $i . '</option>';
                    }
                  } ?>
                </select>
              </div>年
              <div class="cp_ipselect cp_sl04" style="width:20%">
                <select name="bornmonth" required>
                  <option value="" hidden>-</option>
                  <?php for ($i = 1; $i <= 12; $i++) {
                    if ($userData['bornmonth'] == $i) {
                      echo '<option value="' . $i . '" selected>' . $i . '</option>';
                    } else {
                      echo '<option value="' . $i . '">' . $i . '</option>';
                    }
                  } ?>
                </select>
              </div>月
              <div class="cp_ipselect cp_sl04" style="width:20%">
                <select name="bornday" required>
                  <option value="" hidden>-</option>
                  <?php for ($i = 1; $i <= 31; $i++) {
                    if ($userData['bornday'] == $i) {
                      echo '<option value="' . $i . '" selected>' . $i . '</option>';
                    } else {
                      echo '<option value="' . $i . '">' . $i . '</option>';
                    }
                  } ?>
                </select>
              </div>日

            </label>
            <?php if (!empty($err_msg['bornyear']) || !empty($err_msg['bornmonth']) || !empty($err_msg['bornday'])): ?>
              <br><span class="err_warning">入力してください。</span>
            <?php endif; ?>
          </div>

          <div class="cp_iptxt">
            <label class="ef">郵便番号<span class="required">必須</span>
              <br>
              <input type="text" name="zip11" class="henkan<?php if (!empty($err_msg['zip'])) echo ' form_warning' ?>" value="<?php echo getUserFormData('zip11') ?>" onKeyUp="AjaxZip3.zip2addr(this,'','addr11','addr11');" maxlength="7">
              <?php if (!empty($err_msg['zip'])): ?>
                <span class="err_warning"><?php getErrMsg('zip') ?></span>
              <?php endif; ?>
            </label>
          </div>
          <div class="cp_iptxt">
            <label class="ef">住所<span class="required">必須</span>
              <br>
              <input type="text" name="addr11" class="<?php if (!empty($err_msg['address'])) echo 'form_warning' ?>" value="<?php echo getUserFormData('addr11') ?>">
              <?php if (!empty($err_msg['address'])): ?>
                <span class="err_warning"><?php getErrMsg('address') ?></span>
              <?php endif; ?>
            </label>
          </div>
          <div class="cp_iptxt">
            <label class="ef">電話番号<span class="required">必須</span>
              <br>
              <input type="text" name="number" class="henkan<?php if (!empty($err_msg['number'])) echo ' form_warning' ?>" value="<?php echo getUserFormData('number') ?>" maxlength="12">
              <?php if (!empty($err_msg['number'])): ?>
                <span class="err_warning"><?php getErrMsg('number') ?></span>
              <?php endif; ?>
            </label>
          </div>



          <div class="imgDrop-container" style="margin:0 auto; height: 140px;">
            <label class="area-drop">
              <input type="file" name="pic" class="input-file">
              <img src="<?php echo getUserImgForm('pic'); ?>" alt="" class="prev-img" style="<?php if (empty(getUserImgForm('pic'))) echo 'display:none;' ?>">
              <p>プロフ画像</p>
            </label>
          </div>





          <input type="hidden" name="token" value="">
          <p class="center pt-30">
            <button class="btn" type="submit" name="submit" style="background-color:pink">変更する</button>
          </p>
          <input type="hidden" name="token" value="<?= $token ?>">
        </form>


      <?php elseif ($page_flg === 2): ?>

        <p class="center pt-20 pb-20">登録が完了しました。<br>

        <?php endif; ?>

    </div>

  </main>

  <footer>
    <div class="footer">
      ©︎ TOY REUSE
    </div>
  </footer>


  <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script><!-- 郵便番号を住所に変換する -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script>
    var $dropArea = $('.area-drop');
    var $fileInput = $('.input-file');
    $dropArea.on('dragover', function(e) {
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', '3px #ccc dashed');
    });
    $dropArea.on('dragleave', function(e) {
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', 'none');
    });
    $fileInput.on('change', function(e) {
      $dropArea.css('border', 'none');
      $(this).siblings('p').text('');
      var file = this.files[0], // 2. files配列にファイルが入っています
        $img = $(this).siblings('.prev-img'), // 3. jQueryのsiblingsメソッドで兄弟のimgを取得
        fileReader = new FileReader(); // 4. ファイルを読み込むFileReaderオブジェクト

      // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
      fileReader.onload = function(event) {
        // 読み込んだデータをimgに設定
        $img.attr('src', event.target.result).show();
      };

      // 6. 画像読み込み
      fileReader.readAsDataURL(file);

    });

    //-----------全角を半角に、スペース・ハイフンを無しする
    //changeイベントはinput要素が変更を完了した時に実行
    $('.henkan').change(function() {
      //要素のvalue属性を変数に代入。
      var text = $(this).val();
      //全角英数字、スペース、ハイフンを対象に置き換え
      var hen = text.replace(/[Ａ-Ｚａ-ｚ０-９]/g, function(s) {
          return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
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
  </script>
  <script>

  </script>
</body>

</html>