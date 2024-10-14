<?php
//---------------お決まり---------------------
require 'function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　商品詳細ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//----------------------------セキュリティ系-----------------------
//クロスサイトリクエストフォージェリ（CSRF）対策
if (!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
}
$token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');



if (!empty($_SESSION['user_id'])) {
  $userData = getUser($_SESSION['user_id']);
}
// 商品IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
$productDetail = getOneProduct($p_id);
$sideName = $productDetail['p_name'] . '商品ページ' . ' - ';
$page_flg = 1;

$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
$category = $productDetail['category_id'];



if (!empty($_POST['confirm']) && isLogin()) {
  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']) {
    echo "不正アクセスの可能性あり";
    exit();
  }
  require('auth.php');
  $page_flg = 2;
}




if (!empty($_POST['buy']) && isLogin()) {
  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']) {
    echo "不正アクセスの可能性あり";
    exit();
  }
  // 重複防止のため、tokenを削除→更新しても不正表示が出る。
  unset($_SESSION['token']);

  require('auth.php');

  try {
    $dbh = dbConnect();
    $dbh->beginTransaction(); //トランザクション開始

    $sql = 'INSERT INTO p_board SET product_id = :p_id, sale_user = :s_user, buy_user = :b_user, create_date = now()';
    $data = array(
      ':p_id' => $productDetail['id'],
      ':s_user' => $productDetail['user_id'],
      ':b_user' => $userData['id'],
    );
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      debug('掲示板作成成功');
    } else {
      debug('掲示板作成失敗');
      throw new PDOException('掲示板作成失敗');
    }
    $b_id = $dbh->lastInsertId();

    $sql = 'UPDATE products SET bought_flg = 1 WHERE id = :p_id AND bought_flg = 0';
    $data = array(
      ':p_id' => $productDetail['id'],
    );
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      debug('購入フラグ成功');
    } else {
      debug('購入フラグ失敗');
      throw new PDOException('掲示板作成失敗');
    }


    $dbh->commit(); //トランザクション実行


  } catch (PDOException $e) {
    error_log('SQLエラー発生' . $e->getMessage());
    $dbh->rollBack();
    exit();
  }


  $page_flg = 3;
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
  <link href="style.css" rel="stylesheet">
  <style>
    .heart {
      width: 70px;
      height: 50px;
      background: url(images/twitter_fave.png) no-repeat;
      background-position: 0 0;
      cursor: pointer;
    }

    .heart:hover {
      cursor: pointer;
      opacity: 0.6;
    }

    .active2 {
      width: 70px;
      height: 50px;
      background: url(images/twitter_fave.png) no-repeat;
      background-position: -3519px 0;
      -webkit-transition: background 1s steps(55);
      transition: background 1s steps(55);
      text-indent: 100%;
      white-space: nowrap;
      overflow: hidden;
    }
  </style>
</head>

