<div class="gdSaveFile__upload__header">
    <h3>Upload Savefile</h3>
    <p class="gdSaveFile__upload__description">
        Select your Geometry Dash savefile to inspect and manage it.
    </p>
</div>

<form class="gdSaveFile__upload__form" id="savefile-form" novalidate>
    <input
        class="gdSaveFile__upload__input"
        type="file"
        name="savefile"
        id="savefile-upload"
        accept=".dat"
    >

    <label class="gdSaveFile__upload__dropzone" for="savefile-upload">
        <svg class="gdSaveFile__upload__icon" aria-hidden="true">
            <use href="#icon-upload"></use>
        </svg>
        <span class="gdSaveFile__upload__dropzone__text">
            <span class="gdSaveFile__upload__dropzone__title">Drop your savefile here</span>
            <span class="gdSaveFile__upload__dropzone__subtitle">
                or <span class="gdSaveFile__upload__dropzone__link">browse</span>
                · <span class="gdSaveFile__upload__dropzone__hint">CCGameManager.dat</span>
            </span>
        </span>
    </label>

    <p class="gdSaveFile__upload__error" role="alert" hidden></p>

    <div class="gdSaveFile__upload__file" hidden>
        <div class="gdSaveFile__upload__file__meta">
            <span class="gdSaveFile__upload__file__name"></span>
            <span class="gdSaveFile__upload__file__size"></span>
        </div>
        <button class="gdSaveFile__upload__file__remove" type="button" aria-label="Remove file">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
            </svg>
        </button>
    </div>

    <button class="gdSaveFile__upload__submit gdButton" type="submit" disabled>
        Load savefile
    </button>
</form>

<p class="gdSaveFile__upload__notice">
    <svg aria-hidden="true">
        <use href="#icon-info"></use>
    </svg>
    <span>
        Processed locally, never uploaded. Found in <code>%LOCALAPPDATA%\GeometryDash</code>.
    </span>
</p>