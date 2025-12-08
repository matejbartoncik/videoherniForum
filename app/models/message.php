<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/crypto.php';

/**
 * Send a message
 */
function message_send($sender_id, $recipient_id, $subject, $body) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, recipient_id, subject, body, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$sender_id, $recipient_id, encryptData($subject), encryptData($body)]);
    } catch (PDOException $e) {
        error_log("Error sending message: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch inbox messages for a user
 */
function message_fetch_inbox($user_id, $limit = 50, $offset = 0) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT 
                m.id,
                m.sender_id,
                m.recipient_id,
                m.subject,
                m.body,
                m.created_at,
                m.read_at,
                u.username AS sender_username,
                u.first_name AS sender_first_name,
                u.last_name AS sender_last_name,
                u.avatar_blob AS sender_avatar
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.recipient_id = ?
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching inbox: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch sent messages for a user
 */
function message_fetch_sent($user_id, $limit = 50, $offset = 0) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT 
                m.id,
                m.sender_id,
                m.recipient_id,
                m.subject,
                m.body,
                m.created_at,
                m.read_at,
                u.username AS recipient_username,
                u.first_name AS recipient_first_name,
                u.last_name AS recipient_last_name,
                u.avatar_blob AS recipient_avatar
            FROM messages m
            JOIN users u ON m.recipient_id = u.id
            WHERE m.sender_id = ?
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching sent messages: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch a single message by ID
 */
function message_fetch_by_id($message_id, $user_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT 
                m.*,
                sender.username AS sender_username,
                sender.first_name AS sender_first_name,
                sender.last_name AS sender_last_name,
                sender.avatar_blob AS sender_avatar,
                recipient.username AS recipient_username,
                recipient.first_name AS recipient_first_name,
                recipient.last_name AS recipient_last_name,
                recipient.avatar_blob AS recipient_avatar
            FROM messages m
            JOIN users sender ON m.sender_id = sender.id
            JOIN users recipient ON m.recipient_id = recipient.id
            WHERE m.id = ? AND (m.sender_id = ? OR m.recipient_id = ?)
        ");
        $stmt->execute([$message_id, $user_id, $user_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching message: " . $e->getMessage());
        return null;
    }
}

/**
 * Mark a message as read
 */
function message_mark_as_read($message_id, $user_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET read_at = NOW()
            WHERE id = ? AND recipient_id = ? AND read_at IS NULL
        ");
        return $stmt->execute([$message_id, $user_id]);
    } catch (PDOException $e) {
        error_log("Error marking message as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a message
 */
function message_delete($message_id, $user_id) {
    try {
        $pdo = get_db_connection();
        // Only allow deletion if user is sender or recipient
        $stmt = $pdo->prepare("
            DELETE FROM messages 
            WHERE id = ? AND (sender_id = ? OR recipient_id = ?)
        ");
        return $stmt->execute([$message_id, $user_id, $user_id]);
    } catch (PDOException $e) {
        error_log("Error deleting message: " . $e->getMessage());
        return false;
    }
}

/**
 * Get count of unread messages
 */
function message_count_unread($user_id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM messages
            WHERE recipient_id = ? AND read_at IS NULL
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result ? (int)$result['count'] : 0;
    } catch (PDOException $e) {
        error_log("Error counting unread messages: " . $e->getMessage());
        return 0;
    }
}

/**
 * Fetch all users for recipient selection
 */
function message_fetch_all_users($exclude_user_id = null) {
    try {
        $pdo = get_db_connection();
        if ($exclude_user_id) {
            $stmt = $pdo->prepare("
                SELECT id, username, first_name, last_name, avatar_blob
                FROM users
                WHERE id != ?
                ORDER BY username
            ");
            $stmt->execute([$exclude_user_id]);
        } else {
            $stmt = $pdo->query("
                SELECT id, username, first_name, last_name, avatar_blob
                FROM users
                ORDER BY username
            ");
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching users: " . $e->getMessage());
        return [];
    }
}