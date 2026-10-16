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
            $query = "DELETE from levelrecords where user_id = :user_id and level_id = :level_id";
            $DB->query2($query, $binds);

            echo_json(['success' => '1', "action" => "delete"]);

        } else {
            $query = "INSERT into levelrecords (user_id, level_id, completed_at, attempts) values (:user_id, :level_id, current_timestamp, :attempts)";
            $binds = [
                "user_id" => SESS_USERID,
                "level_id" => INS['level_id'],
                "attempts" => INS['attempts'],
            ];

            $DB->query2($query, $binds);

            echo_json(['success' => '1', "action" => "submit"]);
        }
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