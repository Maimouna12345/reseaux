<?php
require_once '../../db/db.php';

// Configuration FTP
$ftpServer = 'localhost:14147'; 
$ftpUsername = 'root';
$ftpPassword = 'root';
$ftpDirectory = '/documents/'; // Répertoire où stocker les fichiers  

// Fonction pour se connecter au serveur FTP
function connectFTP() {
    global $ftpServer, $ftpUsername, $ftpPassword;
    $ftpConn = ftp_connect($ftpServer);
    if (!$ftpConn) {
        return ['success' => false, 'message' => 'Impossible de se connecter au serveur FTP'];
    }
    
    $login = ftp_login($ftpConn, $ftpUsername, $ftpPassword);
    if (!$login) {
        ftp_close($ftpConn);
        return ['success' => false, 'message' => 'Identifiants FTP incorrects'];
    }
    
    ftp_pasv($ftpConn, true); // Mode passif pour éviter les problèmes de pare-feu
    return ['success' => true, 'connection' => $ftpConn];
}

// Récupérer tous les documents
function getAllDocuments() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT d.*, e.name as employee_name, c.name as client_name 
                               FROM documents d 
                               JOIN employees e ON d.sendby = e.id 
                               JOIN clients c ON d.receivedby = c.id");
        $stmt->execute();
        return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Récupérer un document par ID
