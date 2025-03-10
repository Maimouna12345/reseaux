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
    
    // Traitement des actions
    $message = '';
    $messageType = '';
    
    // Traitement de l'ajout d'un employé
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($name) || empty($email) || empty($password)) {
            $message = "Tous les champs sont obligatoires";
            $messageType = "error";
        } else {
            if (register($name, $email, $password)) {
                $message = "Employé ajouté avec succès";
                $messageType = "success";
            } else {
                $message = "Erreur lors de l'ajout de l'employé. L'email existe peut-être déjà.";
                $messageType = "error";
            }
        }
    }
    
    // Traitement de la mise à jour d'un employé
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($id) || empty($name) || empty($email)) {
            $message = "Le nom et l'email sont obligatoires";
            $messageType = "error";
        } else {
            if (updateEmploye($id, $name, $email, $password)) {
                $message = "Employé mis à jour avec succès";
                $messageType = "success";
            } else {
                $message = "Erreur lors de la mise à jour de l'employé";
                $messageType = "error";
            }
        }
    }
    
    // Traitement de la suppression d'un employé
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        
        if (deleteEmploye($id)) {
            $message = "Employé supprimé avec succès";
            $messageType = "success";
        } else {
            $message = "Erreur lors de la suppression de l'employé";
            $messageType = "error";
        }
    }
    
    // Récupérer tous les employés
    $employees = getAllEmployees();
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-500">Gestion des Employés</h1>
            <div>
                <a href="employee-dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded transition duration-200 mr-2">
                    Retour au tableau de bord
                </a>
                <button id="addEmployeeBtn" class="bg-pink-500 hover:bg-pink-600 text-white py-2 px-4 rounded transition duration-200">
                    Ajouter un employé
                </button>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="mb-4 p-4 rounded <?= $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Liste des employés -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-pink-500 mb-4">Liste des Employés</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Aucun employé trouvé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($emp['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($emp['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($emp['email']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="edit-btn text-blue-600 hover:text-blue-900 mr-2" 
                                            data-id="<?= $emp['id'] ?>" 
                                            data-name="<?= htmlspecialchars($emp['name']) ?>" 
                                            data-email="<?= htmlspecialchars($emp['email']) ?>">
                                        Modifier
                                    </button>
                                    <a href="?action=delete&id=<?= $emp['id'] ?>" 
                                       class="text-red-600 hover:text-red-900"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet employé ?')">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal d'ajout d'employé -->
    <div id="addEmployeeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-md p-6 w-full max-w-md">
            <h2 class="text-xl font-semibold text-pink-500 mb-4">Ajouter un Employé</h2>
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                    <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Ajouter
                    </button>
                    <button type="button" id="closeAddModal" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal de modification d'employé -->
    <div id="editEmployeeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-md p-6 w-full max-w-md">
            <h2 class="text-xl font-semibold text-pink-500 mb-4">Modifier un Employé</h2>
            <form action="" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-4">
                    <label for="edit-name" class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
                    <input type="text" name="name" id="edit-name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="edit-email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" name="email" id="edit-email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="edit-password" class="block text-gray-700 text-sm font-bold mb-2">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" name="password" id="edit-password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Mettre à jour
                    </button>
                    <button type="button" id="closeEditModal" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Gestion des modals
        const addEmployeeBtn = document.getElementById('addEmployeeBtn');
        const addEmployeeModal = document.getElementById('addEmployeeModal');
        const closeAddModal = document.getElementById('closeAddModal');
        const editEmployeeModal = document.getElementById('editEmployeeModal');
        const closeEditModal = document.getElementById('closeEditModal');
        const editButtons = document.querySelectorAll('.edit-btn');
        
        // Ouvrir le modal d'ajout
        addEmployeeBtn.addEventListener('click', () => {
            addEmployeeModal.classList.remove('hidden');
        });
        
        // Fermer le modal d'ajout
        closeAddModal.addEventListener('click', () => {
            addEmployeeModal.classList.add('hidden');
        });
        
        // Fermer le modal de modification
        closeEditModal.addEventListener('click', () => {
            editEmployeeModal.classList.add('hidden');
        });
        
        // Ouvrir le modal de modification avec les données de l'employé
        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const email = button.getAttribute('data-email');
                
                document.getElementById('edit-id').value = id;
                document.getElementById('edit-name').value = name;
                document.getElementById('edit-email').value = email;
                document.getElementById('edit-password').value = '';
                
                editEmployeeModal.classList.remove('hidden');
            });
        });
        
        // Fermer les modals en cliquant à l'extérieur
        window.addEventListener('click', (e) => {
            if (e.target === addEmployeeModal) {
                addEmployeeModal.classList.add('hidden');
            }
            if (e.target === editEmployeeModal) {
                editEmployeeModal.classList.add('hidden');
            }
        });
    </script>
</div>

<?php
    require_once '../components/footer.php';
?>