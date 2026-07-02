<?php
require_once __DIR__ . '/Payload.php';
require_once __DIR__ . '/Token.php';
require_once __DIR__ . '/JWTException.php';

/**
 * Gestionnaire JWT personnalisé pour générer et valider des JSON Web Tokens
 */
class JWT {
    // Clé secrète utilisée pour signer les jetons. Il est recommandé de la modifier ou de la stocker dans des variables d'environnement.
    private static string $secret = "MVC_PHP_TICKETS_JWT_SECRET_KEY_987654321";

    public static function setSecret(string $secret): void {
        self::$secret = $secret;
    }

    private static function base64UrlEncode(string $data): string {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode(string $data): string {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    /**
     * Génère un nouveau jeton JWT pour les données utilisateur fournies
     */
    public static function generate(array $data, int $expirySeconds = 86400): Token {
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);

        $payloadData = $data;
        $payloadData['iat'] = time();
        $payloadData['exp'] = time() + $expirySeconds;
        $payload = json_encode($payloadData);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return new Token($base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature);
    }

    /**
     * Valide un jeton JWT et retourne sa structure de données (payload)
     */
    public static function validate(string $tokenString): Payload {
        $parts = explode('.', $tokenString);
        if (count($parts) !== 3) {
            throw new InvalidTokenException("Format de jeton invalide");
        }

        list($headerB64, $payloadB64, $signatureB64) = $parts;

        $headerJson = self::base64UrlDecode($headerB64);
        $payloadJson = self::base64UrlDecode($payloadB64);

        if (!$headerJson || !$payloadJson) {
            throw new InvalidTokenException("L'encodage du jeton est corrompu");
        }

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!$header || !$payload) {
            throw new InvalidTokenException("Structure de l'en-tête ou des données invalide");
        }

        // Vérification de la signature
        $expectedSignature = hash_hmac('sha256', $headerB64 . "." . $payloadB64, self::$secret, true);
        $expectedSignatureB64 = self::base64UrlEncode($expectedSignature);

        if (!hash_equals($expectedSignatureB64, $signatureB64)) {
            throw new InvalidTokenException("La vérification de la signature a échoué");
        }

        // Vérification de l'expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new ExpiredTokenException("Le jeton a expiré");
        }

        return new Payload($payload);
    }
}
