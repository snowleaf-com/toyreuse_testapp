// imageDisplay.js

export function displayImages() {
  // 画像を表示するための関数
  function displayImage(imgElementId, imgPath) {
    const imgElement = document.getElementById(imgElementId);
    if (imgPath) {
      imgElement.src = imgPath;
      imgElement.style.display = 'block'; // 画像を表示
    }
  }

  // IndexedDBから全ての画像パスを取得して表示
  getAllFromIndexedDB()
    .then((images) => {
      images.forEach((image, index) => {
        displayImage(`image${index + 1}`, image.filePath);
      });
    })
    .catch((error) => {
      console.error('エラー:', error);
    });
}

// IndexedDBから全データを取得する関数
function getAllFromIndexedDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('imageDB', 1);

    request.onsuccess = (event) => {
      const db = event.target.result;
      const transaction = db.transaction(['imageStore'], 'readonly');
      const objectStore = transaction.objectStore('imageStore');
      const getAllRequest = objectStore.getAll(); // 全データを取得

      getAllRequest.onsuccess = () => {
        resolve(getAllRequest.result); // 取得したデータを返す
      };

      getAllRequest.onerror = () => {
        reject('IndexedDBからの取得エラー');
      };
    };

    request.onerror = () => {
      reject('IndexedDBのオープンエラー');
    };
  });
}