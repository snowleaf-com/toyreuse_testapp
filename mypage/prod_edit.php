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
if(!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
}
$token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');


//-------------GETがあるとき---------------
// 変数に格納
if(!empty($_GET)) {

  if(!empty($_GET['p_id'])) {
    $p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
    // それを元にDBから商品情報を取得（他人のデータを取得させないため、
    //商品IDとユーザーIDが一致したものをもってくる
    $productData = getProduct($p_id, $userData['id']);
    
    // GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる
    if(!empty($p_id) && empty($productData)){
      debug('GETパラメータの商品IDが違います。');
      $access_err_msg[] = '不正な値が入力されました。';
    }
    
  } else {
    //GETがp_id以外の時も飛ばす
    $access_err_msg[] = '不正な値が入力されました。';
  }
  
}
//-----------------------------


//商品情報があるかどうかで編集か新規登録か決める、trueで編集
$edit_flg = (empty($productData)) ? false : true;
if($edit_flg) {
  $sideName = '商品編集' . ' - ';
} else {
  $sideName = '商品出品' . ' - ';
}

// POST送信時処理
//================================

//確認するボタン押下時
if(!empty($_POST['confirm'])){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES,true));
  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']){
    echo "不正アクセスの可能性あり";
    exit();
  }

  //変数にユーザー情報を代入
  $name = h($_POST['name']);
  $category = h($_POST['category_id']);
  $price = h($_POST['price']);
  $comment = h($_POST['comment']);

  //画像を仮アップロードし、パスをセッションに格納
  //アップロードファイルがなく、DBに画像データがある場合はそれを格納
  if(empty($_SESSION['pic1'])) {
    $_SESSION['pic1'] = ( !empty($_FILES['pic1']['name']) ) ? uploadImgTemp($_FILES['pic1'],'pic1') : '';
    $_SESSION['pic1'] = ( empty($_SESSION['pic1']) && !empty($productData['pic1']) ) ? $productData['pic1'] : $_SESSION['pic1'];
  } else {
    if(!empty($_FILES['pic1']['name'])) {
      $_SESSION['pic1'] = uploadImgTemp($_FILES['pic1'],'pic1');
    }
  }

  if(empty($_SESSION['pic2'])) {
  $_SESSION['pic2'] = ( !empty($_FILES['pic2']['name']) ) ? uploadImgTemp($_FILES['pic2'],'pic2') : '';
  $_SESSION['pic2'] = ( empty($_SESSION['pic2']) && !empty($productData['pic2']) ) ? $productData['pic2'] : $_SESSION['pic2'];
  } else {
    if(!empty($_FILES['pic2']['name'])) {
      $_SESSION['pic2'] = uploadImgTemp($_FILES['pic2'],'pic2');
    }
  }

  if(empty($_SESSION['pic3'])) {
  $_SESSION['pic3'] = ( !empty($_FILES['pic3']['name']) ) ? uploadImgTemp($_FILES['pic3'],'pic3') : '';
  $_SESSION['pic3'] = ( empty($_SESSION['pic3']) && !empty($productData['pic3']) ) ? $productData['pic3'] : $_SESSION['pic3'];
  } else {
    if(!empty($_FILES['pic3']['name'])) {
      $_SESSION['pic3'] = uploadImgTemp($_FILES['pic3'],'pic3');
    }
  }
  
    // 更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
    if(empty($productData)){//$productDataが無い＝出品なので、普通にバリデーションを行う
      //最大文字数チェック
      validMaxLen($name, 'name', 40);
      //セレクトボックスチェック
      validSelect($category, 'category_id');
      //最大文字数チェック
      validMaxLen($comment, 'comment', 500);
      //半角数字チェック
      validNumber($price, 'price');
      //未入力チェック
      validRequired($name, 'name');
      validRequired($category, 'category');
      validRequired($price, 'price');
      validRequired($comment, 'comment');
    }else{
      if($productData['name'] !== $name){
        //未入力チェック
        validRequired($name, 'name');
        //最大文字数チェック
        validMaxLen($name, 'name', 40);
      }
      if($productData['category_id'] !== $category){
        //セレクトボックスチェック
        validSelect($category, 'category_id');
      }
      if($productData['comment'] !== $comment){
        //最大文字数チェック
        validMaxLen($comment, 'comment', 500);
        validRequired($comment, 'comment');
      }
      if($productData['price'] != $price){ //前回まではキャストしていたが、ゆるい判定でもいい
        //未入力チェック
        validRequired($price, 'price');
        //半角数字チェック
        validNumber($price, 'price');
        validRequired($price, 'price');
      }
    }
  
    if(empty($err_msg)){
      debug('バリデーションOKです。');

      $page_flg = 2;
    }

}

