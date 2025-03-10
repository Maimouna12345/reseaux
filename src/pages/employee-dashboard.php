<?php
    // Start session to access employee data
    session_start();
    
    // Check if employee is logged in, redirect to login page if not
    if (!isset($_SESSION['employee'])) {
        header("Location: login-employee.php");
        exit();
    }
    
    // Get employee data from session
    $employee = $_SESSION['employee'];
    
    require_once '../components/header.php';
    require_once '../api/employe.php';
    require_once '../api/statistiques.php';
    
    // Get document statistics
    $totalDocuments = getTotalDocuments();
    $confirmedDocuments = getConfirmedDocuments();
    $pendingDocuments = getPendingDocuments();
    $documentsSentByEmployee = getDocumentsSentByEmployee($employee['id']);
    $confirmationRate = getDocumentConfirmationRate();
    $recentDocuments = getRecentDocuments(5);
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-500">Tableau de bord employé</h1>
            <div>
                <a href="document-manager.php" class="bg-pink-500 hover:bg-pink-600 text-white py-2 px-4 rounded transition duration-200 mr-2">
                    Gestion des documents
                </a>
                <a href="client-manager.php" class="bg-pink-500 hover:bg-pink-600 text-white py-2 px-4 rounded transition duration-200 mr-2">
                    Gestion des des clients
                </a>
                <a href="employe-manager.php" class="bg-pink-500 hover:bg-pink-600 text-white py-2 px-4 rounded transition duration-200 mr-2">
                    Gestion des employés
                </a>
                <a href="logout-employe.php" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded transition duration-200">
                    Se déconnecter
                </a>
            </div>
        </div>
        
        <div class="bg-pink-50 rounded-lg p-4 mb-6">
            <h2 class="text-xl font-semibold text-pink-700 mb-2">Bienvenue, <?= htmlspecialchars($employee['name']) ?></h2>
            <p class="text-gray-700">Email: <?= htmlspecialchars($employee['email']) ?></p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tasks Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-pink-500 mb-4">Mes Tâches</h2>
            <div class="space-y-4">
                <div class="border-l-4 border-pink-500 pl-4 py-2">
                    <h3 class="font-medium">Gérer les devis</h3>
                    <p class="text-gray-600 text-sm">Créer et envoyer les devis au clients</p>
                </div>
                <div class="border-l-4 border-pink-500 pl-4 py-2">
                    <h3 class="font-medium">Sauvegarder les devis</h3>
                    <p class="text-gray-600 text-sm">Vérifier et  stocker les devis sur la plateforme</p>
                </div>
                <div class="border-l-4 border-pink-500 pl-4 py-2">
                    <h3 class="font-medium">Contacter les clients</h3>
                    <p class="text-gray-600 text-sm">Rappeler les clients pour confirmer les devis</p>
                </div>
            </div>
        </div>
        
        <!-- Document Statistics Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-pink-500 mb-4">Statistiques des Documents</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-pink-50 p-4 rounded-lg text-center">
                    <span class="block text-2xl font-bold text-pink-600"><?= $totalDocuments ?></span>
                    <span class="text-gray-600 text-sm">Documents totaux</span>
                </div>
                <div class="bg-pink-50 p-4 rounded-lg text-center">
                    <span class="block text-2xl font-bold text-pink-600"><?= $documentsSentByEmployee ?></span>
                    <span class="text-gray-600 text-sm">Mes documents envoyés</span>
                </div>
                <div class="bg-pink-50 p-4 rounded-lg text-center">
                    <span class="block text-2xl font-bold text-pink-600"><?= $confirmationRate ?>%</span>
                    <span class="text-gray-600 text-sm">Taux de confirmation</span>
                </div>
                <div class="bg-pink-50 p-4 rounded-lg text-center">
                    <span class="block text-2xl font-bold text-pink-600"><?= $pendingDocuments ?></span>
                    <span class="text-gray-600 text-sm">Documents en attente</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Documents Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h2 class="text-xl font-semibold text-pink-500 mb-4">Documents Récents</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom du document</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Envoyé par</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($recentDocuments)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucun document trouvé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentDocuments as $document): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($document['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($document['client_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($document['employee_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($document['isConfirmed']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmé</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="document-details.php?id=<?= $document['id'] ?>" class="text-pink-600 hover:text-pink-900">Détails</a>
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