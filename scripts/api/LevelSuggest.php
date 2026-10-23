<?php

class LevelSuggest implements BaseScript {
    public function init() {
        $this->fetch_levels();
    }

    private function fetch_levels() {
        $DB = new Database();

        $query  = "SELECT level_id, levelname, levelname_raw, position from levels where (lower(levelname) like :q or lower(levelname_raw) like :q) and sart = :sart order by position asc";
        $binds = [
            "q" => "" . INS['q'] . "%",
            "sart" => INS['sart'],
        ];

        $DB->query2($query, $binds);

        header("Content-Type: application/json");
        echo(json_encode($DB->RSArray, JSON_PRETTY_PRINT));
    }
}