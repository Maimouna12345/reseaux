<?php
    session_start();
    
    // If client is not logged in, redirect to login page
    if (!isset($_SESSION['client'])) {
        header("Location: login-client.php");
        exit();
    }
    
    require_once '../components/header.php';
    
    // Get client data from session
    $client = $_SESSION['client'];
    
    // Include necessary API files
    require_once '../api/document.php';
    
    // Get documents for this client
    $documents = getDocumentsByClient($client['id']);
    $pendingDocuments = array_filter($documents, function($doc) {
        return !$doc['isConfirmed'];
    });
    $confirmedDocuments = array_filter($documents, function($doc) {
        return $doc['isConfirmed'];
    });
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-500">Tableau de bord client</h1>
            <div>
                <a href="logout-client.php" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded transition duration-200">
                    Se déconnecter
                </a>
            </div>
        </div>
        
        <div class="bg-pink-50 rounded-lg p-4 mb-6">
            <h2 class="text-xl font-semibold text-pink-700 mb-2">Bienvenue, <?= htmlspecialchars($client['name']) ?></h2>
            <p class="text-gray-700">Email: <?= htmlspecialchars($client['email']) ?></p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Document Statistics Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-pink-500 mb-4">Mes Devis</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-pink-50 p-4 rounded-lg text-center">
                    <span class="block text-2xl font-bold text-pink-600"><?= count($documents) ?></span>
                    <span class="text-gray-600 text-sm">Total des devis</span>
                </div>
                <div class="bg-pink-50 p-4 rounded-lg text-center">
                    <span class="block text-2xl font-bold text-pink-600"><?= count($pendingDocuments) ?></span>
                    <span class="text-gray-600 text-sm">Devis en attente</span>
                </div>
                <div class="bg-pink-50 p-4 rounded-lg text-center">
                    <span class="block text-2xl font-bold text-pink-600"><?= count($confirmedDocuments) ?></span>
                    <span class="text-gray-600 text-sm">Devis confirmés</span>
                </div>
                <div class="bg-pink-50 p-4 rounded-lg text-center">
                    <span class="block text-2xl font-bold text-pink-600"><?= count($documents) > 0 ? round((count($confirmedDocuments) / count($documents)) * 100) : 0 ?>%</span>
                    <span class="text-gray-600 text-sm">Taux de confirmation</span>
                </div>
            </div>
        </div>
        
        <!-- Tasks Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-pink-500 mb-4">Actions</h2>
            <div class="space-y-4">
                <div class="border-l-4 border-pink-500 pl-4 py-2">
                    <h3 class="font-medium">Consulter vos devis</h3>
                    <p class="text-gray-600 text-sm">Visualisez tous les devis qui vous ont été envoyés</p>
                </div>
                <div class="border-l-4 border-pink-500 pl-4 py-2">
                    <h3 class="font-medium">Confirmer ou rejeter</h3>
                    <p class="text-gray-600 text-sm">Donnez votre réponse aux devis en attente</p>
                </div>
                <div class="border-l-4 border-pink-500 pl-4 py-2">
                    <h3 class="font-medium">Historique</h3>
                    <p class="text-gray-600 text-sm">Consultez l'historique de vos devis confirmés</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Documents Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h2 class="text-xl font-semibold text-pink-500 mb-4">Mes Devis en Attente</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom du document</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Envoyé par</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($pendingDocuments)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Aucun devis en attente</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pendingDocuments as $document): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($document['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($document['employee_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="document-view.php?id=<?= $document['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-2">Voir</a>
                                    <a href="document-action.php?id=<?= $document['id'] ?>&action=confirm" class="text-green-600 hover:text-green-900 mr-2" onclick="return confirm('Êtes-vous sûr de vouloir confirmer ce devis ?')">Confirmer</a>
                                    <a href="document-action.php?id=<?= $document['id'] ?>&action=reject" class="text-red-600 hover:text-red-900" onclick="return confirm('Êtes-vous sûr de vouloir rejeter ce devis ?')">Rejeter</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Confirmed Documents Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h2 class="text-xl font-semibold text-pink-500 mb-4">Mes Devis Confirmés</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom du document</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Envoyé par</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($confirmedDocuments)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Aucun devis confirmé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($confirmedDocuments as $document): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($document['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($document['employee_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmé</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="document-view.php?id=<?= $document['id'] ?>" class="text-blue-600 hover:text-blue-900">Voir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
    require_once '../components/footer.php';
?>