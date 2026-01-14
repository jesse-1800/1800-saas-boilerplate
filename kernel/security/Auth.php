<?php

use App\Models\IDProviderPartnerUsers;
use Auth0\SDK\Auth0;
use GuzzleHttp\Client;
use App\Models\Environment;
use App\Models\PartnerUsers;
use GuzzleHttp\Exception\RequestException;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\InvalidTokenException;
use Kernel\Database\Connection;

class Auth
{
    private $clientId     = 'nfdFy4cNGHfgzh4iTJRRDJPOCwV3opSv';
    private $audience     = 'https://dev-yhwk604x3s4wxknu.us.auth0.com/api/v2/';
    private $clientSecret = 'd_HgAbWGt4_XygEg1UmFde08HTxzVS1oEO9rYodlZi8czPT3rJcY1gUWNsbUoG7m';
    private $domain       = null;
    private $accessToken  = null;

    public function __construct()
    {
        $environment = new Environment();
        $this->domain = $environment->get('VITE_AUTH0_DOMAIN');
    }

    /**
     * CRUD operations for Management API
     **/
    private function auth0_request($method, $endpoint, $options = [])
    {
        try {
            if ($this->accessToken) {
                $access_token = $this->accessToken;
            }
            else {
                $client = new Client();
                $tokenResponse = $client->post("https://{$this->domain}/oauth/token", [
                    'form_params' => [
                        'grant_type'    => 'client_credentials',
                        'client_id'     => $this->clientId,
                        'client_secret' => $this->clientSecret,
                        'audience'      => $this->audience,
                    ]
                ]);
                $tokenData = json_decode($tokenResponse->getBody()->getContents(), true);
                $this->accessToken = $tokenData['access_token'];
                $access_token = $tokenData['access_token'];
            }

            $method = strtoupper($method);
            $endpoint = ltrim($endpoint, '/');
            $client = new Client();
            $params = array_merge([
                'headers' => [
                    'Authorization' => "Bearer $access_token",
                    'Content-Type' => 'application/json'
                ],
            ], $options);
            $response = $client->request($method,"https://$this->domain/$endpoint", $params);

            return json_decode($response->getBody()->getContents(), true);
        }
        catch (RequestException $e) {
            $responseBody = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null;
            return die(
                json([
                    'result' => false,
                    'message' => $responseBody['message'] ?? $e->getMessage(),
                    'response' => $responseBody,
                ])
            );
        }
    }

