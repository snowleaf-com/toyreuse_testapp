// dbHandler.js
const dbName = "imageDB";
const storeName = "imageStore";

// IndexedDBを初期化する関数
export const initDB = () => {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(dbName, 1);

    request.onerror = (event) => {
      console.error("IndexedDB error:", event);
      reject(event);
    };

    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains(storeName)) {
        db.createObjectStore(storeName, { keyPath: "id", autoIncrement: true });
      }
    };

    request.onsuccess = (event) => {
      const db = event.target.result;
      resolve(db);
    };
  });
};

// 画像パスを保存する関数
export const saveImagePath = (filePath) => {
  initDB().then((db) => {
    const transaction = db.transaction(storeName, "readwrite");
    const store = transaction.objectStore(storeName);
    store.add({ filePath });

    transaction.oncomplete = () => {
      console.log("File path saved successfully");
    };

    transaction.onerror = (event) => {
      console.error("Failed to save file path:", event);
    };
  });
};

// 画像パスを削除する関数
export const deleteImagePath = (filePath) => {
  initDB().then((db) => {
    const transaction = db.transaction(storeName, "readwrite");
    const store = transaction.objectStore(storeName);
    const index = store.index("filePath");

    const request = index.getKey(filePath);
    request.onsuccess = (event) => {
      const key = event.target.result;
      if (key !== undefined) {
        store.delete(key);
        console.log("File path deleted successfully");
      }
    };

    transaction.onerror = (event) => {
      console.error("Failed to delete file path:", event);
    };
  });
};

// 全ての画像パスを削除する関数
export const clearAllImagePaths = () => {
  initDB().then((db) => {
    const transaction = db.transaction(storeName, "readwrite");
    const store = transaction.objectStore(storeName);
    const clearRequest = store.clear();

    clearRequest.onsuccess = () => {
      console.log("All file paths deleted successfully");
    };

    clearRequest.onerror = (event) => {
      console.error("Failed to delete all file paths:", event);
    };
  });
};

// 全ての画像パスを取得する関数
export const getAllImages = () => {
  return new Promise((resolve, reject) => {
    initDB().then((db) => {
      const transaction = db.transaction(storeName, "readonly");
      const store = transaction.objectStore(storeName);
      const request = store.getAll();

      request.onsuccess = (event) => {
        resolve(event.target.result);
      };

      request.onerror = (event) => {
        console.error("Failed to get all images:", event);
        reject(event);
      };
    });
  });
};
