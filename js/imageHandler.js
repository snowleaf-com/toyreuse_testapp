// imageHandler.js
import { addImagePath, removeImagePath } from './dbHandler.js';

const createImagePreview = async (src, previewContainer, enableImageSelection, disableImageSelection) => {
  const previewDiv = document.createElement("div");
  previewDiv.classList.add("preview-image");

  const imgElement = document.createElement("img");
  imgElement.src = src;
  previewDiv.appendChild(imgElement);

  const removeButton = createRemoveButton(previewDiv, previewContainer, src, enableImageSelection);
  previewDiv.appendChild(removeButton);

  previewContainer.appendChild(previewDiv);
  imageCount++;

  // 画像パスをIndexedDBに保存
  await addImagePath(src);

  if (imageCount >= maxImages) {
    disableImageSelection();
  }
};

const createRemoveButton = (previewDiv, previewContainer, src, enableImageSelection) => {
  const removeButton = document.createElement("button");
  removeButton.textContent = "×";
  removeButton.classList.add("remove-btn");
  removeButton.onclick = async () => {
    previewContainer.removeChild(previewDiv);
    imageCount--;

    // 画像パスをIndexedDBから削除
    await removeImagePath(src);

    enableImageSelection();
  };
  return removeButton;
};
