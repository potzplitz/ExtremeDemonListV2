
<header class="gdHeader">
    <a class="gdHeader__logo" href="<-URL_HTBASE->">
        <img src="<-URL_HTBASE->assets/logo.png" alt="Extreme Demon List">
    </a>

    <a class="gdBtn <-DISP_LOGINBTN->"
       href="<-URL_HTBASE->account/login">
        Login
    </a>

    <div class="gdHeader__user <-DISP_USER->">
        <button class="gdHeader__user__trigger"
                id="userDropdownTrigger"
                type="button"
                aria-expanded="false"
                aria-controls="userDropdown">

            <img class="gdHeader__user__avatar"
                 src="<-PROFILE_PICTURE->"
                 alt="Profile picture">

            <span class="gdHeader__user__info">
                <span class="gdHeader__user__name"><-USERNAME-></span>
                <span class="gdHeader__user__label">Account</span>
            </span>
        </button>

        <div class="gdHeader__user__dropdown"
             id="userDropdown"
             hidden>

            <div class="gdHeader__user__dropdown__heading">
                <span class="gdHeader__user__dropdown__eyebrow">
                    SIGNED IN AS
                </span>
                <span class="gdHeader__user__dropdown__username">
                    <-USERNAME->
                </span>
            </div>

            <div class="gdHeader__user__dropdown__divider"></div>

            <a href="<-URL_HTBASE->account/profile"
               class="gdHeader__user__dropdown__item">
                <span class="gdHeader__user__dropdown__icon">◉</span>
                <span>My Profile</span>
            </a>

            <a href="<-URL_HTBASE->account/settings"
               class="gdHeader__user__dropdown__item">
                <span class="gdHeader__user__dropdown__icon">⚙</span>
                <span>Settings</span>
            </a>

            <div class="gdHeader__user__dropdown__divider"></div>

            <a href="<-URL_HTBASE->account/logout"
               class="gdHeader__user__dropdown__item gdHeader__user__dropdown__item--danger">
                <span class="gdHeader__user__dropdown__icon">↪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>
</header>
