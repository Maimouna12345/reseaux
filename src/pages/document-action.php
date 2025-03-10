<?php
    // Start session to access client data
    session_start();
    
    // Check if client is logged in, redirect to login page if not
    if (!isset($_SESSION['client'])) {
        header("Location: login-client.php");
        exit();
    }
    
    // Get client data from session
    $client = $_SESSION['client'];
    
    require_once '../api/documents.php';
    
    // Check if document ID and action are provided
    if (!isset($_GET['id']) || !isset($_GET['action'])) {
        header("Location: client-dashboard.php");
        exit();
    }
    
    $documentId = $_GET['id'];
    $action = $_GET['action'];
    $documentResult = getDocumentById($documentId);
    
    // Check if document exists and belongs to this client
    if (!$documentResult['success'] || $documentResult['data']['receivedby'] != $client['id']) {
        header("Location: client-dashboard.php?message=Document non trouvé ou accès non autorisé&type=error");
        exit();
    }
    
    $document = $documentResult['data'];
    
    // Process the action
    if ($action === 'confirm') {
        // Confirm the document
        if (confirmDocument($documentId)) {
            // Redirect with success message
            header("Location: client-dashboard.php?message=Document confirmé avec succès&type=success");
            exit();
        } else {
            // Redirect with error message
            header("Location: client-dashboard.php?message=Erreur lors de la confirmation du document&type=error");
            exit();
        }
    } elseif ($action === 'reject') {
        // Reject the document
        if (rejectDocument($documentId)) {
            // Redirect with success message
            header("Location: client-dashboard.php?message=Document rejeté avec succès&type=success");
            exit();
        } else {
            // Redirect with error message
            header("Location: client-dashboard.php?message=Erreur lors du rejet du document&type=error");
            exit();
        }
    } else {
        // Invalid action
        header("Location: client-dashboard.php?message=Action non valide&type=error");
        exit();
    }
?>