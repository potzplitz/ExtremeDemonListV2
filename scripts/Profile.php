<?php

class Profile implements BaseScript {
    private $user_id = -1;
    public function init() {

        $bread[] = ["link" => "/", "text" => "Main Page"];

        if(ROUTE == "/account/profile" && IS_LOGGED_IN) {
            $this->user_id = SESS_USERID;
            MainController::setTitle("Your Profile");

            $bread[] = ["link" => "/", "text" => "Your Profile"];

            $this->show();

        } else if(str_contains(ROUTE, "/user/")) {
            $this->user_id = (int)h(MainController::getRouteParam("id"));
            $user = $this->resolve_user($this->user_id)['username'];
            
            MainController::setTitle($user);

            $bread[] = ["link" => "/", "text" => "Profile of " . $user];

            $this->show();

        } else {
            header("Location: " . URL_HTBASE);
        }

        $this->render_profile_icons();

        MainController::setBreadcrumb($bread);
    }

    private function show() {

        $user = $this->resolve_user($this->user_id);

        $Template = new Template();
        $Template->load_template("profile/overview.php");
        $Template->load_hash([
            "IMAGE" => URL_HTBASE . "api/profilepicture/" . SESS_USERID,
            "USERNAME" => $user['username'],
            "LASTLOG" => format_date($user['lastlog']),
            "ICONS" => $this->render_profile_icons(),
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    private function resolve_user($user_id) {
        $DB = new Database();

        $query = "SELECT * from users where user_id = :userid";
        $binds = [
            "userid" => $user_id
        ];
        $DB->query2($query, $binds);

        return $DB->RSArray[0];
    }

    private function render_profile_icons() {
        
        $savefile = get_active_savefile($this->user_id);

        $icons = json_decode($savefile[0]['playerdata'] ?? "{}", true);

        $colors = [
            $icons['player_color_1'] ?? 0,
            $icons['player_color_2'] ?? 0,
            $icons['player_glow'] ?? false,
        ];

        $icon_types = [
            "cube" => $icons['player_cube'] ?? 0,
            "ship" => $icons['player_ship'] ?? 0,
            "ball" => $icons['player_ball'] ?? 0,
            "ufo" => $icons['player_ufo'] ?? 0,
            "wave" => $icons['player_wave'] ?? 0,
            "robot" => $icons['player_robot'] ?? 0,
            "spider" => $icons['player_spider'] ?? 0,
            "swing" => $icons['player_swing'] ?? 0,
            "jetpack" => $icons['player_jetpack'] ?? 0,
        ];

        $Template = new Template();

        $rows = "";

        foreach ($icon_types as $key => $icon) {
            $url = "https://gdicon.oat.zone/icon.png?" . http_build_query([
                "type" => $key,
                "value" => $icon,
                "color1" => $colors[0],
                "color2" => $colors[1],
                "glow" => $colors[2] ? 'true' : 'false',
            ]);

            $Template->load_template("savefilemanager/user_icon.php");
            $Template->load_hash([
                "ICONURL" => $url
            ]);
            $Template->compile_template();

            $rows .= $Template->get_output();
        }
        
        return $rows;
    }
}