<?php

class Footer implements BaseScript {
    public function init() {
        $this->render_footer();
    }

    private function render_footer() {
        $Template = new Template();
        $Template->load_template("footer/footer.php");
        $Template->load_hash([
           "YEAR" => date("Y")
        ]);
        $Template->compile_template();

        MainController::setFooter($Template->get_output());
    }
}