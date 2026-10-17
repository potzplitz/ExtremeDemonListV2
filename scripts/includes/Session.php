<?php

class Session {
    private function getSessionCookie(): ?string {
        return $_COOKIE['session'] ?? null;
    }

    private function getPersistentCookie(): ?string {
        return $_COOKIE['persistent'] ?? null;
    }

    public function load_session_data() {
        $DB = new Database();

        $session_id = $this->getSessionCookie() ?? -1;

        // check if a session cookie exists
        if($session_id == -1) { 
            // no session cookie found

            // check if user has a valid persistent session in cookie
            $query = "SELECT userid from sessions_persistent where lower(token_hash) = :hash";
            $binds = [
                "hash" => hash_hmac('sha256', $this->getPersistentCookie() ?? "a", getenv("HMAC_SECRET"), false)
            ];

            $DB->query2($query, $binds);

            // check if user has a valid persistent session
            if(count($DB->RSArray) < 1 || !$this->validate_persistent_session($DB->RSArray[0]['userid'] ?? -1)) { // invalid persistent session => finally log out user
                $this->set_session_constants(-1, null, null);
                return -1; 

            } else { // valid persistent session => renew/restore session and regenerate persistent session

                $this->login_user($DB->RSArray[0]['userid']);
                
                // user is logged in => return
                return;
            }
        }

        // user has an existing session cookie => probably not expired or hijack attempt

        // check if session is valid => expired yes no
        $query = "SELECT * from sessions where sess_ident = :sess_ident";
        $binds = [
            "sess_ident" => $session_id
        ];

        $DB->query2($query, $binds);

        // session expired check 
        if(strtotime($DB->RSArray[0]['expires'] ?? 0) < time()) {
            $this->invalidate_session($session_id); // make current session from cookie invalid
        }

        // execute statement again because session got altered (idk if this is necessary but it works so im keeping it ;_;)
        $DB->query2($query, $binds);

        // check if session in cookie is expired
        if((int)(empty($DB->RSArray[0]['valid']) ? 0 : $DB->RSArray[0]['valid']) == 0) { // session has expired

            // check if user has a valid persistent session in cookie
            $query = "SELECT userid from sessions_persistent where lower(token_hash) = :hash";
            $binds = [
                "hash" => hash_hmac('sha256', $this->getPersistentCookie() ?? "a", getenv("HMAC_SECRET"), false)
            ];

            $DB->query2($query, $binds);

            // validate persistent session cookie
            if(!$this->validate_persistent_session($DB->RSArray[0]['userid'] ?? -1)) { // invalid persistent session => finally log out user

                $this->set_session_constants(-1, null, null);

                return -1;
                
            } else { // valid persistent session => renew/restore session and regenerate persistent session

                $this->login_user($DB->RSArray[0]['userid']);
                
                // user is logged in => return
                return;
            }
        }

        // session in cookie is valid => no renewal or invalidating => all ok so login directly
        $this->set_session_constants($DB->RSArray[0]['userid'], $this->getSessionCookie(), $DB->RSArray[0]['sess_id']);

        // update user last login
        $query = "UPDATE users set lastlog = current_timestamp where user_id = :userid";
        $binds = [
            "userid" => $DB->RSArray[0]['userid']
        ];

        $DB->query2($query, $binds);
    }

    private function login_user($user_id) {

        $DB = new Database();

        $this->regenerate_persistent_session($user_id);
        $this->create_session($user_id);

        // fetch session data from current new session
        $query = "SELECT * from sessions where userid = :userid 
                    order by session_start desc fetch first 1 row only";

        $binds = [
            "userid" => $user_id
        ];
        
        $DB->query2($query, $binds);

        // load session data and set constants
        $this->set_session_constants($DB->RSArray[0]['userid'], $DB->RSArray[0]['sess_ident'], $DB->RSArray[0]['sess_id']);

        // update user last login
        $query = "UPDATE users set lastlog = current_timestamp where user_id = :userid";
        $binds = [
            "userid" => $user_id
        ];

        $DB->query2($query, $binds);
    }

    private function set_session_constants($userid, $ident, $id) {
        define("SESS_USERID", $userid);
        define("SESS_IDENT", $ident);
        define("SESS_ID", $id);
    }

    public function validate_persistent_session($userid) {
        $DB = new Database();

        $persistent = $this->getPersistentCookie() ?? null;
        
        if($persistent === null) {
            return false;
        }

        $query = "SELECT * from sessions_persistent where userid = :userid and valid = 1 
                    and expires_at > current_timestamp order by created_at desc fetch first 10 rows only";

        $binds = [
            "userid" => $userid
        ];

        $DB->query2($query, $binds);

        if(count($DB->RSArray) < 1) {
            return false;
        }

        // iterate through result set of 10 and check if persistent session is ok
        $expected = hash_hmac('sha256', $persistent, getenv("HMAC_SECRET"), false);
        foreach($DB->RSArray as $row) {
            if(hash_equals($expected, strtolower($row['token_hash']))) {
                return true;
            }
        }

        return false;
    }

    public function create_persistent_session($userid) { // gets called from outside this class (in login function in Account management class)
        $DB = new Database();

        // generate persistent session identity hashed to raw hex values
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash_hmac('sha256', $token, getenv("HMAC_SECRET"), false);

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

        // cookie valid for 1 year
        $time = new DateTime();
        $time->modify("+1 year");

        $timestamp = $time->getTimestamp();

        setcookie("persistent", $token, [
            "expires"  => $timestamp,
            "path"     => "/",
            "secure"   => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]);
    }

    private function regenerate_persistent_session($userid) {
        $DB = new Database();

        if(empty($this->getPersistentCookie())) {
            return;
        }

        // invalidate old persistent session
        $query = "UPDATE sessions_persistent set valid = 0 where token_hash = :token_hash and userid = :userid";
        $binds = [
            "token_hash" => hash_hmac('sha256', $this->getPersistentCookie() ?? "a", getenv("HMAC_SECRET"), false),
            "userid" => $userid
        ];

        $DB->query2($query, $binds);

        // unset old persistent cookie
        setcookie("persistent", "", [
            "expires"  => time() - 86400,
            "path"     => "/",
            "secure"   => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]);

        // generate and set new persistent cookie => write in cookie and DB
        $this->create_persistent_session($userid);
    }

    public function create_session($userid) {
        $DB = new Database();

        $sess_ident = bin2hex(random_bytes(64));

        $query = "INSERT into sessions (userid, session_start, sess_ident, expires, valid, referrer, agent) values
                    (:userid, current_timestamp, :sess_ident, current_timestamp + interval '1 day', 1, :referrer, :agent)";

        $binds = [
            "userid" => $userid,
            "sess_ident" => $sess_ident,
            "referrer" => "",
            "agent" => ""
        ];

        $DB->query2($query, $binds);

        setcookie("session", $sess_ident, [
            "expires"  => time() + 86400,
            "path"     => "/",
            "secure"   => true,
            "httponly" => true,
            "samesite" => "Strict"
        ]);
    }

    private function invalidate_session($session) {
        $DB = new Database();

        $query = "UPDATE sessions set valid = 0 where sess_ident = :sess_ident";
        $binds = [
            "sess_ident" => $session
        ];

        $DB->query2($query, $binds);
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
}