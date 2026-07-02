<?php
require_once __DIR__ . '/../bdd.php';

class UserModel {
    
    public static function create(string $email, string $password, string $role = 'student'): int {
        $db = getConnection();
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (email, password, role) VALUES (:email, :password, :role)");
        $stmt->execute([
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);
        
        return (int)$db->lastInsertId();
    }

    
    public static function findByEmail(string $email): ?array {
        $db = getConnection();
        
        $stmt = $db->prepare("SELECT id, email, password, role, created_at FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        $user = $stmt->fetch();
        return $user ? $user : null;
    }
}
