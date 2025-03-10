<?php
$host = 'localhost:3306'; // Adresse du serveur de base de données
$dbname = 'smarttech'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur de la base de données
$password = ''; // Mot de passe de la base de données

try {
    // Établir la connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Vérifier s'il y a déjà des utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
    $userCount = $stmt->fetchColumn();

    if ($userCount == 0) {
        // Aucun utilisateur trouvé, on insère un utilisateur par défaut
        $defaultUser = [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_BCRYPT)
        ];

        $insertStmt = $pdo->prepare("INSERT INTO employees (name, email, password) VALUES (:name, :email, :password)");
        $insertStmt->execute($defaultUser);

        
   
    }


} catch (PDOException $e) {
    // Gérer les erreurs de connexion
    die("Erreur de connexion : " . $e->getMessage());
}
?>