<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

ensure_session_started();

// The user must be logged in
if (!isset($_SESSION['userID'])) {
    redirect_to('/login.php');
}

// Administrators cannot create shops
if (($_SESSION['role'] ?? '') === 'admin') {
    die('Administrators cannot create shops.');
}

$userID = (int)$_SESSION['userID'];

// Check whether the user already has a shop
$stmt = mysqli_prepare(
    $conn,
    'SELECT shop_name
     FROM users
     WHERE userID = ?
     LIMIT 1'
);

mysqli_stmt_bind_param($stmt, 'i', $userID);
mysqli_stmt_execute($stmt);

$existingShop = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

// Users who already have shops go to the product upload page
if (!empty($existingShop['shop_name'])) {
    redirect_to('/upload-product.php');
}

// Values used by the form
$shop_name = '';
$sellerPhone = '';
$province = '';
$city = '';
$area = '';
$collectionAvailable = 0;
$collectionAddress = '';
$error = '';

// Valid South African provinces
$validProvinces = [
    'Gauteng',
    'Limpopo',
    'Mpumalanga',
    'North West',
    'Free State',
    'KwaZulu-Natal',
    'Eastern Cape',
    'Western Cape',
    'Northern Cape'
];

// Process the Create Shop form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Protect the form against invalid requests
    verify_csrf();

    // Get and clean the submitted information
    $shop_name = trim($_POST['shop_name'] ?? '');
    $sellerPhone = trim($_POST['sellerPhone'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $collectionAvailable =
        isset($_POST['collectionAvailable'])
        && $_POST['collectionAvailable'] === '1'
        ? 1
        : 0;

    $collectionAddress = trim($_POST['collectionAddress'] ?? '');

    // Validate the shop name
    if ($shop_name === '') {
        $error = 'Please enter a shop name.';
    }

    // Validate the phone number
    elseif (
        $sellerPhone === ''
        || !preg_match('/^[0-9+\s()\-]{7,20}$/', $sellerPhone)
    ) {
        $error = 'Please enter a valid business phone number.';
    }

    // Validate the province
    elseif (!in_array($province, $validProvinces, true)) {
        $error = 'Please select a valid province.';
    }

    // Validate the city
    elseif ($city === '') {
        $error = 'Please enter your city or town.';
    }

    // Validate the area
    elseif ($area === '') {
        $error = 'Please enter your area or suburb.';
    } elseif (
        $collectionAvailable === 1
        && $collectionAddress === ''
    ) {
        $error = 'Please enter the complete collection address.';
    } elseif (strlen($collectionAddress) > 255) {
        $error = 'The collection address is too long.';
    }

    // Save the shop when all values are valid
    if ($error === '') {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users
             SET
                shop_name = ?,
                sellerPhone = ?,
                province = ?,
                city = ?,
                area = ?,
                collectionAvailable = ?,
                collectionAddress = ?,
                role = 'seller'
             WHERE userID = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'sssssi',
            $shop_name,
            $sellerPhone,
            $province,
            $city,
            $area,
            $collectionAvailable,
            $collectionAddress,
            $userID
        );

        if (mysqli_stmt_execute($stmt)) {

            // Update the active session role
            $_SESSION['role'] = 'seller';

            redirect_to('/upload-product.php');
        } else {
            $error = 'The shop could not be created. Please try again.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('/assets/css/style.css?v=60'); ?>">
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">

        <div class="row justify-content-center">

            <div class="col-md-6">

                <div class="card p-4">

                    <h2 class="mb-3">Create Your Shop</h2>

                    <p>Before uploading products, create a shop name that buyers will see on your listings.</p>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php } ?>

                    <form method="POST">

                        <!-- Security token -->
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo h(csrf_token()); ?>">

                        <!-- Shop name -->
                        <div class="mb-3">

                            <label for="shop_name" class="form-label">
                                Shop Name
                            </label>

                            <input
                                type="text"
                                name="shop_name"
                                id="shop_name"
                                class="form-control"
                                value="<?php echo h($shop_name); ?>"
                                placeholder="Example: Bokang's Spaza"
                                maxlength="100"
                                required>

                        </div>

                        <!-- Business phone -->
                        <div class="mb-3">

                            <label for="sellerPhone" class="form-label">
                                Business Contact Number
                            </label>

                            <input
                                type="tel"
                                name="sellerPhone"
                                id="sellerPhone"
                                class="form-control"
                                value="<?php echo h($sellerPhone); ?>"
                                placeholder="Example: 071 234 5678"
                                maxlength="20"
                                required>

                        </div>

                        <!-- Province -->
                        <div class="mb-3">

                            <label for="province" class="form-label">
                                Province
                            </label>

                            <select
                                name="province"
                                id="province"
                                class="form-select"
                                required>

                                <option value="">Select Province</option>

                                <?php foreach ($validProvinces as $option): ?>

                                    <option
                                        value="<?php echo h($option); ?>"
                                        <?php echo $province === $option ? 'selected' : ''; ?>>
                                        <?php echo h($option); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- City or town -->
                        <div class="mb-3">

                            <label for="city" class="form-label">
                                City or Town
                            </label>

                            <input
                                type="text"
                                name="city"
                                id="city"
                                class="form-control"
                                value="<?php echo h($city); ?>"
                                placeholder="Example: Johannesburg"
                                maxlength="100"
                                required>

                        </div>

                        <!-- Area or suburb -->
                        <div class="mb-3">

                            <label for="area" class="form-label">
                                Area or Suburb
                            </label>

                            <input
                                type="text"
                                name="area"
                                id="area"
                                class="form-control"
                                value="<?php echo h($area); ?>"
                                placeholder="Example: Midrand"
                                maxlength="100"
                                required>

                            <small class="text-muted">
                                Enter only your general trading area. Do not enter your full home or street address.
                            </small>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Do You Offer Collection?
                            </label>

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="collectionAvailable"
                                    id="collectionAvailable"
                                    value="1"
                                    <?php echo $collectionAvailable === 1 ? 'checked' : ''; ?>>

                                <label
                                    class="form-check-label"
                                    for="collectionAvailable">
                                    Yes, customers may collect products from my collection address.
                                </label>

                            </div>

                        </div>

                        <div
                            class="mb-3"
                            id="collectionAddressSection">

                            <label
                                for="collectionAddress"
                                class="form-label">
                                Complete Collection Address
                            </label>

                            <textarea
                                name="collectionAddress"
                                id="collectionAddress"
                                class="form-control"
                                rows="3"
                                maxlength="255"
                                placeholder="Example: Shop 4, Midrand Market, 123 Main Road, Midrand, Johannesburg"><?php echo h($collectionAddress); ?></textarea>

                            <small class="text-muted">
                                This address will be visible to customers when collection is available.
                                Only enter an address where customers can safely collect products.
                            </small>

                        </div>

                        <div class="alert alert-warning">
                            <strong>Delivery responsibility:</strong>
                            MyFriend does not provide delivery services. Shop owners are responsible
                            for arranging their own delivery or collection methods with customers.
                            Sellers should clearly communicate delivery costs, collection points and
                            expected delivery times.
                        </div>
                        <button
                            type="submit"
                            name="create_shop"
                            class="btn btn-shop">
                            Create Shop
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

    <script>
        const collectionCheckbox =
            document.getElementById('collectionAvailable');

        const collectionAddressSection =
            document.getElementById('collectionAddressSection');

        const collectionAddressInput =
            document.getElementById('collectionAddress');

        function updateCollectionAddressField() {
            const collectionIsAvailable =
                collectionCheckbox.checked;

            collectionAddressSection.style.display =
                collectionIsAvailable ? 'block' : 'none';

            collectionAddressInput.required =
                collectionIsAvailable;

            if (!collectionIsAvailable) {
                collectionAddressInput.value = '';
            }
        }

        collectionCheckbox.addEventListener(
            'change',
            updateCollectionAddressField
        );

        updateCollectionAddressField();
    </script>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>