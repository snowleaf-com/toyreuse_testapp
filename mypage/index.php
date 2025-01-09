<?php
//---------------お決まり---------------------
require '../function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
require 'auth.php';

//自分のユーザーデータを取得
$userData = getUser($_SESSION['user_id']);

//自分の出品商品情報を取得
$
 = getMyProduct($userData['id']);

//自分の購入商品情報を取得
$myBoughtProd = myBoughtProd($userData['id']);

//自分の管理コミュニティ情報を取得
$myCommunity = getCommunityMyPage($userData['id']);

//自分の参加コミュニティ情報を取得
$myJoinedCommunity = getJoinedMyCommunity($userData['id']);

//自分のお気に入り情報を取得
$myFavoriteProd = myFavoriteProd($userData['id']);

//出品取引中メッセージを取得
$MySalerMsgsAndBoard = getMySalerMsgsAndBoard($userData['id']);

//購入取引中メッセージを取得
$myBoughtMsgsAndBoard = getMyBoughtMsgAndBoard($userData['id']);


$sideName = 'マイページ' . ' - ';


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
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jscroll/3.4.1/jquery.jscroll.min.js"></script>
  <script>
    var jscrollOption = {
      loadingHtml: '読み込み中', // 記事読み込み中の表示、画像等をHTML要素で指定することも可能
      autoTrigger: true, // 次の表示コンテンツの読み込みを自動( true )か、ボタンクリック( false )にする
      padding: 20, // autoTriggerがtrueの場合、指定したコンテンツの下から何pxで読み込むか指定
      contentSelector: '.jscroll' // 読み込む範囲を指定、指定がなければページごと丸っと読み込む
    }
    $('.jscroll').jscroll(jscrollOption);
  </script>
  <?php
  function embedCommonJS()
  {
    $baseUrl = $_ENV['BASE_URL'];
    echo '<script type="module" src="' . $baseUrl . 'js/common.js"></script>';
  }
  embedCommonJS();
  ?>
</head>

