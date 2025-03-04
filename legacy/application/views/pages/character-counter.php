<link rel="stylesheet" href="assets/css/barcode-generator.css">


<!-- Title -->
<section class="mb-5 text-center">
    <h1 class="display-4"><img src="/assets/image/keyboard.png" class="mr-3" />Character Counter</h1>
    <p class="lead">Quickly count characters, words & lines.</p>
</section>

<!-- Character counter -->
<section class="my-5">

    <div class="card mb-5">
        <h2 class="card-header">Count characters, words & lines</h2>
        <div class="card-body">

            <div class="form-group mb-4">
                <label for="input-text">Text to count</label>
                <textarea rows="6" val="" class="form-control" id="input-text" placeholder="Type or paste text here"></textarea>
                <div class="row text-center mt-5">
                    <div class="col-sm">
                        <h4>Characters</h4>
                        <span id="output-character-count" class="display-4">0</span>
                    </div>
                    <div class="col-sm">
                        <h4>Words</h4>
                        <span id="output-word-count" class="display-4">0</span>
                    </div>
                    <div class="col-sm">
                        <h4>Lines</h4>
                        <span id="output-line-count" class="display-4">0</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="card mb-5">
        <h2 class="card-header">Recommended lengths</h2>
        <div class="card-body">
            <h2>SEO</h2>
            <div class="h5">Meta title</div>
            <blockquote class="blockquote">
                <p class="mb-0">Google typically displays the first <strong>50–60 characters</strong> of a title tag.</p>
                <footer class="blockquote-footer"><a href="https://moz.com/learn/seo/title-tag">moz.com</a></footer>
            </blockquote>
            <div class="h5">Meta description</div>
            <blockquote class="blockquote">
                <p class="mb-0">We recommend descriptions between <strong>50–160 characters.</strong>.</p>
                <footer class="blockquote-footer"><a href="https://moz.com/learn/seo/meta-description">moz.com</a></footer>
            </blockquote>
        </div>
    </div>


</section>

<script type="module" src="/assets/js/character-count.js"></script>