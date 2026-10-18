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



Detailseite:
anzeigen welcher der nächst einfachere/schwerere ist
Plazierungshistory anzeigen
Victors anzeigen (https://api.aredl.net/v2/api/aredl/levels/61079355/records?page=2)

performance:

datenbankklasse anpassen dass bie jedem select ein timer gestartet wird und wenn er beendet ist timer stoppen und wenn execution time über einer sekunde => nachricht und sql optimieren


safe file parsing/upload:

safe file auf dem browser im js parsen und dann als json an den server schicken

folgende daten:
gd username
gd user_id
gd star count
icon set
completed levels
completed level attempts
completed level jumps
level progress auf uncompleted levels
level attempts auf unocmpleted levels
level jumps auf uncompleted levels

dann bei jedem upload jeden upload speichern in einer tabelle mit safefile_id, user_id, uploaded_at, data (ganze daten in json format)

in einstellungen/safefile manager(!!! gute idee !!!) managen können welche safefiles man hat und man kann dann auch diese wieder löschen

json hash erstellen und da dann einen unique drauf machen

api endpunkt dafür erstellen (session_id, daten)