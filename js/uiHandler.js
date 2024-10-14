// uiHandler.js
import { handleFiles, enableImageSelection, disableImageSelection } from './imageHandler.js';

export const initDropAreaEvents = (dropArea, imageInput, previewContainer, errorList) => {
  const preventDefaults = (e) => {
    e.preventDefault();
    e.stopPropagation();
  };

  ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    dropArea.addEventListener(eventName, preventDefaults, false);
  });

  dropArea.addEventListener("dragover", () => {
    dropArea.classList.add("dragover");
  });

  dropArea.addEventListener("dragleave", () => {
    dropArea.classList.remove("dragover");
  });

  dropArea.addEventListener("drop", (event) => {
    dropArea.classList.remove("dragover");
    const files = event.dataTransfer.files;
    handleFiles(files, previewContainer, errorList, 
      () => enableImageSelection(dropArea, imageInput), 
      () => disableImageSelection(dropArea, imageInput));
  });

  // dropAreaがクリックされた場合、inputを開く
  dropArea.addEventListener("click", () => {
    imageInput.click();
  });
};

export const initImageInputEvents = (imageInput, previewContainer, errorList, dropArea) => {
  imageInput.addEventListener("change", (event) => {
    const files = event.target.files;
    handleFiles(files, previewContainer, errorList, 
      () => enableImageSelection(dropArea, imageInput), 
      () => disableImageSelection(dropArea, imageInput));
  });

  // labelをクリックした場合は、dropAreaのイベントをキャンセル
  document.querySelector('label[for="imageInput"]').addEventListener("click", (event) => {
    event.preventDefault(); // labelのデフォルト動作をキャンセル
  });
};
