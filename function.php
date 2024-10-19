<?php
require 'vendor/autoload.php';  // Composerでインストールしたパッケージの読み込み

use Dotenv\Dotenv;
// .envファイルの読み込み
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

//================================
// ログ
//================================
//ログを取るか
date_default_timezone_set('Asia/Tokyo');
ini_set('log_errors', 'on');
//ログの出力ファイルを指定
ini_set('error_log', 'php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = true;
//デバッグログ関数
function debug($str)
{
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバッグ：' . $str);
  }
}

//================================
// セッション準備・セッション有効期限を延ばす
//================================
//セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60 * 60 * 24 * 30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart()
{
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：' . session_id());
  debug('セッション変数の中身：' . print_r($_SESSION, true));
  debug('現在日時タイムスタンプ：' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ：' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}



//================================
// データベース関数
//================================
//--------接続
function dbConnect()
{
  // 環境変数からデータベース接続情報を取得
  $dsn = 'mysql:dbname=' . $_ENV['DB_NAME'] . ';host=' . $_ENV['DB_HOST'] . ';charset=' . $_ENV['DB_CHARSET'];
  $user = $_ENV['DB_USER'];
  $password = $_ENV['DB_PASS'];

  $options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );

  // PDOインスタンスを生成
  try {
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
  } catch (PDOException $e) {
    echo '接続失敗: ' . $e->getMessage();
    exit;
  }
}

//------------データベースクエリ用関数（文字列）
function queryPost($dbh, $sql, $data)
{
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if (!$stmt->execute($data)) {
    debug('クエリに失敗しました。');
    debug('失敗したSQL：' . print_r($stmt, true));
    $err_msg['common'] = 'エラーが発生しました。\n管理者に問い合わせてください。';
    throw new PDOException('SQLエラー');
  }
  debug('クエリ成功。');
  return $stmt;
}
//------------データベースクエリ用関数（数字）　テスト中
function queryPostNum($dbh, $sql, $data)
{
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if (!$stmt->execute($data)) {
    debug('クエリに失敗しました。');
    debug('失敗したSQL：' . print_r($stmt, true));
    $err_msg['common'] = 'エラーが発生しました。';
    return 0;
  }
  debug('クエリ成功。');
  return $stmt;
}


//================================
// セキュリティ
//================================
//------------サニタイズ
function h($str)
{
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}





//================================
// バリデーション
//================================
function validRequired($str, $key)
{
  global $err_msg;
  if ($str === '') {
    $err_msg[$key] = '入力してください。';
  }
}
function validSelect($str, $key)
{
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = '正しくありません';
  }
}
function getErrMsg($key)
{
  global $err_msg;
  if (!empty($err_msg[$key])) {
    echo $err_msg[$key];
  }
}

function validKana($str, $key)
{
  $preg = "/^[ァ-ヾ]+$/u";
  if (!preg_match($preg, $str)) {
    global $err_msg;
    $err_msg[$key] = 'カタカナで入力してください。';
  }
}

function validEmail($str, $key)
{
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = '正しい形式で入力してください。';
  }
}

function validMinLen($str, $key, $min = 6)
{
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = $min . '字以上で入力してください。';
  }
}

function validMaxLen($str, $key, $max = 256)
{
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = $max . '字以下で入力してください。';
  }
}
//半角数字チェック
function validNumber($str, $key)
{
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = '半角数字で入力してください。';
  }
}
function validEmailDup($email)
{
  global $err_msg;
  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(id) AS cnt FROM members WHERE mail = :mail AND delete_flg = 0';
    $data = array(
      ':mail' => $email,
    );
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetchColumn();

    if ($result > 0) {
      $err_msg['mail'] = '登録済みアドレスです。';
    }
  } catch (PDOException $e) {
    error_log('SQLエラーです' . $e->getMessage());
    debug('SQLでPDOExceptionが作動しました。');
    $err_msg['common'] = 'エラーが発生いたしました。';
  }
}



