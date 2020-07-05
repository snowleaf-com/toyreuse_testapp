<div class="index_top_header"><!-- 最上段表示メニュー -->
    <h2>当サイトは赤ちゃん用品のフリマサービスです。</h2>
  </div>
  <div class="index_header"><!-- ヘッダーの大枠 -->
    <div class="index_header_left">
      <a href="../"><img src="../toplogo.png" alt="トップロゴ"></a>
    </div>
    <div class="index_header_center">
    <!-- 検索フォーム -->

    </div>
    <div class="index_header_right">

    <?php if(!empty($_SESSION['user_id'])) : ?>
      <button class="btn_top" onclick="location.href='../'">トップページ</button><br>
      <button class="btn_top2" onclick="location.href='logout.php'">ログアウト</button>
    <?php else: ?>
      <button class="btn_top" onclick="location.href='../registration_mail_form.php'">新規登録</button><br>
      <button class="btn_top2" onclick="location.href='../login.php'">ログイン</button>
    <?php endif; ?>


    </div>
  </div>
  <div class="index_menu"><!-- メニューバー大枠 -->
    <ul>
      <li>
        <?php putoutLink('index.php','マイページ'); ?>
      </li>
      <li>
        <?php putoutLink('prod_edit.php','商品出品'); ?>
      </li>
      <li>
        <?php putoutLink('member_edit.php','会員情報変更'); ?>
      </li>
      <li style="font-size: 13px;">
        <?php if(basename($_SERVER['SCRIPT_NAME']) === 'mail_pass_edit.php') {
          echo '<a href="mail_pass_edit.php" class="active" style="line-height:20px">メールアドレス<br>・パスワード変更</a>';
        } else {
          echo '<a href="mail_pass_edit.php" style="line-height:20px">メールアドレス<br>・パスワード変更</a>';
        }
        ?>
      </li>
      <li>
        <?php putoutLink('contact.php','お問い合わせ'); ?>
      </li>
      <li>
        <?php putoutLink('comm_edit.php','コミュニティ作成'); ?>
      </li>
    </ul>
  </div>