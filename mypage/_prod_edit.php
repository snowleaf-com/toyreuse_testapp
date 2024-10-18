<?php
//---------------お決まり---------------------
require '../function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　商品登録・編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
require 'auth.php';

$sideName = '';
$userData = getUser($_SESSION['user_id']);
$edit_flg = '';
$page_flg = 1;
$productData = '';

$access_err_msg = array();

//カテゴリーデータ取得
$categoryData = getCategory();

//----------------------------セキュリティ系-----------------------
//クロスサイトリクエストフォージェリ（CSRF）対策
if (!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

// ページ読み込み時にセッションの仮アップロードデータをリセット（GETリクエスト時）
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  unset($_SESSION['pic1']);
  unset($_SESSION['pic2']);
  unset($_SESSION['pic3']);
}

//-------------GETがあるとき---------------
if (!empty($_GET)) {

  if (!empty($_GET['p_id'])) {
    $p_id = h($_GET['p_id']);
    // それを元にDBから商品情報を取得（他人のデータを取得させないため、商品IDとユーザーIDが一致したものをもってくる）
    $productData = getProduct($p_id, $userData['id']);

    // GETパラメータはあるが、改ざんされている場合
    if (!empty($p_id) && empty($productData)) {
      debug('GETパラメータの商品IDが違います。');
      $access_err_msg[] = '不正な値が入力されました。';
    }
  } else {
    //GETがp_id以外の時も飛ばす
    $access_err_msg[] = '不正な値が入力されました。';
  }
}
//----------------------------

//商品情報があるかどうかで編集か新規登録か決める、trueで編集
$edit_flg = (empty($productData)) ? false : true;
if ($edit_flg) {
  $sideName = '商品編集' . ' - ';
} else {
  $sideName = '商品出品' . ' - ';
}

// POST送信時処理
//================================

//確認するボタン押下時
if (!empty($_POST['confirm'])) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));
  debug('FILE情報：' . print_r($_FILES, true));

  // CSRFトークンの確認
  if ($_POST['token'] !== $_SESSION['token']) {
    echo "不正アクセスの可能性あり";
    exit();
  }

  // 変数にユーザー情報を代入
  $name = h($_POST['name']);
  $category = h($_POST['category_id']);
  $price = h($_POST['price']);
  $comment = h($_POST['comment']);

  // バリデーション
  if (empty($productData)) {
    // 新規登録時のバリデーション
    validMaxLen($name, 'name', 40);
    validSelect($category, 'category_id');
    validMaxLen($comment, 'comment', 500);
    validNumber($price, 'price');
    validRequired($name, 'name');
    validRequired($category, 'category');
    validRequired($price, 'price');
    validRequired($comment, 'comment');
  } else {
    // 編集時のバリデーション
    if ($productData['name'] !== $name) {
      validRequired($name, 'name');
      validMaxLen($name, 'name', 40);
    }
    if ($productData['category_id'] !== $category) {
      validSelect($category, 'category_id');
    }
    if ($productData['comment'] !== $comment) {
      validMaxLen($comment, 'comment', 500);
      validRequired($comment, 'comment');
    }
    if ($productData['price'] != $price) {
      validRequired($price, 'price');
      validNumber($price, 'price');
    }
  }

  if (empty($err_msg)) {
    debug('バリデーションOKです。');
    $page_flg = 2;
  }
}

// 修正するボタン押下時
if (!empty($_POST['back'])) {
  $page_flg = 1; // 編集ページに戻る
}

