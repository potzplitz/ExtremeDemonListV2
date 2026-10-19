Startseite:

Top 100 => fertig
Tags => fertig
Tags vorschläge (markierung welche tags zu den completions passen)
Recommendations basierend auf completions => fertig
listenupdates (Platzierungsänderungen etc.)

inputs sanitizen (evil tags)

Liste:

Victors pro level noch mit anzeigen => danach auch filtern/sortieren können
Suchfunktion

platz unterschied rechner



Detailseite:
anzeigen welcher der nächst einfachere/schwerere ist
Plazierungshistory anzeigen
Victors anzeigen (https://api.aredl.net/v2/api/aredl/levels/61079355/records?page=2)

performance:

datenbankklasse anpassen dass bie jedem select ein timer gestartet wird und wenn er beendet ist timer stoppen und wenn execution time über einer sekunde => nachricht und sql optimieren


JULIAN FEEDBACK

TODO: (ideen)
=============
- seite wechseln button zusätzlich noch unten am ende anfügen
- pfeilbutton (links o. rechts) ausgrauen wenn man auf erster oder letzter seite ist
- doppelpfeil button einfügen um ans ende oder den anfang zu springen (erste seite / letzte seite)


https://gdicon.oat.zone/icon.png?type=cube&value=47&color1=12&color2=70


account mit gd account verknüpfen:

in einstellungen unter section account wird es einen punkt geben mit "connect your account with geometry dash" (notice dass man dadurch dann verifizierter user ist)

dann dialog mit, "token erzeugen", was dann ein token erzeugt und dann button "check token" dann request an getGJUserInfo20 mit account id (wird beim registrieren abgefragt(getGJUsers20.php mit username), wenn nicht gefunden, kann ein user sie mit seinem savefile ermitteln den er hochladen kann (dort dann noch notice dass man nur seine eigenen savefile hochladen soll)). wenn token übereinstimmen dann in db feld "verified" in users auf 1 setzen. zusätzlich dort noch ein feld mit token oder evtl eine extra tabelle für die geometry dash account linkung (überlegen was schlauer ist)


    // private function render_profile_icons() {
        
    //     $savefile = get_active_savefile($this->user_id);

    //     $icons = json_decode($savefile[0]['playerdata'] ?? "{}", true);

    //     $colors = [
    //         $icons['player_color_1'] ?? 0,
    //         $icons['player_color_2'] ?? 0,
    //         $icons['player_glow'] ?? false,
    //     ];

    //     $icon_types = [
    //         "cube" => $icons['player_cube'] ?? 0,
    //         "ship" => $icons['player_ship'] ?? 0,
    //         "ball" => $icons['player_ball'] ?? 0,
    //         "ufo" => $icons['player_ufo'] ?? 0,
    //         "wave" => $icons['player_wave'] ?? 0,
    //         "robot" => $icons['player_robot'] ?? 0,
    //         "spider" => $icons['player_spider'] ?? 0,
    //         "swing" => $icons['player_swing'] ?? 0,
    //         "jetpack" => $icons['player_jetpack'] ?? 0,
    //     ];

    //     $Template = new Template();

    //     $rows = "";

    //     foreach ($icon_types as $key => $icon) {
    //         $url = "https://gdicon.oat.zone/icon.png?" . http_build_query([
    //             "type" => $key,
    //             "value" => $icon,
    //             "color1" => $colors[0],
    //             "color2" => $colors[1],
    //             "glow" => $colors[2] ? 'true' : 'false',
    //         ]);

    //         $Template->load_template("savefilemanager/user_icon.php");
    //         $Template->load_hash([
    //             "ICONURL" => $url
    //         ]);
    //         $Template->compile_template();

    //         $rows .= $Template->get_output();
    //     }
        
    //     return $rows;
    // }