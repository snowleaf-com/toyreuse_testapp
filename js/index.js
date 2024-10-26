// index.js
import { initDropAreaEvents, initImageInputEvents } from './uiHandler.js';
import { displayImages, displayInitialImages } from './imageDisplay.js';
import { initSubmitButtonEvent } from './submitHandler.js';
import { managePId } from './pIdManager.js';

document.addEventListener("DOMContentLoaded", async () => {
  await managePId();// GETパラメータ変更によるindexedDBデータ削除処理

  const imageInput = document.getElementById("imageInput");
  const dropArea = document.getElementById("dropArea");
  const previewContainer = document.getElementById("previewContainer");
  const errorList = document.getElementById("errorList");

  if (pageFlg === 1) {
    displayInitialImages(previewContainer, errorList, dropArea, imageInput); // 画像表示用
    initDropAreaEvents(dropArea, imageInput, previewContainer, errorList);
    initImageInputEvents(imageInput, previewContainer, errorList, dropArea);
  } else if (pageFlg === 2) {
    displayImages();
    initSubmitButtonEvent("productsForm", "submitButton"); // 送信ボタンのイベントを初期化
  }
});