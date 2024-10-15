// imageHandle.js
import { saveImagePath, deleteImagePath, getAllImages } from './dbHandler.js';

export const maxImages = 3;
export const maxSize = 5 * 1024 * 1024; // 5MB
let imageCount = 0;

export const handleFiles = (files, previewContainer, errorList, enableImageSelection, disableImageSelection) => {
  const remainingSlots = maxImages - imageCount;
  const filesToPreview = Array.from(files).slice(0, remainingSlots);
  resetErrorList(errorList);

  filesToPreview.forEach((file) => {
    if (file.size > maxSize) {
      addError(errorList, `ファイル "${file.name}" は5MBを超えています。`);
      return;
    }
    if (imageCount >= maxImages) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      createImagePreview(e.target.result, previewContainer, enableImageSelection, disableImageSelection);
    };
    reader.readAsDataURL(file);
  });
};

const resetErrorList = (errorList) => {
  errorList.innerHTML = ""; // 以前のエラーリストをクリア
};

const addError = (errorList, message) => {
  const errorItem = document.createElement("li");
  errorItem.textContent = message;
  errorList.appendChild(errorItem);
};

const createImagePreview = async (src, previewContainer, enableImageSelection, disableImageSelection) => {
  const previewDiv = document.createElement("div");
  previewDiv.classList.add("preview-image");

  const imgElement = document.createElement("img");
  imgElement.src = src;
  previewDiv.appendChild(imgElement);


  // IndexedDBに画像パスを保存し、保存された画像のidを取得
  const id = await saveImagePath(src); // ここでIDを取得


  const removeButton = createRemoveButton(previewDiv, previewContainer, id, enableImageSelection);
  previewDiv.appendChild(removeButton);

  previewContainer.appendChild(previewDiv);
  imageCount++;

  // 画像パス追加後のIndexedDBの中身を表示
  console.log("画像パス追加後のIndexedDBの中身:");
  const allImages = await getAllImages();
  console.log(allImages); // IndexedDBの全画像パスを表示

  imageInput.value = "";

  if (imageCount >= maxImages) {
    disableImageSelection();
  }
};

const createRemoveButton = (previewDiv, previewContainer, id, enableImageSelection) => {
  const removeButton = document.createElement("button");
  removeButton.textContent = "×";
  removeButton.classList.add("remove-btn");
  removeButton.onclick = async () => {
    previewContainer.removeChild(previewDiv);
    imageCount--;

    // 画像パスをIndexedDBから削除
    await deleteImagePath(id);

    // 画像パス削除後のIndexedDBの中身を表示
    console.log("画像パス削除後のIndexedDBの中身:");
    const allImages = await getAllImages();
    console.log(allImages); // IndexedDBの全画像パスを表示

    enableImageSelection();
  };
  return removeButton;
};

export const disableImageSelection = (dropArea, imageInput) => {
  dropArea.classList.add("disabled");
  imageInput.disabled = true;
};

export const enableImageSelection = (dropArea, imageInput) => {
  dropArea.classList.remove("disabled");
  imageInput.disabled = false;
};
