// index.js
import { initDropAreaEvents, initImageInputEvents } from './uiHandler.js';
import { displayImages } from './imageDisplay.js';

document.addEventListener("DOMContentLoaded", () => {

  if (pageFlag === 1) {
    const imageInput = document.getElementById("imageInput");
    const dropArea = document.getElementById("dropArea");
    const previewContainer = document.getElementById("previewContainer");
    const errorList = document.getElementById("errorList");

    initDropAreaEvents(dropArea, imageInput, previewContainer, errorList);
    initImageInputEvents(imageInput, previewContainer, errorList, dropArea);
  } else if (pageFlag === 2) {
    displayImages();
  }
});