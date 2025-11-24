<?php
require_once __DIR__ . '/../core/db.php';

/**
 * Toggle like on a post (add if not exists, remove if exists)
 */
function like_toggle($post_id, $user_id) {
    try {
        $pdo = db();

        // Check if like already exists
        if (like_exists($post_id, $user_id)) {
            return like_remove($post_id, $user_id);
        } else {
            return like_add($post_id, $user_id);
        }
    } catch (PDOException $e) {
        error_log("Error toggling like: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a like to a post
 */
function like_add($post_id, $user_id) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO post_likes (post_id, user_id, created_at)
            VALUES (?, ?, NOW())
        ");
        return $stmt->execute([$post_id, $user_id]);
    } catch (PDOException $e) {
        // Duplicate entry is OK
        return false;
    }
}

/**
 * Remove a like from a post
 */
function like_remove($post_id, $user_id) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            DELETE FROM post_likes 
            WHERE post_id = ? AND user_id = ?
        ");
        return $stmt->execute([$post_id, $user_id]);
    } catch (PDOException $e) {
        error_log("Error removing like: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if user has liked a post
 */
function like_exists($post_id, $user_id) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM post_likes
            WHERE post_id = ? AND user_id = ?
        ");
        $stmt->execute([$post_id, $user_id]);
        $result = $stmt->fetch();
        return $result && $result['count'] > 0;
    } catch (PDOException $e) {
        error_log("Error checking like: " . $e->getMessage());
        return false;
    }
}

/**
 * Get like count for a post
 */
function like_count_by_post($post_id) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM post_likes
            WHERE post_id = ?
        ");
        $stmt->execute([$post_id]);
        $result = $stmt->fetch();
        return $result ? (int)$result['count'] : 0;
    } catch (PDOException $e) {
        error_log("Error counting likes: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get users who liked a post
 */
function like_fetch_users($post_id, $limit = 100) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                u.username,
                u.first_name,
                u.last_name,
                u.avatar_blob
            FROM post_likes pl
            JOIN users u ON pl.user_id = u.id
            WHERE pl.post_id = ?
            ORDER BY pl.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$post_id, $limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching likes: " . $e->getMessage());
        return [];
    }
}