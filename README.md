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
| 番号 | 名前       | Email               | Password   |
|:--:|-----------|---------------------|-----------|
| 1 | 山田 太郎 | user1@example.com   | user12345 |
| 2 | 佐藤 花子 | user2@example.com   | user12345 |
| 3 | 鈴木 次郎 | user3@example.com   | user12345 |
| 4 | 高橋 恵   | user4@example.com   | user12345 |
| 5 | 田中 一郎 | user5@example.com   | user12345 |

## 使用技術（実行環境）
* PHP : 7.4.9
* Laravel : 8.83.8
* MySQL : 8.0.26
* nginx : 1.21.1

## ER図
<img width="498" alt="Image" src="https://github.com/user-attachments/assets/8d3ad193-f6f5-4fdd-b6d3-a4d2a513361d" />

## URL
* 開発環境 : http://localhost/
* phpMyAdmin : http://localhost:8080/
* mailhog : http://localhost:8025 
