<?php

enum ListType {
    case AREDL;
    case NCL;
}

class GDList implements BaseScript {
    private $listtype;
    private $result_count = 0;
    private $active_completed = 0;
    private $active_uncompleted = 0;
    public function init() {

        $bread[] = ["link" => "/", "text" => "Main Page"];
        

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

        $this->active_completed = (string)(INS['filter_completed'] ?? "1") === "0" ? 1 : 0;
        $this->active_uncompleted = (string)(INS['filter_uncompleted'] ?? "1") === "0" ? 1 : 0;

        if($this->active_completed) {
            $this->active_uncompleted = 0;

        } else if($this->active_uncompleted) {
            $this->active_completed = 0;

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
                "POSITION" => $elem['position']
            ]);
            $Template->compile_template();
            $rows .= $Template->get_output();
        }

        return $rows;
    }

    private function get_levels() {
        $DB = new Database();

        $page = max(1, (int) (INS['page'] ?? 1));
        $searchterm = INS['q'] ?? '';

        $query = "SELECT * from levels where sart = :sart";
        $binds = [
            "offset" => ($page - 1) * 100,
            "sart" => ($this->listtype == ListType::AREDL ? "AREDL" : "NCL"),
        ];
        
        if(!empty($searchterm)) {
            $query .= " and (lower(levelname) like :searchterm or lower(levelname_raw) like :searchterm or cast(level_id as text) like :searchterm)";
            $binds['searchterm'] = "%" . $searchterm . "%";
        }

        $query .= " order by position asc offset :offset fetch first 100 rows only";

        $DB->query2($query, $binds);

        $this->result_count = $DB->rows;

        return $DB->RSArray;
    }
}