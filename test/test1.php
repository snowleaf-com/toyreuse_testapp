<?php
require '../function.php';

echo '<pre>';
var_dump($_POST);
var_dump($_SESSION);
echo '</pre>';

$flg = 0;

//$_SESSION['token']がない時、tokenを詰める
//更に、変数に詰める。
if(!isset($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];
}
//あれば、$tokenにつめておく。
$token = $_SESSION['token'];



//1度目のPOSTがあったとき。
//条件にページ数は書かない。
//POSTされた時に結局上から読み込まれて、$flg = 0になるから。
if(!empty($_POST['flg0'])) {
  //POSTの中のtokenと、セッションのtokenが同じかどうかを見ている
  //違った場合、（違う事はないのだが）エラーとする。
  if($_POST['token0'] !== $_SESSION['token']) {
    header('Location: error.html');
  }

  //1度目でのPOSTでも再読み込みを禁止したい場合
  //$_SESSIONをunsetし、再びトークンを生成して、$tokenに詰めれば
  //POSTとSESSIONは同じになるから、次ページ移動してもエラーにならない
  unset($_SESSION['token']);
  $_SESSION['token'] = bin2hex(random_bytes(32));
  $token = $_SESSION['token'];

  $flg = 1;
}

//2度目のPOSTがあったとき。
if(!empty($_POST['flg1'])) {
  //POSTの中のtokenと、セッションのtokenが同じかどうかを見ている
  //違った場合、（違う事はないのだが）エラーとする。
  if($_POST['token1'] !== $_SESSION['token']) {
    echo 'エラーです';
    exit();
  }
  //
  unset($_SESSION['token']);

  
  $dbh = dbConnect();
  $sql = 'INSERT INTO test SET date=now()';
  $data = array();
  $stmt = queryPost($dbh, $sql, $data);
  
  $flg = 2;

}



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  

<?php if($flg === 0) : ?>

  投稿画面です。
  <form method="post">
    <input type="text" name="token0" value="<?=$token?>">
    <input type="submit" name="flg0">
  </form>
  
<?php elseif($flg === 1) : ?>
    
    確認画面です。
    <form method="post">
      <input type="text" name="token1" value="<?=$token?>">
    <input type="submit" name="flg1">
  </form>
    
<?php elseif($flg === 2) : ?>
      
    完了画面です。


<?php endif; ?>
</body>
</html>