//(修正するボタン)
if(!empty($_POST['back'])) {
  $page_flg = 1;
}


//送信ボタン押下時
if(!empty($_POST['submit'])) {
  //遷移前に生成したトークンと同じかどうかを比べて違ったらエラーを出す
  if ($_POST['token'] !== $_SESSION['token']){
    echo "不正アクセスの可能性あり";
    exit();
  }
  //次に進むページがない場合はunsetする。＝重複防止
  unset($_SESSION['token']);

  //変数にユーザー情報を代入
  $name = h($_POST['name']);
  $category = h($_POST['category_id']);
  $price = h($_POST['price']);
  $comment = h($_POST['comment']);


  $pic1 = getImgForm('pic1');

  //本アップロード処理
  function startsWith($str1, $str2) {
    $length = mb_strlen($str2);
    return (mb_substr($str1, 0, $length) === $str2);
  }
  if(startsWith($pic1, 'tmp_uploads')) {
    $kari1 = mb_substr($pic1, 12);
    $kari2 = 'uploads/' . $kari1;
    rename($pic1, $kari2);
    $pic1 = $kari2;
  }
  
  
  $pic2 = getImgForm('pic2');

    if(startsWith($pic2, 'tmp_uploads')) {
      $kari1 = mb_substr($pic2, 12);
      $kari2 = 'uploads/' . $kari1;
      rename($pic2, $kari2);
      $pic2 = $kari2;
    }
    
    
    $pic3 = getImgForm('pic3');

    if(startsWith($pic3, 'tmp_uploads')) {
      $kari1 = mb_substr($pic3, 12);
      $kari2 = 'uploads/' . $kari1;
      rename($pic3, $kari2);
      $pic3 = $kari2;
    }
    


  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    // 編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
    if($edit_flg){
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
    }else{
      debug('DB新規登録です。');
      $sql = 'INSERT INTO 
      products (name, category_id, price, comment, pic1, pic2, pic3, user_id, create_date ) 
      values (:name, :category, :price, :comment, :pic1, :pic2, :pic3, :u_id, :date)';
      $data = array(
        ':name' => $name,
        ':category' => $category,
        ':comment' => $comment,
        ':price' => $price,
        ':pic1' => $pic1,
        ':pic2' => $pic2,
        ':pic3' => $pic3,
        ':u_id' => $_SESSION['user_id'],
        ':date' => date('Y-m-d H:i:s'));
    }
    debug('SQL：'.$sql);
    debug('流し込みデータ：'.print_r($data,true));
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ成功の場合
    if($stmt){
      if(!empty($_SESSION['pic1']) || !empty($_SESSION['pic2']) || !empty($_SESSION['pic3'])) {
        unset($_SESSION['pic1']);
        unset($_SESSION['pic2']);
        unset($_SESSION['pic3']);
      }
      $page_flg = 3;
    }

  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
    $access_err_msg[] = 'エラーが発生しました';
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
                if($edit_flg) {
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
        <b><?php echo $userData['nickname'] ?></b> さんのマイページ
      </div>
    </div>
  </div>
<main><!-- メイン大枠 -->

  <div class="main_title">
  <?php
        if($edit_flg) {
          echo '商品名: ' . getFormData('name') . ' の編集';
        } else {
          echo '商品出品';
        }
        ?>
  </div>
  <div class="main_wrap3">

    <?php if(empty($access_err_msg)) : ?>
    <?php if($page_flg === 1) : ?>
      <span class="err_warning"><?php getErrMsg('common'); ?></span>
    <form method="post" action="" enctype="multipart/form-data">
    <div class="cp_iptxt">
      <label class="ef">商品タイトル<span class="required">必須</span>
        <br>
        <input type="text" name="name" class="<?php if(!empty($err_msg['name'])) echo 'form_warning' ?>" value="<?php echo getFormData('name') ?>">
        <?php if(!empty($err_msg['name'])): ?>
          <span class="err_warning"><?php getErrMsg('name') ?></span>
        <?php endif; ?>
      </label>
    </div>
  

    <div class="selectwrap2">
      <label class="ef">カテゴリー<span class="required">必須</span><br>
        <div class="cp_ipselect cp_sl01">
          <select name="category_id" class="<?php if(!empty($err_msg['category_id'])) echo 'form_warning ' ?>select">
            <option value="" hidden>選択して下さい</option>
            <?php foreach($categoryData as $key => $val): ?>

            <?php if(getFormData('category_id') === $val['id']): ?>

            <?php echo '<option value="' . $val['id'] . '" selected>' . $val['name'] . '</option>'; ?>
            
            <?php else: ?>
            
            <?php echo '<option value="' . $val['id'] . '">' . $val['name'] . '</option>'; ?>

            <?php endif; ?>
            

            <?php endforeach; ?>
          </select>
        </div>
        <?php if(!empty($err_msg['category_id'])): ?>
          <span class="err_warning"><?php getErrMsg('category_id') ?></span>
        <?php endif; ?>
      </label>
    </div>


    <div class="cp_iptxtarea">
      <label class="ef">詳細<span class="required">必須</span>
        <br>
        <textarea name="comment" class="<?php if(!empty($err_msg['comment'])) echo 'form_warning' ?>"><?php echo getFormData('comment') ?></textarea>
        <?php if(!empty($err_msg['comment'])): ?>
          <span class="err_warning"><?php getErrMsg('comment') ?></span>
        <?php endif; ?>
      </label>
    </div>


    <div class="cp_iptxt">
      <label class="ef">金額<span class="required">必須</span>
        <br>
        <input type="number" name="price" placeholder="" class="<?php if(!empty($err_msg['price'])) echo 'form_warning' ?>" value="<?php echo getFormData('price'); ?>">円
        <?php if(!empty($err_msg['price'])): ?>
          <span class="err_warning"><?php getErrMsg('price') ?></span>
        <?php endif; ?>
      </label>
    </div>


    <div class="imgDrop">

      <div class="imgDrop-container">
        <label class="area-drop">
          <input type="file" name="pic1" class="input-file">
          <img src="<?php echo getImgForm('pic1'); ?>" alt="" class="prev-img" style="<?php if(empty(getImgForm('pic1'))) echo 'display:none;' ?>">
            <p>画像１</p>
        </label>
      </div>


    <div class="imgDrop-container">
      <label class="area-drop">
        <input type="file" name="pic2" class="input-file">
        <img src="<?php echo getImgForm('pic2'); ?>" alt="" class="prev-img" style="<?php if(empty(getImgForm('pic2'))) echo 'display:none;' ?>">
          <p>画像２</p>
      </label>
    </div>

    <div class="imgDrop-container">
      <label class="area-drop">
        <input type="file" name="pic3" class="input-file">
        <img src="<?php echo getImgForm('pic3'); ?>" alt="" class="prev-img" style="<?php if(empty(getImgForm('pic3'))) echo 'display:none;' ?>">
          <p>画像３</p>
      </label>
    </div>
    </div>


    <p class="center pt-30">
    <input type="hidden" name="token" value="<?=$token?>">
    <button class="btn" type="submit" name="confirm" value="確認する" style="background-color:pink">確認する</button>
    </p>
    </form>

    
    <!-- ページフラグが２のとき -->
    <?php elseif($page_flg === 2) : ?>


      
      <h3 class="center"><span class="err_warning"><?php getErrMsg('common'); ?></span></h3>
        <p class="center">以下でよろしいですか？</p>
        <form method="post" action="">

          <div class="cp_iptxt">
            <label class="ef">商品タイトル
            <p class="confirm_p"><b><?php echo $name ?></b></p>
            <input type="hidden" name="name" value="<?php echo $name ?>">
          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">カテゴリー
            <p class="confirm_p">
              <?php foreach($categoryData as $key => $val): ?>
                <?php if($val['id'] === $category) : ?>
                <b><?php echo $val['name'] ?></b></p>
                <?php endif; ?>
              <?php endforeach; ?>
            <input type="hidden" name="category_id" value="<?php echo $category ?>">
          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">詳細
            <p class="confirm_p" style="white-space: pre;"><b><?php echo $comment ?></b></p>
            <input type="hidden" name="comment" value="<?php echo $comment ?>">
          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">金額
            <p class="confirm_p">
            <b><?php echo number_format((int)$price) ?></b> 円</p>
            <input type="hidden" name="price" value="<?php echo $price ?>">
          </label>
          </div>

          <div class="cp_iptxt">
            <label class="ef">画像１
            <p class="confirm_p">
            <img src="<?php echo getImgForm('pic1') ?>" width="200px" height="200px" style="object-fit:contain;<?php if(empty(getImgForm('pic1'))) echo ' display:none;' ?>"></p>
            <input type="hidden" name="pic1" value="<?php echo getImgForm('pic1') ?>">
          </label>
          </div>
          <div class="cp_iptxt">
            <label class="ef">画像２
            <p class="confirm_p">
            <img src="<?php echo getImgForm('pic2') ?>" width="200px" height="200px" style="object-fit:contain;<?php if(empty(getImgForm('pic2'))) echo ' display:none;' ?>"></p>
            <input type="hidden" name="pic2" value="<?php echo getImgForm('pic2') ?>">
          </label>
          </div>
          <div class="cp_iptxt">
            <label class="ef">画像３
            <p class="confirm_p">
            <img src="<?php echo getImgForm('pic3') ?>" width="200px" height="200px" style="object-fit:contain;<?php if(empty(getImgForm('pic3'))) echo ' display:none;' ?>"></p>
            <input type="hidden" name="pic3" value="<?php echo getImgForm('pic3') ?>">
          </label>
          </div>


            <input type="hidden" name="token" value="<?=$token?>">
            <p class="center pt-30">
            <button class="btn_s" type="submit" name="back" style="background-color:azure" value="修正する">修正する</button>
            <button class="btn_s" type="submit" name="submit" value="登録する">登録する</button>
            </p>

        </form>


    <!-- ページフラグが３のとき -->
      <?php elseif($page_flg === 3): ?>
        
        <p class="center pt-20 pb-20">登録が完了しました。<br>
        
        <?php endif; ?>
        
        <?php else: ?>
    
        <?php foreach($access_err_msg as $val) {
          echo '<p class="pt-20 pb-20" style="text-align:center">' . $val . '</p><br>';
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



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

<script>
  // var $ftr = $('.footer');
  //   if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
  //     $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px; width: 100%; text-align: center; font-size: 10px; color: #999;' });
  //   }
    // 画像ライブプレビュー
    var $dropArea = $('.area-drop');
    var $fileInput = $('.input-file');
    $dropArea.on('dragover', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', '3px #ccc dashed');
    });
    $dropArea.on('dragleave', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', 'none');
    });
    $fileInput.on('change', function(e){
      $dropArea.css('border', 'none');
      $(this).siblings('p').text('');
      var file = this.files[0],            // 2. files配列にファイルが入っています
          $img = $(this).siblings('.prev-img'), // 3. jQueryのsiblingsメソッドで兄弟のimgを取得
          fileReader = new FileReader();   // 4. ファイルを読み込むFileReaderオブジェクト

      // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
      fileReader.onload = function(event) {
        // 読み込んだデータをimgに設定
        $img.attr('src', event.target.result).show();
      };

      // 6. 画像読み込み
      fileReader.readAsDataURL(file);

    });
</script>
</body>
</html>