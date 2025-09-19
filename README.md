## セットアップ手順

1. **リポジトリをクローン**

   ```bash
   git clone <リポジトリURL>
   ```

2. **dockerディレクトリに移動**

   ```bash
   cd docker
   ```

3. **データベース名の設定**
   `docker-compose.yml` 内の `db` サービスにある `MYSQL_DATABASE` の値を、各自任意のデータベース名に設定してください。

   例:

   ```yaml
   environment:
     MYSQL_ROOT_PASSWORD: root
     MYSQL_DATABASE: dev_platform_db # 任意のデータベース名を指定
   ```

4. **Dockerイメージのビルド**

   ```bash
   docker-compose build
   ```

5. **コンテナの起動**
   ```bash
   docker-compose up -d
   ```
6. **ブラウザからlocalhostにアクセス**

## PHP周りのバージョン

- **PHP**: 7.3
- **FuelPHP**: 1.8

## ログについて

- **アクセスログ**: Dockerのコンテナのログ
- **FuelPHPのエラーログ**: /var/www/html/intern_kadai/fuel/app/logs/
  - 年月日ごとにログが管理されている
  - tail -f {見たいログファイル}でログを出力

## MySQLコンテナ設定

このプロジェクトには、MySQLを使用するDBコンテナが含まれています。設定は以下の通りです。

- **MySQLバージョン**: 8.0
- **ポート**: `3306`
- **環境変数**:
  - `MYSQL_ROOT_PASSWORD`: root
  - `MYSQL_DATABASE`: dev_platform_db

### アクセス情報

- **ホスト**: `localhost`
- **ポート**: `3306`
- **ユーザー名**: `root`
- **パスワード**: `root`
- **データベース名**: dev_platform_db
