<section class="gdSection gdProfile__wrapper gdSection--xxxxl">
    <div class="gdProfile__profileheader gdProfile__mainwindow">
        <div class="gdProfile__profileheader__pfp">
            <img class="gdProfile__profileheader__pfp__avatar" src="<-IMAGE->" alt="" width="100" height="100">
            <div class="gdProfile__profileheader__pfp__infowrapper">
                <span class="gdProfile__profileheader__pfp__country"><-LOCALE-></span>
                <h1 class="gdProfile__profileheader__pfp__username"><-USERNAME-></h1>
                <p class="gdProfile__profileheader__pfp__meta">
                    <span class="gdProfile__profileheader__pfp__metalabel">Last active</span>
                    <-LASTLOG->
                </p>
                <p class="gdProfile__profileheader__pfp__meta">
                    <span class="gdProfile__profileheader__pfp__metalabel">Joined</span>
                    <-FIRST_LOGIN->
                </p>
            </div>
        </div>
        <div class="gdProfile__profileheader__icons">
            <-ICONS->
        </div>
    </div>
    <div class="gdProfile__infowrapper">
        <div class="gdProfile__infowrapper__info gdProfile__mainwindow">
            <p class="gdProfile__infowrapper__info__header">SKILLSET</p>
            <div class="gdProfile__infowrapper__info__tags">
                <-SKILLSET_TAGS->
            </div>
        </div>
        <div class="gdProfile__infowrapper__info gdProfile__mainwindow">
            <p class="gdProfile__infowrapper__info__header">COMPLETIONS</p>
            <div class="gdProfile__infowrapper__info__stats">
                <div class="gdProfile__infowrapper__info__stat">
                    <span class="gdProfile__infowrapper__info__value"><-COMPLETIONS_AREDL-></span>
                    <span class="gdProfile__infowrapper__info__sub">AREDL</span>
                </div>
                <div class="gdProfile__infowrapper__info__stat">
                    <span class="gdProfile__infowrapper__info__value"><-COMPLETIONS_NCL-></span>
                    <span class="gdProfile__infowrapper__info__sub">NCL</span>
                </div>
            </div>
        </div>
        <div class="gdProfile__infowrapper__info gdProfile__mainwindow">
            <p class="gdProfile__infowrapper__info__header">PROGRESS (>40%)</p>
            <div class="gdProfile__infowrapper__info__stats">
                <div class="gdProfile__infowrapper__info__stat">
                    <span class="gdProfile__infowrapper__info__value"><-PROGRESS_AREDL-></span>
                    <span class="gdProfile__infowrapper__info__sub">AREDL</span>
                </div>
                <div class="gdProfile__infowrapper__info__stat">
                    <span class="gdProfile__infowrapper__info__value"><-PROGRESS_NCL-></span>
                    <span class="gdProfile__infowrapper__info__sub">NCL</span>
                </div>
            </div>
        </div>
    </div>
    <div class="gdProfile__mainwindow gdProfile__hardest <-HARDEST_MOD->" style="--hardest-thumb: url('<-THUMB->')">
        <a class="gdProfile__hardest__thumb" href="<-LINK->">
            <img src="<-THUMB->" alt="" loading="lazy">
        </a>
        <div class="gdProfile__hardest__content">
            <p class="gdProfile__hardest__label">HARDEST DEMON</p>
            <span class="gdProfile__hardest__position">#<-POSITION-></span>
            <h2 class="gdProfile__hardest__name"><-NAME-></h2>
            <a class="gdBtn gdBtn--secondary" href="<-LINK->">Go to level</a>
        </div>
    </div>
        <div class="gdProfile__row">
        <div class="gdProfile__mainwindow gdProfile__activity">
            <p class="gdProfile__title">RECENT ACTIVITY</p>
            <div class="gdProfile__activity__list">
                <-ACTIVITY->
            </div>
        </div>
        <div class="gdProfile__mainwindow gdProfile__hardest gdProfile__hardest--compact <-NCL_MOD->" style="--hardest-thumb: url('<-NCL_THUMB->')">
            <a class="gdProfile__hardest__thumb" href="<-NCL_LINK->">
                <img src="<-NCL_THUMB->" alt="" loading="lazy">
            </a>
            <div class="gdProfile__hardest__content">
                <p class="gdProfile__hardest__label">HARDEST NCL</p>
                <span class="gdProfile__hardest__position">#<-NCL_POSITION-></span>
                <h2 class="gdProfile__hardest__name"><-NCL_NAME-></h2>
                <a class="gdBtn gdBtn--secondary" href="<-NCL_LINK->">Go to level</a>
            </div>
        </div>
    </div>
    <div class="gdProfile__mainwindow gdProfile__completions">
        <p class="gdProfile__title">COMPLETIONS</p>
        <div class="gdProfile__completions__list">
            <div class="gdProfile__completions__head">
                <span>#</span>
                <span>Level</span>
                <span class="gdProfile__completions__creator">Creator</span>
                <span class="gdProfile__completions__date">Date</span>
            </div>
            <-COMPLETION_ROWS->
        </div>
    </div>
    <div class="gdProfile__mainwindow gdProfile__progresslist">
        <p class="gdProfile__title">PROGRESS (>40%)</p>
        <div class="gdProfile__progresslist__list">
            <-PROGRESS_ROWS->
        </div>
    </div>
</section>