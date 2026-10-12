
#!/bin/bash

cd /mnt/nas/development/www/css || exit 1

compile_scss() {
    find scss -type f -name '*.scss' ! -name '_*' | while read -r file; do
        relative="${file#scss/}"
        target="output/${relative%.scss}.css"

        mkdir -p "$(dirname "$target")"
        sass "$file" "$target" || echo "Fehler beim Kompilieren: $file"
    done
}

snapshot() {
    find scss -type f -name '*.scss' -exec stat -c '%n %Y %s' {} \; | sort
}

compile_scss
snapshot > /tmp/sass-files.current

echo ">>> Sass-Polling aktiv. Prüfe alle 2 Sekunden."

while sleep 2; do
    snapshot > /tmp/sass-files.new

    if ! cmp -s /tmp/sass-files.current /tmp/sass-files.new; then
        mv /tmp/sass-files.new /tmp/sass-files.current
        echo ">>> Änderung erkannt – kompiliere SCSS..."
        compile_scss
    else
        rm -f /tmp/sass-files.new
    fi
done
