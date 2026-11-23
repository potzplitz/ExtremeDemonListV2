<?php

class Settings implements BaseScript {
    public function init() {
        MainController::addJavascript("settings/profilepictureupload.js");
        MainController::setTitle("Settings");

        if(ROUTE == "/account/settings" && IS_LOGGED_IN && empty(INS['username'])) {
            $this->show_settings();
        } else if(ROUTE == "/account/settings" && IS_LOGGED_IN && !empty(INS['username'])) {
            $this->update_userdata(INS);
        } else if(ROUTE == "/account/pfp" && IS_LOGGED_IN) {
            $this->update_pfp();
        }
    }

    private function update_pfp() {
        $maxSize = 5 * 1024 * 1024;   // 5 MB
        $outSize = 512;               // gespeichert als max. 512x512

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->pfp_json(['error' => 'Method not allowed.'], 405);
        }

        $userId = SESS_USERID ?? null;
        $relPath = $userId ? $this->pfp_path($userId) : null;
        if (!$relPath) {
            $this->pfp_json(['error' => 'Not logged in.'], 401);
        }

        // Datei-Uploads landen in $_FILES, nicht in $_POST
        $pfp = $_FILES['pfp'] ?? null;
        if (!is_array($pfp) || $pfp['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($pfp['tmp_name'])) {
            $this->pfp_json(['error' => 'No valid file uploaded.'], 400);
        }

        if ($pfp['size'] > $maxSize) {
            $this->pfp_json(['error' => 'File is too large (max. 5 MB).'], 413);
        }

        // Echten Bildtyp prüfen, nicht Dateiendung/MIME vom Browser vertrauen
        $info = @getimagesize($pfp['tmp_name']);
        if ($info === false || !in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)) {
            $this->pfp_json(['error' => 'Only PNG, JPG or WebP allowed.'], 415);
        }

        // Schutz vor riesigen Bildern, die GD den Speicher sprengen
        if ($info[0] > 4096 || $info[1] > 4096) {
            $this->pfp_json(['error' => 'Image is too large (max. 4096 px).'], 422);
        }

        $src = match ($info[2]) {
            IMAGETYPE_PNG  => @imagecreatefrompng($pfp['tmp_name']),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($pfp['tmp_name']),
            IMAGETYPE_WEBP => @imagecreatefromwebp($pfp['tmp_name']),
        };
        if (!$src) {
            $this->pfp_json(['error' => 'Could not read the image.'], 422);
        }

        // Mittig quadratisch zuschneiden und neu kodieren
        // (entfernt Metadaten und eventuell eingeschleusten Code)
        $w    = imagesx($src);
        $h    = imagesy($src);
        $side = min($w, $h);
        $size = min($outSize, $side);

        $dst = imagecreatetruecolor($size, $size);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, intdiv($w - $side, 2), intdiv($h - $side, 2), $size, $size, $side, $side);

        $absPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $relPath;
        $dir = dirname($absPath);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $this->pfp_json(['error' => 'Upload directory is not writable.'], 500);
        }

        // Erst in eine Temp-Datei schreiben, dann ersetzen:
        // schlägt das Speichern fehl, bleibt das alte Bild erhalten
        $tmpPath = $absPath . '.tmp';
        if (!imagewebp($dst, $tmpPath, 85) || !rename($tmpPath, $absPath)) {
            @unlink($tmpPath);
            $this->pfp_json(['error' => 'Could not save the image.'], 500);
        }

        $this->pfp_json(['success' => true, 'path' => $relPath]);
    }

    // Fester Pfad pro User, relativ zum Document-Root
    private function pfp_path($userId) {
        $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $userId);
        return $id === '' ? null : 'assets/profilepictures/' . $id . '.webp';
    }

    // Für <-PFP_URL-> im Template; ?v= sorgt dafür, dass der Browser nach
    // einem Upload nicht das alte Bild aus dem Cache zeigt
    private function pfp_url($userId) {
        $relPath = $this->pfp_path($userId);
        $absPath = $relPath ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $relPath : null;

        if (!$absPath || !is_file($absPath)) {
            return 'assets/img/default-pfp.png'; // ANPASSEN: euer Standard-Avatar
        }

        return $relPath . '?v=' . filemtime($absPath);
    }

    private function pfp_json(array $data, int $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function show_settings() {
        

        $user = $this->get_userdata();
        
        $Template = new Template();
        $Template->load_template("settings/overview.php");
        $Template->load_hash([
            "USERNAME" => $user[0]['username'],
            "EMAIL" => $user[0]['email'],
            "PFP_URL" => URL_HTBASE . "api/profilepicture/" . SESS_USERID
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    private function get_userdata() {
        $DB = new Database();

        $query = "SELECT * from users where user_id = :userid";
        $binds = [
            "userid" => SESS_USERID
        ];

        $DB->query2($query, $binds);
        return $DB->RSArray;
    }

    private function update_userdata($updateData) {
        $DB = new Database();

        $query = "UPDATE users set username = :username, email = :email where user_id = :userid";
        $binds = [
            "username" => $updateData['username'],
            "email" => $updateData['email'],
            "userid" => SESS_USERID
        ];

        $DB->query2($query, $binds);

        $message = "";

        if($DB->rows <= 0) {
            $message = "Nothing has changed!";
        } else {
            $message = "Successfully changed!";
        }

        header("Location: " . URL_HTBASE . "account/settings?message=" . $message);
    }
}