$(function () {
    const $root = $("#burgermenu");
    if (!$root.length) return;

    const $toggle = $("#burgermenuToggle");
    const $panel = $("#burgermenuPanel");
    const $backdrop = $("#burgermenuBackdrop");
    const mq = window.matchMedia("(max-width: 900px)");

    const isOpen = () => $root.hasClass("is-open");

    const setOpen = (open) => {
        $root.toggleClass("is-open", open);
        $toggle.attr({
            "aria-expanded": String(open),
            "aria-label": open ? "Close menu" : "Open menu",
        });
        $("html").css("overflow", open ? "hidden" : "");
    };

    $toggle.on("click", () => setOpen(!isOpen()));
    $backdrop.on("click", () => setOpen(false));

    // Menü schließen, sobald ein Link angeklickt wird
    $panel.on("click", "a", () => setOpen(false));

    $(document).on("keydown.burgermenu", (e) => {
        if (e.key === "Escape" && isOpen()) {
            setOpen(false);
            $toggle.trigger("focus");
        }
    });

    // Auf Desktop-Breite automatisch schließen
    // (matchMedia bleibt nativ, jQuery hat dafür kein Gegenstück)
    mq.addEventListener("change", (e) => {
        if (!e.matches) setOpen(false);
    });
});