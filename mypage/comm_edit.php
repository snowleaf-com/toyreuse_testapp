<?php
//---------------お決まり---------------------
require '../function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　コミュニティ編集・作成ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
require 'auth.php';




$sideName = '';
$userData = getUser($_SESSION['user_id']);
$edit_flg = '';
$page_flg = 1;

$access_err_msg = array();



//----------------------------セキュリティ系-----------------------
//クロスサイトリクエストフォージェリ（CSRF）対策
if (!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
}
$token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');


//-------------GETがあるとき---------------
// 変数に格納
if (!empty($_GET)) {

  if (!empty($_GET['c_id'])) {
    $c_id = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
    // それを元にDBから商品情報を取得（他人のデータを取得させないため、
    //商品IDとユーザーIDが一致したものをもってくる
    $communityData = getMyCommunity($c_id, $userData['id']);

    // GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる
    if (!empty($c_id) && empty($communityData)) {
      debug('GETパラメータのコミュニティIDが違います。');
      $access_err_msg[] = '不正な値が入力されました。';
    }
  } else {
    //GETがp_id以外の時も飛ばす
    $access_err_msg[] = '不正な値が入力されました。';
  }
}
//-----------------------------


//商品情報があるかどうかで編集か新規登録か決める、trueで編集
$edit_flg = (empty($communityData)) ? false : true;
if ($edit_flg) {
  $sideName = 'コミュニティ編集' . ' - ';
} else {
  $sideName = 'コミュニティ作成' . ' - ';
}


//1回目のボタン押下時
if (!empty($_POST['confirm'])) {
  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']) {
    echo "不正アクセスの可能性あり";
    exit();
  }

  $title = h($_POST['title']);
  $comment = h($_POST['comment']);

  validRequired($title, 'title');
  validRequired($comment, 'comment');

  if (empty($err_msg)) {
    $page_flg = 2;
  }
}


//戻るボタン押下
if (!empty($_POST['back'])) {
  $page_flg = 1;
}


