<header class="gdHeader">
    <a class="gdHeader__logo" href="<-URL_HTBASE->">
        <img src="<-URL_HTBASE->assets/logo.png" alt="Extreme Demon List">
    </a>

    <nav class="gdHeader__nav" aria-label="Main navigation">
        <a href="<-URL_HTBASE->extremedemons"
           class="gdHeader__nav__link <-NAV_ACTIVE_LIST->">
            Extreme Demon List
        </a>
        <a href="<-URL_HTBASE->ninecircles"
           class="gdHeader__nav__link <-NAV_ACTIVE_LEADERBOARD->">
            Nine Circles List
        </a>
        <!-- <a href="<-URL_HTBASE->submit"
           class="gdHeader__nav__link <-NAV_ACTIVE_SUBMIT->">
            Submit Record
        </a> -->
    </nav>

    <a class="gdBtn gdHeader__login <-DISP_LOGINBTN->"
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

            <a href="<-URL_HTBASE->account/savefiles"
               class="gdHeader__user__dropdown__item">
                <span class="gdHeader__user__dropdown__icon">◉</span>
                <span>Savefile Manager</span>
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
    <-BURGERMENU->
</header>

<nav class="gdBreadcrumb" aria-label="Breadcrumb">
    <div class="gdBreadcrumb__inner">
        <a class="gdBreadcrumb__back" href="<-BREADCRUMB_MOBILE->" aria-label="Back">
            <svg class="gdBreadcrumb__back__icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 5l-7 7 7 7" />
            </svg>
        </a>
        <div class="gdBreadcrumb__trail"><-BREADCRUMB-></div>
    </div>
</nav>