    /**
     * This function uses Proposals @ 1800 Office Solutions (Backend)
     * That's why it uses a different $clientId,$audience,$clientSecret
     * This returns JSON response on fail, and php TRUE on success
     */
    public function isLoggedIn()
    {
        // Check if already authenticated in this session
        if (Session::get('auth0_authenticated') === true && Session::get('auth0_user_id')) {
            $tokenExp = Session::get('auth0_token_exp');

            // If token is expired, clear session and fall through to validation
            if ($tokenExp && $tokenExp < time()) {
                Session::set('auth0_authenticated', null);
                Session::set('auth0_user_id', null);
                Session::set('auth0_token_exp', null);
                // Don't return here - fall through to token validation below
            } else {
                // Token is still valid
                return true;
            }
        }

        $config = new SdkConfiguration([
            'strategy'       => SdkConfiguration::STRATEGY_API,
            'domain'         => $this->domain,
            'clientId'       => 'lzPcWFqoVq6026sAYt7xcaRI8CSL86Af',
            'clientSecret'   => 'F8qar3qP0fqxwHJs3ML2pCsICWcGtCIcfbqzfWoYXmfZshL-CbS8A6cJ8M0SAIWn',
            'audience'       => ['https://my-php-validation-identifier.com'],
            'tokenAlgorithm' => 'RS256',
        ]);
        $token = $this->extractToken();
        $auth0 = new Auth0($config);
        try {
            $decodedToken = $auth0->decode($token);
            $user = (object)$decodedToken->toArray();
            Session::set('auth0_authenticated', true);
            Session::set('auth0_user_id', $user->sub);
            Session::set('auth0_token_exp', $user->exp ?? null);
            return true;
        }
        catch (InvalidTokenException $e) {
            http_response_code(401);
            Session::set('auth0_authenticated', null);
            Session::set('auth0_user_id', null);
            Session::set('auth0_token_exp', null);
            return json([
                'result' => false,
                'message' => 'Unauthorized: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * CRUD operations for Auth0 users uses Management API
     * so it uses a different $clientId,$audience,$clientSecret
     */
    public function fetch_users($bypass_auth = false, $return_raw = false)
    {
        // Bypassable via function call (not via route)
        if (!$bypass_auth) { $this->isLoggedIn(); }

        $auth0_users = $this->auth0_request("get","/api/v2/users");
        $allowed_users = array();

        // Find and bind roles from Partner Users profile
        foreach ($auth0_users as $i => $auth0_user) {
            $find_user = (new IDProviderPartnerUsers)::where('user_id',$auth0_user['user_id'])->first();

            // Only return users with explorer access
            if ($find_user && $find_user->explorer_access == 1) {
                $auth0_user['role'] = $find_user?->role ?? "User";
                if (!isset($auth0_user['blocked'])) {
                    $auth0_user['blocked'] = false;
                }
                $allowed_users[] = $auth0_user;
            }
        }
        Connection::initialize('default');
        return $return_raw ? $allowed_users : json($allowed_users);
    }

    /**
     * Returns single user role
     */
    public function get_user_role($user_id)
    {
        $role = $this->auth0_request("get","/api/v2/users/$user_id/roles");
        if (isset($role[0]['name'])) {
            return $role[0]['name'];
        }
        return ("");
    }

    /**
     * Create a new user in Auth0
     * uses Management API
     *
     * partner_id,
     * username,
     * password,
     * firstname,
     * lastname,
     * role_id
     */
    public function create_user($user_info)
    {
        try {
            $user_data = [
                'email' => $user_info->username,
                'connection' => 'Username-Password-Authentication',
            ];
            if ($user_info->password) {
                $user_data['password'] = $user_info->password;
            }
            if ($user_info->firstname || $user_info->lastname) {
                $user_data['name'] = trim("$user_info->firstname $user_info->lastname");
                $user_data['user_metadata'] = [
                    'first_name' => $user_info->firstname,
                    'last_name'  => $user_info->lastname,
                ];
            }

            // Insert user to Auth0
            $user = $this->auth0_request("post","/api/v2/users", ['body' => json_encode($user_data)]);

            // Assign role to user
            if (isset($user['user_id'])) {
                $this->auth0_request(
                    "post","/api/v2/users/{$user['user_id']}/roles",
                    ['json' => ['roles' => [$user_info->role_id]]]
                );

                // Link the user to a partner
                PartnerUsers::insert([
                    'user_id'    => $user['user_id'],
                    'partner_id' => $user_info->partner_id,
                    'email_signature' => "",
                ]);

                // Purge users session when a user is added
                Session::remove('auth0_users');

                return json([
                    'result'  => true,
                    'message' => 'User successfully added!',
                    'profile' => (object) $user,
                ]);
            }
            else {
                return json([
                    'result'  => false,
                    'message' => 'Error: cannot create user at this time.'
                ]);
            }
        }
        catch (\Exception $e) {
            http_response_code(500);
            return json([
                'result' => false,
                'message' => 'Auth0 Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a user in Auth0
     */
    public function delete_user(string $user_id)
    {
        $user_id = rawurlencode($user_id);

        // Purge users session when a user is added
        Session::remove('auth0_users');

        return $this->auth0_request("delete","/api/v2/users/$user_id");
    }

    /**
     * Block or unblock a user in Auth0
     */
    public function set_user_blocked(string $user_id, bool $blocked = true)
    {
        $user_id = rawurlencode($user_id);
        return $this->auth0_request(
            "patch",
            "/api/v2/users/$user_id",
            ['json' => ['blocked' => $blocked]]
        );
    }

    /**
     * It just extracts the frontend token from headers
     * nothing special here.
     */
    private function extractToken(): string
    {
        $header = $_SERVER['HTTP_X_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            exit('Unauthorized: Missing or invalid token');
        }
        return $matches[1];
    }

    /**
     * Fetch all roles from Auth0
     */
    public function fetch_roles()
    {
        if (Session::get('auth0_roles')) {
            return json_decode(Session::get('auth0_roles'), true);
        }
        $fetch_roles = $this->auth0_request("get","/api/v2/roles");
        Session::set('auth0_roles', json_encode($fetch_roles));
        return $fetch_roles;
    }

    /**
     * Fetch single user info
     */
    public function fetch_user_info($user_id)
    {
        $user_info = $this->auth0_request("get","/api/v2/users/$user_id");
        $user_role = $this->get_user_role($user_id);
        $user_info['role'] = $user_role;
        return $user_info;
    }

    /**
     * Update user profile
     */
    public function update_profile(object $profile)
    {
        $user_id = rawurlencode($profile->user_id);

        // Auth0 prevents updating password and email at the same time
        if (!empty($profile->new_password)) {
            $data = array(
                'password' => $profile->new_password
            );
        }

        // The rest can be updated together
        else {
            $data = array(
                'name' => trim("$profile->firstname $profile->lastname"),
                'email' => $profile->email,
                'user_metadata' => [
                    'first_name' => $profile->firstname,
                    'last_name'  => $profile->lastname,
                ],
            );
            if (!empty($profile->picture)) {
                $data['picture'] = $profile->picture;
            }
        }

        // Purge users session when a user is added
        Session::remove('auth0_users');

        return $this->auth0_request("patch", "/api/v2/users/$user_id", ['json' => $data]);
    }
}