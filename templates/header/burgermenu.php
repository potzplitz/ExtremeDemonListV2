<div class="gdHeader__burgermenu" id="burgermenu">
    <button class="gdHeader__burgermenu__toggle"
            id="burgermenuToggle"
            type="button"
            aria-expanded="false"
            aria-controls="burgermenuPanel"
            aria-label="Open menu">
        <span class="gdHeader__burgermenu__toggle__bar"></span>
        <span class="gdHeader__burgermenu__toggle__bar"></span>
        <span class="gdHeader__burgermenu__toggle__bar"></span>
    </button>

    <div class="gdHeader__burgermenu__backdrop"
         id="burgermenuBackdrop"></div>

    <div class="gdHeader__burgermenu__panel"
         id="burgermenuPanel"
         aria-label="Menu">

        <!-- Account (eingeloggt) -->
        <div class="<-DISP_USER->">
            <div class="gdHeader__burgermenu__account">
                <img class="gdHeader__burgermenu__account__avatar"
                     src="<-PROFILE_PICTURE->"
                     alt="Profile picture">

                <div class="gdHeader__burgermenu__account__info">
                    <span class="gdHeader__burgermenu__account__eyebrow">
                        SIGNED IN AS
                    </span>
                    <span class="gdHeader__burgermenu__account__name">
                        <-USERNAME->
                    </span>
                </div>
            </div>
        </div>

        <!-- Login (ausgeloggt) -->
        <div class="<-DISP_LOGINBTN->">
            <a class="gdBtn gdHeader__burgermenu__login"
               href="<-URL_HTBASE->account/login">
                Login
            </a>
        </div>

        <div class="gdHeader__burgermenu__divider"></div>

        <!-- Reiter -->
        <nav aria-label="Main navigation">
            <span class="gdHeader__burgermenu__heading">NAVIGATION</span>

            <a href="<-URL_HTBASE->extremedemons"
               class="gdHeader__burgermenu__item <-NAV_ACTIVE_LIST->">
                <span class="gdHeader__burgermenu__icon">◆</span>
                <span>Extreme Demon List</span>
            </a>

            <a href="<-URL_HTBASE->ninecircles"
               class="gdHeader__burgermenu__item <-NAV_ACTIVE_LEADERBOARD->">
                <span class="gdHeader__burgermenu__icon">★</span>
                <span>Nine Circles List</span>
            </a>

            <!-- <a href="<-URL_HTBASE->submit"
               class="gdHeader__burgermenu__item <-NAV_ACTIVE_SUBMIT->">
                <span class="gdHeader__burgermenu__icon">✚</span>
                <span>Submit Record</span>
            </a> -->
        </nav>

        <!-- Account-Menüpunkte (eingeloggt) -->
        <div class="<-DISP_USER->">
            <div class="gdHeader__burgermenu__divider"></div>

            <span class="gdHeader__burgermenu__heading">ACCOUNT</span>

            <a href="<-URL_HTBASE->account/profile"
               class="gdHeader__burgermenu__item">
                <span class="gdHeader__burgermenu__icon">◉</span>
                <span>My Profile</span>
            </a>

            <a href="<-URL_HTBASE->account/settings"
               class="gdHeader__burgermenu__item">
                <span class="gdHeader__burgermenu__icon">⚙</span>
                <span>Settings</span>
            </a>

            <div class="gdHeader__burgermenu__divider"></div>

            <a href="<-URL_HTBASE->account/logout"
               class="gdHeader__burgermenu__item gdHeader__burgermenu__item--danger">
                <span class="gdHeader__burgermenu__icon">↪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>