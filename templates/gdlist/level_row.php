<div class="gdList__item <-CHECKED_CLASS->">
    <img src="https://levelthumbs.prevter.me/thumbnail/<-LEVEL_ID->/small" alt="" loading="lazy" decoding="async">

    <div class="gdList__item__info">
        <p class="gdList__item__position">#<-POSITION-></p>

        <span class="gdList__item__face <-DISP_DEMONFACE->">
            <img src="<-URL_HTBASE->/assets/demonfaces/<-DIFFICULTY->.png"
                alt="<-DIFFICULTY->" width="56" height="56"
                loading="lazy" decoding="async">
        </span>

        <p class="gdList__item__name" title="<-LEVELNAME->"><-LEVELNAME-></p>
    </div>

    <div class="gdList__item__submitwrapper <-HIDE_NOT_LOGGED_IN->">
        <input type="hidden" class="gdList__item__levelid" value="<-LEVEL_ID->">

        <label class="gdList__item__attempts">
            <span class="gdList__item__attempts__label">Attempts</span>
            <input type="number" autocomplete="off" class="gdInput gdInput--small"
                   value="<-ATTEMPTS->" min="0" step="1" inputmode="numeric"
                   aria-label="Attempts">
        </label>

        <input type="hidden" class="gdList__item__state" value="<-CHECKED->">

        <button type="button" class="gdBtn gdBtn--secondary gdList__item__check"
                aria-pressed="false" aria-label="Mark as completed" title="Completed">
            <svg class="icon" width="24" height="21" aria-hidden="true">
                <use href="#checkmark"></use>
            </svg>
        </button>
    </div>
</div>