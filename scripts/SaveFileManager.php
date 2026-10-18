<?php

class SaveFileManager implements BaseScript {

    private $count_savefile = 0;

    public function init() {
        MainController::setTitle("Save File Manager");
        MainController::addJavascript("savefilemanager/readsavefile.js");

        $bread[] = ["link" => "/", "text" => "Main Page"];

        if(ROUTE == "/account/savefiles" && IS_LOGGED_IN) {
            $bread[] = ["link" => "/", "text" => "Save File Manager"];
            $this->show();

        } else if(ROUTE == "/api/account/savefiles/upload" && IS_LOGGED_IN) {
            $this->handle_upload();

        }else if(ROUTE == "/api/account/savefiles/delete" && IS_LOGGED_IN) {
            $this->handle_delete();
        
        } else if(ROUTE == "/api/account/savefiles/setactive" && IS_LOGGED_IN) {
            $this->handle_setactive();
        
        } else if(ROUTE == "/api/account/savefiles/rename" && IS_LOGGED_IN) {
            $this->handle_rename();
        
        } else if(ROUTE == "/api/account/savefiles/updaterecords" && IS_LOGGED_IN) {
            $this->handle_update_records();
        
        } else if(ROUTE == "/api/account/savefiles/deleteimported" && IS_LOGGED_IN) {
            $this->handle_delete_imported();
        
        } else {
            if(ROUTE == "/account/savefiles") {
                header("Location: " . URL_HTBASE);
                exit();
            } else {
                echo_json(["error" => "you need to be logged in to do that!"]);
                exit();
            }
        }

        MainController::setBreadcrumb($bread);

    }

