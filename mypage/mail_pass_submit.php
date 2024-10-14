<?php
//---------------お決まり---------------------
require '../function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　メール変更受諾ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
require 'auth.php';

//-------------------変数の定義-------------
$page_flg = 1;
$sideName = 'メールアドレス変更受付' . ' - ';

$userData = getUser($_SESSION['user_id']);
$access_err_msg = [];



// --------------------------GETパラメータ--------------------
//空の場合、変更ページに移動する
if (empty($_GET)) {
  header("Location: mail_pass_edit.php");
  exit();
} else {
  //GETがあるが、urltokenがない場合、エラーを表示する
  $urltoken = isset($_GET['urltoken']) ? $_GET['urltoken'] : NULL;
  if ($urltoken == '') {
    $access_err_msg['urltoken'] = "もう一度登録をやりなおして下さい。";
  } else {
    try {
      $dbh = dbConnect();
      //先ほど登録したトークンなどを取得し、時間は1時間以内とする。
      $sql = 'SELECT count(id) AS cnt FROM pre_passmail_edit WHERE urltoken = :urltoken AND userid=:userid AND flg = 0 AND date > now() -interval 1 hour';
      $data = array(
        ':urltoken' => $urltoken,
        ':userid' => $userData['id'],
      );
      $stmt = queryPost($dbh, $sql, $data);
      $cnt = $stmt->fetchColumn();

      if ($cnt > 0) { //照合できた場合

        //まず、変更したいメールアドレスを取得する。
        $sql = 'SELECT mail FROM pre_passmail_edit WHERE urltoken = :urltoken AND userid=:userid AND flg=0';
        $data = array(
          ':urltoken' => $urltoken,
          ':userid' => $userData['id'],
        );
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($result)) {
          debug('アドレス取得OK');
        }

        $dbh->beginTransaction(); //トランザクション開始

        //membersテーブルのmailカラムを変更する。
        $sql = 'UPDATE members SET mail=:mail WHERE id=:id AND delete_flg = 0';
        $data = array(
          ':mail' => $result['mail'],
          ':id' => $userData['id'],
        );
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
          debug('mailカラム変更OK');
        }

        //tokenなどを保持する列を論理削除する
        $sql = 'UPDATE pre_passmail_edit SET flg=1 WHERE userid=:userid';
        $data = array(
          ':userid' => $userData['id'],
        );
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
          debug('token論理削除OK');
        }

        $dbh->commit(); //実行

      } else { //照合できなかった場合エラー
        $access_err_msg['urltoken'] = "このURLはご利用できません。有効期限が過ぎた等の問題があります。<br>もう一度登録をやりなおして下さい。";
      }
    } catch (PDOException $e) {
      $dbh->rollBack();
      error_log('SQLエラーです' . $e->getMessage());
      debug('SQLでPDOExceptionが作動しました。');
      $access_err_msg['common'] = 'エラーが発生いたしました';
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

      <?php if (!empty($access_err_msg)) : ?>
        <p class="center pt-20 pb-20">
          <?php foreach ($access_err_msg as $key) {
            echo $key;
          } ?>

        <?php else: ?>

        <p class="center pt-20 pb-20">メールアドレスを変更しました。<br>

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