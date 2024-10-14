// imageHandler.js
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

const createImagePreview = (src, previewContainer, enableImageSelection, disableImageSelection) => {
  const previewDiv = document.createElement("div");
  previewDiv.classList.add("preview-image");

  const imgElement = document.createElement("img");
  imgElement.src = src;
  previewDiv.appendChild(imgElement);

  const removeButton = createRemoveButton(previewDiv, previewContainer, enableImageSelection);
  previewDiv.appendChild(removeButton);

  previewContainer.appendChild(previewDiv);
  imageCount++;

  imageInput.value = "";

  if (imageCount >= maxImages) {
    disableImageSelection();
  }
};

const createRemoveButton = (previewDiv, previewContainer, enableImageSelection) => {
  const removeButton = document.createElement("button");
  removeButton.textContent = "×";
  removeButton.classList.add("remove-btn");
  removeButton.onclick = () => {
    previewContainer.removeChild(previewDiv);
    imageCount--;
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
