document.addEventListener("DOMContentLoaded", () => {
  const imageInput = document.getElementById("imageInput");
  const dropArea = document.getElementById("dropArea");
  const previewContainer = document.getElementById("previewContainer");
  const maxImages = 3;
  const maxSize = 5 * 1024 * 1024; // 5MBをバイトに換算
  let imageCount = 0;

  const handleFiles = (files) => {
    const remainingSlots = maxImages - imageCount;
    const filesToPreview = Array.from(files).slice(0, remainingSlots);
    const errorList = document.getElementById("errorList");
    errorList.innerHTML = ""; // 以前のエラーリストをクリア

    filesToPreview.forEach((file) => {
      // ファイルサイズが制限を超えているか確認
      if (file.size > maxSize) {
        const errorItem = document.createElement("li");
        errorItem.textContent = `ファイル "${file.name}" は5MBを超えています。`;
        errorList.appendChild(errorItem);
        return;
      }
      if (imageCount >= maxImages) return;

      const reader = new FileReader();
      reader.onload = (e) => {
        const previewDiv = document.createElement("div");
        previewDiv.classList.add("preview-image");

        const imgElement = document.createElement("img");
        imgElement.src = e.target.result;
        previewDiv.appendChild(imgElement);

        const removeButton = document.createElement("button");
        removeButton.textContent = "×";
        removeButton.classList.add("remove-btn");
        removeButton.onclick = () => {
          previewContainer.removeChild(previewDiv);
          imageCount--;
          enableImageSelection();
          imageInput.value = ""; // 画像を削除したとき
        };
        previewDiv.appendChild(removeButton);

        previewContainer.appendChild(previewDiv);
        imageCount++;

        // 画像を追加した後にinputの値をリセット
        imageInput.value = "";

        if (imageCount >= maxImages) {
          disableImageSelection();
        }
      };
      reader.readAsDataURL(file);
    });
  };

  const disableImageSelection = () => {
    dropArea.classList.add("disabled");
    imageInput.disabled = true;
  };

  const enableImageSelection = () => {
    dropArea.classList.remove("disabled");
    imageInput.disabled = false;
  };

  imageInput.addEventListener("change", (event) => {
    const files = event.target.files;
    handleFiles(files);
  });

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

  const handleDrop = (event) => {
    dropArea.classList.remove("dragover");
    const files = event.dataTransfer.files;
    handleFiles(files);
  };

  // dropAreaがクリックされた場合、inputを開く
  dropArea.addEventListener("click", () => {
    imageInput.click();
  });

  // labelをクリックした場合は、dropAreaのイベントをキャンセル
  document
    .querySelector('label[for="imageInput"]')
    .addEventListener("click", (event) => {
      event.preventDefault(); // labelのデフォルト動作をキャンセル
    });

  dropArea.addEventListener("drop", handleDrop);
});
