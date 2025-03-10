<?php
require_once '../../db/db.php';

function login($email, $password) {
    global $pdo;
    try {
        // Récupérer l'utilisateur correspondant à l'email
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($user && password_verify($password, $user['password'])) {
            // L'utilisateur est authentifié, renvoyer les informations de l'utilisateur
            return $user;
        } else {
            // L'authentification a échoué
            return false;
        }
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function register($name, $email, $password) {
    global $pdo;
    try {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE email =?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            // L'utilisateur existe déjà, renvoyer une erreur
            return false;
        } else {
            // L'utilisateur n'existe pas, insérer un nouvel utilisateur
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO employees (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword]);
            
            // Envoyer un email avec les identifiants
            envoyerEmailBienvenue($name, $email, $password);
            
            return true;
        }
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

function envoyerEmailBienvenue($nom, $email, $motDePasse) {
    $destinataire = $email;
    $sujet = "Bienvenue dans notre entreprise - Vos identifiants d'accès";
    
    // En-têtes pour l'email HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@votreentreprise.com" . "\r\n";
    
    // Corps du message en HTML
    $message = "
    <html>
    <head>
        <title>Bienvenue dans notre entreprise</title>
    </head>
    <body>
        <h2>Bienvenue dans notre entreprise, {$nom} !</h2>
        <p>Votre compte a été créé avec succès. Voici vos identifiants d'accès :</p>
        <p><strong>Email :</strong> {$email}</p>
        <p><strong>Mot de passe :</strong> {$motDePasse}</p>
        <p>Veuillez garder ces informations en sécurité et nous vous conseillons de changer votre mot de passe après votre première connexion.</p>
        <p>Si vous avez des questions, n'hésitez pas à contacter notre équipe de support.</p>
        <p>Cordialement,<br>L'équipe de votre entreprise</p>
    </body>
    </html>
    ";
    
    // Envoi de l'email
    $resultat = mail($destinataire, $sujet, $message, $headers);
    
    if (!$resultat) {
        error_log("Erreur lors de l'envoi de l'email à : " . $email);
    }
    
    return $resultat;
}

function getAllEmployees() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM employees");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $employees;
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Get all employees error: " . $e->getMessage());
        return [];
    }
}

function getEmployeById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id =?");
        $stmt->execute([$id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        return $employee;
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Get employee by ID error: " . $e->getMessage());
        return false;
    }
}

function deleteEmploye($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM employees WHERE id =?");
        $stmt->execute([$id]);
        return true;
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Delete employee error: " . $e->getMessage());
        return false;
    }
}

function updateEmploye($id, $name, $email, $password = null) {
    global $pdo;
    try {
        // Si un nouveau mot de passe est fourni, le hacher
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE employees SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $email, $hashedPassword, $id]);
        } else {
            // Sinon, mettre à jour uniquement le nom et l'email
            $stmt = $pdo->prepare("UPDATE employees SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $id]);
        }
        return true;
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Update employee error: " . $e->getMessage());
        return false;
    }
}

?>