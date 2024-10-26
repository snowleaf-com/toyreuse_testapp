// pIdManager.js
import { clearAllImagePaths } from "./dbHandler.js";

export const managePId = async () => {
  if (pId) {  // p_idがセットされている場合のみ実行
    const savedPId = localStorage.getItem('savedPId');  // localStorageから以前のp_idを取得

    if (savedPId !== pId) {
      try {
        await clearAllImagePaths();  // p_idが異なる場合、IndexedDBをクリア
        localStorage.setItem('savedPId', pId);  // 現在のp_idを保存
      } catch (error) {
        console.error("IndexedDBのクリアに失敗しました", error);
      }
    }
    
    // savedPIdが存在しない場合は、currentPIdを保存
    if (!savedPId) {
      localStorage.setItem('savedPId', pId);
    }
  }
};
