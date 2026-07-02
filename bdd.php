<?php

function getConnection() {
    $host = 'localhost';
    $dbname = 'eval_mvc_tickets';
    $username = 'root';
    $password = ''; 

    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {

        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Erreur de connexion à la base de données',
            'message' => 'Impossible de se connecter à la base de données. Assurez-vous que MySQL est démarré.'
        ]);
        exit;
    }
}
