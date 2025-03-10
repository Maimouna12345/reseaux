<?php
require_once '../../db/db.php';


function getTotalDocuments() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM documents");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting total documents: " . $e->getMessage());
        return 0;
    }
}


function getConfirmedDocuments() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM documents WHERE isConfirmed = TRUE");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting confirmed documents: " . $e->getMessage());
        return 0;
    }
}


function getPendingDocuments() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM documents WHERE isConfirmed = FALSE");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting pending documents: " . $e->getMessage());
        return 0;
    }
}


function getDocumentsSentByEmployee($employeeId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE sendby = ?");
        $stmt->execute([$employeeId]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting documents sent by employee: " . $e->getMessage());
        return 0;
    }
}

function getDocumentConfirmationRate() {
    $total = getTotalDocuments();
    if ($total == 0) return 0;
    
    $confirmed = getConfirmedDocuments();
    return round(($confirmed / $total) * 100);
}

function getDocumentsByClient($clientId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE receivedby = ?");
        $stmt->execute([$clientId]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting documents by client: " . $e->getMessage());
        return 0;
    }
}


function getRecentDocuments($limit = 5) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT d.*, e.name as employee_name, c.name as client_name 
            FROM documents d
            JOIN employees e ON d.sendby = e.id
            JOIN clients c ON d.receivedby = c.id
            ORDER BY d.id DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting recent documents: " . $e->getMessage());
        return [];
    }
}
?>