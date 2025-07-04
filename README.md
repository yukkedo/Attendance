# 勤怠管理アプリ

## 環境構築
### Dockerビルド
1.  git clone git@github.com:coachtech-material/laravel-docker-template.git
2. docker-compose up -d --build

### Laravel環境構築
1. docker-compose exec php bash
2. composer install
3. .env.example ファイルから .envを作成し、環境変数を変更
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed

### メール認証
* 仕様技術 : mailhog  
    image: mailhog/mailhog  
    ports:
      - "1025:1025"  
      - "8025:8025"

### PHPUnitテスト
// データベースの作成
1. docker-compose exec mysql bash
2. mysql -u root -p
   > password: root
3. CREATE DATABASE demo_test;

// テスト用　.envファイルの作成
1. docker-compose exec php bash
2. cp .env .env.testing
3. php artisan key:generate --env=testing
4. php artisan config:clear
5. php artisan migrate --env=testing

### テストアカウント
#### 管理者ユーザー
    name : 管理者 花子
    email : admin@gmail.com
    password : admin12345

#### 一般ユーザー
* 一般ユーザー1
    name : 山田 太郎
    email : user1@example.com
    password : user12345

* 一般ユーザー2
    name : 佐藤 花子
    email : user2@example.com
    password : user12345

* 一般ユーザー3
    name : 鈴木 次郎
    email : user3@example.com
    password : user12345

* 一般ユーザー4
    name : 高橋 恵
    email : user4@example.com
    password : user12345

* 一般ユーザー5
    name : 田中 一郎
    email : user5@example.com
    password : user12345

## 使用技術（実行環境）
* PHP : 7.4.9
* Laravel : 8.83.8
* MySQL : 8.0.26
* nginx : 1.21.1

## ER図


## URL
* 開発環境 : http://localhost/
* phpMyAdmin : http://localhost:8080/
* mailhog : http://localhost:8025 