<body>
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
        <?php if ($category == 1): ?>
          <a href="index.php?c_id=1" class="active">０〜６ヶ月</a>
        <?php else: ?>
          <a href="index.php?c_id=1">０〜６ヶ月</a>
        <?php endif; ?>
      </li>
      <li>
        <?php if ($category == 2): ?>
          <a href="index.php?c_id=2" class="active">７ヶ月〜１歳</a>
        <?php else: ?>
          <a href="index.php?c_id=2">７ヶ月〜１歳</a>
        <?php endif; ?>
      </li>
      <li>
        <?php if ($category == 3): ?>
          <a href="index.php?c_id=3" class="active">１歳〜２歳</a>
        <?php else: ?>
          <a href="index.php?c_id=3">１歳〜２歳</a>
        <?php endif; ?>
      </li>
      <li>
        <?php if ($category == 4): ?>
          <a href="index.php?c_id=4" class="active">３歳〜</a>
        <?php else: ?>
          <a href="index.php?c_id=4">３歳〜</a>
        <?php endif; ?>
      </li>
      <li>
        <?php if ($category == 5): ?>
          <a href="index.php?c_id=5" class="active">その他</a>
        <?php else: ?>
          <a href="index.php?c_id=5">その他</a>
        <?php endif; ?>
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
            <a itemprop="item" href="./index.php?c_id=<?php echo $category ?>">
              <span itemprop="name"><?php echo $productDetail['c_name'] ?></span>
            </a>
            <meta itemprop="position" content="2" />
          </li>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" style="cursor:default; text-decoration:none;color:#444">
              <span itemprop="name"><?php echo $productDetail['p_name'] ?> のページ</span>
            </a>
            <meta itemprop="position" content="3" />
          </li>
        </ol>
      </div>
      <div class="bc_right">
        <?php if (!empty($userData)) : ?>
          <a href="mypage/" class="cp_link"><b><?php echo $userData['nickname'] ?></b> さんのマイページ</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <main><!-- メイン大枠 -->

    <div class="main_wrap4">






      <?php if ($page_flg === 1): ?>

        <div class="flex" style="background: #fff;margin-top: 10px;margin-bottom: 10px;border: 1px solid #000;">
          <h1 class="prod_title2">
            <?php echo $productDetail['p_name'] ?>
          </h1>
          <?php if (isLogin()): ?>
            <div id="sample">
              <div class="heart<?php if (isLike($_SESSION['user_id'], $p_id)) {
                                  echo ' active2';
                                } ?>" width="70px" height="50px" data-detailid="<?php echo $productDetail['id'] ?>">
                <p style="font-size:10px;color:#888;text-align:center;line-height:75px;">お気に入り!</p>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <div class="prod_flex">
          <div class="prod_left">
            <img src="mypage/<?php echo showImg($productDetail['pic1']) ?>" id="js-switch-img-main">
            <p class="price">¥ <?php echo number_format($productDetail['price']) ?></p>
            <?php if ($productDetail['bought_flg'] == 1): ?>
              <p class="sold">売り切れました。</p>
            <?php endif; ?>
          </div>
          <div class="prod_right">
            <img src="mypage/<?php echo showImg($productDetail['pic1']) ?>" class="js-switch-img-sub">
            <img src="mypage/<?php echo showImg($productDetail['pic2']) ?>" class="js-switch-img-sub">
            <img src="mypage/<?php echo showImg($productDetail['pic3']) ?>" class="js-switch-img-sub">
          </div>
        </div>

        <div class="prod_detail">
          <p style="white-space: pre;"><?php echo $productDetail['comment'] ?></p>
        </div>

        <div class="prod_detail" style="text-align:right">
          出品者情報<br>
          <?php echo $productDetail['nickname'] ?><br>
          <img src="mypage/<?php echo showImg($productDetail['pic']) ?>" width="70px" height="70px" style="object-fit: contain; border-radius: 50%; border: 1px solid gray;">
        </div>

        <div class="prod_flex">
          <a href="<?php echo 'index.php' . appendGetParam(['p_id']) ?>" class="cp_link">戻る</a>

          <?php if ($productDetail['bought_flg'] == 0): ?><!-- ①売り切れてない-->
            <?php if (isLogin()) : ?><!--②ログインしている場合-->

              <?php if ($_SESSION['user_id'] === $productDetail['user_id']): ?><!--③出品者が自分の商品を 見るとき-->
                <p><a href="mypage/prod_edit.php?p_id=<?php echo $productDetail['id'] ?>" class="cp_link">編集する</a></p>
              <?php else: ?><!--③違うとき-->
                <form action="prod.php?p_id=<?php echo $productDetail['id'] ?>" method="post">
                  <button class="btn_s" name="confirm" value="購入する" style="background:pink;font-size:20px;width:200px">購入する</button>
                  <input type="hidden" name="token" value="<?= $token ?>">
                </form>
              <?php endif; ?><!--③終わり-->

            <?php else: ?><!--②ログインしていない場合-->
              <p>購入する場合は、<a href="login.php" class="cp_link">ログイン</a>か<a href="registration_mail_form.php" class="cp_link">新規登録</a>してください。</p>

            <?php endif; ?><!--②終わり -->

          <?php elseif ($productDetail['bought_flg'] == 1): ?><!-- ①売り切れのとき -->
            <?php if (!isLogin() || empty($userData['id'])): ?><!-- ④ユーザーデータ無しまたはログインしてない-->
              <p>この商品は売り切れました。</p>
            <?php else: ?><!-- ④ユーザーデータがあるまたはログインしている -->
              <?php if ($result = isSalerAndBuyer($p_id, $userData['id'])): ?><!-- ⑤購入者か出品者の場合 -->
                <a href="prod_board.php?b_id=<?php echo $result['id'] ?>">商品取引掲示板へ</a>
              <?php else: ?><!-- ⑤違う場合 -->
                <p>この商品は売り切れました。</p>
              <?php endif; ?><!-- ⑤終わり-->
            <?php endif; ?><!--　④終わり-->
          <?php endif; ?><!--①終わり-->
        </div>

      <?php elseif ($page_flg === 2): ?>

        <div class="buy_confirm_container">

          <p>以下の商品を購入しますか？</p>
          <div class="prod_flex">
            <div style="margin-right:20px;">
              <img src="mypage/<?php echo showImg($productDetail['pic1']) ?>" width="100px" height="100px" style="object-fit:cover;">
            </div>
            <div>
              <p style="font-size:20px;"><?php echo $productDetail['p_name'] ?></p>
              <p style="padding-top:10px;"><?php echo $productDetail['comment'] ?></p>
              <p style="padding-top:10px;">支払い金額：¥ <b><?php echo number_format($productDetail['price']) ?></b></p>
            </div>
          </div>

          <p>配送先</p>
          〒<?php echo substr_replace($userData['zip'], '-', 3, 0) ?><br>
          <?php echo $userData['address'] ?><br>
          <?php echo $userData['username'] ?>

          <p>よろしければ購入ボタンを押してください。</p>
          <div class="center">
            <form action="prod.php?p_id=<?php echo $productDetail['id'] ?>" method="post">
              <button class="btn_s" name="buy" value="購入する" style="background:pink;font-size:20px;width:200px;">購入する</button>
              <input type="hidden" name="token" value="<?= $token ?>">
            </form>
          </div>
        </div>


      <?php elseif ($page_flg === 3): ?>

        <div class="buy_confirm_container">
          <p>購入しました。</p>
          出品者とメッセージのやりとりをしてください。<br>
          <a href="prod_board.php?b_id=<?php echo $b_id ?>" class="cp_link">取引ナビへお進みください。</a>
        </div>

      <?php endif; ?>


    </div><!-- main_wrap -->
  </main>

  <footer>
    <div class="footer">
      ©︎ TOY REUSE
    </div>
  </footer>



  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
  <script>
    // var $ftr = $('.footer');
    //   if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
    //     $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px; width: 100%; text-align: center; font-size: 10px; color: #999;' });
    //   }
    // 画像切替

    $(function() {

      var $switchImgSubs = $('.js-switch-img-sub'),
        $switchImgMain = $('#js-switch-img-main');
      $switchImgSubs.on('click', function(e) {
        $switchImgMain.attr('src', $(this).attr('src'));
      });


      var $like;
      var likeDetailId;
      $like = $('.heart') || null;
      likeDetailId = $like.data('detailid') || null;

      if (likeDetailId !== undefined && likeDetailId !== null) {
        $like.on('click', function() {
          var $this = $(this);
          $.ajax({
            type: 'POST',
            url: 'ajaxLike.php',
            data: {
              detailId: likeDetailId
            }
          }).done(function(data) {
            console.log('Ajax Success');
            $this.toggleClass('active2');
          }).fail(function(msg) {
            console.log('Ajax Error');
          });


        });
      }
    });
  </script>
</body>

</html>