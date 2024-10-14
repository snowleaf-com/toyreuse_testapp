<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Image Upload with Preview and Drag & Drop</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>

  <p>商品画像（３枚まで/１枚あたり最大5MB）</p>
  <div class="image-upload-container">
    <input type="file" id="imageInput" accept="image/*" multiple>
    <div id="dropArea" class="drop-area">
      <label for="imageInput" class="select-button">
        画像を選択する
      </label>
      <p>またはドラッグ&ドロップ</p>
    </div>
    <div id="previewContainer"></div>
    <div id="errorList" class="error-list"></div>
  </div>

  <script src="script.js"></script>
</body>

</html>