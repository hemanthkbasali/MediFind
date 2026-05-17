
<?php


require_once __DIR__ . '/../includes/functions.php';

$query = input('q');
$city = input('city');
$area = input('area');

$like = '%' . $query . '%';
$cityLike = '%' . $city . '%';
$areaLike = '%' . $area . '%';

$stmt = $conn->prepare(
    'SELECT
        s.id AS stock_id,
        s.quantity,
        s.price,
        s.batch_no,
        s.expiry_date,
        m.id AS medicine_id,
        m.name AS medicine_name,
        m.generic_name,
        m.strength,
        m.form,
        p.id AS pharmacy_id,
        p.pharmacy_name,
        p.phone,
        p.address,
        p.area,
        p.city
    FROM stock s
    INNER JOIN medicines m ON m.id = s.medicine_id
    INNER JOIN pharmacies p ON p.id = s.pharmacy_id
    WHERE p.status = "active"
      AND s.quantity > 0
      AND (
            ? = ""
            OR m.name LIKE ?
            OR m.generic_name LIKE ?
            OR m.category LIKE ?
      )
      AND (? = "" OR p.city LIKE ?)
      AND (? = "" OR p.area LIKE ?)
    ORDER BY m.name ASC, s.price ASC, p.area ASC'
);

$stmt->bind_param(
    'ssssssss',
    $query,
    $like,
    $like,
    $like,
    $city,
    $cityLike,
    $area,
    $areaLike
);

$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$substitutes = [];

if ($query !== '') {
    $medicineStmt = $conn->prepare(
        'SELECT id
         FROM medicines
         WHERE name LIKE ?
            OR generic_name LIKE ?
         ORDER BY name
         LIMIT 1'
    );

    $medicineStmt->bind_param('ss', $like, $like);
    $medicineStmt->execute();

    $medicine = $medicineStmt->get_result()->fetch_assoc();

    if ($medicine) {
        $subStmt = $conn->prepare(
            'SELECT
                sm.reason,
                m.name,
                m.generic_name,
                m.strength,
                m.form
             FROM substitute_medicines sm
             INNER JOIN medicines m
                ON m.id = sm.substitute_medicine_id
             WHERE sm.medicine_id = ?
             ORDER BY m.name'
        );

        $subStmt->bind_param('i', $medicine['id']);
        $subStmt->execute();

        $substitutes = $subStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

$page_title = 'Search Results';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section-heading">
    <div>
        <h1>Search Results</h1>

        <p class="muted">
            <?= count($results); ?> matching stock record<?= count($results) === 1 ? '' : 's'; ?>

            <?php if ($query !== ''): ?>
                for "<?= e($query); ?>"
            <?php endif; ?>
        </p>
    </div>

    <a class="button secondary" href="<?= e(app_url('user/search.php')); ?>">
        New Search
    </a>
</section>

<?php if ($substitutes): ?>
<section class="card">
    <h2>Possible substitutes</h2>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Generic</th>
                    <th>Form</th>
                    <th>Reason</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($substitutes as $substitute): ?>
                <tr>
                    <td>
                        <?= e($substitute['name']); ?>
                        <?= e($substitute['strength']); ?>
                    </td>

                    <td><?= e($substitute['generic_name']); ?></td>
                    <td><?= e($substitute['form']); ?></td>
                    <td><?= e($substitute['reason']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<section class="card">

<?php if (!$results): ?>

    <p>
        No available stock matched your search.
        Try a generic name or wider location.
    </p>

<?php else: ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Pharmacy</th>
                    <th>Area</th>
                    <th>Stock</th>
                    <th>Price</th>
                    <th>Expiry</th>
                    <th>Order</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td>
                        <strong>
                            <?= e($row['medicine_name']); ?>
                        </strong>
                        <br>

                        <span class="muted">
                            <?= e($row['generic_name']); ?>,
                            <?= e($row['strength']); ?>
                            <?= e($row['form']); ?>
                        </span>
                    </td>

                    <td>
                        <strong>
                            <?= e($row['pharmacy_name']); ?>
                        </strong>
                        <br>

                        <span class="muted">
                            <?= e($row['phone']); ?>
                        </span>
                    </td>

                    <td>
                        <?= e($row['area']); ?>,
                        <?= e($row['city']); ?>
                    </td>

                    <td>
                        <?= (int) $row['quantity']; ?>
                    </td>

                    <td>
                        Rs. <?= number_format((float) $row['price'], 2); ?>
                    </td>

                    <td>
                        <?= e($row['expiry_date']); ?>
                    </td>

                    <td>
                        <form action="<?= e(app_url('user/place_order.php')); ?>" method="POST">
                            <input
                                type="hidden"
                                name="stock_id"
                                value="<?= (int) $row['stock_id']; ?>"
                            >

                            <input
                                type="number"
                                name="quantity"
                                min="1"
                                max="<?= (int) $row['quantity']; ?>"
                                value="1"
                                required
                            >

                            <button type="submit" class="button">
                                Reserve
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>