//送信ボタン押下時
if (!empty($_POST['submit'])) {
  // CSRFトークンの確認
  if ($_POST['token'] !== $_SESSION['token']) {
    echo "不正アクセスの可能性あり";
    exit();
  }
  // トークンをクリア
  unset($_SESSION['token']);

  // 変数にユーザー情報を代入
  $name = h($_POST['name']);
  $category = h($_POST['category_id']);
  $price = h($_POST['price']);
  $comment = h($_POST['comment']);

  // セッションから画像パスを取得
  $pic1 = getImgForm('pic1');
  $pic2 = getImgForm('pic2');
  $pic3 = getImgForm('pic3');

  // 本アップロード処理
  function startsWith($str1, $str2)
  {
    if (empty($str1)) {
      return false;
    }
    $length = mb_strlen($str2);
    return (mb_substr($str1, 0, $length) === $str2);
  }

  // pic1 の移動処理
  if (!empty($pic1) && startsWith($pic1, 'tmp_uploads/')) {
    $kari1 = mb_substr($pic1, strlen('tmp_uploads/'));
    $kari2 = 'uploads/' . $kari1;
    if (rename($pic1, $kari2)) {
      $pic1 = $kari2;
    } else {
      error_log("ファイルの移動に失敗しました: " . $pic1);
      $access_err_msg[] = '画像1のアップロードに失敗しました。';
    }
  } elseif (empty($pic1)) {
    $pic1 = null;
  }

  // pic2 の移動処理
  if (!empty($pic2) && startsWith($pic2, 'tmp_uploads/')) {
    $kari1 = mb_substr($pic2, strlen('tmp_uploads/'));
    $kari2 = 'uploads/' . $kari1;
    if (rename($pic2, $kari2)) {
      $pic2 = $kari2;
    } else {
      error_log("ファイルの移動に失敗しました: " . $pic2);
      $access_err_msg[] = '画像2のアップロードに失敗しました。';
    }
  } elseif (empty($pic2)) {
    $pic2 = null;
  }

  // pic3 の移動処理
  if (!empty($pic3) && startsWith($pic3, 'tmp_uploads/')) {
    $kari1 = mb_substr($pic3, strlen('tmp_uploads/'));
    $kari2 = 'uploads/' . $kari1;
    if (rename($pic3, $kari2)) {
      $pic3 = $kari2;
    } else {
      error_log("ファイルの移動に失敗しました: " . $pic3);
      $access_err_msg[] = '画像3のアップロードに失敗しました。';
    }
  } elseif (empty($pic3)) {
    $pic3 = null;
  }

  // データベースへの登録処理
  if (empty($access_err_msg)) {
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      if ($edit_flg) {
        debug('DB更新です。');
        $sql = 'UPDATE products SET name = :name, category_id = :category, price = :price, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE user_id = :u_id AND id = :p_id';
        $data = array(
          ':name' => $name,
          ':category' => $category,
          ':price' => $price,
          ':comment' => $comment,
          ':pic1' => $pic1,
          ':pic2' => $pic2,
          ':pic3' => $pic3,
          ':u_id' => $_SESSION['user_id'],
          ':p_id' => $p_id
        );
      } else {
        debug('DB新規登録です。');
        $sql = 'INSERT INTO 
                products (name, category_id, price, comment, pic1, pic2, pic3, user_id, create_date ) 
                VALUES (:name, :category, :price, :comment, :pic1, :pic2, :pic3, :u_id, :date)';
        $data = array(
          ':name' => $name,
          ':category' => $category,
          ':price' => $price,
          ':comment' => $comment,
          ':pic1' => $pic1,
          ':pic2' => $pic2,
          ':pic3' => $pic3,
          ':u_id' => $_SESSION['user_id'],
          ':date' => date('Y-m-d H:i:s')
        );
      }
      debug('SQL：' . $sql);
      debug('流し込みデータ：' . print_r($data, true));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt) {
        // 仮アップロードのセッションデータをクリア
        unset($_SESSION['pic1']);
        unset($_SESSION['pic2']);
        unset($_SESSION['pic3']);
        $page_flg = 3;
      }
    } catch (PDOException $e) {
      error_log('エラー発生:' . $e->getMessage());
      $access_err_msg[] = 'エラーが発生しました';
    }
  }
}
?>

<?php require 'mypage_head.php' ?>
<link rel="stylesheet" href="styles.css">
<script>
  // JavaScriptファイルに渡す
  const pageFlag = <?php echo $page_flg; ?>;
