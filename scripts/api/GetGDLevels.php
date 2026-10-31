<?php
class GetGDLevels implements BaseScript {
    private $keyMap = [
        // --- getGJLevels21 (+ downloadGJLevel22) ---
        1  => 'level_id',
        2  => 'level_name',
        3  => 'description_base64',
        5  => 'level_version',
        6  => 'creator_id',            // User-ID, nicht Account-ID → über den Creators-Block auflösen
        8  => 'difficultyDenominator', // 0 = N/A, 10 = Schwierigkeit gesetzt
        9  => 'level_difficulty',      // 0 unrated, 10 easy … 50 insane
        10 => 'downloads',
        12 => 'officialSong',
        13 => 'game_version',
        14 => 'likes',                 // likes - dislikes
        15 => 'level_length',          // 0 tiny … 4 XL, 5 Platformer
        17 => 'is_demon',
        18 => 'stars',
        19 => 'featured_score',
        25 => 'is_auto',
        30 => 'copied_id',             // 0 = Original
        31 => 'two_player_mode',
        35 => 'song_id',
        36 => 'extra_string',
        37 => 'coins',                 // Anzahl User-Coins
        38 => 'coins_verified',
        39 => 'stars_requested',
        42 => 'is_epic',               // 0 none, 1 epic, 2 legendary, 3 mythic
        43 => 'demon_difficulty',      // 3 easy, 4 medium, 0 hard, 5 insane, 6 extreme
        44 => 'is_gauntlet',
        45 => 'object_count',          // gecappt bei 65535
        46 => 'editor_time',           // in Suchergebnissen laut Doku nicht korrekt
        47 => 'editor_time_copies',    // dito
        54 => 'song_size',             // Bytes, Songs + SFX
        62 => 'upload_timestamp',
        63 => 'update_timestamp',
        65 => 'timely_id',             // nur bei type 32–34

        // --- nur downloadGJLevel22 ---
        4  => 'level_string',
        27 => 'password',              // XOR mit Key 26364
        28 => 'upload_date',           // relativ, z. B. "5 years"
        29 => 'update_date',
        40 => 'is_ldm',
        41 => 'daily_number',
        52 => 'song_ids',
        53 => 'sfx_ids',
        57 => 'verification_time',     // Frames bei 240 FPS
    ];
    
    private $mode = "";
    public function __construct() {
    }

    public function init() {

        $params = MainController::getRouteParam("level_id");

        if(str_contains(ROUTE, "/api/gdwrapper/levels")) {
            $this->request(array_merge(["level_id" => $params], INS));
        }
    }

    public function request($inHash) {
        $api = new RobApi();
        $api->set_request_method(Methods::POST);
        $api->set_rob_script("getGJLevels21.php");
        $api->set_keymap($this->keyMap);
        
        $api->start_request([
            "str" => (string)$inHash['level_id'],
            "star" => 1,
            "type" => 0,
            "secret" => "Wmfd2893gb7"
        ]);
        
        header("Content-Type: application/json");
        echo ($api->parse_api_response());

        // $response = $api->parse_api_response();
        // $response = json_decode($response, true);

        // $url = 'https://history.geometrydash.eu/api/v1/date/level/' . $inHash['id'];
        // $history_response = file_get_contents($url);

        // if($history_response === false) {
        //     throw new Exception("Error while fetching level History Data (upload time)");
        // }

        // $data = json_decode($history_response, true);
        // $data = $data['low']['estimation'];

        // $response = array_merge($response, ["upload_date" => $data]);

        // header("Content-Type: application/json");
        // echo json_encode($response, JSON_PRETTY_PRINT);
    }
}