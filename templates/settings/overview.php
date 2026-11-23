<section class="gdSection gdSection--xxxxl">
    <div class="gdSettings gdSettings__box">
        <h3>General</h3>
        <p>Manage your general settings of the Extreme Demon List</p>
        <div class="separator"></div>
        <div class="gdSettings__content">
            <form class="gdSettings__settings" method="POST" action="<-URL_HTBASE->account/settings">
                <input class="gdInput gdInput--text" name="username" type="text" placeholder="Username" value="<-USERNAME->">
                <input class="gdInput gdInput--text" name="email" type="email" placeholder="E-Mail" value="<-EMAIL->">
                <input class="gdBtn" value="Submit" type="submit">
            </form>
            <div class="gdSettings__pfp">
                <div class="gdPfpUpload" data-pfp-upload data-upload-url="<-URL_HTBASE->account/pfp">
                    <label class="gdPfpUpload__drop" title="Change profile picture">
                        <input class="gdPfpUpload__input" id="pfpInput" type="file" accept="image/png,image/jpeg,image/webp">
                        <img class="gdPfpUpload__img" src="<-PFP_URL->" alt="Profile picture">
                        <span class="gdPfpUpload__overlay">
                            <span class="gdPfpUpload__overlayHint">Change</span>
                            <span class="gdPfpUpload__loading">Uploading…</span>
                        </span>
                        <svg class="gdPfpUpload__progress" viewBox="0 0 100 100" aria-hidden="true">
                            <circle class="gdPfpUpload__progressTrack" cx="50" cy="50" r="47" pathLength="100"></circle>
                            <circle class="gdPfpUpload__progressBar" cx="50" cy="50" r="47" pathLength="100"></circle>
                        </svg>
                    </label>
                    <div class="gdPfpUpload__info">
                        <span class="gdPfpUpload__title">Profile picture</span>
                        <span class="gdPfpUpload__hint">PNG, JPG or WebP · max. 5 MB</span>
                        <label class="gdPfpUpload__btn" for="pfpInput">Choose image</label>
                        <p class="gdPfpUpload__msg" aria-live="polite"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>