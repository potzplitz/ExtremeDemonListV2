
$(function () {
    const $trigger = $("#userDropdownTrigger");
    const $dropdown = $("#userDropdown");

    if (!$trigger.length || !$dropdown.length) {
        return;
    }

    function setDropdown(open) {
        $trigger.attr("aria-expanded", String(open));
        $dropdown.prop("hidden", !open);
    }

    $trigger.on("click", function () {
        const isOpen = $trigger.attr("aria-expanded") === "true";
        setDropdown(!isOpen);
    });

    $(document).on("click", function (event) {
        if (
            !$(event.target).closest(
                "#userDropdownTrigger, #userDropdown"
            ).length
        ) {
            setDropdown(false);
        }
    });

    $(document).on("keydown", function (event) {
        if (event.key === "Escape") {
            setDropdown(false);
            $trigger.trigger("focus");
        }
    });

    $dropdown.find("a").on("click", function () {
        setDropdown(false);
    });
});
