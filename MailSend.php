<?php

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

class MailSend
{
  private $smtpHost;
  private $smtpPort;
  private $smtpUsername;
  private $smtpPassword;
  private $smtpFromEmail;
  private $smtpFromName;

  public function __construct()
  {
    // .envファイルの読み込み
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // 環境変数の取得
    $this->smtpHost = $_ENV['SMTP_HOST'];
    $this->smtpPort = $_ENV['SMTP_PORT'];
    $this->smtpUsername = $_ENV['SMTP_USERNAME'];
    $this->smtpPassword = $_ENV['SMTP_PASSWORD'];
    $this->smtpFromEmail = $_ENV['SMTP_FROM_EMAIL'];
    $this->smtpFromName = $_ENV['SMTP_FROM_NAME'];
  }

  public function sendMail($sub, $message, $receive)
  {
    mb_language("japanese");
    mb_internal_encoding("UTF-8");

    $mail = new PHPMailer(true);
    try {
      // SMTP設定
      $mail->isSMTP();
      $mail->Host = $this->smtpHost;
      $mail->Port = $this->smtpPort;
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = 'tls';
      $mail->Username = $this->smtpUsername;
      $mail->Password = $this->smtpPassword;

      // 送信者情報
      $mail->setFrom($this->smtpFromEmail, mb_encode_mimeheader($this->smtpFromName, "ISO-2022-JP", "UTF-8"));

      // 受信者
      $mail->addAddress($receive);

      // メール内容
      $mail->CharSet = 'UTF-8';
      $mail->Encoding = '7bit';
      $mail->Subject = mb_encode_mimeheader($sub, "ISO-2022-JP", "UTF-8");
      $mail->Body = mb_convert_encoding($message, "UTF-8", "auto");

      $mail->send();
    } catch (Exception $e) {
      $this->debug("送信エラー: " . $mail->ErrorInfo);
    }
  }

  private function debug($message)
  {
    // デバッグ方法に応じて実装
    // 例: error_log($message);
    echo $message;
  }
}
