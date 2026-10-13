<section class="gdSection gdLogin gdSection--xxxxl">
    <div class="gdLogin__nest1">
        <div class="gdLogin__nest1__nest2">
            <header class="gdLogin__nest1__nest2__header">
                <span class="gdLogin__nest1__nest2__header__eyebrow">
                    EXTREME DEMON LIST
                </span>

                <h1 class="gdLogin__nest1__nest2__header__title">
                    Welcome Back
                </h1>

                <p class="gdLogin__nest1__nest2__header__description">
                    Sign in to track your progress and manage your demons.
                </p>
            </header>

            <div class="gdAlert gdAlert--error" role="alert">
                <span class="gdAlert__icon" aria-hidden="true">!</span>

                <div class="gdAlert__content <-DISP_ERROR->">
                    <span class="gdAlert__content__title">Login failed</span>
                    <p class="gdAlert__content__message">
                        <-ERROR->
                    </p>
                </div>
            </div>

            <form class="gdLogin__nest1__nest2__form" method="POST" action="<-URL_HTBASE->account/login">
                <input type="hidden" name="submit_login" value="1">
                <div class="gdInput__group">
                    <label class="gdInput__group__label" for="username">
                        Username or Email
                    </label>

                    <input
                        class="gdInput"
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username or email"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="gdInput__group">
                    <div class="gdInput__group__label">
                        <label for="password">Password</label>
                        <a href="<-URL_HTBASE->account/forgot">Forgot password?</a>
                    </div>

                    <input
                        class="gdInput"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <label class="gdCheck">
                    <input type="checkbox" name="remember">
                    <span>Keep me signed in</span>
                </label>

                <button
                    type="submit"
                    class="gdBtn gdBtn--full gdLogin__nest1__nest2__form__submit"
                >
                    <span>Sign In</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </form>

            <footer class="gdLogin__nest1__nest2__footer">
                <span>Don't have an account?</span>
                <a href="<-URL_HTBASE->account/register">Create an account</a>
            </footer>
        </div>

        <div class="gdLogin__nest1__decoration">
            <span class="gdLogin__nest1__decoration__line"></span>
        </div>
    </div>
</section>