<?php
require_once __DIR__ . '/../core/db.php';

/**
 * Create a new topic (post)
 */
function topic_create($user_id, $title, $content) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            INSERT INTO posts (author_id, title, content, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        return $stmt->execute([$user_id, $title, $content]);
    } catch (PDOException $e) {
        error_log("Error creating topic: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch all topics with author information
 */
function topic_fetch_all($limit = 50, $offset = 0) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.title,
                p.content,
                p.created_at,
                p.updated_at,
                p.author_id,
                u.username AS author_username,
                u.first_name AS author_first_name,
                u.last_name AS author_last_name,
                u.avatar_blob AS author_avatar,
                (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) AS comments_count,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS likes_count
            FROM posts p
            JOIN users u ON p.author_id = u.id
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching topics: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch a single topic by ID
 */
function topic_fetch_by_id($topic_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT 
                p.*,
                u.username AS author_username,
                u.first_name AS author_first_name,
                u.last_name AS author_last_name,
                u.avatar_blob AS author_avatar,
                (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) AS comments_count,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS likes_count
            FROM posts p
            JOIN users u ON p.author_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$topic_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching topic: " . $e->getMessage());
        return null;
    }
}

/**
 * Update a topic
 */
function topic_update($topic_id, $title, $content) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            UPDATE posts 
            SET title = ?, content = ?, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$title, $content, $topic_id]);
    } catch (PDOException $e) {
        error_log("Error updating topic: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a topic
 */
function topic_delete($topic_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$topic_id]);
    } catch (PDOException $e) {
        error_log("Error deleting topic: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a like to a topic
 */
function topic_add_like($topic_id, $user_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            INSERT INTO post_likes (post_id, user_id, created_at)
            VALUES (?, ?, NOW())
        ");
        return $stmt->execute([$topic_id, $user_id]);
    } catch (PDOException $e) {
        // Duplicate entry is OK (user already liked)
        return false;
    }
}

/**
 * Remove a like from a topic
 */
function topic_remove_like($topic_id, $user_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            DELETE FROM post_likes 
            WHERE post_id = ? AND user_id = ?
        ");
        return $stmt->execute([$topic_id, $user_id]);
    } catch (PDOException $e) {
        error_log("Error removing like: " . $e->getMessage());
        return false;
    }
}