<?php
// デバッグ用にエラー表示を有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//---------------お決まり---------------------
require '../function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　商品登録・編集の完了ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
require 'auth.php';

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');
$access_err_msg = array();
$edit_flg = '';
$p_id = '';
$sideName = '';
$userData = getUser($_SESSION['user_id']); // ユーザー情報取得

if (!isset($_SESSION['edit_flg']) || $_SESSION['edit_flg'] === '') {
  // セッション変数が設定されていない場合または、変数が空文字である場合はアクセスを拒否
  $access_err_msg[] = '不正なアクセスです。';
  $sideName = 'エラー' . ' - ';
} else {
  // $_SESSION['edit_flg'] が true または false の場合の処理
  $edit_flg = $_SESSION['edit_flg']; // true または false が入る
  unset($_SESSION['edit_flg']);

  // products_id の存在チェック
  if (!empty($_SESSION['products_id'])) {
    $p_id = $_SESSION['products_id'];
    unset($_SESSION['products_id']);
  }

  // edit_flg に基づく処理
  if ($edit_flg === true) {
    $sideName = '商品編集完了' . ' - ';
  } else {
    $sideName = '商品出品完了' . ' - ';
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
                if (empty($access_err_msg)) {
                  if ($edit_flg) {
                    echo '商品編集完了';
                  } else {
                    echo '商品出品完了';
                  }
                } else {
                  echo 'エラー';
                }
                ?>
              </span>
            </a>
            <meta itemprop="position" content="3" />
          </li>
        </ol>
      </div>
      <div class="bc_right">
        <b><?php echo h($userData['nickname']) ?></b> さんのマイページ
      </div>
    </div>
  </div>
  <main><!-- メイン大枠 -->

    <div class="main_title">
      <?php
      if (empty($access_err_msg)) {
        if ($edit_flg) {
          echo '商品名: ' . h(getFormData('name')) . ' の編集';
        } else {
          echo '商品出品';
        }
      } else {
        echo 'エラー';
      }
      ?>
    </div>
    <div class="main_wrap3">

      <?php if (empty($access_err_msg)) : ?>

        <p class="center pt-20 pb-20">
          <?php
          if ($edit_flg) {
            echo '編集';
          } else {
            echo '出品';
          }
          ?>
          が完了しました。<br>
          <a href="./">マイページへ戻る</a>
        </p>
      <?php else: ?>

        <?php foreach ($access_err_msg as $val) {
          echo '<p class="pt-20 pb-20" style="text-align:center">' . h($val) . '</p><br>';
        }
        ?>
      <?php endif; ?>
    </div>

  </main>

  <footer>
    <div class="footer">
      ©︎ TOY REUSE
    </div>
  </footer>
</body>

</html>