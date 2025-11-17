<?php
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class JwtMiddleware {
    private static $secret = 'CHANGE_THIS_SECRET_TO_SECURE_RANDOM';

    public static function generateToken($payload, $expSeconds = 3600) {
        $now = time();
        $token = array_merge([
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $expSeconds
        ], $payload);
        return JWT::encode($token, self::$secret, 'HS256');
    }

    public static function validateToken($jwt) {
        try {
            $decoded = JWT::decode($jwt, new Key(self::$secret, 'HS256'));
            return (array)$decoded;
        } catch(Exception $e) {
            return null;
        }
    }
}
