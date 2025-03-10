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
    require_once '../api/documents.php';
    require_once '../api/client.php';
    
    // Get all clients for the dropdown
    $clients = getAllClients();
    
    // Get all documents for this employee
    $documents = getDocumentsByEmployee($employee['id']);
    
    // Handle success/error messages
    $message = '';
    $messageType = '';
    
    if (isset($_GET['success'])) {
        $message = $_GET['success'];
        $messageType = 'success';
    } elseif (isset($_GET['error'])) {
        $message = $_GET['error'];
        $messageType = 'error';
    }
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-500">Gestion des Documents</h1>
            <div>
                <a href="employee-dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded transition duration-200 mr-2">
                    Retour au tableau de bord
                </a>
                <button id="openModalBtn" class="bg-pink-500 hover:bg-pink-600 text-white py-2 px-4 rounded transition duration-200">
                    Ajouter un document
                </button>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="<?= $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> p-4 rounded-lg mb-6">
                <?php htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Documents List -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-pink-500 mb-4">Mes Documents</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom du document</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'envoi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($documents['data'])): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucun document trouvé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documents['data'] as $document): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($document['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($document['client_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= isset($document['created_at']) ? date('d/m/Y', strtotime($document['created_at'])) : 'N/A' ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($document['isConfirmed']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmé</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="../api/documents.php?action=download&id=<?= $document['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">Télécharger</a>
                                    <button class="text-yellow-600 hover:text-yellow-900 mr-3 edit-btn" 
                                            data-id="<?= $document['id'] ?>" 
                                            data-name="<?= htmlspecialchars($document['name']) ?>" 
                                            data-client="<?= $document['receivedby'] ?>">
                                        Modifier
                                    </button>
                                    <button class="text-red-600 hover:text-red-900 delete-btn" 
                                            data-id="<?= $document['id'] ?>" 
                                            data-name="<?= htmlspecialchars($document['name']) ?>">
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Document Modal -->
<div id="addDocumentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-pink-500">Ajouter un document</h3>
            <button id="closeModalBtn" class="text-gray-500 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form action="../api/documents.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nom du document</label>
                <input type="text" id="name" name="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="client" class="block text-gray-700 text-sm font-bold mb-2">Client</label>
                <select id="client" name="receivedby" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Sélectionner un client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="file" class="block text-gray-700 text-sm font-bold mb-2">Fichier PDF</label>
                <input type="file" id="file" name="file" accept=".pdf" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <p class="text-xs text-gray-500 mt-1">Seuls les fichiers PDF sont acceptés</p>
            </div>
            <input type="hidden" name="sendby" value="<?= $employee['id'] ?>">
            <div class="flex justify-end">
                <button type="button" id="cancelAddBtn" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded mr-2">Annuler</button>
                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white py-2 px-4 rounded">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Document Modal -->
<div id="editDocumentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-pink-500">Modifier le document</h3>
            <button id="closeEditModalBtn" class="text-gray-500 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="editForm" action="../api/documents.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" id="edit_id" name="id">
            <div class="mb-4">
                <label for="edit_name" class="block text-gray-700 text-sm font-bold mb-2">Nom du document</label>
                <input type="text" id="edit_name" name="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="edit_client" class="block text-gray-700 text-sm font-bold mb-2">Client</label>
                <select id="edit_client" name="receivedby" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Sélectionner un client</option>
                    <?php foreach ($clients['data'] as $client): ?>
                        <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="edit_file" class="block text-gray-700 text-sm font-bold mb-2">Nouveau fichier PDF (optionnel)</label>
                <input type="file" id="edit_file" name="file" accept=".pdf" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <p class="text-xs text-gray-500 mt-1">Laissez vide pour conserver le fichier actuel</p>
            </div>
            <input type="hidden" name="sendby" value="<?= $employee['id'] ?>">
            <input type="hidden" name="isConfirmed" value="0">
            <div class="flex justify-end">
                <button type="button" id="cancelEditBtn" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded mr-2">Annuler</button>
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-red-500">Confirmer la suppression</h3>
            <button id="closeDeleteModalBtn" class="text-gray-500 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="mb-4">Êtes-vous sûr de vouloir supprimer le document <span id="deleteDocumentName" class="font-semibold"></span> ?</p>
        <form id="deleteForm" action="../api/documents.php" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" id="delete_id" name="id">
            <div class="flex justify-end">
                <button type="button" id="cancelDeleteBtn" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded mr-2">Annuler</button>
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">Supprimer</button>
            </div>
        </form>
    </div>
</div>


<?php
    require_once '../components/footer.php';
?>
<script>
    // Modal functionality
    const addModal = document.getElementById('addDocumentModal');
    const editModal = document.getElementById('editDocumentModal');
    const deleteModal = document.getElementById('deleteModal');
    
    // Add 
    document.getElementById('openModalBtn').addEventListener('click', () => {
        addModal.classList.remove('hidden');
    });
    
    document.getElementById('closeModalBtn').addEventListener('click', () => {
        addModal.classList.add('hidden');
    });
    
    document.getElementById('cancelAddBtn').addEventListener('click', () => {
        addModal.classList.add('hidden');
    });
    
    // Edit
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const client = button.getAttribute('data-client');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_client').value = client;
            
            editModal.classList.remove('hidden');
        });
    });
    
    document.getElementById('closeEditModalBtn').addEventListener('click', () => {
        editModal.classList.add('hidden');
    });
    
    document.getElementById('cancelEditBtn').addEventListener('click', () => {
        editModal.classList.add('hidden');
    });
    
    // Delete
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteDocumentName').textContent = name;
            
            deleteModal.classList.remove('hidden');
        });
    });
    
    document.getElementById('closeDeleteModalBtn').addEventListener('click', () => {
        deleteModal.classList.add('hidden');
    });
    
    document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
        deleteModal.classList.add('hidden');
    });
    
    // Handle PUT and DELETE requests
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('../api/documents.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'document-manager.php?success=' + encodeURIComponent('Document mis à jour avec succès');
            } else {
                window.location.href = 'document-manager.php?error=' + encodeURIComponent(data.message || 'Erreur lors de la mise à jour du document');
            }
        })
        .catch(error => {
            window.location.href = 'document-manager.php?error=' + encodeURIComponent('Erreur de connexion au serveur');
        });
    });
    
    document.getElementById('deleteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('../api/documents.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'document-manager.php?success=' + encodeURIComponent('Document supprimé avec succès');
            } else {
                window.location.href = 'document-manager.php?error=' + encodeURIComponent(data.message || 'Erreur lors de la suppression du document');
            }
        })
        .catch(error => {
            window.location.href = 'document-manager.php?error=' + encodeURIComponent('Erreur de connexion au serveur');
        });
    });
</script>