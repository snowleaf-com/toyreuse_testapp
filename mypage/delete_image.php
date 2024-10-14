<?php
require '../function.php';
require 'auth.php';

//Todo::: CSRF対策を追加
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pic = $_POST['pic'] ?? '';

  if (in_array($pic, ['pic1', 'pic2', 'pic3'])) {
    if (isset($_SESSION[$pic])) {
      $tempFilePath = $_SESSION[$pic];
      if (file_exists($tempFilePath) && strpos($tempFilePath, 'tmp_uploads/') === 0) {
        unlink($tempFilePath);
      }
      unset($_SESSION[$pic]);
      echo 'success';
    }
  }
}
