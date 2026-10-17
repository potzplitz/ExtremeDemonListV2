<section class="gdSection gdSection--xxxxl">
    <p class="gdList__countTime"><-RESULT_INFOS-></p>
    <form class="gdList__controls" method="GET" action="<-URL_HTBASE-><-LIST_URL->">
        <div class="gdList__controls__filters">
            <input type="text" class="gdInput gdList__controls__search" placeholder="Search..." name="q" value="<-SEARCHTERM->">
            <input type="submit" class="gdBtn gdBtn--secondary" value="Search">

            <button type="submit" name="toggle" value="completed" class="gdBtn gdBtn--secondary <-ACTIVE_CLASS->">
                <svg class="icon" width="24" height="21" aria-hidden="true">
                    <use href="#checkmark"></use>
                </svg>
            </button>

            <input type="hidden" name="completed" value="<-ACTIVE_COMPLETED->">
            <input type="hidden" name="uncompleted" value="<-ACTIVE_UNCOMPLETED->">

            <button type="submit" name="toggle" value="uncompleted" class="gdBtn gdBtn--secondary <-ACTIVE_CLASS_UNCOMPLETED->">
                <svg class="icon" width="24" height="21" aria-hidden="true">
                    <use href="#uncompleted"></use>
                </svg>
            </button>
        </div>

        <div class="gdList__controls__paging">
            <button type="submit" name="prev" value="1" class="gdBtn gdBtn--secondary">
                <svg class="icon" width="24" height="21" aria-hidden="true">
                    <use href="#icon-chevron-left"></use>
                </svg>
            </button>

            <input type="number" class="gdInput gdList__paging"
                name="page" min="1" value="<-PAGE->">

            <button type="submit" name="next" value="1" class="gdBtn gdBtn--secondary">
                <svg class="icon" width="24" height="21" aria-hidden="true">
                    <use href="#icon-chevron-right"></use>
                </svg>
            </button>
        </div>
    </form>
    <div class="gdList__rows__wrapper">
        <-ROWS->
        <h3 class="DISP_NOT_FOUND gdList__notfound"><-NOT_FOUND-></h3>
    </div>
</section>