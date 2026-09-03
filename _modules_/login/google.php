<?php

    if (!class_exists('GoogleOAuthClient')) {

        class GoogleOAuthClient {
            private $clientId;
            private $clientSecret;
            private $redirectUri;
            private $authorizationUri;
            private $tokenCredentialUri;
            private $scope;
            private $accessToken;

            public function __construct(array $config) {
                $this->clientId = $config['clientId'] ?? '';
                $this->clientSecret = $config['clientSecret'] ?? '';
                $this->redirectUri = $config['redirectUri'] ?? '';
                $this->authorizationUri = $config['authorizationUri'] ?? 'https://accounts.google.com/o/oauth2/v2/auth';
                $this->tokenCredentialUri = $config['tokenCredentialUri'] ?? 'https://oauth2.googleapis.com/token';
                $this->scope = $config['scope'] ?? ['email', 'profile'];
            }

            public function createAuthUrl(array $config = []): string {
                $params = [
                    'client_id' => $this->clientId,
                    'redirect_uri' => $this->redirectUri,
                    'response_type' => 'code',
                    'scope' => implode(' ', $this->scope),
                    'access_type' => 'offline',
                    'prompt' => 'select_account'
                ];
                return $this->authorizationUri . '?' . http_build_query($params);
            }

            /**
             * Mirrors Google_Client::fetchAccessTokenWithAuthCode().
             *
             * @param string $code
             * @param callable|null $httpHandler
             * @return array<mixed>
             */
            public function fetchAccessTokenWithAuthCode($code, callable $httpHandler = null): array {
                $params = [
                    'code' => $code,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $this->redirectUri,
                    'grant_type' => 'authorization_code'
                ];

                $ch = curl_init($this->tokenCredentialUri);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/x-www-form-urlencoded'
                ]);

                $response = curl_exec($ch);
                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($status < 200 || $status >= 300) {
                    return ['error' => 'HTTP status ' . $status, 'details' => $response];
                }

                $data = json_decode($response, true);
                if (isset($data['access_token'])) {
                    $this->accessToken = $data['access_token'];
                }
                return $data ?? [];
            }

            public function setAccessToken($token) {
                if (is_array($token)) {
                    $this->accessToken = $token['access_token'] ?? null;
                } else {
                    $this->accessToken = $token;
                }
            }

            public function getAccessToken() {
                return $this->accessToken;
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

                $ch = curl_init('https://oauth2.googleapis.com/revoke');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['token' => $tokenToRevoke]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/x-www-form-urlencoded'
                ]);

                $response = curl_exec($ch);
                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                return $status >= 200 && $status < 300;
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

                $ch = curl_init('https://www.googleapis.com/oauth2/v1/userinfo?alt=json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $accessToken
                ]);

                $response = curl_exec($ch);
                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($status < 200 || $status >= 300) {
                    return null;
                }

                $data = json_decode($response, true);
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