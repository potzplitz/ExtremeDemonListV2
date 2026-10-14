<section class="gdSection gdLogin gdRegister gdSection--xxxxl">
    <div class="gdLogin__nest1">
        <div class="gdLogin__nest1__nest2">

            <header class="gdLogin__nest1__nest2__header">
                <span class="gdLogin__nest1__nest2__header__eyebrow">
                    EXTREME DEMON LIST
                </span>

                <h1 class="gdLogin__nest1__nest2__header__title">
                    Create Account
                </h1>

                <p class="gdLogin__nest1__nest2__header__description">
                    Join the community, track your progress, and conquer the hardest demons.
                </p>
            </header>
            <div class="gdAlert gdAlert--error <-DISP_ERROR->" role="alert">
                <span class="gdAlert__icon" aria-hidden="true">!</span>

                <div class="gdAlert__content">
                    <span class="gdAlert__content__title">
                        Registration failed
                    </span>

                    <p class="gdAlert__content__message">
                        <-ERROR->
                    </p>
                </div>
            </div>
            <form
                class="gdLogin__nest1__nest2__form gdRegister__form"
                method="POST"
                action="<-URL_HTBASE->account/register"
            >
                <input type="hidden" name="submit_register" value="1">

                <div class="gdInput__group">
                    <label class="gdInput__group__label" for="username">
                        Username
                    </label>

                    <input
                        class="gdInput"
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Choose a username"
                        autocomplete="username"
                        minlength="3"
                        maxlength="30"
                        required
                    >

                    <span class="gdInput__group__hint">
                        Choose a unique username for your profile.
                    </span>
                </div>

                <div class="gdInput__group">
                    <label class="gdInput__group__label" for="email">
                        Email Address
                    </label>

                    <input
                        class="gdInput"
                        type="email"
                        id="email"
                        name="email"
                        placeholder="you@example.com"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="gdInput__group">
                    <label class="gdInput__group__label" for="password">
                        Password
                    </label>

                    <input
                        class="gdInput"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        autocomplete="new-password"
                        minlength="12"
                        required
                    >

                    <span class="gdInput__group__hint">
                        Use at least 12 characters.
                    </span>
                </div>

                <div class="gdInput__group">
                    <label class="gdInput__group__label" for="password_confirm">
                        Confirm Password
                    </label>

                    <input
                        class="gdInput"
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        placeholder="Enter your password again"
                        autocomplete="new-password"
                        minlength="12"
                        required
                    >
                </div>

                <label class="gdCheck gdRegister__terms">
                    <input
                        type="checkbox"
                        name="terms"
                        value="1"
                        required
                    >

                    <span>
                        I agree to the
                        <a href="<-URL_HTBASE->terms">Terms of Service</a>
                        and
                        <a href="<-URL_HTBASE->privacy">Privacy Policy</a>.
                    </span>
                </label>

                <button
                    type="submit"
                    class="gdBtn gdBtn--full gdLogin__nest1__nest2__form__submit"
                >
                    <span>Create Account</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </form>

            <footer class="gdLogin__nest1__nest2__footer">
                <span>Already have an account?</span>
                <a href="<-URL_HTBASE->account/login">Sign in</a>
            </footer>

        </div>

        <div class="gdLogin__nest1__decoration">
            <span class="gdLogin__nest1__decoration__line"></span>
        </div>
    </div>
</section>