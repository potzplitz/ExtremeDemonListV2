<section class="gdSection gdSection--xxxxl">
    <div class="gdSaveFile__wrapper">
        <div class="gdSaveFile__upper">
            <div class="gdSaveFile__newestfile gdSaveFile__mainwindow">
                <div class="gdSaveFile__newestfile__headerrow">
                    <h3>Active Savefile</h3>
                    <p>Uploaded at: <-ACTIVE_SAVE_DATE-></p>
                </div>
                <div class="gdSaveFile__newestfile__infos">
                    <-CARDS->
                </div>
                <div class="w-100 mt-3 flex-row align-items-end justify-content-end gap-2 <-HIDE_NO_SAVEFILE->">
                    <button class="gdBtn" type="button" data-action="import-records" data-save-id="<-ACTIVE_SAVE_ID->">Import Levels into List</button>
                    <button class="gdBtn" type="button" data-action="delete-imported">Delete imported Levels from List</button>
                </div>
            </div>
            <div class="gdSaveFile__mainwindow gdSaveFile__upload">
                <-UPLOADER->
            </div>
        </div>
        <div class="gdSaveFile__lower">
            <div class="gdSaveFile__mainwindow gdSaveFile__savelist">
                <-SAVEFILETABLE->
            </div>
        </div>
    </div>
</section>