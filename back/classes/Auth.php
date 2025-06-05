<?php
class Auth {
    public static function generateJWT($email) {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode(['email' => $email, 'exp' => time() + 3600]);
        $base64UrlHeader = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", JWT_SECRET, true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    public static function verifyJWT($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        list($header, $payload, $signature) = $parts;
        $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)), '+/', '-_'), '=');
        if (!hash_equals($expected, $signature)) return false;

        $payload_data = json_decode(base64_decode($payload), true);
        if ($payload_data['exp'] < time()) return false;

        return $payload_data['email'];
    }
}
