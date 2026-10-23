<?php

class OpenOverlay implements BaseScript {
    public function init() {
        $this->render_overlay_content();
    }

    public function render_overlay_content() {
        $Template = new Template();
        $Template->load_template("main/overlay.php");
        $Template->load_hash([
            "TITLE" => h(INS['title']) ?? "",
            "CONTENT" => INS['content'] ?? ""
        ]);
        $Template->compile_template();

        $json_out = [
            "overlay" => $Template->get_output()
        ];

        header("Content-Type: application/json");
        echo(json_encode($json_out, JSON_PRETTY_PRINT));
    }
}