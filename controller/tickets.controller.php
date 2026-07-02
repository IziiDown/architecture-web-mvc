<?php
require_once __DIR__ . '/../model/tickets.model.php';

class TicketsController {
    
    private function getJsonInput(): array {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

   
    public function getAll(): void {
        $tickets = TicketsModel::getAll();
        
        $formattedTickets = [];
        foreach ($tickets as $ticket) {
            $formattedTickets[] = [
                'id' => (int)$ticket['id'],
                'titre' => $ticket['titre'],
                'description' => $ticket['description'],
                'categorie' => $ticket['categorie'],
                'priorite' => $ticket['priorite'],
                'statut' => $ticket['statut'],
                'created_by' => (int)$ticket['created_by'],
                'creator_email' => $ticket['creator_email'],
                'created_at' => $ticket['created_at']
            ];
        }
        
        $data = $formattedTickets;
        $message = "Tickets retrieved successfully";
        $responseCode = 200;
        
        require __DIR__ . '/../view/tickets.json.php';
    }

    
    public function GetbyId($id): void {
        $ticketId = (int)$id;
        $ticket = TicketsModel::getById($ticketId);
        
        $data = [
            'id' => (int)$ticket['id'],
            'titre' => $ticket['titre'],
            'description' => $ticket['description'],
            'categorie' => $ticket['categorie'],
            'priorite' => $ticket['priorite'],
            'statut' => $ticket['statut'],
            'created_by' => (int)$ticket['created_by'],
            'creator_email' => $ticket['creator_email'],
            'created_at' => $ticket['created_at']
        ];
        $message = "Ticket retrieved successfully";
        $responseCode = 200;
        
        require __DIR__ . '/../view/tickets.json.php';
    }

   
    public function create(): void {
        $input = $this->getJsonInput();
        
        $titre = $input['titre'] ?? '';
        $description = $input['description'] ?? '';
        $categorie = $input['categorie'] ?? '';
        $priorite = $input['priorite'] ?? '';
        $userId = $_REQUEST['user']['id'];

        $ticketId = TicketsModel::create($titre, $description, $categorie, $priorite, $userId);
        $ticket = TicketsModel::getById($ticketId);
        
        $data = [
            'id' => (int)$ticket['id'],
            'titre' => $ticket['titre'],
            'description' => $ticket['description'],
            'categorie' => $ticket['categorie'],
            'priorite' => $ticket['priorite'],
            'statut' => $ticket['statut'],
            'created_by' => (int)$ticket['created_by'],
            'creator_email' => $ticket['creator_email'],
            'created_at' => $ticket['created_at']
        ];
        $message = "Ticket created successfully";
        $responseCode = 201;
        
        require __DIR__ . '/../view/tickets.json.php';
    }

    
    public function updateStatus($id): void {
        $ticketId = (int)$id;
        $input = $this->getJsonInput();
        $statut = $input['statut'] ?? ($input['status'] ?? '');

        TicketsModel::updateStatus($ticketId, $statut);
        $updatedTicket = TicketsModel::getById($ticketId);

        $data = [
            'id' => (int)$updatedTicket['id'],
            'titre' => $updatedTicket['titre'],
            'description' => $updatedTicket['description'],
            'categorie' => $updatedTicket['categorie'],
            'priorite' => $updatedTicket['priorite'],
            'statut' => $updatedTicket['statut'],
            'created_by' => (int)$updatedTicket['created_by'],
            'creator_email' => $updatedTicket['creator_email'],
            'created_at' => $updatedTicket['created_at']
        ];
        $message = "Ticket status updated successfully";
        $responseCode = 200;

        require __DIR__ . '/../view/tickets.json.php';
    }
}
