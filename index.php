<?php
//---------------お決まり---------------------
require 'function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　トップページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


if (!empty($_SESSION['user_id'])) {
  $userData = getUser($_SESSION['user_id']);
}

$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページめ
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';


// $link = 'p=' . $currentPageNum;
$link = '';

if (!empty($category)) {
  $link .= '&c_id=' . $category;
}

if (!empty($_GET['q'])) {
  $link .= '&q=' . $_GET['q'];
  $wordSearch = $_GET['q'];
} else {
  $wordSearch = 0;
}

// 表示件数
$listSpan = 20;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan); //1ページ目なら(1-1)*20 = 0 、 ２ページ目なら(2-1)*20 = 20

// DBから商品データを取得
function getAllProduct($category, $wordSearch, $currentMinNum = 1, $span = 20)
{
  debug('商品情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT id FROM products WHERE delete_flg = 0';
    if (!empty($category)) $sql .= ' AND category_id = ' . $category;
    if (!empty($wordSearch)) $sql .= " AND (name LIKE \"%{$wordSearch}%\" OR comment LIKE \"%{$wordSearch}%\")";
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total'] / $span); //総ページ数
    if (!$stmt) {
      return false;
    }

    // ページング用のSQL文作成
    $sql = 'SELECT * FROM products WHERE delete_flg = 0';
    if (!empty($category)) $sql .= ' AND category_id = ' . $category;
    if (!empty($wordSearch)) $sql .= " AND (name LIKE \"%{$wordSearch}%\" OR comment LIKE \"%{$wordSearch}%\")";

    $sql .= " ORDER BY id DESC";
    $sql .= ' LIMIT ' . $span . ' OFFSET ' . $currentMinNum;
    $data = array();
    debug('SQL：' . $sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

$productData = getAllProduct($category, $wordSearch, $currentMinNum, 20);



$sideName = '';
// $sideName = '' . ' - ';


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
</head>

<body>
  <div class="index_top_header"><!-- 最上段表示メニュー -->
    <h2>当サイトは赤ちゃん用品のフリマサービスです。</h2>
  </div>
  <div class="index_header"><!-- ヘッダーの大枠 -->
    <div class="index_header_left">
      <a href="./"><img src="images/toplogo.png" alt="トップロゴ"></a>
    </div>
    <div class="index_header_center">
      <!-- 検索フォーム -->
      <form action="" method="get">
        <?php if (!empty($category)): ?>
          <input type="hidden" name="c_id" value="<?php echo $category ?>">
        <?php endif; ?>
        <input type="search" name="q" value="<?php if (!empty($_GET['q'])) echo $_GET['q'] ?>" placeholder="何かお探しですか？"><button type="submit" class="search_submit">検索</button>
      </form>
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
        <?php if (empty($category)): ?>
          <ol class="index_breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
              itemtype="https://schema.org/ListItem">
              <a itemprop="item" style="cursor:default; text-decoration:none;color:#444">
                <span itemprop="name">ホーム</span>
              </a>
              <meta itemprop="position" content="1" />
            </li>
          </ol>
        <?php else: ?>
          <ol class="index_breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
              itemtype="https://schema.org/ListItem">
              <a itemprop="item" href="./">
                <span itemprop="name">ホーム</span>
              </a>
              <meta itemprop="position" content="1" />
            </li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
              <a itemprop="item" style="cursor:default; text-decoration:none;color:#444">
                <?php if ($category == 1): ?>
                  <span itemprop="name">０〜６ヶ月</span>
                <?php elseif ($category == 2): ?>
                  <span itemprop="name">７ヶ月〜１歳</span>
                <?php elseif ($category == 3): ?>
                  <span itemprop="name">１歳〜２歳</span>
                <?php elseif ($category == 4): ?>
                  <span itemprop="name">３歳〜</span>
                <?php elseif ($category == 5): ?>
                  <span itemprop="name">その他</span>
                <?php endif; ?>
              </a>
              <meta itemprop="position" content="2" />
            </li>
          </ol>

        <?php endif; ?>
      </div>
      <div class="bc_center">

      </div>
      <div class="bc_right">
        <?php if (!empty($userData) && isLogin()) : ?>
          <a href="mypage/" class="cp_link"><b><?php echo $userData['nickname'] ?></b> さんのマイページ</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <main><!-- メイン大枠 -->

    <div class="main_wrap">

      <?php foreach ($productData['data'] as $key => $val): ?>
        <div class="prod">
          <a href="prod.php?p=<?php echo $currentPageNum ?><?php echo $link ?>&p_id=<?php echo $val['id'] ?>"></a>
          <div class="prod_pic">
            <img src="mypage/<?php echo showImg($val['pic1']) ?>">
            <?php if ($val['bought_flg'] == 1): ?>
              <p class="soldout">SOUD OUT</p>
            <?php endif; ?>
            <p class="price">¥<?php echo number_format((int)$val['price']) ?></p>
          </div>
          <div class="prod_title">
            <?php
            if (mb_strlen($val['name']) > 20) {
              echo mb_substr($val['name'], 0, 19) . '...';
            } else {
              echo $val['name'];
            }
            ?>
          </div>
        </div>
      <?php endforeach; ?>


    </div>
    <?php pagination(
      $productData,
      $currentPageNum,
      $productData['total_page'],
      $currentMinNum,
      $listSpan,
      $link,
      $pageColNum = 5,
      ''
    ) ?>


  </main>



  <footer>
    <div class="footer">
      ©︎ TOY REUSE
    </div>
  </footer>



  <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
  var $ftr = $('.footer');
    if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
      $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px; width: 100%; text-align: center; font-size: 10px; color: #999;' });
    }
</script> -->
</body>

</html>