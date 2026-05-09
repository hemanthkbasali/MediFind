<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM medicines WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        set_flash($ok ? 'success' : 'danger', $ok ? 'Medicine deleted.' : 'Could not delete medicine with stock, orders, or substitutes.');
        redirect('admin/manage_medicines.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $name = input('name');
    $genericName = input('generic_name');
    $brand = input('brand');
    $category = input('category');
    $strength = input('strength');
    $form = input('form');
    $description = input('description');
    $requiresPrescription = isset($_POST['requires_prescription']) ? 1 : 0;

    if ($id > 0) {
        $stmt = $conn->prepare(
            'UPDATE medicines
             SET name = ?, generic_name = ?, brand = ?, category = ?, strength = ?, form = ?, description = ?, requires_prescription = ?
             WHERE id = ?'
        );
        $stmt->bind_param('sssssssii', $name, $genericName, $brand, $category, $strength, $form, $description, $requiresPrescription, $id);
        $ok = $stmt->execute();
        set_flash($ok ? 'success' : 'danger', $ok ? 'Medicine updated.' : 'Could not update medicine.');
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO medicines (name, generic_name, brand, category, strength, form, description, requires_prescription)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssssssi', $name, $genericName, $brand, $category, $strength, $form, $description, $requiresPrescription);
        $ok = $stmt->execute();
        set_flash($ok ? 'success' : 'danger', $ok ? 'Medicine added.' : 'Could not add medicine.');
    }

    redirect('admin/manage_medicines.php');
}

if ($editId > 0) {
    $stmt = $conn->prepare('SELECT * FROM medicines WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

$medicines = $conn->query(
    'SELECT m.*,
            COUNT(DISTINCT s.id) AS stock_rows,
            COUNT(DISTINCT sm.id) AS substitute_rows
     FROM medicines m
     LEFT JOIN stock s ON s.medicine_id = m.id
     LEFT JOIN substitute_medicines sm ON sm.medicine_id = m.id
     GROUP BY m.id
     ORDER BY m.name'
)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Manage Medicines';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section-heading">
    <div>
        <h1>Manage Medicines</h1>
        <p class="muted">Maintain the central medicine catalogue used by pharmacies.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('admin/dashboard.php')); ?>">Dashboard</a>
</section>

<section class="card">
    <h2><?= $editing ? 'Edit Medicine' : 'Add Medicine'; ?></h2>
    <form method="post" class="form-card">
        <?= csrf_field(); ?>
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0); ?>">

        <div class="grid three">
            <div>
                <label for="name">Medicine name</label>
                <input type="text" id="name" name="name" value="<?= e($editing['name'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="generic_name">Generic name</label>
                <input type="text" id="generic_name" name="generic_name" value="<?= e($editing['generic_name'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="brand">Brand</label>
                <input type="text" id="brand" name="brand" value="<?= e($editing['brand'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="grid three">
            <div>
                <label for="category">Category</label>
                <input type="text" id="category" name="category" value="<?= e($editing['category'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="strength">Strength</label>
                <input type="text" id="strength" name="strength" value="<?= e($editing['strength'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="form">Form</label>
                <input type="text" id="form" name="form" value="<?= e($editing['form'] ?? ''); ?>" required>
            </div>
        </div>

        <label for="description">Description</label>
        <input type="text" id="description" name="description" value="<?= e($editing['description'] ?? ''); ?>">

        <label class="check-row">
            <input type="checkbox" name="requires_prescription" value="1" <?= !empty($editing['requires_prescription']) ? 'checked' : ''; ?>>
            Requires prescription
        </label>

        <button type="submit">Save Medicine</button>
        <?php if ($editing): ?>
            <a class="button secondary" href="<?= e(app_url('admin/manage_medicines.php')); ?>">Cancel Edit</a>
        <?php endif; ?>
    </form>
</section>

<section class="card">
    <h2>Medicine Catalogue</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Generic</th>
                    <th>Category</th>
                    <th>Form</th>
                    <th>Prescription</th>
                    <th>Stock Rows</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($medicines as $medicine): ?>
                    <tr>
                        <td><?= e($medicine['name']); ?><br><span class="muted"><?= e($medicine['brand']); ?></span></td>
                        <td><?= e($medicine['generic_name']); ?></td>
                        <td><?= e($medicine['category']); ?></td>
                        <td><?= e($medicine['strength']); ?> <?= e($medicine['form']); ?></td>
                        <td><?= ((int) $medicine['requires_prescription']) === 1 ? 'Yes' : 'No'; ?></td>
                        <td><?= (int) $medicine['stock_rows']; ?></td>
                        <td>
                            <a class="button small" href="<?= e(app_url('admin/manage_medicines.php?edit=' . (int) $medicine['id'])); ?>">Edit</a>
                            <form method="post" class="inline-form" data-confirm="Delete this medicine? Related records may block deletion.">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $medicine['id']; ?>">
                                <button type="submit" class="danger-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
