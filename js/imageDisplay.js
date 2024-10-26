// imageDisplay.js
import { getAllImages } from './dbHandler.js';
import { displaySavedImage, disableImageSelection, enableImageSelection, maxImages, setImageCount, getImageCount } from './imageHandler.js';


// IndexedDBから取得した画像を表示する関数
export const loadExistingImages = async (previewContainer, errorList, dropArea, imageInput, initialImageCount = 0) => {
  // 編集フラグがtrueの場合、initialImageCountを使って既存画像数を初期化
  let currentImageCount = initialImageCount;

  // productsDataがある場合、まずはその画像をプレビューに表示
  for (let i = 0; i < initialImageCount; i++) {
    const imgPath = productsData[`pic${i + 1}`];
    if (imgPath) {
      displaySavedImage(imgPath, previewContainer, `product-img-${i}`, 
        () => enableImageSelection(dropArea, imageInput),
        () => disableImageSelection(dropArea, imageInput),
        false // 削除ボタンを無効にする
      );
    }
  }

  const images = await getAllImages();
  images.slice(0, maxImages - initialImageCount).forEach((image) => {
    displaySavedImage(image.filePath, previewContainer, image.id, 
      () => enableImageSelection(dropArea, imageInput),
      () => disableImageSelection(dropArea, imageInput)
    );
    currentImageCount++;
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