<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JWTService
{
    // Generate a JWT token
    public static function generateToken($data)
    {
        $payload = [
            'iss' => 'localhost',  // Issuer of the token
            'sub' => $data,        // Subject of the token (data to encode)
            'iat' => time(),       // Issued at
            'exp' => time() + (10 * 365 * 24 * 60 * 60) // Expiry time (1 hour)
        ];

        $jwt_secret = env('APP_KEY');
        return JWT::encode($payload, $jwt_secret, 'HS256');
    }

    // Validate and decode a JWT token
    public static function validateToken($token)
    {
        try {
            $jwt_secret = env('APP_KEY');
            return JWT::decode($token, $jwt_secret);
        } catch (ExpiredException $e) {
            return response()->json(['error' => 'Token has expired.'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error decoding token.'], 400);
        }
    }
}