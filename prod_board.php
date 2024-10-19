<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　連絡掲示板ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


$userId = $_SESSION['user_id'];
//掲示板の有無と、ユーザーIDから、本人がからんでいる掲示板である事を確認
$b_id = (!empty($_GET['b_id'])) ? $_GET['b_id'] : '';
$result = searchBoard($b_id, $userId);
if ($result['cnt'] > 0) {
  // 掲示板が確かに作成されていたら、商品情報と、出品者情報を取得する
  $productAndSale = getOneProduct($result['product_id']);
} else {
  debug('エラー発生:指定ページに不正な値が入りました');
  header("Location: index.php"); //トップページへ
}

//相手のIDを取得する。
if ($result['buy_user'] == $userId) {
  $partnerId = $result['sale_user'];
}
if ($result['sale_user'] == $userId) {
  $partnerId = $result['buy_user'];
}
$myUserInfo = getUser($userId);
$partnerInfo = getUser($partnerId);


//掲示板とメッセージ情報を取得
$viewData = getMsgAndBoard($b_id);

$sideName = '商品ページ' . ' - ';



if (!empty($_POST)) {
  //ログイン認証
  require('auth.php');
  //バリデーションチェック
  $msg = (isset($_POST['msg'])) ? $_POST['msg'] : '';
  //最大文字数チェック
  validMaxLen($msg, 'msg', 500);
  //未入力チェック
  validRequired($msg, 'msg');

  if (empty($err_msg)) {
    debug('バリデーションOKです。');

    try {
      $dbh = dbConnect();
      $sql = 'INSERT INTO p_message SET p_board_id = :b_id, send_date = now(), to_user = :partnerid, from_user =:userid, msg = :msg, create_date = now()';
      $data = array(
        ':b_id' => $b_id,
        ':partnerid' => $partnerId,
        ':userid' => $userId,
        ':msg' => $msg,
      );
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
        debug('メッセージ投稿しました。');
        $_SESSION['msg_success'] = '投稿しました。';
        header('Location: prod_board.php?b_id=' . $b_id);
        exit();
      } else {
        debug('エラー');
        throw new PDOException('SQLエラー');
      }
    } catch (PDOException $e) {
      error_log('エラー発生:' . $e->getMessage());
    }
  }
}

?>

<?php require 'header.php' ?>

