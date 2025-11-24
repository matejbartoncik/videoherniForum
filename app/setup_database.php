<?php
/**
 * Database Setup Script - Complete version for Docker MySQL
 */

$host = '127.0.0.1';
$dbname = 'videoherniforum';
$user = 'phpuser';
$pass = 'phppassword';

echo "<!DOCTYPE html><html><head><title>Database Setup</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;}.error{color:red;}.info{color:blue;}";
echo "pre{background:#fff;padding:10px;border:1px solid #ddd;}</style></head><body>";
echo "<h1>🗄️ Database Setup</h1>";

if (!extension_loaded('pdo_mysql')) {
    echo "<p class='error'>❌ PDO MySQL extension is not installed!</p>";
    exit;
}

try {
    echo "<p class='info'>📡 Connecting to MySQL server...</p>";
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✅ Connected to MySQL server</p>";

    echo "<p class='info'>🏗️ Creating database '$dbname'...</p>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p class='success'>✅ Database '$dbname' created/exists</p>";

    $pdo->exec("USE `$dbname`");
    echo "<p class='info'>📂 Switched to database '$dbname'</p>";

    echo "<p class='info'>📋 Creating tables...</p>";

    // Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name    VARCHAR(100) NOT NULL,
            last_name     VARCHAR(100) NOT NULL,
            email         VARCHAR(255) NOT NULL,
            username      VARCHAR(50)  NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role          ENUM('user','admin') NOT NULL DEFAULT 'user',
            phone         VARCHAR(30),
            avatar_path   VARCHAR(255),
            created_at    DATETIME NOT NULL,
            updated_at    DATETIME NOT NULL,
            UNIQUE KEY uq_users_email    (email),
            UNIQUE KEY uq_users_username (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p class='success'>✅ Table 'users' created</p>";

    // Posts table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            author_id  BIGINT UNSIGNED NOT NULL,
            title      VARCHAR(200) NOT NULL,
            content    TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            KEY idx_posts_author (author_id),
            CONSTRAINT fk_posts_author
                FOREIGN KEY (author_id) REFERENCES users(id)
                ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p class='success'>✅ Table 'posts' created</p>";

    // Post comments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS post_comments (
            id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id    BIGINT UNSIGNED NOT NULL,
            author_id  BIGINT UNSIGNED NOT NULL,
            body       TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            KEY idx_post_comments_post   (post_id),
            KEY idx_post_comments_author (author_id),
            CONSTRAINT fk_post_comments_post
                FOREIGN KEY (post_id) REFERENCES posts(id)
                ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT fk_post_comments_author
                FOREIGN KEY (author_id) REFERENCES users(id)
                ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p class='success'>✅ Table 'post_comments' created</p>";

    // Post likes table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS post_likes (
            post_id    BIGINT UNSIGNED NOT NULL,
            user_id    BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (post_id, user_id),
            KEY idx_post_likes_user (user_id),
            CONSTRAINT fk_post_likes_post
                FOREIGN KEY (post_id) REFERENCES posts(id)
                ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT fk_post_likes_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p class='success'>✅ Table 'post_likes' created</p>";

    // Messages table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            sender_id    BIGINT UNSIGNED NOT NULL,
            recipient_id BIGINT UNSIGNED NOT NULL,
            subject      VARCHAR(150),
            body         TEXT NOT NULL,
            created_at   DATETIME NOT NULL,
            read_at      DATETIME NULL,
            KEY idx_messages_sender    (sender_id),
            KEY idx_messages_recipient (recipient_id),
            CONSTRAINT fk_messages_sender
                FOREIGN KEY (sender_id) REFERENCES users(id)
                ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT fk_messages_recipient
                FOREIGN KEY (recipient_id) REFERENCES users(id)
                ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p class='success'>✅ Table 'messages' created</p>";

    // Insert demo users
    echo "<p class='info'>👤 Creating demo users...</p>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch()['count'];

    if ($count == 0) {
        $passwordHash = password_hash('password', PASSWORD_BCRYPT);

        $pdo->exec("
            INSERT INTO users (first_name, last_name, email, username, password_hash, role, created_at, updated_at)
            VALUES 
            ('Admin', 'User', 'admin@example.com', 'admin', '$passwordHash', 'admin', NOW(), NOW()),
            ('Jan', 'Novák', 'jan@example.com', 'jan', '$passwordHash', 'user', NOW(), NOW()),
            ('Petra', 'Svobodová', 'petra@example.com', 'petra', '$passwordHash', 'user', NOW(), NOW()),
            ('Martin', 'Dvořák', 'martin@example.com', 'martin', '$passwordHash', 'user', NOW(), NOW())
        ");
        echo "<p class='success'>✅ Created 4 demo users:</p>";
        echo "<pre>";
        echo "Username: admin   | Password: password | Role: admin\n";
        echo "Username: jan     | Password: password | Role: user\n";
        echo "Username: petra   | Password: password | Role: user\n";
        echo "Username: martin  | Password: password | Role: user\n";
        echo "</pre>";
    } else {
        echo "<p class='info'>ℹ️ Users already exist (skipped)</p>";
    }

    // Insert sample posts
    echo "<p class='info'>📝 Creating sample posts...</p>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts");
    $count = $stmt->fetch()['count'];

    if ($count == 0) {
        $pdo->exec("
            INSERT INTO posts (author_id, title, content, created_at, updated_at)
            VALUES 
            (1, 'Vítejte na fóru!', 'Toto je první příspěvek na našem novém videoherním fóru. Užijte si diskuze o vašich oblíbených hrách!', NOW(), NOW()),
            (2, 'Jaká je vaše oblíbená hra roku 2025?', 'Zajímalo by mě, jaké hry se vám letos nejvíc líbily. Sdílejte své tipy!', NOW(), NOW()),
            (3, 'Turnaj v Counter-Strike', 'Organizujeme komunitní turnaj. Přihlašte se na Discordu!', NOW(), NOW())
        ");
        echo "<p class='success'>✅ Created 3 sample posts</p>";
    } else {
        echo "<p class='info'>ℹ️ Posts already exist (skipped)</p>";
    }

    // Insert sample messages
    echo "<p class='info'>✉️ Creating sample messages...</p>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM messages");
    $count = $stmt->fetch()['count'];

    if ($count == 0) {
        $pdo->exec("
            INSERT INTO messages (sender_id, recipient_id, subject, body, created_at)
            VALUES 
            (2, 1, 'Vítám', 'Ahoj admin! Děkuji za vytvoření tohoto fóra.', NOW()),
            (3, 1, 'Dotaz', 'Kdy bude další turnaj?', NOW()),
            (4, 2, 'Re: Oblíbená hra', 'Souhlasím s tebou ohledně té hry!', NOW())
        ");
        echo "<p class='success'>✅ Created 3 sample messages</p>";
    } else {
        echo "<p class='info'>ℹ️ Messages already exist (skipped)</p>";
    }

    echo "<hr>";
    echo "<h2 class='success'>🎉 Database Setup Complete!</h2>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Delete or rename this setup file for security</li>";
    echo "<li>Go to: <a href='index.php?page=login'>Login Page</a></li>";
    echo "<li>Use credentials: <code>admin</code> / <code>password</code></li>";
    echo "</ol>";

} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Make sure Docker MySQL is running: <code>docker ps</code></p>";
}

echo "</body></html>";