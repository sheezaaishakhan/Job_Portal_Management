<?php
// ===== DATABASE CONNECTION =====
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "product_module";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ===== CREATE PRODUCTS TABLE =====
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql);

// ===== VALIDATION FUNCTIONS =====
function validateProductName($name) {
    $errors = [];
    if (empty(trim($name))) {
        $errors[] = "Product name is required";
    } elseif (strlen($name) < 3) {
        $errors[] = "Product name must be at least 3 characters";
    } elseif (strlen($name) > 100) {
        $errors[] = "Product name cannot exceed 100 characters";
    }
    return $errors;
}

function validatePrice($price) {
    $errors = [];
    if (empty($price)) {
        $errors[] = "Price is required";
    } elseif (!is_numeric($price)) {
        $errors[] = "Price must be a valid number";
    } elseif ((float)$price <= 0) {
        $errors[] = "Price must be greater than 0";
    } elseif ((float)$price > 999999.99) {
        $errors[] = "Price is too large";
    }
    return $errors;
}

function validateQuantity($quantity) {
    $errors = [];
    if (empty($quantity) && $quantity !== "0") {
        $errors[] = "Quantity is required";
    } elseif (!is_numeric($quantity) || intval($quantity) != $quantity) {
        $errors[] = "Quantity must be a whole number";
    } elseif ((int)$quantity < 0) {
        $errors[] = "Quantity cannot be negative";
    } elseif ((int)$quantity > 999999) {
        $errors[] = "Quantity is too large";
    }
    return $errors;
}

function validateCategory($category) {
    $errors = [];
    if (empty(trim($category))) {
        $errors[] = "Category is required";
    } elseif (strlen($category) > 50) {
        $errors[] = "Category cannot exceed 50 characters";
    }
    return $errors;
}

function validateDescription($description) {
    $errors = [];
    if (!empty($description) && strlen($description) > 500) {
        $errors[] = "Description cannot exceed 500 characters";
    }
    return $errors;
}

// ===== HANDLE FORM SUBMISSION =====
$message = "";
$message_type = "";
$errors = [];

// ADD PRODUCT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_product"])) {
    $name = trim($_POST["name"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");

    // Validate all fields
    $errors = array_merge(
        validateProductName($name),
        validatePrice($price),
        validateQuantity($quantity),
        validateCategory($category),
        validateDescription($description)
    );

    if (empty($errors)) {
        $name = $conn->real_escape_string($name);
        $price = (float)$price;
        $quantity = (int)$quantity;
        $category = $conn->real_escape_string($category);
        $description = $conn->real_escape_string($description);

        $sql = "INSERT INTO products (name, description, price, quantity, category) 
                VALUES ('$name', '$description', $price, $quantity, '$category')";

        if ($conn->query($sql) === TRUE) {
            $message = "Product added successfully!";
            $message_type = "success";
            // Clear form
            $_POST = [];
        } else {
            $message = "Error: " . $conn->error;
            $message_type = "error";
        }
    } else {
        $message_type = "error";
    }
}

// DELETE PRODUCT
if (isset($_GET["delete_id"])) {
    $id = intval($_GET["delete_id"]);
    $sql = "DELETE FROM products WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        $message = "Product deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting product";
        $message_type = "error";
    }
}

// UPDATE PRODUCT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_product"])) {
    $id = intval($_POST["id"] ?? 0);
    $name = trim($_POST["name"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");

    // Validate all fields
    $errors = array_merge(
        validateProductName($name),
        validatePrice($price),
        validateQuantity($quantity),
        validateCategory($category),
        validateDescription($description)
    );

    if (empty($errors)) {
        $name = $conn->real_escape_string($name);
        $price = (float)$price;
        $quantity = (int)$quantity;
        $category = $conn->real_escape_string($category);
        $description = $conn->real_escape_string($description);

        $sql = "UPDATE products SET name='$name', description='$description', price=$price, 
                quantity=$quantity, category='$category' WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            $message = "Product updated successfully!";
            $message_type = "success";
            // Redirect to avoid form resubmission
            header("Location: oel.php");
            exit();
        } else {
            $message = "Error: " . $conn->error;
            $message_type = "error";
        }
    } else {
        $message_type = "error";
    }
}

// GET PRODUCT FOR EDITING
$edit_product = null;
if (isset($_GET["edit_id"])) {
    $id = intval($_GET["edit_id"]);
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $edit_product = $result->fetch_assoc();
    }
}

// READ ALL PRODUCTS
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - CRUD</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow p-4">

        <h2 class="text-center mb-4">Product Management System</h2>

        <!-- MESSAGE -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- ERRORS -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <h4><?php echo $edit_product ? "Edit Product" : "Add Product"; ?></h4>

        <form method="POST">
            <?php if ($edit_product): ?>
                <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control"
                        value="<?php echo $edit_product['name'] ?? $_POST['name'] ?? ''; ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control" required>
                        <option value="">Select</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Books">Books</option>
                        <option value="Food">Food</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" class="form-control"
                        value="<?php echo $edit_product['price'] ?? $_POST['price'] ?? ''; ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control"
                        value="<?php echo $edit_product['quantity'] ?? $_POST['quantity'] ?? ''; ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control"><?php echo $edit_product['description'] ?? $_POST['description'] ?? ''; ?></textarea>
            </div>

            <?php if ($edit_product): ?>
                <button type="submit" name="update_product" class="btn btn-primary w-100">Update</button>
            <?php else: ?>
                <button type="submit" name="add_product" class="btn btn-success w-100">Add</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- TABLE -->
    <div class="card mt-4 shadow p-3">
        <h4>Product List</h4>

        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['category']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>

                    <td>
                        <a href="?edit_id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?delete_id=<?php echo $row['id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>

<?php
$conn->close();
?>
