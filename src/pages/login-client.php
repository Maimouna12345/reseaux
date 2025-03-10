<?php
    session_start();
    
    // If client is already logged in, redirect to dashboard
    if (isset($_SESSION['client'])) {
        header("Location: client-dashboard.php");
        exit();
    }
    
    require_once '../components/header.php';
    require_once '../api/client.php';
    
    $error = '';
    
    // Process login form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = "Tous les champs sont obligatoires";
        } else {
            $client = login($email, $password);
            
            if ($client) {
                // Store client data in session
                $_SESSION['client'] = $client;
                
                // Redirect to client dashboard
                header("Location: client-dashboard.php");
                exit();
            } else {
                $error = "Nom d'utilisateur ou mot de passe incorrect";
            }
        }
    }
?>


    <div class="w-full max-w-sm p-6 bg-white rounded-2xl shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-pink-500">Espace Client</h2>
        <p class="text-sm text-gray-600 text-center mb-6">
            Connectez-vous pour accéder à vos devis
        </p>
        
        <?php if (!empty($error)): ?>
            <div class="bg-pink-100 text-pink-700 px-4 py-2 rounded mb-4">
                <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <form class="space-y-4" action="" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    required 
                    class="mt-1 p-2 w-full border border-gray-300 rounded focus:ring-pink-500 focus:border-pink-500"
                    placeholder="Entrer votre email">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input 
                    id="password" 
                    name="password" 
                    type="password" 
                    required 
                    class="mt-1 p-2 w-full border border-gray-300 rounded focus:ring-pink-500 focus:border-pink-500"
                    placeholder="Entrer votre mot de passe">
            </div>
            <button 
                type="submit" 
                class="w-full bg-pink-500 text-white font-bold py-2 px-4 rounded hover:bg-pink-600 transition duration-200">
                Se connecter
            </button>
        </form>
        <a href="/projetreseaux/index.php">
            <div class="bg-white text-center border border-pink-500 text-pink-500 hover:bg-pink-600 hover:text-white py-2 px-4 rounded transition duration-200 w-full mt-4">
                Retourner à l'accueil
            </div>
        </a>
    </div>
<?php
    require_once '../components/footer.php';
?>