<!-- Title -->
<section class="mb-5 text-center">
    <h1 class="display-4"><img src="/assets/image/discount.png" class="mr-3" />Percentage calculator</h1>
    <p class="lead">Save time by letting the savvy goose do the hard work.</p>
</section>

<!-- What is x% of y (Form 1) -->
<section class="my-5">
    <h3>What is <span class="badge badge-info">x</span>% of <span class="badge badge-info">y</span></h3>
    <p><i>Example: 50% of 1000 = 500.</i></p>
    <div class="card">
        <div class="card-body">
            <form id="form-1">
                <div class="form-row">
                    <div class="form-group col-sm">
                        <label for="form-1-x"><span class="badge badge-info">x</span>%</label>
                        <input required type="number" step="0.01" class="form-control" id="form-1-x" placeholder="x%">
                    </div>
                    <div class="form-group col-sm">
                        <label for="form-1-y"><span class="badge badge-info">y</span></label>
                        <input required type="number" step="0.01" class="form-control" id="form-1-y" placeholder="y">
                    </div>
                    <div class="form-group col-sm">
                        <label for="form-1-btn">Calculate</label>
                        <button required type="submit" class="btn btn-primary btn-block" id="form-1-btn">
                            Calculate
                        </button>
                    </div>
                </div>
                <div class="text-center">
                    <p class="lead">The result is</p>
                    <div id="form-1-result" class="display-4">--</div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- x is what percent of y (Form 2) -->
<section class="my-5">
    <h3><span class="badge badge-info">x</span> is what percent of <span class="badge badge-info">y</span></h3>
    <p><i>Example: 50 is 5% of 1000.</i></p>
    <div class="card">
        <div class="card-body">
            <form id="form-2">
                <div class="form-row">
                    <div class="form-group col-sm">
                        <label for="form-2-x"><span class="badge badge-info">x</span></label>
                        <input required type="number" step="0.01" class="form-control" id="form-2-x" placeholder="x">
                    </div>
                    <div class="form-group col-sm">
                        <label for="form-2-y"><span class="badge badge-info">y</span></label>
                        <input required type="number" step="0.01" class="form-control" id="form-2-y" placeholder="y">
                    </div>
                    <div class="form-group col-sm">
                        <label for="form-2-btn">Calculate</label>
                        <button required type="submit" class="btn btn-primary btn-block" id="form-2-btn">
                            Calculate
                        </button>
                    </div>
                </div>
                <div class="text-center">
                    <p class="lead">The result is</p>
                    <div id="form-2-result" class="display-4">--</div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- What is the % difference from x to y (Form 3) -->
<section class="my-5">
    <h3>What is the % increase/decrease from <span class="badge badge-info">x</span> to <span class="badge badge-info">y</span></h3>
    <p>
        <i>Example 1: the difference from 50 to 1000 is 1900%.</i>
        <br>
        <i>Example 2: the difference from 100 to 200 is 100%.</i>
    </p>
    <div class="card">
        <div class="card-body">
            <form id="form-3">
                <div class="form-row">
                    <div class="form-group col-sm">
                        <label for="form-3-x"><span class="badge badge-info">x</span></label>
                        <input required type="number" step="0.01" class="form-control" id="form-3-x" placeholder="x">
                    </div>
                    <div class="form-group col-sm">
                        <label for="form-3-y"><span class="badge badge-info">y</span></label>
                        <input required type="number" step="0.01" class="form-control" id="form-3-y" placeholder="y">
                    </div>
                    <div class="form-group col-sm">
                        <label for="form-3-btn">Calculate</label>
                        <button required type="submit" class="btn btn-primary btn-block" id="form-3-btn">
                            Calculate
                        </button>
                    </div>
                </div>
                <div class="text-center">
                    <p class="lead">The result is</p>
                    <div id="form-3-result" class="display-4">--</div>
                </div>
            </form>
        </div>
    </div>
</section>

<script src="/assets/js/percentage-calculator.js"></script>