<?php

class AuthController
{
    /**
     * Analyser le corps de la requête JSON entrante
     */
    private function getJsonInput(): array
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Gérer l'inscription de l'utilisateur
     */
    public function register(): void
    {
        $input = $this->getJsonInput();

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'student';

        $userId =:create($email, $password, $role);

        $data = [
            'user' => [
                'id' => $userId,
                'email' => $email,
                'role' => $role
            ]
        ];
        $message = "User registered successfully";

        require __DIR__ . '/../view/register.json.php';
    }

    /**
     * Gérer la connexion de l'utilisateur et la génération du JWT
     */
    public function login(): void
    {
        $input = $this->getJsonInput();

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        $user = UserModel::findByEmail($email);

        $payload = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        $token = JWT::generate($payload, 86400);

        $data = [
            'token' => $token->toString(),
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
        $message = "Login successful";

        require __DIR__ . '/../view/login.json.php';
    }
}
