<?php
require_once './db/db.php';
require_once './src/components/header.php'
?>




    <a href="./src/pages/login-client.php" class="block font-bold text-pink-500 text-center">
        <div class="bg-white rounded shadow-lg p-8 size-48 border border-pink-500">
            Se connecter en tant que client
        </div>
    </a>
    <a href="./src/pages/login-employee.php" class="block font-bold text-pink-500 text-center">
        <div class="bg-white rounded shadow-lg p-8 size-48 border border-pink-500">
            Se connecter en tant qu'employé
        </div>
    </a>

<?php
require_once './src/components/footer.php'
?>