<?php
//---------------お決まり---------------------
require '../function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　コミュニティトップページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

$sideName = 'コミュニティトップ' . ' - ';

if (!empty($_SESSION['user_id'])) {
  $userData = getUser($_SESSION['user_id']);
}


$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページめ
// 表示件数
$listSpan = 5;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan); //1ページ目なら(1-1)*20 = 0 、 ２ページ目なら(2-1)*20 = 20

$link = '';
if (!empty($_GET['q'])) {
  $link .= '&q=' . $_GET['q'];
  $wordSearch = $_GET['q'];
} else {
  $wordSearch = 0;
}

$communityDetail = getCommunity($wordSearch, $currentMinNum, $span = 5);


if (!empty($_POST)) {
  if (isLogin()) {
    $c_id  = h($_POST['c_id']);
    try {
      $dbh = dbConnect();
      $sql = 'INSERT INTO c_join SET community_id = :c_id, user_id = :u_id, join_date = now()';
      $data = [
        ':c_id' => $c_id,
        ':u_id' => $userData['id'],
      ];
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
        debug('クエリ成功');
        $_SESSION['msg_success'] = '参加しました。';
        header('Location: board.php?c_id=' . $c_id);
        exit();
      } else {
        debug('失敗');
      }
    } catch (PDOException $e) {
      $err_msg['common'] = 'SQL失敗しました。';
      error_log('SQLエラー：' . $e->getMessage());
    }
  } else {
    $err_msg['common'] = '参加するためにはログインしてください。';
  }
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
      <form action="" method="get">
        <input type="search" name="q" value="<?php if (!empty($_GET['q'])) echo $_GET['q'] ?>" placeholder="コミュニティ内検索"><button type="submit" class="search_submit">検索</button>
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
            <a itemprop="item" style="cursor:default; text-decoration:none;color:#444">
              <span itemprop="name">コミュニティ</span>
            </a>
            <meta itemprop="position" content="2" />
          </li>
          <!-- <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
              <a itemprop="item" href="./">
              <span itemprop="name">商品ページ</span>
          </a>
          <meta itemprop="position" content="3" />
        </li> -->
        </ol>
      </div>
      <div class="bc_right">
        <!-- 全 19 件　見つかりました -->
      </div>
    </div>
  </div>
  <main><!-- メイン大枠 -->

    <div class="main_wrap4">
      <p class="center err_warning"><?php getErrMsg('common'); ?></p>
      <p class="center err_warning"><?php if (!isLogin()) echo 'ログインしてご利用ください。'; ?></p>
      <?php foreach ($communityDetail['data'] as $key => $val): ?>
        <div class="comm_wrap">
          <div class="comm_img">
            <img src="../mypage/<?php echo showImg($val['pic']) ?>">
          </div>
          <div class="comm_detail">
            <h3>管理者：<?php echo $val['nickname'] ?></h3>
            <h1><?php echo $val['title'] ?></h1>
            <h2><?php echo $val['comment'] ?></h2>
          </div>
          <div class="comm_join">

            <?php if (isLogin()): ?>
              <?php if (getJoinedUser($val['id'], $userData['id'])): ?>
                <a href="board.php?c_id=<?php echo $val['id'] ?>"><button style="background: #aaa">参加中</button></a>
              <?php else: ?>
                <form method="post" action="">
                  <input type="hidden" name="c_id" value="<?php echo $val['id'] ?>">
                  <button type="submit" name="join">参加！</button>
                </form>
              <?php endif; ?>
            <?php endif; ?>

          </div>
        </div>
      <?php endforeach; ?>

    </div><!-- main_wrap -->
    <?php pagination($communityDetail, $currentPageNum, $communityDetail['total_page'], $currentMinNum, $listSpan, $link, $pageColNum = 5, '',) ?>
  </main>

  <footer>
    <div class="footer">
      ©︎ TOY REUSE
    </div>
  </footer>



  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script>
    // メッセージ表示
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if (msg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
      $jsShowMsg.slideToggle('slow');
      setTimeout(function() {
        $jsShowMsg.slideToggle('slow');
      }, 3000);
    }
  </script>
</body>

</html>