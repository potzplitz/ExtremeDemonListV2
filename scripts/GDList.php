<?php

enum ListType {
    case AREDL;
    case NCL;
}

class GDList implements BaseScript {
    private $listtype;
    private $result_count = 0;
    private $result_time = 0;
    private $active_completed = 0;
    private $active_uncompleted = 0;
    private $page = 0;
    private $per_page = 100;
    private $max_page = 0;
    public function init() {

        $bread[] = ["link" => "/", "text" => "Main Page"];
        
        MainController::addJavascript("gdlist/submit_record.js");

        switch(ROUTE) {
            case "/extremedemons":
                $this->listtype = ListType::AREDL;
                MainController::setTitle("Extreme Demon List");
                $bread[] = ["link" => "#", "text" => "Extreme Demon List"];
                break;
            case "/ninecircles":
                $this->listtype = ListType::NCL;
                MainController::setTitle("Nine Circles List");
                $bread[] = ["link" => "#", "text" => "Nine Circles List"];
                break;
            default:
                exit("error");
        }

        if(!empty(INS['q'])) {
            $bread[] = ["link" => "#", "text" => ('Results for "' . h(INS['q']) . '"')];
        }

        MainController::setBreadcrumb($bread);

        $this->active_completed   = (int)(INS['completed'] ?? 0) === 1 ? 1 : 0;
        $this->active_uncompleted = (int)(INS['uncompleted'] ?? 0) === 1 ? 1 : 0;
        $this->page = (int)(INS['page'] ?? 1);

        switch (INS['toggle'] ?? null) {
            case 'completed':
                $this->active_completed = 1 - $this->active_completed;
                if ($this->active_completed) $this->active_uncompleted = 0;
                $this->page = 1;
                break;
            case 'uncompleted':
                $this->active_uncompleted = 1 - $this->active_uncompleted;
                if ($this->active_uncompleted) $this->active_completed = 0;
                $this->page = 1;
                break;
        }

        if(isset(INS['prev'])) {
            $this->page--;

        } else if(isset(INS['next'])) {
            $this->page++;

        }

        $this->show();
    }

    private function show() {

        $Template = new Template();
        $Template->load_template("gdlist/list.php");
        $Template->load_hash([
            "ROWS" => $this->render_levels(),
            "RESULTS" => $this->result_count,
            "ACTIVE_COMPLETED" => $this->active_completed,
            "ACTIVE_CLASS" => ($this->active_completed == 1 ? "gdList__controls__active" : ""),
            "ACTIVE_UNCOMPLETED" => $this->active_uncompleted,
            "ACTIVE_CLASS_UNCOMPLETED" => ($this->active_uncompleted == 1 ? "gdList__controls__active" : ""),
            "SEARCHTERM" => h(INS['q'] ?? ''),
            "PAGE" => $this->page,
            "NEXT_PAGE" => ($this->page + 1),
            "PREV_PAGE" => max(1, $this->page - 1),
            "LIST_URL" => ($this->listtype == ListType::NCL ? "ninecircles" : "extremedemons"),
            "NOT_FOUND" => ($this->result_count <= 0 ? "Level was not found!" : ""),
            "DISP_NOT_FOUND" => ($this->result_count <= 0 ? "" : "hiddenIp"),
            "RESULT_INFOS" => $this->result_count . " Levels found (" . $this->result_time . "s.)",
            "NOT_LOGGED_IN" => (IS_LOGGED_IN ? '' : 'hiddenIp'),
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    private function render_levels() {

        $level = $this->get_levels();

        $rows = "";

        foreach($level as $elem) {
            $Template = new Template();
            $Template->load_template("gdlist/level_row.php");
            $Template->load_hash([
                "LEVELNAME" => $elem['levelname'],
                "LEVEL_ID" => $elem['level_id'],
                "POSITION" => $elem['position'],
                "CHECKED_CLASS" => ($elem['completed'] == 1 ? "gdList__item--checked" : ""),
                "ATTEMPTS" => $elem['attempts'] ?? 0,
                "HIDE_NOT_LOGGED_IN" => (IS_LOGGED_IN ? "" : "hiddenIp"),
            ]);
            $Template->compile_template();
            $rows .= $Template->get_output();
        }

        return $rows;
    }

    private function get_levels() {
        $DB = new Database();

        timer_start("levelquery");

        $searchterm = INS['q'] ?? '';

        $binds = [
            "sart" => ($this->listtype == ListType::AREDL ? "AREDL" : "NCL"),
            "userid" => SESS_USERID,
        ];

        $query = "SELECT case when exists (SELECT 1 from levelrecords c where c.level_id = a.level_id and c.user_id = :userid) then 1 else 0 end as completed, "; 
        $query .= "(SELECT attempts from levelrecords d where d.level_id = a.level_id and d.user_id = :userid) as attempts";
        $query .= ", a.* from levels a ";

        if($this->active_completed == 1) {
            $query .= "join levelrecords b on b.level_id = a.level_id";
        }

        $query .= " where a.sart = :sart";
        
        if(!empty($searchterm)) {
            $query .= " and (lower(a.levelname) like :searchterm or lower(a.levelname_raw) like :searchterm or cast(a.level_id as text) like :searchterm)";
            $binds['searchterm'] = "%" . $searchterm . "%";
        }

        if($this->active_uncompleted == 1) {
            $query .= " and not exists(SELECT 1 from levelrecords c where c.level_id = a.level_id)";
        }

        $DB->query2($query, $binds);
        $this->result_count = $DB->rows;

        $this->max_page = max(1, (int)ceil($this->result_count / $this->per_page));
        $this->page = min(max(1, $this->page), $this->max_page);

        $query .= " order by a.position asc offset :offset fetch first 100 rows only";

        $binds['offset'] = ($this->page - 1) * $this->per_page;

        $DB->query2($query, $binds);

        $this->result_time = timer_end("levelquery");

        return $DB->RSArray;
    }
}