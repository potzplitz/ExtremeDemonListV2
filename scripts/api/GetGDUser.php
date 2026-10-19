<?php
class GetGDUser implements BaseScript {
    private $keyMap = [ // GetGJUsers20.php
        1  => 'userName',
        2  => 'userID',
        3  => 'stars',
        4  => 'demons',
        6  => 'ranking',
        7  => 'accountHighlight',
        8  => 'creatorpoints',
        9  => 'iconID',
        10 => 'color',
        11 => 'color2',
        13 => 'secretCoins',
        14 => 'iconType',
        15 => 'special',
        16 => 'accountID',
        17 => 'usercoins',
        18 => 'messageState',
        19 => 'friendsState',
        20 => 'youTube',
        21 => 'accIcon',
        22 => 'accShip',
        23 => 'accBall',
        24 => 'accBird',
        25 => 'accDart',
        26 => 'accRobot',
        27 => 'accStreak',
        28 => 'accGlow',
        29 => 'isRegistered',
        30 => 'globalRank',
        31 => 'friendstate',
        38 => 'messages',
        39 => 'friendRequests',
        40 => 'newFriends',
        41 => 'NewFriendRequest',
        42 => 'age',
        43 => 'accSpider',
        44 => 'twitter',
        45 => 'twitch',
        46 => 'diamonds',
        48 => 'accExplosion',
        49 => 'modlevel',
        50 => 'commentHistoryState',
        51 => 'color3',
        52 => 'moons',
        53 => 'accSwing',
        54 => 'accJetpack',
        55 => 'demons_breakdown',
        56 => 'classicLevels',
        57 => 'platformerLevels'
    ];

    private $mode = "";
    public function __construct() {
    }

    public function init() {

        $params = MainController::getRouteParam("user_id");

        if(str_contains(ROUTE, "/api/gdwrapper/user")) {
            $this->request(array_merge(["user_id" => $params], INS));
        }
    }

    private function request($inHash) {
        header("Content-Type: application/json");

        // 1. Suche: liefert nur das Anzeige-Icon, aber die accountID
        $user = $this->rob_request("getGJUsers20.php", [
            "str" => (string)$inHash['user_id'],
        ])->parse_to_array();

        // Leer bei "-1" (nicht gefunden) und "0" (unregistrierter Spieler ohne Account)
        if (empty($user['accountID'])) {
            http_response_code(404);
            echo json_encode(["error" => "user not found"]);
            return;
        }

        // 2. Profil: liefert alle Icons
        echo $this->rob_request("getGJUserInfo20.php", [
            "targetAccountID" => $user['accountID'],
        ])->parse_api_response();
    }

    private function rob_request(string $script, array $data): RobApi {
        $api = new RobApi();
        $api->set_request_method(Methods::POST);
        $api->set_rob_script($script);
        $api->set_keymap($this->keyMap);
        $api->start_request($data + ["secret" => "Wmfd2893gb7"]);

        return $api;
    }
}