<?php

class RenewToken implements BaseScript {
    public function init() {
        $this->validate_token();
        $this->renew();
    }

    private function validate_token() {
        $body = INS;
        
        if(!isset($body['persistent'])) {
            $this->throw_api_error("Persistent Token is required!");
        }
    }

    private function renew() {
        $body = INS;
        $Session = new Session();
        $new = $Session->renew_token($body['persistent']);

        if(!$new) {
            echo_json(["error" => "Invalid Persistent Session!"]);
            return false;
        }

        echo_json($new);
    }

    private function throw_api_error($message) {
        echo_json(["error" => $message]);
        exit();
    }
}