<body>
  <p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>
  <div class="index_top_header"><!-- 最上段表示メニュー -->
    <h2>当サイトは赤ちゃん用品のフリマサービスです。</h2>
  </div>
  <div class="index_header"><!-- ヘッダーの大枠 -->
    <div class="index_header_left">
      <img src="images/toplogo.png" alt="トップロゴ">
    </div>
    <div class="index_header_center">
      <!-- 検索フォーム -->


    </div>
    <div class="index_header_right">
      <?php if (isLogin()) : ?>
        <button class="btn_top" onclick="location.href='./mypage/'">マイページ</button><br>
        <button class="btn_top2" onclick="location.href='mypage/logout.php'">ログアウト</button>
      <?php else: ?>
        <button class="btn_top" onclick="location.href='registration_mail_form.php'">新規登録</button><br>
        <button class="btn_top2" onclick="location.href='login.php'">ログイン</button>
      <?php endif; ?>
    </div>
  </div>
  <div class="index_menu"><!-- メニューバー大枠 -->
    <ul>
      <li>
        <a href="index.php?c_id=1">０〜６ヶ月</a>
      </li>
      <li>
        <a href="index.php?c_id=2">７ヶ月〜１歳</a>
      </li>
      <li>
        <a href="index.php?c_id=3">１歳〜２歳</a>
      </li>
      <li>
        <a href="index.php?c_id=4">３歳〜</a>
      </li>
      <li>
        <a href="index.php?c_id=5">その他</a>
      </li>
      <li>
        <a href="community/">コミュニティ</a>
      </li>
    </ul>
  </div>
  <div class="index_breadcrumb_wrap"><!-- パンくず大枠 -->
    <div class="bc_container">
      <div class="bc_left">
        <ol class="index_breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
          <li itemprop="itemListElement" itemscope
            itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="index.php">
              <span itemprop="name">ホーム</span>
            </a>
            <meta itemprop="position" content="1" />
          </li>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="prod.php?p_id=<?php echo $result['product_id']; ?>">
              <span itemprop="name">商品ページ</span>
            </a>
            <meta itemprop="position" content="2" />
          </li>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" style="cursor:default; text-decoration:none;color:#444">
              <span itemprop="name">連絡掲示板</span>
            </a>
            <meta itemprop="position" content="3" />
          </li>
        </ol>
      </div>
      <div class="bc_right">
        <?php if (!empty($myUserInfo)) : ?>
          <a href="mypage/" class="cp_link"><b><?php echo $myUserInfo['nickname'] ?></b> さんのマイページ</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <main><!-- メイン大枠 -->

    <div class="main_wrap4">

      <div class="prod_detail">
        <div class="prod_flex">

          <?php if ($userId == $result['buy_user']): ?>
            <div class="prod_owner">
              <img src="mypage/<?php echo showImg($partnerInfo['pic']) ?>"><br>
              出品者：<?php echo $partnerInfo['username'] ?><br>
              住所：<?php echo $partnerInfo['address'] ?><br>
              電話番号：<?php echo $partnerInfo['number'] ?>
            </div>

          <?php elseif ($userId == $result['sale_user']): ?>
            <div class="prod_owner">
              <img src="mypage/<?php echo showImg($partnerInfo['pic']) ?>"><br>
              落札者：<?php echo $partnerInfo['username'] ?><br>
              住所：<?php echo $partnerInfo['address'] ?><br>
              電話番号：<?php echo $partnerInfo['number'] ?>
            </div>
          <?php endif; ?>

          <div class="prod_productdetail">
            <img src="mypage/<?php echo showImg($productAndSale['pic1']) ?>"><br>
            <?php echo $productAndSale['p_name'] ?><br>
            ¥ <?php echo number_format($productAndSale['price']) ?><br>
            取引開始日：<?php echo $result['date'] ?>
          </div>
        </div>
      </div>

      <div class="prod_detail2" id="js-scroll-bottom">
        <?php if ($viewData): ?>
          <div class="line-bc"><!--①LINE会話全体を囲う-->
            <?php foreach ($viewData as $key => $val): ?>

              <!--②左コメント始-->
              <?php if (!empty($val['from_user']) && ($val['from_user'] == $partnerId)): ?>
                <div class="balloon6">
                  <div class="faceicon">
                    <img src="mypage/<?php echo showImg($partnerInfo['pic']) ?>">
                  </div>
                  <div class="chatting">
                    <div class="says">
                      <p style="white-space:pre;font-size:14px"><?php echo $val['msg'] ?></p>
                    </div>
                    <span style="font-size:10px;color:gray;text-align:left"><?php echo $val['send_date'] ?></span>
                  </div>
                </div>
                <!--②/左コメント終-->
              <?php else: ?>
                <!--③右コメント始-->
                <div class="mycomment_container">
                  <div class="mycomment">
                    <p style="white-space:pre;font-size:14px"><?php echo $val['msg'] ?></p>
                  </div>
                  <span style="font-size:10px;color:gray"><?php echo $val['send_date'] ?></span>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
            <!--/③右コメント終-->

          </div><!--/①LINE会話終了-->
        <?php else: ?>
          <p class="center">まだメッセージ投稿がありません。<br>メッセージを送信しましょう。</p>
        <?php endif ?>


      </div>
      <form action="" method="post">
        <textarea style="display:block;width:100%;margin:10px auto;height:200px;box-sizing:border-box;border:2px solid #999;border-radius:10px;" placeholder="連絡事項を入力してください" name="msg" class="<?php if (!empty($err_msg['msg'])) echo 'form_warning' ?>"><?php if (!empty($_POST['msg'])) echo $_POST['msg'] ?></textarea>
        <?php if (!empty($err_msg['msg'])): ?>
          <br><span class="err_warning"><?php getErrMsg('msg') ?></span>
        <?php endif; ?>
        <div class="prod_flex">
          <a href="prod.php?p_id=<?php echo $productAndSale['id'] ?>" class="cp_link">商品ページへ</a>
          <button class="btn_s" style="background:pink;font-size:14px;width:200px">投稿する</button>
      </form>
    </div>






    </div><!-- main_wrap -->
  </main>

  <footer>
    <div class="footer">
      ©︎ TOY REUSE
    </div>
  </footer>



  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script>
    $(function() {
      //scrollHeightは要素のスクロールビューの高さを取得するもの
      $('#js-scroll-bottom').animate({
        scrollTop: $('#js-scroll-bottom')[0].scrollHeight
      }, 'fast');

      // メッセージ表示
      var $jsShowMsg = $('#js-show-msg');
      var msg = $jsShowMsg.text();
      if (msg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
        $jsShowMsg.slideToggle('slow');
        setTimeout(function() {
          $jsShowMsg.slideToggle('slow');
        }, 3000);
      }
    });
  </script>
</body>

</html>