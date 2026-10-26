<?php

class Settings implements BaseScript {
    public function init() {
        if(ROUTE == "/account/settings" && IS_LOGGED_IN) {
            $this->show_settings();
        }
    }

    private function show_settings() {
        
    }
}