//================================
// 取得系関数
//================================
// ユーザーデータ取得関数
function getUser($u_id)
{
  debug('ユーザー情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM members WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


//マイページ商品情報取得用関数
function getProduct($p_id, $u_id)
{
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM products WHERE id = :p_id AND user_id = :u_id AND delete_flg = 0 AND bought_flg = 0';
    $data = array(
      ':u_id' => $u_id,
      ':p_id' => $p_id
    );
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      throw new PDOException('ダメ');
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//マイページ商品一覧情報取得用関数
function getMyProduct($userid)
{
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT p.id AS p_id, p.name, p.category_id, p.comment, p.price, p.pic1, p.pic2, p.pic3, p.user_id, p.delete_flg, p.create_date, p.bought_flg, b.id AS b_id, b.sale_user, b.buy_user FROM products AS p LEFT JOIN p_board AS b ON p.id = b.product_id WHERE p.user_id = :userid AND p.delete_flg = 0 ORDER BY p.bought_flg ASC, p.create_date DESC';
    $data = array(
      ':userid' => $userid,
    );
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return 0;
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//商品詳細ページ用関数
function getOneProduct($p_id)
{
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT p.id, p.name AS p_name, p.category_id, p.comment, p.price, p.pic1, p.pic2, p.pic3, p.user_id, p.create_date, p.bought_flg, c.name AS c_name, m.nickname, m.username, m.address, m.number, m.pic FROM products AS p 
            LEFT JOIN category AS c ON p.category_id = c.id 
            LEFT JOIN members AS m ON p.user_id = m.id 
            WHERE p.id = :p_id AND p.delete_flg = 0';
    $data = array(
      ':p_id' => $p_id,
    );
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      throw new PDOException('ページ不正');
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
    exit();
  }
}


//カテゴリー取得用関数
function getCategory()
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM category';
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    return $stmt->fetchAll();
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


//================================
// その他
//================================
// 画像処理正規
function uploadImg($file, $key)
{
  debug('画像アップロード処理開始');
  debug('FILE情報：' . print_r($file, true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
        case UPLOAD_ERR_OK: // OK
          break;
        case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default: // その他の場合
          throw new RuntimeException('その他のエラーが発生しました');
      }

      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
        throw new RuntimeException('画像形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：' . $path);
      return $path;
    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

// 画像処理（仮）
function uploadImgTemp($file, $key)
{
  debug('画像アップロードテンポラ処理開始');
  debug('テンポラFILE情報：' . print_r($file, true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
        case UPLOAD_ERR_OK: // OK
          break;
        case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
        case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default: // その他の場合
          throw new RuntimeException('その他のエラーが発生しました');
      }

      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
        throw new RuntimeException('画像形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'tmp_uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('テンポラファイルは正常にアップロードされました');
      debug('テンポラファイルパス：' . $path);
      return $path;
    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}



//メール送信関数
function sendMail($from, $to, $subject, $comment)
{
  if (!empty($to) && !empty($subject) && !empty($comment)) {
    //文字化けしないように設定（お決まりパターン）
    mb_language("Japanese"); //現在使っている言語を設定する
    mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定

    //メールを送信（送信結果はtrueかfalseで返ってくる）
    $result = mb_send_mail($to, $subject, $comment, "From: " . $from);
    //送信結果を判定
    if ($result) {
      debug('メールを送信しました。');
    } else {
      debug('【エラー発生】メールの送信に失敗しました。');
    }
  }
}


// フォーム入力保持
function getFormData($key, $flg = false)
{
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $productData;
  global $err_msg;
  // ユーザーデータがある場合
  if (!empty($productData)) {
    //フォームのエラーがある場合
    if (!empty($err_msg[$key])) {
      //POSTにデータがある場合
      if (isset($method[$key])) {
        return h($method[$key]);
      } else {
        //ない場合（基本ありえない）はDBの情報を表示
        return h($productData[$key]);
      }
    } else {
      //POSTにデータがあり、DBの情報と違う場合
      if (isset($method[$key]) && $method[$key] !== $productData[$key]) {
        return h($method[$key]);
      } else {
        return h($productData[$key]);
      }
    }
  } else {
    if (isset($method[$key])) {
      return h($method[$key]);
    }
  }
}

// ユーザーフォーム入力保持
function getUserFormData($key, $flg = false)
{
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $userData;
  global $err_msg;
  // ユーザーデータがある場合
  if (!empty($userData)) {
    //フォームのエラーがある場合
    if (!empty($err_msg[$key])) {
      //POSTにデータがある場合
      if (isset($method[$key])) {
        return h($method[$key]);
      } else {
        //ない場合（基本ありえない）はDBの情報を表示
        return h($userData[$key]);
      }
    } else {
      //POSTにデータがあり、DBの情報と違う場合
      if (isset($method[$key]) && $method[$key] !== $userData[$key]) {
        return h($method[$key]);
      } else {
        return h($userData[$key]);
      }
    }
  } else {
    if (isset($method[$key])) {
      return h($method[$key]);
    }
  }
}

//sessionを１回だけ取得できる
function getSessionFlash($key)
{
  if (!empty($_SESSION[$key])) {
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}


//商品画像呼び出し処理
function getImgForm($key)
{
  global $productData;
  //まずSESSIONにパスがあるかどうかであったらそのパスをリターンする
  if (!empty($_SESSION[$key])) {
    return $_SESSION[$key];
  } else { //SESSIONになければDBを確認する
    if (!empty($productData)) {
      return h($productData[$key]);
    }
  }
}


//ユーザーの画像呼び出し処理
function getUserImgForm($key)
{
  global $userData;

  //まずSESSIONにパスがあるかどうかであったらそのパスをリターンする
  if (!empty($_SESSION[$key])) {
    return $_SESSION[$key];
  } else { //SESSIONになければDBを確認する
    if (!empty($userData[$key])) {
      return h($userData[$key]);
    }
  }
}


//画像表示用関数
function showImg($path)
{

  if (empty($path)) {
    return 'noimage.png';
  } else {
    return $path;
  }
}



function getMyCommunity($c_id, $u_id)
{
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM community WHERE id = :c_id AND made_by_id = :u_id AND delete_flg = 0';
    $data = array(
      ':c_id' => $c_id,
      ':u_id' => $u_id,
    );
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return 0;
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


// ユーザーフォーム入力保持
function getCommunityFormData($key, $flg = false)
{
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $communityData;
  global $err_msg;
  // ユーザーデータがある場合
  if (!empty($communityData)) {
    //フォームのエラーがある場合
    if (!empty($err_msg[$key])) {
      //POSTにデータがある場合
      if (isset($method[$key])) {
        return h($method[$key]);
      } else {
        //ない場合（基本ありえない）はDBの情報を表示
        return h($communityData[$key]);
      }
    } else {
      //POSTにデータがあり、DBの情報と違う場合
      if (isset($method[$key]) && $method[$key] !== $communityData[$key]) {
        return h($method[$key]);
      } else {
        return h($communityData[$key]);
      }
    }
  } else {
    if (isset($method[$key])) {
      return h($method[$key]);
    }
  }
}



// 自分の管理コミュニティ
function getCommunityMyPage($u_id)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT community.id, community.title FROM community WHERE community.made_by_id = :u_id ORDER BY community.create_date DESC';
    $data = [
      ':u_id' => $u_id,
    ];
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetchAll();


    foreach ($result as $key => $val) {

      $sql = 'SELECT user_id FROM c_join WHERE community_id = :id';
      $data = [
        ':id' => $val['id']
      ];
      $stmt = queryPost($dbh, $sql, $data);
      $result[$key]['user_id'] = $stmt->fetchAll();

      $sql = 'SELECT message FROM c_message WHERE community_id = :id';
      $stmt = queryPost($dbh, $sql, $data);
      $result[$key]['message'] = $stmt->fetchAll();
    }

    return $result;
  } catch (PDOException $e) {
    error_log('データ取得できません:' . $e->getMessage());
  }
}

// 自分の参加コミュニティ

// 参加しているコミュニティを調べる→
// 参加コミュニティと結合→
// 参加コミュニティの名前と、参加者と

function getJoinedMyCommunity($u_id)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT community.id, community.title FROM community 
            LEFT JOIN c_join ON c_join.community_id = community.id 
            WHERE c_join.user_id = :u_id ORDER BY community.create_date DESC';
    $data = [
      ':u_id' => $u_id,
    ];
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetchAll();

    if ($result) {
      foreach ($result as $key => $val) {
        $sql = 'SELECT user_id FROM c_join WHERE community_id = :id';
        $data = [
          ':id' => $val['id']
        ];
        $stmt = queryPost($dbh, $sql, $data);
        $result[$key]['user_id'] = $stmt->fetchAll();

        $sql = 'SELECT message FROM c_message WHERE community_id = :id';
        $stmt = queryPost($dbh, $sql, $data);
        $result[$key]['message'] = $stmt->fetchAll();
      }
    }


    return $result;
  } catch (PDOException $e) {
    error_log('データ取得できません:' . $e->getMessage());
  }
}




//そのページの時、activeをつける
function putoutLink($link, $name)
{

  if (basename($_SERVER['SCRIPT_NAME']) !== $link) {
    echo '<a href="' . $link . '">' . $name . '</a>';
  } else {
    echo '<a href="' . $link . '" class="active">' . $name . '</a>';
  }
}


//お気に入りがあるかどうか
function isLike($u_id, $p_id)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(*) AS cnt FROM p_favorite WHERE product_id=:p_id AND user_id=:u_id';
    $data = array(
      ':p_id' => $p_id,
      ':u_id' => $u_id,
    );
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['cnt'] > 0) {
      debug('お気に入りデータあり');
      return true;
    } else {
      debug('お気に入りデータなし');
      return false;
    }
  } catch (PDOException $e) {
    error_log('SQLエラー：' . $e->getMessage());
  }
}

function isLogin()
{
  if (!empty($_SESSION['login_date'])) {
    debug('ログイン済みユーザーです。');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
      debug('ログイン有効期限オーバーです。');

      // セッションを削除（ログアウトする）
      @session_destroy();
      return false;
    } else {
      debug('ログイン有効期限以内です。');
      //最終ログイン日時を現在日時に更新
      $_SESSION['login_date'] = time();

      return true;
    }
  } else {
    debug('未ログインユーザーです。');
    return false;
  }
}



function myFavoriteProd($u_id)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT p.id, p.name, p.price, p.pic1, p.bought_flg FROM products AS p 
            LEFT JOIN p_favorite AS f ON p.id = f.product_id 
            WHERE f.user_id = :u_id AND p.delete_flg = 0';
    $data = array(
      ':u_id' => $u_id,
    );
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (PDOException $e) {
    error_log('SQLエラー：' . $e->getMessage());
  }
}


