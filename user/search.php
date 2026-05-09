<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Medicine Search';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="form-wrap wide">
    <h1>Search Medicine Stock</h1>
    <form class="card form-card" action="<?= e(app_url('user/search_results.php')); ?>" method="get">
        <label for="q">Medicine name, generic, or category</label>
        <input type="search" id="q" name="q" placeholder="Example: Metformin, insulin, antibiotic" required>

        <div class="grid two">
            <div>
                <label for="city">City</label>
                <input type="text" id="city" name="city" placeholder="Mumbai">
            </div>
            <div>
                <label for="area">Area</label>
                <input type="text" id="area" name="area" placeholder="Bandra">
            </div>
        </div>

        <button type="submit">Search</button>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
