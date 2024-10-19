<?php
//---------------お決まり---------------------
require 'function.php';
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　タイムアウトページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


$sideName = 'タイムアウト';
debug('タイムアウトです。');

?>

<?php require 'header.php' ?>

<body class="r_m_f">
  <!-- ヘッダーロゴ -->
  <header class="r_m_f_header">
    <img src="images/toplogo.png">
  </header>

  <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
    <li itemprop="itemListElement" itemscope
      itemtype="https://schema.org/ListItem">
      <a itemprop="item" href="index.php">
        <span itemprop="name">ホーム</span>
      </a>
      <meta itemprop="position" content="1" />
    </li>
  </ol>



  <main class="r_m_f_container">
    <div class="r_m_f_main">
      <h1>タイムアウト</h1>
    </div>
    <div class="r_m_f_main2">
      <p class="center">タイムアウトしたか、不正な処理が行われました。<br>再度<a href="login.php" class="cp_link">ログイン</a>をしてください。<br><br>
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