function getMsgAndBoard($b_id)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT m.id AS m_id, m.msg, DATE_FORMAT(m.send_date, "%Y/%m/%d %H:%i") AS send_date, m.to_user, m.from_user, product_id, sale_user, b.id AS b_id, buy_user, b.create_date, fin_flg FROM p_board AS b LEFT JOIN p_message AS m ON m.p_board_id = b.id WHERE b.id = :b_id AND m.delete_flg = 0 ORDER BY m.send_date ASC';
    $data = array(
      ':b_id' => $b_id,
    );
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      debug('エラー');
      return false;
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function searchBoard($b_id, $u_id)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(id) AS cnt, product_id, sale_user, buy_user, DATE_FORMAT(create_date, "%Y/%m/%d") AS `date`, fin_flg FROM p_board WHERE id=:b_id AND (sale_user = :u_id OR buy_user = :u_id)';
    $data = array(
      ':b_id' => $b_id,
      ':u_id' => $u_id,
    );
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function isSalerAndBuyer($p_id, $u_id = '')
{
  if (empty($u_id)) {
    return false;
  } else {
    try {
      $dbh = dbConnect();
      $sql = 'SELECT id,count(id) AS cnt FROM p_board WHERE product_id = :p_id AND (sale_user = :u_id OR buy_user = :u_id)';
      $data = array(
        ':p_id' => $p_id,
        ':u_id' => $u_id,
      );
      $stmt = queryPost($dbh, $sql, $data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($result['cnt'] > 0) {
        return $result;
      } else {
        return false;
      }
    } catch (PDOException $e) {
      error_log('エラー発生:' . $e->getMessage());
    }
  }
}

function myBoughtProd($u_id)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT p.id AS p_id, b.id AS b_id, p.name, p.category_id, p.comment, p.price, p.pic1, p.bought_flg FROM p_board AS b LEFT JOIN products AS p ON b.product_id = p.id WHERE b.buy_user = :u_id AND b.delete_flg = 0 ORDER BY b.create_date DESC';
    $data = [
      ':u_id' => $u_id,
    ];
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return 0;
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination($productData, $currentPageNum, $totalPageNum, $currentMinNum, $listSpan, $link = '', $pageColNum = 5, $frontPagination = '')
{
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if ($currentPageNum == $totalPageNum && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
    // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  } elseif ($currentPageNum == ($totalPageNum - 1) && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
    // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  } elseif ($currentPageNum == 2 && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
    // 現ページが1の場合は左に何も出さない。右に５個出す。
  } elseif ($currentPageNum == 1 && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
    // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  } elseif ($totalPageNum < $pageColNum) {
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
    // それ以外は左に２個出す。
  } else {
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  if ($productData['total'] == 0) {
    $frontPagination = '0件表示';
  } elseif ($productData['total'] == 1) {
    $frontPagination = '1件表示';
  } else {
    $frontPagination = $currentMinNum + 1 . '- ';
    if ($currentPageNum == $productData['total_page']) {
      $frontPagination .= $productData['total'] . '件表示';
    } else {
      $frontPagination .= $listSpan * $currentPageNum . '件表示';
    }
  }
  $frontPagination .= ' (計' . $productData['total'] . '件)';




  echo '<div class="pagination_wrap">';
  echo '<div class="pagination_list">';
  echo '<ul>';
  echo '<li class="single">' . $frontPagination . '</li>';
  if (($currentPageNum != 1) && ($productData['total'] > 0)) {
    echo '<li><a href="?p=1' . $link . '">&lt;</a></li>';
  }
  for ($i = $minPageNum; $i <= $maxPageNum; $i++) {
    if ($currentPageNum == $i) {
      echo '<li class="active">' . $i . '</li>';
    } else {
      echo '<li><a href="?p=' . $i . $link . '">' . $i . '</a></li>';
    }
  }
  if ($currentPageNum != $maxPageNum && $maxPageNum > 1) {
    echo '<li><a href="?p=' . $maxPageNum . $link . '">&gt;</a></li>';
  }
  echo '</ul>';
  echo '</div>';
  echo '</div>';
}



function getMySalerMsgsAndBoard($u_id)
{
  debug('自分の出品取引情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // まず、掲示板レコード取得
    // SQL文作成
    $sql = 'SELECT b.id AS b_id, p.id AS p_id, p.name, p.price, m.username FROM p_board AS b 
            LEFT JOIN products AS p ON p.id = b.product_id 
            LEFT JOIN members AS m ON b.buy_user = m.id 
            WHERE b.sale_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    if (!empty($rst)) {
      foreach ($rst as $key => $val) {
        // SQL文作成
        $sql = 'SELECT msg.id, DATE_FORMAT(msg.send_date, "%Y/%m/%d %H:%i") AS send_date, msg.msg, m.username FROM p_message AS msg
                LEFT JOIN members AS m ON m.id = msg.from_user 
                WHERE msg.p_board_id = :id AND msg.delete_flg = 0 ORDER BY msg.send_date DESC';
        $data = array(':id' => $val['b_id']);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }

    if ($stmt) {
      // クエリ結果の全データを返却
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


function getMyBoughtMsgAndBoard($u_id)
{
  debug('自分の購入取引情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // まず、掲示板レコード取得
    // SQL文作成
    $sql = 'SELECT b.id AS b_id, p.id AS p_id, p.name, p.price, m.username FROM p_board AS b 
            LEFT JOIN products AS p ON p.id = b.product_id 
            LEFT JOIN members AS m ON b.sale_user = m.id 
            WHERE b.buy_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    if (!empty($rst)) {
      foreach ($rst as $key => $val) {
        // SQL文作成
        $sql = 'SELECT msg.id, DATE_FORMAT(msg.send_date, "%Y/%m/%d %H:%i") AS send_date, msg.msg, m.username FROM p_message AS msg
                LEFT JOIN members AS m ON m.id = msg.from_user 
                WHERE msg.p_board_id = :id AND msg.delete_flg = 0 ORDER BY msg.send_date DESC';
        $data = array(':id' => $val['b_id']);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }

    if ($stmt) {
      // クエリ結果の全データを返却
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}



function getCommunity($wordSearch, $currentMinNum = 1, $span = 5)
{
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT id FROM community WHERE delete_flg = 0';
    if (!empty($wordSearch)) $sql .= " AND (title LIKE \"%{$wordSearch}%\" OR comment LIKE \"%{$wordSearch}%\")";
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total'] / $span); //総ページ数
    if (!$stmt) {
      return false;
    }



    $sql = 'SELECT c.id, c.title, c.comment, m.nickname, m.pic FROM community AS c 
            LEFT JOIN members AS m ON c.made_by_id = m.id 
            WHERE c.delete_flg = 0';
    if (!empty($wordSearch)) $sql .= " AND (c.title LIKE \"%{$wordSearch}%\" OR c.comment LIKE \"%{$wordSearch}%\")";
    $sql .= ' ORDER BY c.create_date DESC';
    $sql .= ' LIMIT ' . $span . ' OFFSET ' . $currentMinNum;
    $data = [];
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


//コミュニティに参加しているかどうかを調べる
function getJoinedUser($c_id, $u_id)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(id) AS cnt FROM c_join WHERE community_id = :c_id AND user_id = :u_id';
    $data = [
      ':c_id' => $c_id,
      ':u_id' => $u_id
    ];
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetchColumn();
    if ($result > 0) {
      return true;
    } else {
      return false;
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


function getCommunityMsg($c_id)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT DATE_FORMAT(msg.send_date, "%Y/%m/%d %H:%i") AS send_date, msg.message, msg.from_user, m.nickname, m.pic FROM c_message AS msg 
            LEFT JOIN members AS m ON msg.from_user = m.id 
            WHERE msg.community_id = :c_id AND msg.delete_flg = 0';
    $data = [
      ':c_id' => $c_id
    ];
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


function getOneCommunity($c_id)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT c.id, c.title, c.comment, m.nickname, m.pic FROM community AS c 
            LEFT JOIN members AS m ON c.made_by_id = m.id 
            WHERE c.id = :c_id AND c.delete_flg = 0';
    $data = [
      ':c_id' => $c_id
    ];
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (PDOException $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array())
{
  if (!empty($_GET)) {
    $str = '?';
    foreach ($_GET as $key => $val) {
      if (!in_array($key, $arr_del_key, true)) { //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key . '=' . $val . '&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}