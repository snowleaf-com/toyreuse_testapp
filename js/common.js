// common.js
import { clearAllImagePaths, initDB, storeName } from './dbHandler.js';

const checkIfDataExists = async () => {
    const db = await initDB();
    const transaction = db.transaction(storeName, "readonly");
    const store = transaction.objectStore(storeName);
    
    return new Promise((resolve) => {
        const getAllRequest = store.getAll();
        
        getAllRequest.onsuccess = () => {
            resolve(getAllRequest.result.length > 0); // データが存在する場合はtrueを返す
        };

        getAllRequest.onerror = () => {
            console.error("Failed to retrieve data:", getAllRequest.error);
            resolve(false); // エラーが発生した場合はfalseを返す
        };
    });
};

document.addEventListener("DOMContentLoaded", async () => {
    const currentPage = window.location.pathname;

    // /akachan/mypage/_prod_edit.php以外のページにアクセスした場合
    if (currentPage !== '/akachan/mypage/_prod_edit.php') {
        const dataExists = await checkIfDataExists(); // データの存在をチェック

        if (dataExists) {
            // IndexedDBをクリアする処理を呼び出す
            clearAllImagePaths();
        }
    }
});
