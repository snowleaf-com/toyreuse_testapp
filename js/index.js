// index.js
import { initDropAreaEvents, initImageInputEvents } from './uiHandler.js';

document.addEventListener("DOMContentLoaded", () => {
  const imageInput = document.getElementById("imageInput");
  const dropArea = document.getElementById("dropArea");
  const previewContainer = document.getElementById("previewContainer");
  const errorList = document.getElementById("errorList");

  initDropAreaEvents(dropArea, imageInput, previewContainer, errorList);
  initImageInputEvents(imageInput, previewContainer, errorList, dropArea);
});