</script>
<script type="module" src="../js/index.js"></script>

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
                  echo '商品編集';
                } else {
                  echo '商品出品';
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
      if ($edit_flg) {
        echo '商品名: ' . h(getFormData('name')) . ' の編集';
      } else {
        echo '商品出品';
      }
      ?>
    </div>
    <div class="main_wrap3">

      <?php if (empty($access_err_msg)) : ?>
        <?php if ($page_flg === 1) : ?>
          <span class="err_warning"><?php getErrMsg('common'); ?></span>
          <form method="post" action="" enctype="multipart/form-data">
            <div class="cp_iptxt">
              <label class="ef">商品タイトル<span class="required">必須</span>
                <br>
                <input type="text" name="name" class="<?php if (!empty($err_msg['name'])) echo 'form_warning' ?>" value="<?php echo getFormData('name') ?>">
                <?php if (!empty($err_msg['name'])): ?>
                  <span class="err_warning"><?php getErrMsg('name') ?></span>
                <?php endif; ?>
              </label>
            </div>


            <div class="selectwrap2">
              <label class="ef">カテゴリー<span class="required">必須</span><br>
                <div class="cp_ipselect cp_sl01">
                  <select name="category_id" class="<?php if (!empty($err_msg['category_id'])) echo 'form_warning ' ?>select">
                    <option value="" hidden>選択して下さい</option>
                    <?php foreach ($categoryData as $key => $val): ?>

                      <?php if ((int)getFormData('category_id') === (int)$val['id']): ?>

                        <option value="<?php echo h($val['id']); ?>" selected><?php echo h($val['name']); ?></option>

                      <?php else: ?>

                        <option value="<?php echo h($val['id']); ?>"><?php echo h($val['name']); ?></option>

                      <?php endif; ?>


                    <?php endforeach; ?>
                  </select>
                </div>
                <?php if (!empty($err_msg['category_id'])): ?>
                  <span class="err_warning"><?php getErrMsg('category_id') ?></span>
                <?php endif; ?>
              </label>
            </div>


            <div class="cp_iptxtarea">
              <label class="ef">詳細<span class="required">必須</span>
                <br>
                <textarea name="comment" class="<?php if (!empty($err_msg['comment'])) echo 'form_warning' ?>"><?php echo getFormData('comment') ?></textarea>
                <?php if (!empty($err_msg['comment'])): ?>
                  <span class="err_warning"><?php getErrMsg('comment') ?></span>
                <?php endif; ?>
              </label>
            </div>


            <div class="cp_iptxt">
              <label class="ef">金額<span class="required">必須</span>
                <br>
                <input type="number" name="price" placeholder="" class="<?php if (!empty($err_msg['price'])) echo 'form_warning' ?>" value="<?php echo h(getFormData('price')); ?>">円
                <?php if (!empty($err_msg['price'])): ?>
                  <span class="err_warning"><?php getErrMsg('price') ?></span>
                <?php endif; ?>
              </label>
            </div>


            <div class="imgDrop">
              <div>商品画像（3枚まで / 1枚あたり最大5MB）</div>
              <div class="image-upload-container">
                <div id="previewContainer"></div>
                <div id="errorList" class="error-list"></div>
                <input type="file" id="imageInput" accept="image/*" multiple>
                <div id="dropArea" class="drop-area">
                  <label for="imageInput" class="select-button">
                    画像を選択する
                  </label>
                  <p>またはドラッグ&ドロップ</p>
                </div>
              </div>

            </div>


            <p class="center pt-30">
              <input type="hidden" name="token" value="<?php echo h($token); ?>">
              <button class="btn" name="confirm" id="confirmButton" value="確認する" style="background-color:pink">確認する</button>
            </p>
          </form>


          <!-- ページフラグが２のとき -->
        <?php elseif ($page_flg === 2) : ?>



          <h3 class="center"><span class="err_warning"><?php getErrMsg('common'); ?></span></h3>
          <p class="center">以下でよろしいですか？</p>
          <form method="post" action="">

            <div class="cp_iptxt">
              <label class="ef">商品タイトル
                <p class="confirm_p"><b><?php echo h($name) ?></b></p>
                <input type="hidden" name="name" value="<?php echo h($name) ?>">
              </label>
            </div>

            <div class="cp_iptxt">
              <label class="ef">カテゴリー
                <p class="confirm_p">
                  <?php foreach ($categoryData as $key => $val): ?>
                    <?php if ((int)$val['id'] === (int)$category) : ?>
                      <b><?php echo h($val['name']) ?></b>
                </p>
              <?php endif; ?>
            <?php endforeach; ?>
            <input type="hidden" name="category_id" value="<?php echo h($category) ?>">
              </label>
            </div>

            <div class="cp_iptxt">
              <label class="ef">詳細
                <p class="confirm_p" style="white-space: pre;"><b><?php echo h($comment) ?></b></p>
                <input type="hidden" name="comment" value="<?php echo h($comment) ?>">
              </label>
            </div>

            <div class="cp_iptxt">
              <label class="ef">金額
                <p class="confirm_p">
                  <b><?php echo number_format((int)$price) ?></b> 円
                </p>
                <input type="hidden" name="price" value="<?php echo h($price) ?>">
              </label>
            </div>

            <div class="cp_iptxt">
              <label class="ef">画像１
                <p class="confirm_p">
                  <img id="image1" src="" width="200px" height="200px" style="object-fit:contain; display:none;">
                </p>
              </label>
            </div>

            <div class="cp_iptxt">
              <label class="ef">画像２
                <p class="confirm_p">
                  <img id="image2" src="" width="200px" height="200px" style="object-fit:contain; display:none;">
                </p>
              </label>
            </div>

            <div class="cp_iptxt">
              <label class="ef">画像３
                <p class="confirm_p">
                  <img id="image3" src="" width="200px" height="200px" style="object-fit:contain; display:none;">
                </p>
              </label>
            </div>

            <!-- トークンやボタンなど他のフォームフィールド -->
            <p class="center pt-30">
              <button class="btn_s" id="editButton" type="submit" name="back" style="background-color:azure" value="修正する">修正する</button>
              <button class="btn_s" type="submit" name="submit" value="登録する">登録する</button>
            </p>

          </form>


          <!-- ページフラグが３のとき -->
        <?php elseif ($page_flg === 3): ?>

          <p class="center pt-20 pb-20">登録が完了しました。<br>
            <a href="mypage.php">マイページへ戻る</a>
          </p>
        <?php endif; ?>

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