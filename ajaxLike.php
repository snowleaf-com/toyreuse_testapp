<?php

require 'function.php';


debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　お気に入りajax通信ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


if(!empty($_POST)) {
  debug('POST通信があります。');
  $p_id = $_POST['detailId'];
  $u_id = $_SESSION['user_id'];
  debug('アルバムID:' . $p_id);

  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(*) AS cnt FROM p_favorite WHERE product_id = :p_id AND user_id = :u_id';
    $data = array(
      ':p_id' => $p_id,
      ':u_id' => $u_id,
    );
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result['cnt'] > 0) {
      $sql = 'DELETE FROM p_favorite WHERE product_id = :p_id AND user_id = :u_id';
      $data = array(
        ':p_id' => $p_id,
        ':u_id' => $u_id,
      );
      $stmt = queryPost($dbh, $sql, $data);
    } else {
      $sql = 'INSERT INTO p_favorite SET product_id = :p_id, user_id = :u_id, create_date = now()';
      $data = array(
        ':p_id' => $p_id,
        ':u_id' => $u_id,
      );
      $stmt = queryPost($dbh, $sql, $data);
    }

  } catch(PDOException $e) { 
    error_log('SQLエラー:' . $e->getMessage());
  }
}