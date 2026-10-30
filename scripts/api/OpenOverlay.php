<?php

class OpenOverlay implements BaseScript {

    private const MODALTYPES = [
        "ok" => "DISP_OK",
        "confirm" => "DISP_CONFIRM",
        "yes_no" => "DISP_YES_NO",
        "cancel" => "DISP_CANCEL",
        "yes_cancel" => "DISP_YES_CANCEL"
    ];

    public function init() {
        $this->render_overlay_content();
    }

    private function render_overlay_content() {

        $modaltype = INS['type'] ?? "confirm";

        $placeholder_arr = [];

        if(array_key_exists($modaltype, self::MODALTYPES)) {
            foreach(self::MODALTYPES as $type => $placeholder) {
                if($modaltype == $type) {
                    $placeholder_arr[$placeholder] =  "";
                } else {
                    $placeholder_arr[$placeholder] = "d-none";
                }
            }

        } else {
            echo_json(["error" => "Invalid modal type!"]);
            return;
        }

        $Template = new Template();
        $Template->load_template("main/overlay.php");
        $Template->load_hash(array_merge([
            "TITLE" => h(INS['title'] ?? ""),
            "CONTENT" => h(INS['content'] ?? ""),
        ], $placeholder_arr));
        $Template->compile_template();

        $json_out = [
            "overlay" => $Template->get_output()
        ];

        header("Content-Type: application/json");
        echo(json_encode($json_out, JSON_PRETTY_PRINT));
    }
}