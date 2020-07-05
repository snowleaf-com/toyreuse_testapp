<?php
//---------------お決まり---------------------
require '../function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログアウトページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if(empty($_SESSION['user_id'])) {
  echo '不正アクセスです';
  exit();
}

$sideName = 'ログアウトしました。';
debug('ログアウトします。');
// セッションを削除（ログアウトする）
@session_destroy();

?>

<?php require 'mypage_head.php' ?>

<body class="r_m_f">
<!-- ヘッダーロゴ -->
<header class="r_m_f_header">
  <img src="../toplogo.png">
</header>

<ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
  <li itemprop="itemListElement" itemscope
      itemtype="https://schema.org/ListItem">
    <a itemprop="item" href="../index.php">
        <span itemprop="name">ホーム</span>
    </a>
    <meta itemprop="position" content="1" />
  </li>
</ol>



  <main class="r_m_f_container">
    <div class="r_m_f_main">
      <h1>ログアウト</h1>
    </div>
    <div class="r_m_f_main2">
      <p class="center">ログアウトしました。<br>
      再度ログインする方は<a href="../login.php" class="cp_link">こちら</a>から。<br>
    </div>




  </main>
<footer>
  <div class="footer">
    ©︎ TOY REUSE
  </div>
</footer>



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

<script>

</script>
</body>
</html>