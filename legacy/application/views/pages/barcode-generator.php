<link rel="stylesheet" href="assets/css/barcode-generator.css">

<!-- Title -->
<section class="mb-5 text-center">
    <h1 class="display-4"><img src="/assets/image/barcode.png" class="mr-3" />Barcode generator</h1>
    <p class="lead">Generate and print code 128 barcodes in seconds.</p>
</section>


<!-- Barcode generator -->
<section class="my-5">
    <div class="row">
        <div class="col-md">
            <div class="card mb-5">
                <h2 class="card-header">1. Generate</h2>
                <div class="card-body">

                    <!-- Label input -->
                    <div class="form-group mb-4">
                        <label for="generate-label">Label</label>
                        <input type="text" class="form-control" id="generate-label" placeholder="my label">
                    </div>

                    <!-- Value input -->
                    <div class="form-group mb-0">
                        <label for="generate-value"><span class="text-danger">*</span> Value (barcode output)</label>
                        <input required type="value" class="form-control" id="generate-value" placeholder="value">
                    </div>

                </div>
            </div>

        </div>

        <div class="col-md">

            <div class="card mb-5">
                <h2 class="card-header">2. Print</h2>
                <div class="card-body">

                    <!-- Output -->
                    <p class="lead text-center">Here's your barcode</p>

                    <!-- Sticker -->
                    <div id="output-sticker-container">
                        <div id="output-sticker">

                            <div id="output-label">
                                my label
                            </div>

                            <div id="output-code">
                                ÌvalueÈÎ
                            </div>

                            <div id="output-value">
                                value
                            </div>

                        </div>
                    </div>

                    <!-- Print button -->
                    <button required type="button" class="btn btn-primary btn-block mt-4" id="output-print-btn">
                        Print
                    </button>

                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <h2 class="card-header">3. Share</h2>
        <div class="card-body">
            <div class="alert alert-info">You can link to this page with url parameters.</div>
            <p><b>Example:</b> http://savvygoose.com/barcode-generator?<span class="badge badge-info" title="Pre-fills the value">value=12345</span>&<span class="badge badge-info" title="Pre-fills the label">label=mylabel</span>&<span class="badge badge-info" title="Prints on page load">print=true</span></p>
            <p>Test the <a href="http://savvygoose.com/barcode-generator?value=12345&label=mylabel&print=true">example link</a> above.</p>

        </div>
    </div>
</section>

<script src="assets/js/libs/code-128-encoder.min.js"></script>
<script type="module" src="assets/js/barcode-generator.js"></script>