    private function show() {

        $active_savefile = $this->get_active_savefile()[0] ?? [];

        if(count($active_savefile) <= 0) {
            $cards = "<div class='w-100 h-100 d-flex flex-row align-items-center justify-content-center' style='padding-bottom: 3rem;'><span style='color: white;'>No Savefile Uploaded!</span></div>";
        } else {
            $cards = $this->render_infocards($active_savefile);
        }
        
        $Template = new Template();
        $Template->load_template("savefilemanager/overview.php");
        $Template->load_hash([
            "ACTIVE_SAVE_DATE" => !empty($active_savefile['uploaded_at']) ? date('m/d/Y \a\t H:i', strtotime($active_savefile['uploaded_at'])) : "N/A",
            "CARDS" => $cards,
            "UPLOADER" => $this->render_uploader(),
            "SAVEFILETABLE" => $this->render_savefiletable(),
            "ACTIVE_SAVE_ID" => $active_savefile['savefile_id'],
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    private function handle_delete_imported() {
        $DB = new Database();

        $query = "DELETE from levelrecords where user_id = :userid and auto_generated = 1";
        $binds = [
            "userid" => SESS_USERID
        ];

        $DB->query2($query, $binds);
        echo_json(['success' => "1"]);
    }
    private function handle_update_records() {
        if (SESS_USERID == 2) {
            echo_json(['error' => 'Import is disabled for this account.']);
            return;
        }

        $DB = new Database();

        $query = "DELETE from levelrecords where user_id = :userid and auto_generated = 1";
        $binds = [
            "userid" => SESS_USERID
        ];

        $DB->query2($query, $binds);

        $query = "INSERT INTO levelrecords (user_id, level_id, completed_at, attempts, auto_generated, progress)
                SELECT s.user_id,
                        e.key::int,
                        current_timestamp,
                        coalesce((e.value->>'attempts')::int, 0),
                        1,
                        coalesce((e.value->>'progress')::int, 0)
                FROM savefiles s
                CROSS JOIN LATERAL jsonb_each(s.leveldata::jsonb -> 'onlinelevels') e
                WHERE s.savefile_id = :savefile_id
                    AND s.user_id = :userid
                    AND EXISTS (SELECT 1 FROM levels l WHERE l.level_id = e.key::int)
                ON CONFLICT (user_id, level_id) DO UPDATE
                    SET attempts = EXCLUDED.attempts,
                        progress = GREATEST(levelrecords.progress, EXCLUDED.progress)
                    WHERE levelrecords.auto_generated = 1";

        $binds = [
            "savefile_id" => INS['savefile_id'],
            "userid"      => SESS_USERID,
        ];

        $DB->query2($query, $binds);

        echo_json(['success' => "1"]);
    }

    private function handle_rename() {
        $DB = new Database();

        $query = "UPDATE savefiles set savefile_name = :name where savefile_id = :savefile_id and user_id = :userid";
        $binds = [
            "name" => h(INS['value']),
            "savefile_id" => INS['savefile_id'],
            "userid" => SESS_USERID
        ];

        $DB->query2($query, $binds);
        echo_json(['success' => "1"]);
    }

    private function handle_setactive() {
        $DB = new Database();

        $querySetActive = "UPDATE savefiles set active = 1 where savefile_id = :savefile_id and user_id = :userid";
        $binds = [
            "savefile_id" => INS['savefile_id'],
            "userid" => SESS_USERID
        ];

        $querySetInactive = "UPDATE savefiles set active = 0 where active = 1 and user_id = :userid and savefile_id != :savefile_id";

        $DB->query2($querySetInactive, $binds);
        $DB->query2($querySetActive, $binds);

        echo_json(['success' => "1"]);
    }

    private function handle_delete() {
        $DB = new Database();

        $query = "DELETE from savefiles where user_id = :userid and savefile_id = :savefile_id";
        $binds = [
            "userid" => SESS_USERID,
            "savefile_id" => INS['savefile_id']
        ];

        $DB->query2($query, $binds);

        echo_json(['success' => "1"]);
    }

    private function handle_upload() {
        $DB = new Database();

        $playerdata_raw = INS['playerdata'] ?? [];
        $leveldata_raw = INS['leveldata'] ?? [];

        $query = "INSERT into savefiles (user_id, gd_uuid, uploaded_at, data_hash, leveldata, playerdata, savefile_name) values
                    (:userid, :gd_uuid, current_timestamp, :data_hash, :leveldata, :playerdata, 'Untitled')";

        $playerdata = json_decode($playerdata_raw, true);
        $leveldata = json_decode($leveldata_raw, true);

        $data_hash = md5(($playerdata_raw . $leveldata_raw));

        if($this->check_save_md5($data_hash)) {
            echo_json(['error' => "This Savefile was already uploaded!"]);
            return;
        }

        $binds = [
            "userid" => SESS_USERID,
            "gd_uuid" => $playerdata['player_userid'],
            "data_hash" => $data_hash,
            "leveldata" => $leveldata_raw,
            "playerdata" => $playerdata_raw
        ];

        $DB->query2($query, $binds);

        $query = "UPDATE savefiles set active = 0 where user_id = :userid";
        $binds = [
            "userid" => SESS_USERID
        ];

        $DB->query2($query, $binds);

        echo_json(["success" => "1"]);
    }

    private function check_save_md5($md5 = 'b') {
        $DB = new Database();

        $query = "SELECT 1 from savefiles where data_hash = :hash";
        $binds = [
            "hash" => $md5
        ];

        $DB->query2($query, $binds);

        return $DB->rows > 0;
    }

    private function render_infocards($active_savefile) {
        $names = [
            // "savefile_id" => "Savefile ID",
            // "user_id" => "User ID",
            "username" => "Username (GD)",
            "gd_uuid" => "Account ID (GD)",
            "attempts" => "Total Attempts",
            "levels_completed" => "Completed Levels",
            "savefile_name" => "Save File Name",
            "total_stars" => "Stars (All Levels)",
            "total_clicks" => "Clicks (All Levels)",
            "total_attempts" => "Attempts (All Levels)",
            "total_demons" => "Total Demons (All)",
            "total_extremes" => "Total Extreme Demons"
        ];

        $json_out_playerdata = json_decode($active_savefile['playerdata'] ?? '{}', true) ?: [];
        $json_out_leveldata  = json_decode($active_savefile['leveldata'] ?? '{}', true) ?: [];

        $total_extremes = 0;

        foreach ($json_out_leveldata['onlinelevels'] ?? [] as $level) {
            if ((int)($level['progress'] ?? 0) >= 100 && (int)($level['demon_type'] ?? 0) === 6) {
                $total_extremes++;
            }
        }

        $json_out_playerdata['total_extremes'] = $total_extremes;

        $active_savefile = array_merge($active_savefile, $json_out_playerdata);

        $rows = "";

        foreach ($names as $key => $title) {
            if (!array_key_exists($key, $active_savefile)) {
                continue;
            }

            $Template = new Template();
            $Template->load_template("savefilemanager/currentinfo_card.php");
            $Template->load_hash([
                "CARD_TITLE" => $title,
                "CARD_VALUE" => (string) $active_savefile[$key]
            ]);
            $Template->compile_template();

            $rows .= $Template->get_output();
        }

        return $rows;
    }

    private function render_uploader() {
        $Template = new Template();
        $Template->load_template("savefilemanager/uploader.php");
        $Template->load_hash([]);
        $Template->compile_template();
        return $Template->get_output();
    }

    private function render_savefiletable() {
        $Template = new Template();
        $Template->load_template("savefilemanager/savefiletable.php");
        $Template->load_hash([
            "ROWS" => $this->render_savefile_rows(),
            "SAVE_COUNT" => $this->count_savefile,
        ]);
        $Template->compile_template();

        return $Template->get_output();
    }

    private function render_savefile_rows() {
        $Template = new Template();
        
        $savefiles = $this->get_all_savefiles();

        $this->count_savefile = count($savefiles);

        if(count($savefiles) <= 0) {
            $Template->load_template("savefilemanager/savefiletable_row_empty.php");
            $Template->load_hash([]);
            $Template->compile_template();

            return $Template->get_output();
        }

        $firstchild = true;
        $rows = "";

        foreach($savefiles as $savefile) {

            $template_hash = [
                "SAVE_ID" => $savefile['savefile_id'],
                "SAVE_DATE" => $savefile['uploaded_at'],
                "STARS" => json_decode($savefile['playerdata'], true)['total_stars'] ?? "N/A",
                "DEMONS" => json_decode($savefile['playerdata'], true)['total_demons'] ?? "N/A",
                "USER_COINS" => json_decode($savefile['playerdata'], true)['total_user_coins'] ?? "N/A",
                "SAVE_NAME" => $savefile['savefile_name']
            ];

            if($firstchild) {
                $template_hash['ROW_CLASS'] = "is-active";
                $firstchild = false;

            } else {
                $template_hash['ROW_CLASS'] = "";
            }

            $Template->load_template("savefilemanager/savefiletable_row.php");
            $Template->load_hash($template_hash);
            $Template->compile_template();

            $rows .= $Template->get_output();
        }

        return $rows;
    }

    private function get_all_savefiles() {
        $DB = new Database();

        $query = "SELECT * from savefiles where user_id = :userid order by active desc, uploaded_at desc";
        $binds = [
            "userid" => SESS_USERID
        ];

        $DB->query2($query, $binds);
        return $DB->RSArray;
    }

    private function get_active_savefile() {
        $DB = new Database();

        $query = "SELECT * from savefiles where user_id = :userid order by active desc, uploaded_at desc fetch first 1 row only";
        $binds = [
            "userid" => SESS_USERID
        ];

        $DB->query2($query, $binds);
        return $DB->RSArray;
    }
}