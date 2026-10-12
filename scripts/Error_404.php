<?php

class Error_404 {
    public function show() {
        MainController::setTitle("404 Not Found");
        $url = "https://noasaservice.lol/api/get";

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET        => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);

        $no = json_decode($response, true)['message'];

        $Template = new Template();
        $Template->load_template("main/404.php");
        $Template->load_hash([
            "NO" => $no
        ]);
        $Template->compile_template();
        $Template->show_template();
    }
}