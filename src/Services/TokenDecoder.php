<?php

namespace App\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\Request;

class TokenDecoder {
    private $JWTEncoder;

    public function __construct(JWTEncoderInterface $JWTEncoder) {
        $this->JWTEncoder = $JWTEncoder;
    }

    public function decodeToken(Request $request) {
        $extractor = new AuthorizationHeaderTokenExtractor('Bearer', 'Authorization');

        $token = $extractor->extract($request);

        if (! $token) {
            throw new \Exception('No token provided');
        }

        return $this->JWTEncoder->decode($token);
    }
}