//2回目のボタン押下時（DB登録）
if (!empty($_POST['submit'])) {
  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']) {
    echo "不正アクセスの可能性あり";
    exit();
  }
  //次に進むページがない場合はunsetする。＝重複防止
  unset($_SESSION['token']);

  //変数にユーザー情報を代入
  $title = h($_POST['title']);
  $comment = h($_POST['comment']);


  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    // 編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
    if ($edit_flg) {
      debug('DB更新です。');
      $sql = 'UPDATE community SET title = :title, comment = :comment WHERE id = :id AND made_by_id = :userid AND delete_flg = 0';
      $data = array(
        ':title' => $title,
        ':comment' => $comment,
        ':id' => $c_id,
        ':userid' => $userData['id'],
      );
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
        debug('コミュニティ編集成功');
        $page_flg = 3;
      } else {
        throw new PDOException('クエリ失敗');
      }
    } else {
      debug('DB新規登録です。');

      $dbh->beginTransaction();
      $sql = 'INSERT INTO community SET title = :title, comment = :comment, made_by_id = :u_id, create_date = now()';
      $data = array(
        ':title' => $title,
        ':comment' => $comment,
        ':u_id' => $userData['id'],
      );
      debug('SQL：' . $sql);
      debug('流し込みデータ：' . print_r($data, true));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        debug('新規登録成功');
      } else {
        $dbh->rollBack();
        throw new PDOException('クエリ失敗');
      }

      //作ったコミュニティのIDを取得し、
      $comm_id = $dbh->lastInsertId();
      debug('作ったコミュニティIDは' . $comm_id);

      //作った人をコミュニティ参加者に入れる
      $sql = 'INSERT INTO c_join SET community_id=:comm_id, user_id=:u_id, join_date=now()';
      $data = array(
        ':comm_id' => $comm_id,
        ':u_id' => $userData['id']
      );
      debug('SQL：' . $sql);
      debug('流し込みデータ：' . print_r($data, true));
      $stmt = queryPost($dbh, $sql, $data);


      // クエリ成功の場合
      if ($stmt) {
        $page_flg = 3;
        $dbh->commit();
      } else {
        $dbh->rollBack();
        throw new PDOException('クエリ失敗');
      }
    }
  } catch (PDOException $e) {
    $dbh->rollBack();
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = 'エラーが発生しました';
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
              <span itemprop="name">
                <?php
                if ($edit_flg) {
                  echo 'コミュニティ編集';
                } else {
                  echo 'コミュニティ作成';
                }
                ?>
              </span>
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
      <?php
      if ($edit_flg) {
        echo 'コミュニティ編集';
      } else {
        echo 'コミュニティ作成';
      }
      ?>
    </div>
    <div class="main_wrap3">
      <?php if ($page_flg === 1) : ?><!-- ページフラグ１ -->
        <form method="post" action="">
          <div class="cp_iptxt">

            <?php if ($edit_flg) : ?><!--コミュニティ名の変更はできなくする-->
              <label class="ef">コミュニティ名<br>
                <p style="font-size:20px"><b><?php echo $communityData['title'] ?></b></p>
                <input type="hidden" name="title" value="<?php echo $communityData['title'] ?>">
              </label>


            <?php else: ?><!--$edit_flg-->
              <label class="ef">コミュニティ名<span class="required">必須</span>
                <input type="text" name="title" placeholder="例)わいわい盛り上がる会" class="<?php if (!empty($err_msg['title'])) echo 'form_warning' ?>" value="<?php echo getCommunityFormData('title') ?>">
                <?php if (!empty($err_msg['title'])): ?>
                  <span class="err_warning"><?php getErrMsg('title') ?></span>
                <?php endif; ?>
              </label>
            <?php endif; ?><!--$edit_flg-->
          </div>

          <div class="cp_iptxtarea">
            <label class="ef">説明<span class="required">必須</span>
              <br>
              <textarea name="comment" class="<?php if (!empty($err_msg['comment'])) echo 'form_warning' ?>" placeholder="例)みんなで仲良く語り合いましょう"><?php echo getCommunityFormData('comment') ?></textarea>
              <?php if (!empty($err_msg['comment'])): ?>
                <span class="err_warning"><?php getErrMsg('comment') ?></span>
              <?php endif; ?>
            </label>
          </div>

          <input type="hidden" name="token" value="<?= $token ?>">
          <p class="center pt-30">
            <button class="btn" type="submit" name="confirm" style="background-color:pink" value="確認する">確認する</button>
          </p>
        </form>

      <?php elseif ($page_flg === 2) : ?><!-- ページフラグ２ -->
        <h3 class="center"><span class="err_warning"><?php getErrMsg('common'); ?></span></h3>
        <p class="center">以下でよろしいですか？</p>
        <form method="post" action="">
          <div class="cp_iptxt">
            <label class="ef">コミュニティ名
              <p class="confirm_p"><b><?php echo $title ?></b></p>
              <input type="hidden" name="title" value="<?php echo $title ?>">
            </label>
          </div>

          <div class="cp_iptxtarea">
            <label class="ef">説明
              <br>
              <p class="confirm_p" style="white-space: pre;"><b><?php echo $comment ?></b></p>
              <input type="hidden" name="comment" value="<?php echo $comment ?>">
            </label>
          </div>

          <input type="hidden" name="token" value="<?= $token ?>">
          <p class="center pt-30">
            <button class="btn_s" type="submit" name="back" style="background-color:azure" value="修正する">修正する</button>
            <button class="btn_s" type="submit" name="submit" value="登録する">登録する</button>
          </p>

        </form>

      <?php elseif ($page_flg === 3) : ?><!-- ページフラグ３ -->

        <p class="center pt-20 pb-20">登録が完了しました。<br>

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