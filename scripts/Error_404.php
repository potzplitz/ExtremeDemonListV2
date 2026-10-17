<?php

class Error_404 {
    public function show() {
        MainController::setTitle("404 Not Found");
        $url = "https://noasaservice.lol/api/get";

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 2,
            CURLOPT_CONNECTTIMEOUT => 1,
        ]);
        $response = curl_exec($ch);
        $no = json_decode($response ?: '', true)['message'] ?? "No.";

        $Template = new Template();
        $Template->load_template("main/404.php");
        $Template->load_hash([
            "NO" => h($no)
        ]);
        $Template->compile_template();
        $Template->show_template();
    }
}