<body>
  <p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>

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
            <a itemprop="item" style="cursor:default; text-decoration:none;color:#444">
              <span itemprop="name">マイページ</span>
            </a>
            <meta itemprop="position" content="2" />
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
      お気に入り一覧
    </div>

    <div class="main_wrap2">
      <?php if ($myFavoriteProd): ?>
        <?php foreach ($myFavoriteProd as $key => $val): ?>
          <div class="mypage_prod">
            <div class="mypage_prod_pic">
              <img src="<?php echo $val['pic1'] ?>">
              <?php if ($val['bought_flg'] == 1): ?>
                <p class="soldout">SOUD OUT</p>
              <?php endif; ?>
              <p class="price">¥ <?php echo number_format($val['price']) ?></p>
              <a href="../prod.php?p_id=<?php echo $val['id'] ?>">
                <p class="detail">商品ページへ</p>
              </a>
            </div>
            <div class="mypage_prod_title">
              <p style="font-size:16px;">
                <?php
                if (mb_strlen($val['name']) > 20) {
                  echo mb_substr($val['name'], 0, 19) . '...';
                } else {
                  echo $val['name'];
                }
                ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="margin: 5px auto;">お気に入りはありません。</p>
      <?php endif; ?>
    </div>

    <div class="main_title">
      商品一覧
    </div>
    <div class="tab-wrap">
      <input id="TAB-05" type="radio" name="TAB3" class="tab-switch" checked="checked" /><label class="tab-label" for="TAB-05">出品商品</label>
      <div class="tab-content jscroll">

        <?php if (!empty($myProduct)) : ?>
          <div class="main_wrap5">
            <?php foreach ($myProduct as $key => $val): ?>
              <div class="mypage_prod">
                <div class="mypage_prod_pic">
                  <img src="<?php echo showImg($val['pic1']) ?>">
                  <?php if ($val['bought_flg'] == 0) : ?>
                    <a href="../prod.php?p_id=<?php echo $val['p_id'] ?>">
                      <p class="detail">商品ページへ</p>
                    </a>
                    <p class="price">¥ <?php echo number_format((int)$val['price']) ?></p>
                    <a href="prod_edit.php?p_id=<?php echo $val['p_id'] ?>">
                      <p class="edit">編集する</p>
                    </a>
                  <?php else: ?>
                    <p class="soldout">SOUD OUT</p>
                    <a href="../prod_board.php?b_id=<?php echo $val['b_id'] ?>">
                      <p class="detail">取引ページへ</p>
                    </a>
                    <p class="price">¥ <?php echo number_format((int)$val['price']) ?></p>
                  <?php endif; ?>
                </div>
                <div class="mypage_prod_title">
                  <p style="font-size:16px;">
                    <?php
                    if (mb_strlen($val['name']) > 20) {
                      echo mb_substr($val['name'], 0, 19) . '...';
                    } else {
                      echo $val['name'];
                    }
                    ?>
                  </p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p style="margin: 5px auto;">商品は出品していません。</p>
        <?php endif; ?>
      </div>
      <input id="TAB-06" type="radio" name="TAB3" class="tab-switch" /><label class="tab-label" for="TAB-06">購入商品</label>
      <div class="tab-content">
        <?php if (!empty($myBoughtProd)) : ?>
          <div class="main_wrap5">
            <?php foreach ($myBoughtProd as $key => $val) : ?>
              <div class="mypage_prod">
                <div class="mypage_prod_pic">
                  <img src="<?php echo showImg($val['pic1']) ?>">
                  <p class="soldout">SOUD OUT</p>
                  <a href="../prod_board.php?b_id=<?php echo $val['b_id'] ?>">
                    <p class="detail">取引ページへ</p>
                  </a>
                  <p class="price">¥ <?php echo number_format((int)$val['price']) ?></p>
                </div>
                <div class="mypage_prod_title">
                  <p style="font-size:16px;">
                    <?php
                    if (mb_strlen($val['name']) > 20) {
                      echo mb_substr($val['name'], 0, 19) . '...';
                    } else {
                      echo $val['name'];
                    }
                    ?>
                  </p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p style="margin: 5px auto;">商品を購入していません。</p>
        <?php endif; ?>
      </div>
    </div>


    <div class="main_title">
      取引詳細
    </div>

    <div class="tab-wrap">
      <input id="TAB-01" type="radio" name="TAB" class="tab-switch" checked="checked" /><label class="tab-label" for="TAB-01">出品取引</label>
      <div class="tab-content">
        <!-- テーブル -->
        <table>
          <tr>
            <th width="13%">購入者</th>
            <th width="25%">商品名</th>
            <th width="7%">金額</th>
            <th witdh="55%">最終コメント</th>
          </tr>
          <?php foreach ($MySalerMsgsAndBoard as $key => $val): ?>
            <tr>
              <td><?php echo $val['username'] ?></td>
              <td><a href="../prod.php?p_id=<?php echo $val['p_id'] ?>" class="cp_link">

                  <?php
                  if (mb_strlen($val['name']) > 10) {
                    echo mb_substr($val['name'], 0, 9) . '...';
                  } else {
                    echo $val['name'];
                  }
                  ?>


                </a></td>
              <td>¥<?php echo number_format($val['price']) ?></td>
              <td>
                <?php
                // デフォルトのusernameを設定
                echo isset($val['msg'][0]['username']) && !empty($val['msg'][0]['username']) ? $val['msg'][0]['username'] : '-';
                ?>:
                <a href="../prod_board.php?b_id=<?php echo $val['b_id'] ?>" class="cp_link">
                  <?php
                  // デフォルトのmsgを設定
                  if (isset($val['msg'][0]['msg']) && !empty($val['msg'][0]['msg'])) {
                    if (mb_strlen($val['msg'][0]['msg']) > 20) {
                      echo mb_substr($val['msg'][0]['msg'], 0, 19) . '...';
                    } else {
                      echo $val['msg'][0]['msg'];
                    }
                  } else {
                    echo 'メッセージはありません';
                  }
                  ?>
                </a><br>
                （<?php
                  // デフォルトのsend_dateを設定
                  echo isset($val['msg'][0]['send_date']) && !empty($val['msg'][0]['send_date']) ? $val['msg'][0]['send_date'] : '-';
                  ?>）
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
        <!-- テーブル終わり -->
      </div>
      <input id="TAB-02" type="radio" name="TAB" class="tab-switch" /><label class="tab-label" for="TAB-02">購入取引</label>
      <div class="tab-content">
        <!-- テーブル -->
        <table>
          <tr>
            <th width="13%">出品者</th>
            <th width="25%">商品名</th>
            <th width="7%">金額</th>
            <th witdh="55%">最終コメント</th>
          </tr>
          <?php foreach ($myBoughtMsgsAndBoard as $key => $val): ?>
            <tr>
              <td><?php echo $val['username'] ?></td>
              <td><a href="../prod.php?p_id=<?php echo $val['p_id'] ?>" class="cp_link">

                  <?php
                  if (mb_strlen($val['name']) > 10) {
                    echo mb_substr($val['name'], 0, 9) . '...';
                  } else {
                    echo $val['name'];
                  }
                  ?>


                </a></td>
              <td>¥<?php echo number_format($val['price']) ?></td>
              <td>
                <?php echo $val['msg'][0]['username'] ?>:
                <a href="../prod_board.php?b_id=<?php echo $val['b_id'] ?>" class="cp_link">
                  <?php
                  if (mb_strlen($val['msg'][0]['msg']) > 20) {
                    echo mb_substr($val['msg'][0]['msg'], 0, 19) . '...';
                  } else {
                    echo $val['msg'][0]['msg'];
                  }
                  ?>
                </a><br>
                （<?php echo $val['msg'][0]['send_date'] ?>)
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
        <!-- テーブル終わり -->
      </div>
      <!-- <input id="TAB-03" type="radio" name="TAB" class="tab-switch" /><label class="tab-label" for="TAB-03">ボタン 3</label>
    <div class="tab-content">
        コンテンツ 3
    </div> -->
    </div>










    <div class="main_title">
      コミュニティ一覧
    </div>
    <div class="tab-wrap">
      <input id="TAB-03" type="radio" name="TAB2" class="tab-switch" checked="checked" /><label class="tab-label" for="TAB-03">管理コミュニティ</label>
      <div class="tab-content">
        <!-- テーブル -->
        <table>
          <tr>
            <th width="64%">コミュニティ名</th>
            <th width="12%">参加者数</th>
            <th width="12%">コメント数</th>
            <th witdh="12%">編集</th>
          </tr>
          <?php foreach ($myCommunity as $key => $val): ?>
            <tr>
              <td><a href="../community/board.php?c_id=<?php echo $val['id'] ?>" class="cp_link"><?php echo $val['title'] ?></a></td>
              <td><?php echo count($val['user_id']) ?>人</td>
              <td><?php echo count($val['message']) ?>件</td>
              <td><a href="comm_edit.php?c_id=<?php echo $val['id'] ?>" class="cp_link">編集</a></td>
            </tr>
          <?php endforeach; ?>
        </table>
        <!-- テーブル終わり -->
      </div>
      <input id="TAB-04" type="radio" name="TAB2" class="tab-switch" /><label class="tab-label" for="TAB-04">参加コミュニティ</label>
      <div class="tab-content">
        <table>
          <tr>
            <th width="64%">コミュニティ名</th>
            <th width="12%">参加者数</th>
            <th width="12%">コメント数</th>
          </tr>
          <?php foreach ($myJoinedCommunity as $key => $val): ?>
            <tr>
              <td><a href="../community/board.php?c_id=<?php echo $val['id'] ?>" class="cp_link"><?php echo $val['title'] ?></a></td>
              <td><?php echo count($val['user_id']) ?>人</td>
              <td><?php echo count($val['message']) ?>件</td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
      <!-- <input id="TAB-03" type="radio" name="TAB" class="tab-switch" /><label class="tab-label" for="TAB-03">ボタン 3</label>
    <div class="tab-content">
        コンテンツ 3
    </div> -->
    </div>





  </main>

  <footer>
    <div class="footer">
      ©︎ TOY REUSE
    </div>
  </footer>



  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script>
    var $ftr = $('.footer');
    if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
      $ftr.attr({
        'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px; width: 100%; text-align: center; font-size: 10px; color: #999;'
      });
    }

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