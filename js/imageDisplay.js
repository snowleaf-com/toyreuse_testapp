// imageDisplay.js
import { getAllImages } from './dbHandler.js';
import { displaySavedImage, disableImageSelection, enableImageSelection, maxImages, setImageCount, getImageCount } from './imageHandler.js';


// IndexedDBから取得した画像を表示する関数
export const loadExistingImages = async (previewContainer, errorList, dropArea, imageInput) => {
  const images = await getAllImages(); // IndexedDBから画像パスを取得
  images.forEach((image) => {
    // 既存の画像を表示
    displaySavedImage(image.filePath, previewContainer, image.id, () => enableImageSelection(dropArea, imageInput), () => disableImageSelection(dropArea, imageInput));
  });
  
  // 画像の数でimageCountを初期化
  setImageCount(images.length);
  // 画像が最大数に達している場合は画像選択を無効にする
  if (getImageCount() >= maxImages) {
    disableImageSelection(dropArea, imageInput);
  } else {
    enableImageSelection(dropArea, imageInput);
  }
};

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