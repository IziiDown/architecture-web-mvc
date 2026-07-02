<?php
require_once __DIR__ . '/../bdd.php';

class TicketsModel {
   
    public static function getAll(): array {
        $db = getConnection();
        
        $stmt = $db->query("
            SELECT t.id, t.titre, t.description, t.categorie, t.priorite, t.statut, t.created_by, t.created_at, u.email as creator_email 
            FROM tickets t 
            LEFT JOIN users u ON t.created_by = u.id 
            ORDER BY t.created_at DESC
        ");
        
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = getConnection();
        
        $stmt = $db->prepare("
            SELECT t.id, t.titre, t.description, t.categorie, t.priorite, t.statut, t.created_by, t.created_at, u.email as creator_email 
            FROM tickets t 
            LEFT JOIN users u ON t.created_by = u.id 
            WHERE t.id = :id
        ");
        $stmt->execute([':id' => $id]);
        
        $ticket = $stmt->fetch();
        return $ticket ? $ticket : null;
    }

    
    public static function create(string $titre, string $description, string $categorie, string $priorite, int $created_by): int {
        $db = getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO tickets (titre, description, categorie, priorite, created_by, statut) 
            VALUES (:titre, :description, :categorie, :priorite, :created_by, 'Nouveau')
        ");
        
        $stmt->execute([
            ':titre' => $titre,
            ':description' => $description,
            ':categorie' => $categorie,
            ':priorite' => $priorite,
            ':created_by' => $created_by
        ]);
        
        return (int)$db->lastInsertId();
    }

    
    public static function updateStatus(int $id, string $statut): bool {
        $db = getConnection();
        
        $stmt = $db->prepare("UPDATE tickets SET statut = :statut WHERE id = :id");
        return $stmt->execute([
            ':statut' => $statut,
            ':id' => $id
        ]);
    }
}
