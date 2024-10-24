// dbHandler.js
export const storeName = "imageStore";
const dbName = "imageDB";

// IndexedDBを初期化する関数
export const initDB = () => {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(dbName, 1);
    
    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains(storeName)) {
        const objectStore = db.createObjectStore(storeName, { keyPath: 'id', autoIncrement: true });
        
        // filePathインデックスを作成
        objectStore.createIndex("filePath", "filePath", { unique: false });
      }
    };

    request.onsuccess = (event) => {
      const db = event.target.result;
      resolve(db);
    };
    request.onerror = (event) => {
      console.error("IndexedDB error:", event);
      reject(event);
    };
  });
};
// 画像パスを保存する関数
export const saveImagePath = (filePath, file) => {
  return new Promise((resolve, reject) => { // Promiseを返す
    initDB().then((db) => {
      const transaction = db.transaction(storeName, "readwrite");
      const store = transaction.objectStore(storeName);

      const request = store.add({ filePath, file }); // 画像パスを追加

      request.onsuccess = (event) => {
        const id = event.target.result; // 生成されたIDを取得
        console.log("File path added successfully with ID:", id);
        resolve(id); // IDを返す
      };

      transaction.onerror = (event) => {
        console.error("Failed to save file path:", event);
        reject(event); // エラーを返す
      };
    }).catch(reject); // initDBのエラーもキャッチ
  });
};


// 画像パスを削除する関数
export const deleteImagePath = (id) => {
  return new Promise((resolve, reject) => { // Promiseを返す
    initDB().then((db) => {
      const transaction = db.transaction(storeName, "readwrite");
      const store = transaction.objectStore(storeName);

      const request = store.delete(id);

      request.onsuccess = () => {
        console.log("File path deleted successfully with ID:", id);
        resolve();
      };

      request.onerror = (event) => {
        console.error("Failed to delete file path:", event);
        reject(event);
      };
    });
  });
};

// 指定したオブジェクトストアから全データを削除する関数
export const clearAllImagePaths = async () => {
  try {
    const db = await initDB(); // データベースを初期化
    const transaction = db.transaction(storeName, "readwrite");
    const store = transaction.objectStore(storeName);
    
    const clearRequest = store.clear(); // オブジェクトストア内のすべてのデータを削除

    clearRequest.onsuccess = () => {
      console.log("All data deleted successfully");
    };

    clearRequest.onerror = (event) => {
      console.error("Failed to delete all data:", event);
    };

    // トランザクションが完了したときの処理
    transaction.oncomplete = () => {
      console.log("Transaction completed successfully");
    };

    transaction.onerror = (event) => {
      console.error("Transaction failed:", event);
    };
  } catch (error) {
    console.error("Error during deletion:", error);
  }
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