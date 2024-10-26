# toyreuse_app

# データベース構造
このプロジェクトのデータベース構造について説明します。
  
## テーブル一覧
| テーブル名          | 説明                                               |
|---------------------|----------------------------------------------------|
| category            | 商品カテゴリー情報を格納するテーブル               |
| community           | コミュニティ情報を格納するテーブル                 |
| c_join              | コミュニティ参加情報を格納するテーブル             |
| c_message           | コミュニティ内のメッセージ情報を格納するテーブル   |
| members             | ユーザー情報を格納するテーブル                     |
| pre_members         | 仮登録ユーザー情報を格納するテーブル               |
| pre_passmail_edit   | パスワード変更メールの仮登録情報を格納するテーブル |
| pre_pass_edit       | パスワード変更の仮登録情報を格納するテーブル       |
| products            | 商品情報を格納するテーブル                         |

  
##テーブル詳細
**category テーブル**
| カラム名     | データ型  | 説明                    |
|--------------|-----------|-------------------------|
| id           | INT       | カテゴリーID（主キー）  |
| name         | VARCHAR   | カテゴリー名            |
  
**community テーブル**
| カラム名     | データ型  | 説明                      |
|--------------|-----------|---------------------------|
| id           | INT       | コミュニティID（主キー）  |
| title        | VARCHAR   | コミュニティタイトル      |
| comment      | TEXT      | コミュニティの説明        |
| made_by_id   | INT       | コミュニティ作成者のID    |
| create_date  | DATETIME  | コミュニティ作成日時      |
| delete_flg   | TINYINT   | 削除フラグ                |
  
**c_join テーブル**
| カラム名     | データ型 | 説明                         |
|--------------|-----------|-----------------------------|
| id           | INT       | 参加ID（主キー）            |
| community_id | INT       | コミュニティID（外部キー）  |
| user_id      | INT       | ユーザーID（外部キー）      |
| join_date    | DATETIME  | 参加日時                    |
  
**c_message テーブル**
| カラム名     | データ型  | 説明                         |
|--------------|-----------|------------------------------|
| id           | INT       | メッセージID（主キー）       |
| community_id | INT       | コミュニティID（外部キー）   |
| from_user    | INT       | 送信者ユーザーID             |
| message      | TEXT      | メッセージ内容               |
| send_date    | DATETIME  | 送信日時                     |
| create_date  | DATETIME  | 作成日時                     |
| delete_flg   | TINYINT   | 削除フラグ                   |
  
**members テーブル**
| カラム名     | データ型  | 説明                    |
|--------------|-----------|-------------------------|
| id           | INT       | ユーザーID（主キー）    |
| mail         | VARCHAR   | メールアドレス          |
| nickname     | VARCHAR   | ニックネーム            |
| username     | VARCHAR   | ユーザー名              |
| userkananame | VARCHAR   | ユーザーのカナ名        |
| password     | VARCHAR   | パスワード              |
| bornyear     | INT       | 生年                    |
| bornmonth    | INT       | 生月                    |
| bornday      | INT       | 生日                    |
| zip          | VARCHAR   | 郵便番号                |
| address      | TEXT      | 住所                    |
  
**pre_members テーブル**
| カラム名        | データ型    | 説明                     |
|-----------------|-------------|--------------------------|
| id              | INT         | 仮登録ID（主キー）       |
| pre_mail        | VARCHAR     | 仮メールアドレス         |
| urltoken        | VARCHAR     | 仮登録用URLトークン      |
| flg             | TINYINT     | フラグ                   |
| date            | DATETIME    | 登録日時                 |
| create_date     | DATETIME    | 作成日時                 |
| update_date     | DATETIME    | 更新日時                 |
  
**pre_passmail_edit テーブル**
| カラム名        | データ型    | 説明                         |
|-----------------|-------------|------------------------------|
| id              | INT         | メール編集仮登録ID（主キー） |
| urltoken        | VARCHAR     | メール編集用URLトークン      |
| userid          | INT         | ユーザーID                   |
| mail            | VARCHAR     | メールアドレス               |
| flg             | TINYINT     | フラグ                       |
| date            | DATETIME    | 登録日時                     |
| create_date     | DATETIME    | 作成日時                     |
| update_date     | DATETIME    | 更新日時                     |
  
**pre_pass_edit テーブル**
| カラム名        | データ型    | 説明                             |
|-----------------|-------------|----------------------------------|
| id              | INT         | パスワード変更仮登録ID（主キー） |
| pre_mail        | VARCHAR     | 仮メールアドレス                 |
| urltoken        | VARCHAR     | パスワード変更用URLトークン      |
| flg             | TINYINT     | フラグ                           |
| date            | DATETIME    | 登録日時                         |
| create_date     | DATETIME    | 作成日時                         |
| update_date     | DATETIME    | 更新日時                         |
  
**products テーブル**
| カラム名        | データ型    | 説明                      |
|-----------------|-------------|---------------------------|
| id              | INT         | 商品ID（主キー）          |
| name            | VARCHAR     | 商品名                    |
| category_id     | INT         | カテゴリーID（外部キー）  |
| comment         | TEXT        | 商品の説明                |
| price           | INT         | 価格                      |
| pic1            | VARCHAR     | 商品画像1                 |
| pic2            | VARCHAR     | 商品画像2                 |
| pic3            | VARCHAR     | 商品画像3                 |
| user_id         | INT         | 出品者ID（外部キー）      |
| delete_flg      | TINYINT     | 削除フラグ                |
| bought_flg      | TINYINT     | 購入フラグ                |
| create_date     | DATETIME    | 出品日時                  |
| update_date     | DATETIME    | 更新日時                  |

  
**リレーションシップ**  
* categoryテーブルとproductsテーブルは1対多のリレーションシップで、1つのカテゴリーに複数の商品が属します。  
* membersテーブルとcommunityテーブルは1対多のリレーションシップで、1人のユーザーが複数のコミュニティを作成できます。  
* communityテーブルとc_messageテーブルも1対多のリレーションシップで、1つのコミュニティに複数のメッセージが存在します。  
* productsテーブルとmembersテーブルも1対多のリレーションシップで、1人のユーザーが複数の商品を出品できます。  