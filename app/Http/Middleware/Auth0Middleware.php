<?php

namespace App\Http\Middleware;

use Closure;
use Auth0\SDK\Exception\InvalidTokenException;
use Auth0\SDK\Helpers\JWKFetcher;
use Auth0\SDK\Helpers\Tokens\AsymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\TokenVerifier;

class Auth0Middleware
{

  public function handle($request, Closure $next, $scopeRequired = null) {

    $authorization = $request->header('Authorization');
    $token = $request->bearerToken();

    if (!$authorization) {
      return response()->json([
        'error' => 'authorization Header not found' 
      ], 401);
    }

    if(!$token) {
      return response()->json([
        'error' => 'no token provided'
      ], 401);
    }

    $decodedToken = $this->validateAndDecode($token);

    if ($scopeRequired && !$this->tokenHasScope($decodedToken, $scopeRequired)) {
      return response()->json([
        'error' => 'insufficient scope'
      ], 403);
    }

    return $next($request);
  }

  public function validateAndDecode($token) {
    try {
      $jwksUri = env('AUTH0_DOMAIN') . '.well-known/jwks.json';
      $jwksFetcher = new JWKFetcher(null, [ 'base_uri' => $jwksUri ]);
      $signatureVerifier = new AsymmetricVerifier($jwksFetcher);
      $tokenVerifier = new TokenVerifier(env('AUTH0_DOMAIN'), env('AUTH0_AUD'), $signatureVerifier);

      return $tokenVerifier->verify($token);
    } catch (InvalidTokenException $e) {
      throw $e; 
    }
  }

  public function tokenHasScope($token, $scopeRequired) {
    if (empty($token['scope'])) {
      return false;
    }

    $tokenScopes = explode(' ', $token['scope']);

    return in_array($scopeRequired, $tokenScopes);
  }

}
