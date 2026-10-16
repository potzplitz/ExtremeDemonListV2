/**
 * Breadcrumb-Enhancement für MainController::getBreadcrumb()
 * - löst das " › " aus den Links und setzt es als eigenes Element dazwischen
 * - markiert die aktuelle Seite (aria-current)
 * - macht "#"-Links nicht fokussierbar
 * - korrigiert den Mobile-Zurück-Link und scrollt den Pfad ans Ende
 * Ohne JS funktioniert die Leiste trotzdem, nur mit dem Trenner im Link.
 */
(() => {
    "use strict";

    const SEPARATOR = "›";
    const SEPARATOR_PATTERN = /\s*›\s*$/;

    function enhance(nav) {
        const trail = nav.querySelector(".gdBreadcrumb__trail");
        if (!trail) return;

        const links = Array.from(trail.querySelectorAll(":scope > a"));
        if (links.length === 0) {
            nav.remove(); // "invisible"
            return;
        }

        links.forEach((link, index) => {
            const isLast = index === links.length - 1;

            // Trennzeichen aus dem letzten Textknoten entfernen
            const lastNode = link.lastChild;
            if (lastNode && lastNode.nodeType === Node.TEXT_NODE) {
                lastNode.textContent = lastNode.textContent.replace(SEPARATOR_PATTERN, "");
            }

            link.title = link.textContent.trim();
            // Klasse setzen, damit Bootstraps a:not([href]):not([class]) nie greift
            link.classList.add("gdBreadcrumb__link");

            // "#"-Links: href bleibt, aber nicht fokussierbar
            if (link.getAttribute("href") === "#") {
                link.setAttribute("tabindex", "-1");
                link.setAttribute("aria-disabled", "true");
            }

            if (isLast) {
                link.setAttribute("aria-current", "page");
            } else {
                const sep = document.createElement("span");
                sep.className = "gdBreadcrumb__sep";
                sep.setAttribute("aria-hidden", "true");
                sep.textContent = SEPARATOR;
                link.after(sep);
            }
        });

        // Zurück = letzter echter Link VOR der aktuellen Seite
        const back = nav.querySelector(".gdBreadcrumb__back");
        if (back) {
            const parent = links.slice(0, -1).filter((l) => l.getAttribute("href") !== "#").pop();
            if (parent) {
                back.href = parent.getAttribute("href");
                back.setAttribute("aria-label", "Back to " + parent.textContent.trim());
            } else {
                back.hidden = true;
            }
        }

        nav.classList.add("is-ready");

        // Mobile: aktuelle Seite sichtbar halten, Fade nur wenn gescrollt
        const updateFade = () => trail.classList.toggle("is-scrolled", trail.scrollLeft > 2);
        trail.addEventListener("scroll", updateFade, { passive: true });
        requestAnimationFrame(() => {
            trail.scrollLeft = trail.scrollWidth;
            updateFade();
        });
    }

    function init() {
        document.querySelectorAll(".gdBreadcrumb").forEach(enhance);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();