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
    
    require_once '../components/header.php';
    require_once '../api/documents.php';
    
    // Check if document ID is provided
    if (!isset($_GET['id'])) {
        header("Location: client-dashboard.php");
        exit();
    }
    
    $documentId = $_GET['id'];
    $documentResult = getDocumentById($documentId);
    
    // Check if document exists and belongs to this client
    if (!$documentResult['success'] || $documentResult['data']['receivedby'] != $client['id']) {
        header("Location: client-dashboard.php?message=Document non trouvé ou accès non autorisé&type=error");
        exit();
    }
    
    $document = $documentResult['data'];
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-500">Détails du Devis</h1>
            <div>
                <a href="client-dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded transition duration-200">
                    Retour au tableau de bord
                </a>
            </div>
        </div>
        
        <div class="bg-pink-50 rounded-lg p-4 mb-6">
            <h2 class="text-xl font-semibold text-pink-700 mb-2"><?= htmlspecialchars($document['name']) ?></h2>
            <p class="text-gray-700 mb-2">
                <span class="font-semibold">Statut:</span> 
                <?php if ($document['isConfirmed']): ?>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmé</span>
                <?php else: ?>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                <?php endif; ?>
            </p>
            <p class="text-gray-700 mb-2"><span class="font-semibold">Envoyé par:</span> <?= htmlspecialchars($document['employee_name']) ?></p>
        </div>
    </div>
    
    <!-- Document Content -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-pink-500 mb-4">Contenu du Devis</h2>
        
        <?php if (!empty($document['urlFile'])): ?>
            <div class="mb-6">
                <iframe src="../uploads/<?= htmlspecialchars($document['urlFile']) ?>" class="w-full h-96 border border-gray-300 rounded"></iframe>
            </div>
            <div class="mb-4">
                <a href="../uploads/<?= htmlspecialchars($document['urlFile']) ?>" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition duration-200">
                    Ouvrir dans un nouvel onglet
                </a>
                <a href="../uploads/<?= htmlspecialchars($document['urlFile']) ?>" download class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded transition duration-200 ml-2">
                    Télécharger
                </a>
            </div>
        <?php else: ?>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <p class="text-yellow-700">Aucun fichier n'est disponible pour ce devis.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Actions Section -->
    <?php if (!$document['isConfirmed']): ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-pink-500 mb-4">Actions</h2>
        <div class="flex space-x-4">
            <a href="document-action.php?id=<?= $document['id'] ?>&action=confirm" 
               class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded transition duration-200"
               onclick="return confirm('Êtes-vous sûr de vouloir confirmer ce devis ?')">
                Confirmer ce devis
            </a>
            <a href="document-action.php?id=<?= $document['id'] ?>&action=reject" 
               class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded transition duration-200"
               onclick="return confirm('Êtes-vous sûr de vouloir rejeter ce devis ?')">
                Rejeter ce devis
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
    require_once '../components/footer.php';
?>