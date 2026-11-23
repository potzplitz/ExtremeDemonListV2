<?php

class Session {
    private const SESSION_LIFETIME = 86400;
    private const PERSISTENT_LIFETIME = 31536000;

    private function getSessionCookie(): ?string {
        // Gets Session Token from Cookie, Bearer or via Parameter
        return $this->sanitizeToken($_COOKIE['session'] ?? null)
            ?? $this->getBearerToken()
            ?? $this->sanitizeToken(INS['session'] ?? null);
    }

    private function getPersistentCookie(): ?string {
        // Gets Persistent Token from Cookie or via Parameter
        return $this->sanitizeToken($_COOKIE['persistent'] ?? null)
            ?? $this->sanitizeToken(INS['persistent'] ?? null);
    }

    private function getBearerToken(): ?string {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if(is_string($header) && preg_match('/^Bearer\s+(\S+)$/i', $header, $matches)) {
            return $this->sanitizeToken($matches[1]);
        }

        return null;
    }

    /**
     * SECURITY: Only accepts tokens in the format this class generates (hex strings).
     * Prevents arrays (e.g. ?session[]=x) causing TypeErrors and rejects junk input early.
     * @param mixed $token
     * @return string|null
     */
    private function sanitizeToken(mixed $token): ?string {
        if(!is_string($token) || $token === '' || strlen($token) > 256 || !ctype_xdigit($token)) {
            return null;
        }

        return $token;
    }

    private function hashToken(string $token): string {
        $secret = getenv('HMAC_SECRET');

        // SECURITY: never hash with an empty/missing key (would make the hashes trivially computable)
        if(!is_string($secret) || $secret === '') {
            throw new RuntimeException("HMAC_SECRET is not configured");
        }

        return hash_hmac('sha256', $token, $secret);
    }

    public function getDeviceType() {
        return mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device', 0, 255);
    }

