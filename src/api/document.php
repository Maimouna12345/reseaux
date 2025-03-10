<?php
require_once '../../db/db.php';

/**
 * Get all documents for a specific client
 * 
 * @param int $clientId The ID of the client
 * @return array Array of documents
 */
function getDocumentsByClient($clientId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT d.*, e.name as employee_name 
            FROM documents d
            LEFT JOIN employees e ON d.employee_id = e.id
            WHERE d.client_id = ?
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get documents by client error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a document by its ID
 * 
 * @param int $id The document ID
 * @return array|false Document data or false if not found
 */
function getDocumentById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT d.*, e.name as employee_name, c.name as client_name
            FROM documents d
            LEFT JOIN employees e ON d.employee_id = e.id
            LEFT JOIN clients c ON d.client_id = c.id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get document by ID error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new document
 * 
 * @param string $name Document name
 * @param string $content Document content
 * @param int $clientId Client ID
 * @param int $employeeId Employee ID
 * @return int|false The new document ID or false on failure
 */
function createDocument($name, $content, $clientId, $employeeId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO documents (name, content, client_id, employee_id, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $content, $clientId, $employeeId]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Create document error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update document status (confirm or reject)
 * 
 * @param int $id Document ID
 * @param bool $isConfirmed Whether the document is confirmed
 * @return bool Success status
 */
function updateDocumentStatus($id, $isConfirmed) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            UPDATE documents 
            SET isConfirmed = ?, confirmed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$isConfirmed, $id]);
        return true;
    } catch (PDOException $e) {
        error_log("Update document status error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a document
 * 
 * @param int $id Document ID
 * @return bool Success status
 */
function deleteDocument($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    } catch (PDOException $e) {
        error_log("Delete document error: " . $e->getMessage());
        return false;
    }
}
?>