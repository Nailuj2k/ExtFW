<?php

    @require_once 'vendor/autoload.php';

    use Google\Auth\AccessToken;
    use Google\Auth\HttpHandler\HttpClientCache;
    use Google\Auth\HttpHandler\HttpHandlerFactory;
    use Google\Auth\OAuth2;
    use GuzzleHttp\Psr7\Query;
    use GuzzleHttp\Psr7\Request;
    
    if (!class_exists('GoogleOAuthClient')) {

        class GoogleOAuthClient extends OAuth2 {

            public function createAuthUrl(array $config = []): string {
                return (string) $this->buildFullAuthorizationUri($config);
            }

            /**
             * Mirrors Google_Client::fetchAccessTokenWithAuthCode().
             *
             * @param string $code
             * @param callable|null $httpHandler
             * @return array<mixed>
             */
            public function fetchAccessTokenWithAuthCode($code, callable $httpHandler = null): array {
                $this->setCode($code);
                return $this->fetchAuthToken($httpHandler);
            }

            /**
             * Basic implementation compatible with Google_Client::revokeToken().
             *
             * @param string|array|null $token
             * @return bool
             */
            public function revokeToken($token = null): bool {
                $tokenToRevoke = $token ?? $this->getAccessToken();

                if (is_array($tokenToRevoke)) {
                    $tokenToRevoke = $tokenToRevoke['access_token'] ?? null;
                }

                if (!is_string($tokenToRevoke) || $tokenToRevoke === '') {
                    return false;
                }

                $httpHandler = HttpHandlerFactory::build(HttpClientCache::getHttpClient());
                $request = new Request(
                    'POST',
                    AccessToken::OAUTH2_REVOKE_URI,
                    ['Content-Type' => 'application/x-www-form-urlencoded'],
                    Query::build(['token' => $tokenToRevoke])
                );

                $response = $httpHandler($request);

                return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
            }

            /**
             * Fetches the user info profile using the current access token.
             *
             * @param callable|null $httpHandler
             * @return array<string,mixed>|null
             */
            public function fetchUserInfo(callable $httpHandler = null): ?array  {
                $accessToken = $this->getAccessToken();
                if (!is_string($accessToken) || $accessToken === '') {
                    return null;
                }

                $httpHandler = $httpHandler ?? HttpHandlerFactory::build(HttpClientCache::getHttpClient());
                $request = new Request(
                    'GET',
                    'https://www.googleapis.com/oauth2/v1/userinfo?alt=json',
                    ['Authorization' => 'Bearer ' . $accessToken]
                );

                $response = $httpHandler($request);
                if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                    return null;
                }

                $data = json_decode((string) $response->getBody(), true);
                return is_array($data) ? $data : null;
            }
        
        }

    }

    $client = null;
    $authUrl = null;

    $oauthConfig = [];
    if (isset(CFG::$vars['oauth']) && is_array(CFG::$vars['oauth'])) {
        $oauthConfig = CFG::$vars['oauth'];
    }

    $googleConfig = isset($oauthConfig['google']) && is_array($oauthConfig['google'])
        ? $oauthConfig['google']
        : [];

    $clientID = trim((string)($googleConfig['id'] ?? ''));
    $clientSecret = trim((string)($googleConfig['secret'] ?? ''));
    $host = isset($_SERVER['HTTP_HOST']) ? trim((string)$_SERVER['HTTP_HOST']) : '';

    if ($clientID === '' || $clientSecret === '' || $host === '') {
        return;
    }

    $redirectUri = CFG::$vars['proto'] . $host . '/login/auth/google';

    // New code using generic OAuth2 library
    $client = new GoogleOAuthClient([
      'clientId' => $clientID,
      'clientSecret' => $clientSecret,
      'authorizationUri' => 'https://accounts.google.com/o/oauth2/v2/auth',
      'redirectUri' => $redirectUri,
      'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
      'scope' => ['email', 'profile'],
    ]);

    $authUrl = $client->createAuthUrl();