    public function getReferrer() {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $parts = parse_url($_SERVER['HTTP_REFERER']);

            return $parts['host'] ?? null;

        } else {
            return "none";
        }
    }

    /**
     * Authenticates the User via Session and validates them.
     *  => Web Auth
     *  => ** ONLY CALL IN ROUTER, CENTRAL AUTH **
     * @return bool User/Session is valid/invalid
     */
    public function authenticate_user(): bool {

        // Checks if session is valid, if invalid, attempt renewal with persistent session
        if(!$this->validate_session($this->getSessionCookie())) {

            // Attempts renewal of Session with persistent session, if persistent validation fails, return false
            if(!$this->validate_persistent_session($this->getPersistentCookie())) {

                // invalidate Session, doesn't matter if it exists, just attempt invalidation
                $this->invalidate_session($this->getSessionCookie());

                $this->logout_user();
                return false;

            } else {

                // Persistent Session is valid => rotate persistent session and create a new session
                $new_persistent = $this->renew_persistent_session();

                // checks if the new persistent token is (in)valid
                if ($new_persistent === false) {
                    $this->logout_user();
                    return false;
                }

                // renews the session and returns the new session token
                $new_session = $this->renew_session($new_persistent['userid']);

                // checks if the session is valid, if no return false, if yes login user
                if(strlen($new_session) <= 0) {

                    // session is invalid, return false
                    $this->logout_user();
                    return false;
                } else {

                    // session is valid, login user
                    $success = $this->login_user($new_session);
                    return $success;
                }
            }
        } else {
            // Session valid, return true
            $success = $this->login_user($this->getSessionCookie());

            return $success;
        }
    }

    /**
     * Authenticates the User via Session and validates them.
     *  => API Auth
     *  => ** ONLY CALL IN ROUTER, CENTRAL AUTH **
     * @return bool User/Session is valid/invalid
     */
    public function authenticate_api_request(): bool {
        if(!$this->validate_session($this->getSessionCookie())) {
            $this->logout_user();
            return false;

        } else {
            // Session valid, return true
            $success = $this->login_user($this->getSessionCookie());

            return $success;
        }
    }

    /**
     * Renews the session and persistent token via API
     * @param string $persistent_token
     * @return array|false
     */
    public function renew_token(mixed $persistent_token): array|false {
        // SECURITY: reject non-hex / non-string input (e.g. arrays from the request body)
        $persistent_token = $this->sanitizeToken($persistent_token);

        if($persistent_token === null) {
            return false;
        }

        if(!$this->validate_persistent_session($persistent_token)) {
            return false;
        }

        $userid = $this->get_user_from_active_persistent_session($persistent_token);

        if($userid == -1) {
            return false;
        }

        // SECURITY: rotate exactly the token that was passed in (not whatever is in the cookie)
        $new_persistent = $this->renew_persistent_session($persistent_token);

        // SECURITY: token was already used by a concurrent request (or became invalid) => abort
        if($new_persistent === false || (int)$new_persistent['userid'] !== (int)$userid) {
            return false;
        }

        $new_session = $this->renew_session($userid);

        if(strlen($new_session) <= 0) {
            return false;
        }

        $this->invalidate_persistent_session($persistent_token);

        return [
            "userid" => $new_persistent['userid'],
            "new_persistent_token" => $new_persistent['token'],
            "new_session" => $new_session
        ];
    }

    /**
     * Creates a fresh new session and returns the ident
     * @param int $userid
     * @return string
     */
    public function create_session(int $userid): string {
        $DB = new Database();

        $sess_ident = bin2hex(random_bytes(64));

        $query = "INSERT into sessions (userid, session_start, sess_ident, expires, valid, referrer, agent) values
                    (:userid, current_timestamp, :sess_ident, current_timestamp + interval '1 day', 1, :referrer, :agent)";

        $binds = [
            "userid" => $userid,
            "sess_ident" => $sess_ident,
            "referrer" => $this->getReferrer(),
            "agent" => $this->getDeviceType()
        ];

        $DB->query2($query, $binds);

        setcookie("session", $sess_ident, [
            "expires"  => time() + self::SESSION_LIFETIME,
            "path"     => "/",
            "secure"   => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]);
        
        return $sess_ident;
    }

    /**
     * Creates a persistent session and returns the persistent ident
     * @param int $userid
     * @return string
     */
    public function create_persistent_session(int $userid):string { // gets called from outside this class (in login function in Account management class)
        $DB = new Database();

        // generate persistent session identity hashed to raw hex values
        $token = bin2hex(random_bytes(32));
        $tokenHash = $this->hashToken($token); // SECURITY: same HMAC, but with secret check

        // insert persistent session into DB
        $query = "INSERT into sessions_persistent (userid, expires_at, token_hash, created_at, valid, agent, referrer) values 
                    (:userid, current_timestamp + interval '1' year, :token_hash, current_timestamp, 1, :useragent, :referrer)";
        $binds = [
            "userid" => $userid,
            "token_hash" => $tokenHash,
            "useragent" => $this->getDeviceType(),
            "referrer" => $this->getReferrer()
        ];

        $DB->query2($query, $binds);

        setcookie("persistent", $token, [
            "expires"  => time() + self::PERSISTENT_LIFETIME,
            "path"     => "/",
            "secure"   => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]);

        return $token;
    }

    /**
     * Sets the session constants for a user to -1
     * @return void
     */
    private function logout_user(): void {

        // unset all cookies
        setcookie("session", "", [
            "expires" => time() - 3600,
            "path" => "/",
            "secure" => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]);

        setcookie("persistent", "", [
            "expires" => time() - 3600,
            "path" => "/",
            "secure" => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]);

        // set session constants to -1
        $this->set_session_constants(-1, -1, -1);
    }

    /**
     * SECURITY: Fails closed. If login fails, the user is logged out and the constants are set to -1
     * (otherwise SESS_USERID stays undefined and every request with a session of a deleted user crashes).
     * @return bool always false
     */
    private function fail_login(): bool {
        $this->logout_user();
        return false;
    }

    /**
     * Logs in user if all checks succeeded.
     * Sets the global constants and loads other user data
     * @return bool
     */
    private function login_user(string $session): bool {
        $DB = new Database();

        // get userid from newly generated session
        $user = $this->get_userid_from_session($session);

        // check if user is valid
        if($user == -1) {
            return $this->fail_login();
        }

        // update user last login
        $query = "UPDATE users set lastlog = current_timestamp where user_id = :userid";
        $binds = [
            "userid" => $user
        ];

        $DB->query2($query, $binds);

        // get the session id based on the ident
        $id = $this->translate_session_to_id($session);

        // SECURITY: session vanished between checks => do not log in with SESS_ID -1
        if($id == -1) {
            return $this->fail_login();
        }

        $this->set_session_constants($user, $session, $id);

        return true;
    }
    
    /**
     * Translates the session identity to the session id ignoring the user
     * @param mixed $session
     * @return int
     */
    private function translate_session_to_id($session): int {
        $DB = new Database();

        $query = "SELECT sess_id from sessions where sess_ident = :ident";
        $binds = [
            "ident" => $session
        ];

        $DB->query2($query, $binds);

        if($DB->rows <= 0) {
            return -1;
        }

        $id = $DB->RSArray[0]['sess_id'];

        return $id;
    }

    /**
     * Sets the Session Constants used globally in the codebase
     * @param mixed $userid
     * @param mixed $ident
     * @param mixed $id
     * @return void
     */
    private function set_session_constants($userid, $ident, $id): void {
        // SECURITY: constants can only be set once per request; never overwrite / warn on redefinition
        if(defined("SESS_USERID")) {
            return;
        }

        define("SESS_USERID", $userid);
        define("SESS_IDENT", $ident);
        define("SESS_ID", $id);
    }

    /**
     * Returns the userID from current active session
     * @param string $session
     * @return int
     */
    private function get_userid_from_session(string $session): int {
        $DB = new Database();

        $query = "SELECT userid from sessions where sess_ident = :session and valid = 1 and expires > current_timestamp";
        $binds = [
            "session" => $session
        ];

        $DB->query2($query, $binds);

        if($DB->rows <= 0) {
            return -1;
        }

        $user = $DB->RSArray[0]['userid'];

        if(!$this->validate_user($user)) {
            return -1;
        }

        return (int)$user;
    }

    /**
     * Validates the current Session in request
     * @param string $session
     * @return bool true/false => Session valid/invalid
     */
    private function validate_session(?string $session): bool {
        $DB = new Database();

        if($session == -1) {
            return false;
        }

        if (empty($session)) {
            return false;
        }
        
        // Checks if session is valid
        $query = "SELECT 1 as valid from sessions where sess_ident = :sess_id and expires > current_timestamp and valid = 1";
        $binds = [
            "sess_id" => $session
        ];

        $DB->query2($query, $binds);

        // check if the session exists
        if($DB->rows <= 0) {
            return false;
        }

        // If session is invalid, invalidate it and return false
        if($DB->RSArray[0]['valid'] != "1") {
            return false;
        }

        return true;
    }

    /**
     * Attempts renewal of the session with persistent session
     * @return string
     */
    private function renew_session(int $userid): string {
        $DB = new Database();

        // Gets Cookie values 
        $persistent_token = $this->getPersistentCookie();
        $session_token = $this->getSessionCookie();

        // If a token is invalid, return false
        if (empty($persistent_token)) {
            return "";
        }

        // Generate new random session ID
        $new_session = bin2hex(random_bytes(64));

        // Insert new Session into Database
        $query = "INSERT into sessions (userid, session_start, sess_ident, expires, valid, referrer, agent) values
            (:userid, current_timestamp, :sess_ident, current_timestamp + interval '1 day', 1, :referrer, :agent)";

        if(!$userid) {
            return "";
        }

        $binds = [
            "userid" => $userid,
            "sess_ident" => $new_session,
            "referrer" => $this->getReferrer(),
            "agent" => $this->getDeviceType(),
        ];

        $DB->query2($query, $binds);

        // Sets the new Session Cookie
        setcookie("session", $new_session, [
            "expires"  => time() + self::SESSION_LIFETIME,
            "path"     => "/",
            "secure"   => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]);

        // Invalidate old session
        if (!empty($session_token)) {
            $this->invalidate_session($session_token);
        }

        return $new_session;
    }

    /**
     * Returns the UserID from the current persistent session
     * @param string $active_persistent
     * @return int
     */
    private function get_user_from_active_persistent_session(string $active_persistent): int {
        $DB = new Database();

        // SECURITY: also check expiry here (was missing)
        $query = "SELECT userid from sessions_persistent where token_hash = :hash and valid = 1 and expires_at > current_timestamp";
        $binds = [
            "hash" => $this->hashToken($active_persistent)
        ];

        $DB->query2($query, $binds);

        if($DB->rows <= 0) {
            return -1;
        }

        return (int)$DB->RSArray[0]['userid'];
    }

    /**
     * SECURITY: Atomically consumes (invalidates) a persistent token and returns its userid.
     * Only ONE request can ever consume a token => no double use on parallel requests (race condition).
     * @param string $persistent
     * @return int|false
     */
    private function consume_persistent_session(string $persistent): int|false {
        $DB = new Database();

        $query = "UPDATE sessions_persistent set valid = 0
                    where token_hash = :hash and valid = 1 and expires_at > current_timestamp
                    RETURNING userid";
        $binds = [
            "hash" => $this->hashToken($persistent)
        ];

        $DB->query2($query, $binds);

        if($DB->rows <= 0) {
            return false;
        }

        $user_id = (int)$DB->RSArray[0]['userid'];

        if(!$this->validate_user($user_id)) {
            return false;
        }

        return $user_id;
    }

    /**
     * Returns the UserID from the old persistent session
     * @param string $old_persistent_session
     * @return mixed
     */
    private function get_user_from_old_persistent_session(string $old_persistent_session): mixed {
        $DB = new Database();

        $query = "SELECT userid from sessions_persistent where token_hash = :hash and valid = 1 and expires_at > current_timestamp";
        $binds = [
            "hash" => $this->hashToken($old_persistent_session)
        ];

        $DB->query2($query, $binds);

        if($DB->rows <= 0) {
            return false;
        }

        $user_id = $DB->RSArray[0]['userid'];

        if(!$this->validate_user($user_id)) {
            return false;
        }

        return $user_id;
    }

    /**
     * Renewal of the persistent session and rotates the tokens
     * @return array|bool
     */
    private function renew_persistent_session(?string $old_persistent = null): array|false {
        $DB = new Database();

        // SECURITY: explicitly passed token wins (API), otherwise cookie/parameter as before
        $old_persistent = $old_persistent ?? $this->getPersistentCookie();

        if(empty($old_persistent)) {
            return false;
        }

        // SECURITY: validate AND invalidate the old token in one atomic statement (prevents token reuse)
        $userid = $this->consume_persistent_session($old_persistent);

        if(!$userid) {
            return false;
        }

        // Generate new persistent token and hash it
        $token = bin2hex(random_bytes(32));
        $tokenhash = $this->hashToken($token);
        
        // insert persistent session into DB
        $query = "INSERT into sessions_persistent (userid, expires_at, token_hash, created_at, valid, agent, referrer) values 
                    (:userid, current_timestamp + interval '1 year', :token_hash, current_timestamp, 1, :useragent, :referrer)";
        $binds = [
            "userid" => $userid,
            "token_hash" => $tokenhash,
            "useragent" => $this->getDeviceType(),
            "referrer" => $this->getReferrer(),
        ];

        $DB->query2($query, $binds);

        // old persistent sess was already invalidated atomically in consume_persistent_session()

        // set new persistent cookie
        setcookie("persistent", $token, [
            "expires" => time() + self::PERSISTENT_LIFETIME,
            "path"     => "/",
            "secure"   => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]);

        return [
            "token" => $token,
            "userid" => $userid
        ];
    }

    /**
     * Validates the current Persistent Session in request
     * @param string $persistent_session
     * @return bool true/false => Persistent Session valid/invalid
     */
    private function validate_persistent_session(?string $persistent_session): bool {
        $DB = new Database();

        if(empty($persistent_session)) {
            return false;
        }
        
        // Validates persistent session
        $query = "SELECT userid from sessions_persistent where token_hash = :tokenhash and valid = 1 and expires_at > current_timestamp";
        $binds = [
            "tokenhash" => $this->hashToken($persistent_session)
        ];

        $DB->query2($query, $binds);

        // if no rows were returned, it's no valid persistent session, so return false. 
        if($DB->rows <= 0) {
            return false;
        }

        $user = $DB->RSArray[0]['userid'];

        // if user for this persistent session was not found or got deleted, return false.
        if(!$this->validate_user($user)) {
            return false;
        }

        // if user and session was found, return true.
        return true;
    }

    /**
     * Validates if User exists in Database
     * @param int $user_id
     * @return bool true/false => User exists true/false
     */
    private function validate_user($user_id): bool {
        $User = new User($user_id);
        $usr = $User->load_user();

        return !($usr['userid'] == -1);
    }

    /**
     * Invalidates a session based on the given session ident
     * @param string $session
     * @return void
     */
    private function invalidate_session(?string $session):void {
        $DB = new Database();

        // nothing to invalidate
        if(empty($session)) {
            return;
        }

        // Invalidate
        $query = "UPDATE sessions set valid = 0 where sess_ident = :ident";
        $binds = [
            "ident" => $session
        ];

        $DB->query2($query, $binds);
    }

    /**
     * Invalidates a persistent session based on the given persistent sess ident
     * @param mixed $persistent
     * @return void
     */
    private function invalidate_persistent_session($persistent): void {
        $DB = new Database();

        // nothing to invalidate (also avoids TypeError in hashToken on null/array)
        if(!is_string($persistent) || $persistent === '') {
            return;
        }

        $query = "UPDATE sessions_persistent set valid = 0 where token_hash = :hash";
        $binds = [
            "hash" => $this->hashToken($persistent)
        ];

        $DB->query2($query, $binds);
    }
}