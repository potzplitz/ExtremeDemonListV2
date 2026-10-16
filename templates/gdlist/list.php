<section class="gdSection gdSection--xxxxl">
    <form class="gdList__controls" method="GET" action="<-URL_HTBASE->extremedemons">
        <input type="text" class="gdInput" placeholder="Search..." name="q" value="<-SEARCHTERM->">
        <input type="submit" class="gdBtn" value="Search">
        <button type="submit" name="filter_completed" value="<-ACTIVE_COMPLETED->" class="gdBtn <-ACTIVE_CLASS->">
            <svg class="icon" width="24" height="21" aria-hidden="true">
                <use href="#checkmark"></use>
            </svg>
        </button>
        <button type="submit" name="filter_uncompleted" value="<-ACTIVE_UNCOMPLETED->" class="gdBtn <-ACTIVE_CLASS_UNCOMPLETED->">
            <svg class="icon" width="24" height="21" aria-hidden="true">
                <use href="#uncompleted"></use>
            </svg>
        </button>
    </form>
    <div class="gdList__rows__wrapper">
        <-ROWS->
    </div>
</section>