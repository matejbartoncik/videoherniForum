<?php
require_once __DIR__ . '/../core/db.php';

/**
 * Add a comment to a post
 */
function comment_create($post_id, $author_id, $body) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO post_comments (post_id, author_id, body, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$post_id, $author_id, $body]);
    } catch (PDOException $e) {
        error_log("Error creating comment: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch comments for a post
 */
function comment_fetch_by_post($post_id, $limit = 100, $offset = 0) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.post_id,
                c.body,
                c.created_at,
                c.author_id,
                u.username AS author_username,
                u.first_name AS author_first_name,
                u.last_name AS author_last_name,
                u.avatar_blob AS author_avatar
            FROM post_comments c
            JOIN users u ON c.author_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$post_id, $limit, $offset]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching comments: " . $e->getMessage());
        return [];
    }
}

/**
 * Delete a comment
 */
function comment_delete($comment_id, $user_id) {
    try {
        $pdo = db();
        // Only allow deletion if user is author
        $stmt = $pdo->prepare("
            DELETE FROM post_comments 
            WHERE id = ? AND author_id = ?
        ");
        return $stmt->execute([$comment_id, $user_id]);
    } catch (PDOException $e) {
        error_log("Error deleting comment: " . $e->getMessage());
        return false;
    }
}

/**
 * Get comment count for a post
 */
function comment_count_by_post($post_id) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM post_comments
            WHERE post_id = ?
        ");
        $stmt->execute([$post_id]);
        $result = $stmt->fetch();
        return $result ? (int)$result['count'] : 0;
    } catch (PDOException $e) {
        error_log("Error counting comments: " . $e->getMessage());
        return 0;
    }
}