function getDocumentById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT d.*, e.name as employee_name, c.name as client_name 
                               FROM documents d 
                               JOIN employees e ON d.sendby = e.id 
                               JOIN clients c ON d.receivedby = c.id 
                               WHERE d.id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $document = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($document) {
            return ['success' => true, 'data' => $document];
        } else {
            return ['success' => false, 'message' => 'Document non trouvé'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Récupérer les documents envoyés par un employé spécifique
function getDocumentsByEmployee($employeeId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT d.*, e.name as employee_name, c.name as client_name 
                               FROM documents d 
                               JOIN employees e ON d.sendby = e.id 
                               JOIN clients c ON d.receivedby = c.id
                               WHERE d.sendby = :employeeId
                               ORDER BY d.id DESC");
        $stmt->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Récupérer les documents destinés à un client spécifique
function getDocumentsByClient($clientId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT d.*, e.name as employee_name, c.name as client_name 
                               FROM documents d 
                               JOIN employees e ON d.sendby = e.id 
                               JOIN clients c ON d.receivedby = c.id
                               WHERE d.receivedby = :clientId
                               ORDER BY d.id DESC");
        $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get documents by client error: " . $e->getMessage());
        return [];
    }
}

// Confirmer un document
function confirmDocument($documentId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE documents SET isConfirmed = TRUE WHERE id = :id");
        $stmt->bindParam(':id', $documentId, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        error_log("Confirm document error: " . $e->getMessage());
        return false;
    }
}

// Rejeter un document (dans ce cas, on pourrait simplement le supprimer ou le marquer comme rejeté)
function rejectDocument($documentId) {
    global $pdo;
    try {
        
        // Option 3: Utiliser un statut (nécessite une colonne 'status' dans la table)
        $stmt = $pdo->prepare("UPDATE documents SET isConfirmed = FALSE WHERE id = :id");
        $stmt->bindParam(':id', $documentId, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        error_log("Reject document error: " . $e->getMessage());
        return false;
    }
}

// Créer un nouveau document
function createDocument($name, $file, $sendby, $receivedby) {
    global $pdo, $ftpDirectory;
    
    try {
        // Vérifier si le fichier a été correctement téléchargé
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erreur lors du téléchargement du fichier'];
        }
        
        // Vérifier que le fichier est un PDF
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'pdf') {
            return ['success' => false, 'message' => 'Seuls les fichiers PDF sont acceptés'];
        }
        
        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if ($mimeType !== 'application/pdf') {
            return ['success' => false, 'message' => 'Le fichier doit être un PDF valide'];
        }
        
        // Générer un nom de fichier unique
        $uniqueFilename = uniqid() . '.pdf';
        $ftpFilePath = $ftpDirectory . $uniqueFilename;
        
        // Connexion FTP
        $ftpResult = connectFTP();
        if (!$ftpResult['success']) {
            return $ftpResult;
        }
        $ftpConn = $ftpResult['connection'];
        
        // Télécharger le fichier sur le serveur FTP
        if (!ftp_put($ftpConn, $ftpFilePath, $file['tmp_name'], FTP_BINARY)) {
            ftp_close($ftpConn);
            return ['success' => false, 'message' => 'Échec du téléchargement du fichier sur le serveur FTP'];
        }
        
        ftp_close($ftpConn);
        
        // Enregistrer les informations du document dans la base de données
        $stmt = $pdo->prepare("INSERT INTO documents (name, urlFile, sendby, receivedby) VALUES (:name, :urlFile, :sendby, :receivedby)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':urlFile', $uniqueFilename, PDO::PARAM_STR);
        $stmt->bindParam(':sendby', $sendby, PDO::PARAM_INT);
        $stmt->bindParam(':receivedby', $receivedby, PDO::PARAM_INT);
        $stmt->execute();
        
        $documentId = $pdo->lastInsertId();
        
        return ['success' => true, 'message' => 'Document créé avec succès', 'id' => $documentId];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Mettre à jour un document
function updateDocument($id, $name, $sendby, $receivedby, $isConfirmed, $file = null) {
    global $pdo, $ftpDirectory;
    
    try {
        // Récupérer les informations actuelles du document
        $stmt = $pdo->prepare("SELECT urlFile FROM documents WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $document = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$document) {
            return ['success' => false, 'message' => 'Document non trouvé'];
        }
        
        $urlFile = $document['urlFile'];
        
        // Si un nouveau fichier est fourni, le télécharger et mettre à jour l'URL
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            // Vérifier que le fichier est un PDF
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($fileExtension !== 'pdf') {
                return ['success' => false, 'message' => 'Seuls les fichiers PDF sont acceptés'];
            }
            
            // Vérifier le type MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if ($mimeType !== 'application/pdf') {
                return ['success' => false, 'message' => 'Le fichier doit être un PDF valide'];
            }
            
            // Connexion FTP
            $ftpResult = connectFTP();
            if (!$ftpResult['success']) {
                return $ftpResult;
            }
            $ftpConn = $ftpResult['connection'];
            
            // Supprimer l'ancien fichier
            @ftp_delete($ftpConn, $ftpDirectory . $urlFile);
            
            // Générer un nouveau nom de fichier
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueFilename = uniqid() . '.' . $fileExtension;
            $ftpFilePath = $ftpDirectory . $uniqueFilename;
            
            // Télécharger le nouveau fichier
            if (!ftp_put($ftpConn, $ftpFilePath, $file['tmp_name'], FTP_BINARY)) {
                ftp_close($ftpConn);
                return ['success' => false, 'message' => 'Échec du téléchargement du fichier sur le serveur FTP'];
            }
            
            ftp_close($ftpConn);
            $urlFile = $uniqueFilename;
        }
        
        // Mettre à jour les informations du document dans la base de données
        $stmt = $pdo->prepare("UPDATE documents SET name = :name, urlFile = :urlFile, sendby = :sendby, receivedby = :receivedby, isConfirmed = :isConfirmed WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':urlFile', $urlFile, PDO::PARAM_STR);
        $stmt->bindParam(':sendby', $sendby, PDO::PARAM_INT);
        $stmt->bindParam(':receivedby', $receivedby, PDO::PARAM_INT);
        $stmt->bindParam(':isConfirmed', $isConfirmed, PDO::PARAM_BOOL);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Document mis à jour avec succès'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Supprimer un document
function deleteDocument($id) {
    global $pdo, $ftpDirectory;
    
    try {
        // Récupérer l'URL du fichier avant de supprimer l'enregistrement
        $stmt = $pdo->prepare("SELECT urlFile FROM documents WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $document = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$document) {
            return ['success' => false, 'message' => 'Document non trouvé'];
        }
        
        // Connexion FTP
        $ftpResult = connectFTP();
        if (!$ftpResult['success']) {
            return $ftpResult;
        }
        $ftpConn = $ftpResult['connection'];
        
        // Supprimer le fichier du serveur FTP
        @ftp_delete($ftpConn, $ftpDirectory . $document['urlFile']);
        ftp_close($ftpConn);
        
        // Supprimer l'enregistrement de la base de données
        $stmt = $pdo->prepare("DELETE FROM documents WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Document supprimé avec succès'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Télécharger un document
function downloadDocument($id) {
    global $pdo, $ftpDirectory;
    
    try {
        // Récupérer les informations du document
        $stmt = $pdo->prepare("SELECT name, urlFile FROM documents WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $document = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$document) {
            return ['success' => false, 'message' => 'Document non trouvé'];
        }
        
        // Connexion FTP
        $ftpResult = connectFTP();
        if (!$ftpResult['success']) {
            return $ftpResult;
        }
        $ftpConn = $ftpResult['connection'];
        
        // Créer un fichier temporaire local
        $tempFile = tempnam(sys_get_temp_dir(), 'download_');
        
        // Télécharger le fichier depuis le serveur FTP
        if (!ftp_get($ftpConn, $tempFile, $ftpDirectory . $document['urlFile'], FTP_BINARY)) {
            ftp_close($ftpConn);
            unlink($tempFile);
            return ['success' => false, 'message' => 'Échec du téléchargement du fichier depuis le serveur FTP'];
        }
        
        ftp_close($ftpConn);
        
        // Simplify MIME type handling - we only have PDFs
        $contentType = 'application/pdf';
        
        // Envoyer le fichier au client
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $document['name'] . '.pdf"');
        header('Content-Length: ' . filesize($tempFile));
        
        readfile($tempFile);
        unlink($tempFile);
        exit;
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// IMPORTANT: N'exécute le code de traitement des requêtes API que si ce fichier est appelé directement
// et non lorsqu'il est inclus avec require/include
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    // Traitement des requêtes API
    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    header('Content-Type: application/json');

    switch ($method) {
        case 'GET':
            if ($action === 'download' && isset($_GET['id'])) {
                $result = downloadDocument($_GET['id']);
                if (!$result['success']) {
                    echo json_encode($result);
                }
            } elseif (isset($_GET['id'])) {
                echo json_encode(getDocumentById($_GET['id']));
            } else {
                echo json_encode(getAllDocuments());
            }
            break;
            
        case 'POST':
            // Gestion des méthodes PUT et DELETE via POST avec _method
            if (isset($_POST['_method'])) {
                if ($_POST['_method'] === 'PUT') {
                    if (!isset($_POST['id']) || !isset($_POST['name']) || !isset($_POST['sendby']) || !isset($_POST['receivedby'])) {
                        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
                        break;
                    }
                    
                    $file = isset($_FILES['file']) ? $_FILES['file'] : null;
                    $isConfirmed = isset($_POST['isConfirmed']) ? (bool)$_POST['isConfirmed'] : false;
                    
                    echo json_encode(updateDocument($_POST['id'], $_POST['name'], $_POST['sendby'], $_POST['receivedby'], $isConfirmed, $file));
                    break;
                } elseif ($_POST['_method'] === 'DELETE') {
                    if (!isset($_POST['id'])) {
                        echo json_encode(['success' => false, 'message' => 'ID manquant']);
                        break;
                    }
                    
                    echo json_encode(deleteDocument($_POST['id']));
                    break;
                }
            }
            
            // Traitement normal POST pour création
            if (!isset($_POST['name']) || !isset($_POST['sendby']) || !isset($_POST['receivedby']) || !isset($_FILES['file'])) {
                echo json_encode(['success' => false, 'message' => 'Données manquantes']);
                break;
            }
            
            echo json_encode(createDocument($_POST['name'], $_FILES['file'], $_POST['sendby'], $_POST['receivedby']));
            break;
            
        case 'PUT':
            parse_str(file_get_contents('php://input'), $putData);
            
            if (!isset($putData['id']) || !isset($putData['name']) || !isset($putData['sendby']) || !isset($putData['receivedby'])) {
                echo json_encode(['success' => false, 'message' => 'Données manquantes']);
                break;
            }
            
            $file = isset($_FILES['file']) ? $_FILES['file'] : null;
            $isConfirmed = isset($putData['isConfirmed']) ? (bool)$putData['isConfirmed'] : false;
            
            echo json_encode(updateDocument($putData['id'], $putData['name'], $putData['sendby'], $putData['receivedby'], $isConfirmed, $file));
            break;
            
        case 'DELETE':
            parse_str(file_get_contents('php://input'), $deleteData);
            
            if (!isset($deleteData['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID manquant']);
                break;
            }
            
            echo json_encode(deleteDocument($deleteData['id']));
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Méthode non supportée']);
    }
}
?>