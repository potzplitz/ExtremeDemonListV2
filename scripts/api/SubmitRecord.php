<?php

class SubmitRecord implements BaseScript {
    public function init() {
        $this->validate_user();
        $this->validate_inputs();
        $this->submit_level();
    }
    private function submit_level() {
        $DB = new Database();

        $query = "SELECT 1 from levelrecords where user_id = :user_id and level_id = :level_id";
        $binds = [
            "user_id" => SESS_USERID,
            "level_id" => INS['level_id']
        ];

        $DB->query2($query, $binds);

        if($DB->rows > 0) {
            $queryExists = "SELECT 1 from levelrecords where user_id = :user_id and level_id = :level_id and auto_generated = 1";
            
            if($DB->query2($queryExists, $binds) && $DB->rows > 0) { // auto generated record has to be overwritten
                $this->delete_level(SESS_USERID, INS['level_id'], 1);
                $this->insert_level();

                echo_json(['success' => '1', "action" => "submit"]);

            } else {
                $this->delete_level(SESS_USERID, INS['level_id'], 0);

                echo_json(['success' => '1', "action" => "delete"]);
            }

        } else {
            $this->insert_level();

            echo_json(['success' => '1', "action" => "submit"]);
        }
    }

    private function delete_level($user_id, $level_id, $auto_generated) {

        $DB = new Database();

        $binds = [
            "user_id" => $user_id,
            "level_id" => $level_id
        ];

        $query = "DELETE from levelrecords where user_id = :user_id and level_id = :level_id";
        $DB->query2($query, $binds);

        
    }

    private function insert_level() {
        $DB = new Database();

        $query = "INSERT into levelrecords (user_id, level_id, completed_at, attempts, auto_generated, progress) values (:user_id, :level_id, current_timestamp, :attempts, 0, 100)";
        $binds = [
            "user_id" => SESS_USERID,
            "level_id" => INS['level_id'],
            "attempts" => INS['attempts'],
        ];

        $DB->query2($query, $binds);

        
    }

    private function validate_user() {
        if(!IS_LOGGED_IN) {
            echo_json(['error' => "You have to be logged in to do that!"]);
            exit();
        }
    }

    private function validate_inputs() {
        $levelId = filter_var(INS['level_id'] ?? null, FILTER_VALIDATE_INT);
        $attempts = filter_var(INS['attempts'] ?? null, FILTER_VALIDATE_INT);

        if ($levelId === false || $levelId === null || $levelId <= 0) {
            echo_json(['error' => 'Invalid level ID']);
            exit();
        }

        if ($attempts === false || $attempts === null || $attempts < 0) {
            echo_json(['error' => 'Invalid attempts']);
            exit();
        }
    }
}