// index.js
import { initDropAreaEvents, initImageInputEvents } from './uiHandler.js';
import { displayImages, loadExistingImages } from './imageDisplay.js';
import { initSubmitButtonEvent } from './submitHandler.js';


document.addEventListener("DOMContentLoaded", () => {
  if (pageFlag === 1) {
    const imageInput = document.getElementById("imageInput");
    const dropArea = document.getElementById("dropArea");
    const previewContainer = document.getElementById("previewContainer");
    const errorList = document.getElementById("errorList");

    // リロード時にIndexedDBから既存の画像を取得して表示し、imageCountを初期化
    loadExistingImages(previewContainer, errorList, dropArea, imageInput);

    initDropAreaEvents(dropArea, imageInput, previewContainer, errorList);
    initImageInputEvents(imageInput, previewContainer, errorList, dropArea);
  } else if (pageFlag === 2) {
    displayImages();

    // 送信ボタンのイベントを初期化
    initSubmitButtonEvent("productsForm", "submitButton");
  }
});