// imageDisplay.js
import { getAllImages } from './dbHandler.js';
import { displaySavedImage, disableImageSelection, enableImageSelection, maxImages, setImageCount, getImageCount } from './imageHandler.js';


// IndexedDBから取得した画像を表示する関数
export const displayInitialImages = async (previewContainer, errorList, dropArea, imageInput) => {
  // 編集フラグがtrueの場合、initialImageCountを使って既存画像数を初期化
  const { pic1, pic2, pic3 } = productsData;
  const existingImages = [pic1, pic2, pic3].filter(Boolean);
  let currentImageCount = existingImages.length;

  // productsDataがある場合、まずはその画像をプレビューに表示
  for (let i = 0; i < currentImageCount; i++) {
    const imgPath = productsData[`pic${i + 1}`];
    if (imgPath) {
      displaySavedImage(imgPath, previewContainer, `product-img-${i}`, 
        () => enableImageSelection(dropArea, imageInput),
        () => disableImageSelection(dropArea, imageInput),
        true // 削除ボタンを無効にする
      );
    }
  }

  // 追加アップロードが可能な場合にのみ、IndexedDBから画像をロード
  if (currentImageCount < maxImages) {
    const images = await getAllImages();
    images.slice(0, maxImages - currentImageCount).forEach((image) => {
      displaySavedImage(image.filePath, previewContainer, image.id, 
        () => enableImageSelection(dropArea, imageInput),
        () => disableImageSelection(dropArea, imageInput)
      );
      currentImageCount++;
    });
  }
  
  // 画像の数でimageCountを初期化
  setImageCount(currentImageCount);
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
    } else {
      imgElement.style.display = 'none'; // 画像がない場合は非表示
    }
  }

  // productsDataから画像を取得
  const { pic1, pic2, pic3 } = productsData;
  const imagesToDisplay = [pic1, pic2, pic3]; // 最初に3つの画像を配列にまとめる

  // IndexedDBから全ての画像パスを取得して表示
  getAllFromIndexedDB()
    .then((images) => {
      // imagesのインデックスを追跡するための変数
      let imageIndex = 0;

      // imagesToDisplayの空いている部分にIndexedDBの画像を埋める
      for (let i = 0; i < 3; i++) {
        if (!imagesToDisplay[i] && imageIndex < images.length) {
          imagesToDisplay[i] = images[imageIndex].filePath; // 空いている場所に画像を追加
          imageIndex++; // インデックスを進める
        }
      }

      // 最終的な画像リストを表示
      imagesToDisplay.forEach((imgPath, index) => {
        displayImage(`image${index + 1}`, imgPath);
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