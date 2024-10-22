import { clearAllImagePaths, getAllImages } from "./dbHandler.js";

// IndexedDBのデータをPHPに送信する関数（モジュール内）
export async function submitFormWithImagePaths(formData) {
  try {
    console.log("Sending data to PHP...");

    // データをPOSTリクエストで送信し、結果が返ってくるまで待機
    const response = await fetch('/akachan/mypage/_prod_edit.php', {
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
        // 画像パスをそれぞれpic1, pic2, pic3としてFormDataに追加
    imagePaths.forEach((image, index) => {
      if (index === 0) {
        formData.append('pic1', image.file);
      } else if (index === 1) {
        formData.append('pic2', image.file);
      } else if (index === 2) {
        formData.append('pic3', image.file);
      }
    });
    
    // フォームデータをPHPに送信
    await submitFormWithImagePaths(formData);
  });
}