<?php
require_once '../../db/db.php';

function login($email, $password) {
    global $pdo;
    try {
        // Récupérer l'utilisateur correspondant à l'email
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
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
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            // L'utilisateur existe déjà, renvoyer une erreur
            return false;
        } else {
            // L'utilisateur n'existe pas, insérer un nouvel utilisateur
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO clients (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword]);
            return true;
        }
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

function getAllClients() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM clients");
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $clients;
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Get all clients error: " . $e->getMessage());
        return [];
    }
}

function getClientById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        return $client;
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Get client by ID error: " . $e->getMessage());
        return false;
    }
}

function deleteClient($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Delete client error: " . $e->getMessage());
        return false;
    }
}

function updateClient($id, $name, $email, $password) {
    global $pdo;
    try {
        // If password is provided, update it as well
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE clients SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $email, $hashedPassword, $id]);
        } else {
            // Otherwise, just update name and email
            $stmt = $pdo->prepare("UPDATE clients SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $id]);
        }
        return true;
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Update client error: " . $e->getMessage());
        return false;
    }
}

?>