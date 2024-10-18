// index.js
import { initDropAreaEvents, initImageInputEvents } from './uiHandler.js';
import { displayImages, loadExistingImages } from './imageDisplay.js';
import { clearAllImagePaths } from './dbHandler.js';

let buttonClicked = false;

document.addEventListener("DOMContentLoaded", () => {

  if (pageFlag === 1) {
    const imageInput = document.getElementById("imageInput");
    const dropArea = document.getElementById("dropArea");
    const previewContainer = document.getElementById("previewContainer");
    const errorList = document.getElementById("errorList");

    const confirmButton = document.getElementById("confirmButton");
        confirmButton.addEventListener("click", function() {
        buttonClicked = true;
        console.log("Confirm button clicked");
        });

    // リロード時にIndexedDBから既存の画像を取得して表示し、imageCountを初期化
    loadExistingImages(previewContainer, errorList, dropArea, imageInput);

    initDropAreaEvents(dropArea, imageInput, previewContainer, errorList);
    initImageInputEvents(imageInput, previewContainer, errorList, dropArea);
  } else if (pageFlag === 2) {
    const editButton = document.getElementById("editButton");
    editButton.addEventListener("click", function() {
        buttonClicked = true;
        console.log("Edit button clicked");
    });

    displayImages();
  }
});


    window.addEventListener("beforeunload", function(event) {
        if (!buttonClicked) {
            clearAllImagePaths();
            console.log("Clearing image paths before unload");
        } else {
            console.log("Button was clicked, not clearing image paths");
        }
    });
