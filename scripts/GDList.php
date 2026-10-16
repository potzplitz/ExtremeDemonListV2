<?php

enum ListType {
    case AREDL;
    case NCL;
}

class GDList implements BaseScript {
    private $listtype;
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

        MainController::setBreadcrumb($bread);

        $this->show();
    }

    private function show() {
        $Template = new Template();
        $Template->load_template("gdlist/list.php");
        $Template->load_hash([
            "ROWS" => $this->render_levels()
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

        $query = "SELECT * from levels where sart = :sart order by position asc offset :offset fetch first 100 rows only";
        $binds = [
            "offset" => ($page - 1) * 100,
            "sart" => ($this->listtype == ListType::AREDL ? "AREDL" : "NCL"),
        ];

        $DB->query2($query, $binds);

        return $DB->RSArray;
    }
}