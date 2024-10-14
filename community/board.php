<?php

//共通変数・関数ファイルを読込み
require('../function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　コミュニティ掲示板ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

$sideName = 'コミュニティ掲示板ページ' . ' - ';


$c_id = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
if (empty($c_id)) {
  header('Location: index.php');
}

$u_id = $_SESSION['user_id'];

if (getJoinedUser($c_id, $u_id)) {
  debug('参加者です');

  $communityDetail = getOneCommunity($c_id);

  $communityMsg = getCommunityMsg($c_id);

  if (!empty($_POST['unsubscribe'])) {
    //ログイン認証
    require('auth.php');

    try {
      $dbh = dbConnect();
      $sql = 'SELECT count(made_by_id) FROM community WHERE made_by_id = :u_id AND id = :c_id';
      $data = [
        ':u_id' => $u_id,
        ':c_id' => $c_id
      ];
      $stmt = queryPost($dbh, $sql, $data);
      $result = $stmt->fetchColumn();
      if ($result > 0) {
        $err_msg['common'] = '管理者は参加解除できません';
        debug('管理者が削除しようとしました。');
      } else {

        $sql = 'DELETE FROM c_join WHERE user_id=:u_id AND community_id =:c_id';
        $data = [
          ':u_id' => $u_id,
          ':c_id' => $c_id,
        ];
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
          debug('削除完了');
          $_SESSION['msg_success'] = '参加解除しました。';
          header('Location: index.php');
          exit();
        } else {
          debug('削除できません');
        }
      }
    } catch (PDOException $e) {
      error_log('エラー発生:' . $e->getMessage());
    }
  }

  if (!empty($_POST['msg'])) {
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
        $sql = 'INSERT INTO c_message SET community_id = :c_id, send_date = now(), from_user = :u_id, message = :msg, create_date = now()';
        $data = array(
          ':c_id' => $c_id,
          ':u_id' => $u_id,
          ':msg' => $msg,
        );
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
          debug('メッセージ投稿しました。');
          $_SESSION['msg_success'] = '投稿しました。';
          header('Location: board.php?c_id=' . $c_id);
          debug($stmt);
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
} else {
  header('Location: index.php');
}


?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $sideName ?>TOY REUSE - 赤ちゃん用品のリサイクル、コミュニティ -</title>
  <link href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Lato:400,700|Noto+Sans+JP:400,700" rel="stylesheet">
  <link href="../style.css" rel="stylesheet">
</head>

<body>
  <p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <div class="index_top_header"><!-- 最上段表示メニュー -->
    <h2>当サイトは赤ちゃん用品のフリマサービスです。</h2>
  </div>
  <div class="index_header"><!-- ヘッダーの大枠 -->
    <div class="index_header_left">
      <a href="../"><img src="../images/toplogo.png" alt="トップロゴ"></a>
    </div>
    <div class="index_header_center">
      <!-- 検索フォーム -->
      <form action="" method="post">
        <input type="search" name="q" value="" placeholder="コミュニティ内検索"><input type="submit" name="btn_search" value="検索" class="search_submit">
      </form>
    </div>
    <div class="index_header_right">
      <?php if (isLogin()) : ?>
        <button class="btn_top" onclick="location.href='../mypage/'">マイページ</button><br>
        <button class="btn_top2" onclick="location.href='../mypage/logout.php'">ログアウト</button>
      <?php else: ?>
        <button class="btn_top" onclick="location.href='../registration_mail_form.php'">新規登録</button><br>
        <button class="btn_top2" onclick="location.href='../login.php'">ログイン</button>
      <?php endif; ?>
    </div>
  </div>
  <div class="index_menu"><!-- メニューバー大枠 -->
    <ul>
      <li style="border:none;">

      </li>
      <li style="border:none;">

      </li>
      <li style="border:none;">

      </li>
      <li style="border:none;">

      </li>
      <li style="border:none;">

      </li>
      <li>
        <a href="./" class="active">コミュニティ</a>
      </li>
    </ul>
  </div>
  <div class="index_breadcrumb_wrap"><!-- パンくず大枠 -->
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
            <a itemprop="item" href="index.php">
              <span itemprop="name">コミュニティ</span>
            </a>
            <meta itemprop="position" content="2" />
          </li>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" style="cursor:default; text-decoration:none;color:#444">
              <span itemprop="name"><?php echo $communityDetail['title'] ?>のページ</span>
            </a>
            <meta itemprop="position" content="3" />
          </li>
        </ol>
      </div>
      <div class="bc_right">
        <!-- 全 19 件　見つかりました -->
      </div>
    </div>
  </div>
  <main><!-- メイン大枠 -->

    <div class="main_wrap4">

      <p class="err_warning center"><?php getErrMsg('common') ?></p>

      <div class="comm_wrap">
        <div class="comm_img">
          <img src="../mypage/<?php echo showImg($communityDetail['pic']) ?>">
        </div>
        <div class="comm_detail">
          <h3>管理者：<?php echo $communityDetail['nickname'] ?></h3>
          <h1><?php echo $communityDetail['title'] ?></h1>
          <h2><?php echo $communityDetail['comment'] ?></h2>
        </div>
        <div class="comm_join">
          <form action="board.php?c_id=<?php echo $c_id ?>" method="post">
            <button name="unsubscribe" value="参加解除">参加解除</button>
          </form>
        </div>
      </div>

      <div class="prod_detail2" id="js-scroll-bottom">
        <?php if ($communityMsg): ?>
          <div class="line-bc"><!--①LINE会話全体を囲う-->
            <?php foreach ($communityMsg as $key => $val): ?>

              <!--②左コメント始-->
              <?php if ($val['from_user'] != $u_id): ?>
                <div class="balloon6">
                  <div class="faceicon">
                    <img src="../mypage/<?php echo $val['pic'] ?>">
                  </div>
                  <div class="chatting">
                    <div class="says">
                      <p style="white-space:pre;font-size:14px"><?php echo $val['message'] ?></p>
                    </div>
                    <span style="font-size:10px;color:gray;text-align:left"><b><?php echo $val['nickname'] ?></b> (<?php echo $val['send_date'] ?>)</span>
                  </div>
                </div>
                <!--②/左コメント終-->
              <?php else: ?>
                <!--③右コメント始-->
                <div class="mycomment_container">
                  <div class="mycomment">
                    <p style="white-space:pre;font-size:14px"><?php echo $val['message'] ?></p>
                  </div>
                  <span style="font-size:10px;color:gray"><b><?php echo $val['nickname'] ?></b> (<?php echo $val['send_date'] ?>)</span>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
            <!--/③右コメント終-->

          </div><!--/①LINE会話終了-->
        <?php else: ?>
          <p class="center">まだメッセージ投稿がありません。<br>メッセージを送信しましょう。</p>
        <?php endif ?>


      </div>
      <?php if (!empty($err_msg['msg'])): ?>
        <br><span class="err_warning"><?php getErrMsg('msg') ?></span>
      <?php endif; ?>
      <form action="board.php?c_id=<?php echo $c_id ?>" method="post">
        <textarea style="display:block;width:100%;margin:10px auto;height:200px;box-sizing:border-box;border:2px solid #999;border-radius:10px;" placeholder="連絡事項を入力してください" name="msg" class="<?php if (!empty($err_msg['msg'])) echo 'form_warning' ?>"><?php if (!empty($_POST)) echo $_POST['msg'] ?></textarea>

        <div class="prod_flex">
          <a href="./" class="cp_link">コミュニティトップへ</a>
          <button type="submit" class="btn_s" style="background:pink;font-size:14px;width:200px">投稿する</button>
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