// submitHandle.js
import { clearAllImagePaths, getAllImages } from "./dbHandler.js";

// IndexedDBのデータをPHPに送信する関数（モジュール内）
export async function submitFormWithImagePaths(formData) {
  try {
    console.log("Sending data to PHP...");

    const url = pId ? `/akachan/mypage/prod_edit.php?p_id=${pId}` : '/akachan/mypage/prod_edit.php';

    // データをPOSTリクエストで送信し、結果が返ってくるまで待機
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    });
    // レスポンスをテキストとして取得
    const responseText = await response.text();
    console.log(responseText); // サーバーからのレスポンスを確認

    try {
      // レスポンスをJSONに変換
      const jsonData = JSON.parse(responseText);
      console.log(jsonData); // 正常にパースできた場合
      if (jsonData.status === 'success') {
        // 成功したら、返されたURLに画面遷移
        window.location.href = jsonData.redirect_url;
        
        // jsonDataの処理が終わった後にIndexedDBの画像データをクリア
        clearAllImagePaths();
      } else {
        console.error('エラー:', jsonData.message);
      }
    } catch (e) {
      console.error('JSON parse error:', e); // エラーをログに出力
    }
  } catch (error) {
    console.error("Error submitting form:", error);
  }
}

// 送信ボタンのイベントを初期化する関数
export function initSubmitButtonEvent(formId, buttonId) {
  document.getElementById(buttonId).addEventListener("click", async (event) => {
    event.preventDefault(); // フォームのデフォルト送信を防ぐ

    // フォームのデータを収集
    const formData = new FormData(document.getElementById(formId));

    // PHP側で$_POST['submit']を受け取るために追加
    formData.append('submit', 'submit'); // または適切な値に変更

    // IndexedDBからデータを取得してフォームデータに追加
    const imagePaths = await getAllImages(); // IndexedDBから取得する関数

    // PHPから渡された画像データを追加
    const productImages = [productsData.pic1, productsData.pic2, productsData.pic3];

    // 空のカラム数をカウント
    const emptyCount = productImages.filter(image => !image).length;

    // 既存の画像をFormDataに追加
    for (let i = 0; i < productImages.length; i++) {
      if (productImages[i]) {
        // 既存の画像があればそのまま追加
        formData.append(`pic${i + 1}`, productImages[i]);
      }
    }

    // IndexedDBからの画像を空いているカラムに追加
    if (emptyCount > 0) {
      // 空のカラムに応じてIndexedDBから画像を追加
      let indexedImageIndex = 0; // IndexedDBの画像インデックス

      for (let i = 0; i < productImages.length; i++) {
        if (!productImages[i] && indexedImageIndex < imagePaths.length) {
          // 空いているカラムにIndexedDBの画像を追加
          formData.append(`pic${i + 1}`, imagePaths[indexedImageIndex].file);
          indexedImageIndex++; // インデックスを進める
        }
      }
    }

    // フォームデータをPHPに送信
    await submitFormWithImagePaths(formData);
  });
}