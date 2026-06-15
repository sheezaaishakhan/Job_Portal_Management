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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .message {
            margin-bottom: 20px;
            padding: 12px 15px;
            border-radius: 5px;
            display: none;
        }

        .message.show {
            display: block;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .errors {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .errors.show {
            display: block;
        }

        .errors ul {
            margin-left: 20px;
            margin-top: 8px;
        }

        .errors li {
            margin: 4px 0;
        }

        .form-section {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #4CAF50;
        }

        .form-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        label .required {
            color: #d32f2f;
        }

        input[type="text"],
        input[type="number"],
        input[type="email"],
        select,
        textarea {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input.error,
        select.error,
        textarea.error {
            border-color: #d32f2f;
            background: #ffebee;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-submit {
            background: #4CAF50;
            color: white;
            flex: 1;
        }

        .btn-submit:hover {
            background: #45a049;
        }

        .btn-cancel {
            background: #757575;
            color: white;
            padding: 10px 20px;
        }

        .btn-cancel:hover {
            background: #616161;
        }

        .table-section {
            margin-top: 30px;
        }

        .table-section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #4CAF50;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #45a049;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-edit, .btn-delete {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 3px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #2196F3;
            color: white;
        }

        .btn-edit:hover {
            background: #0b7dda;
        }

        .btn-delete {
            background: #f44336;
            color: white;
        }

        .btn-delete:hover {
            background: #da190b;
        }

        .no-data {
            text-align: center;
            color: #999;
            padding: 40px;
            font-style: italic;
        }

        .price {
            color: #4CAF50;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 20px;
            }

            .table-wrapper {
                font-size: 12px;
            }

            th, td {
                padding: 8px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-edit, .btn-delete {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Product Management System</h1>

    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?> show">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="errors show">
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- ADD / EDIT FORM -->
    <div class="form-section">
        <h2><?php echo $edit_product ? "Edit Product" : "Add New Product"; ?></h2>
        
        <form method="POST" id="productForm" novalidate>
            <?php if ($edit_product): ?>
                <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Product Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo $edit_product['name'] ?? $_POST['name'] ?? ''; ?>"
                           minlength="3" maxlength="100" required>
                    <small style="color: #999; margin-top: 4px;">3-100 characters</small>
                </div>

                <div class="form-group">
                    <label for="category">Category <span class="required">*</span></label>
                    <select id="category" name="category" required>
                        <option value="">-- Select Category --</option>
                        <option value="Electronics" <?php echo ($edit_product['category'] ?? $_POST['category'] ?? '') === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                        <option value="Clothing" <?php echo ($edit_product['category'] ?? $_POST['category'] ?? '') === 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
                        <option value="Books" <?php echo ($edit_product['category'] ?? $_POST['category'] ?? '') === 'Books' ? 'selected' : ''; ?>>Books</option>
                        <option value="Food" <?php echo ($edit_product['category'] ?? $_POST['category'] ?? '') === 'Food' ? 'selected' : ''; ?>>Food</option>
                        <option value="Others" <?php echo ($edit_product['category'] ?? $_POST['category'] ?? '') === 'Others' ? 'selected' : ''; ?>>Others</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price <span class="required">*</span></label>
                    <input type="number" id="price" name="price" step="0.01" min="0.01" max="999999.99"
                           value="<?php echo $edit_product['price'] ?? $_POST['price'] ?? ''; ?>" required>
                    <small style="color: #999; margin-top: 4px;">Must be greater than 0</small>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity <span class="required">*</span></label>
                    <input type="number" id="quantity" name="quantity" min="0" max="999999"
                           value="<?php echo $edit_product['quantity'] ?? $_POST['quantity'] ?? ''; ?>" required>
                    <small style="color: #999; margin-top: 4px;">Whole numbers only</small>
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <textarea id="description" name="description" maxlength="500"><?php echo $edit_product['description'] ?? $_POST['description'] ?? ''; ?></textarea>
                    <small style="color: #999; margin-top: 4px;">Max 500 characters</small>
                </div>
            </div>

            <div class="form-buttons">
                <?php if ($edit_product): ?>
                    <button type="submit" name="update_product" class="btn-submit">💾 Update Product</button>
                    <a href="oel.php" class="btn-cancel">✕ Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_product" class="btn-submit">➕ Add Product</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- DISPLAY ALL PRODUCTS -->
    <div class="table-section">
        <h2>Product List</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td class="price">$<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['description'], 0, 30)) . (strlen($row['description']) > 30 ? '...' : ''); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="oel.php?edit_id=<?php echo $row['id']; ?>">
                                            <button class="btn-edit">✏️ Edit</button>
                                        </a>
                                        <a href="oel.php?delete_id=<?php echo $row['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this product?');">
                                            <button class="btn-delete">🗑️ Delete</button>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-data">No products found. Add one to get started!</p>
        <?php endif; ?>
    </div>
</div>

<script>
    // Frontend Validation
    document.getElementById('productForm').addEventListener('submit', function(e) {
        let errors = [];
        
        // Validate Product Name
        const name = document.getElementById('name').value.trim();
        if (!name) {
            errors.push('Product name is required');
        } else if (name.length < 3) {
            errors.push('Product name must be at least 3 characters');
        } else if (name.length > 100) {
            errors.push('Product name cannot exceed 100 characters');
        }

        // Validate Price
        const price = document.getElementById('price').value.trim();
        if (!price) {
            errors.push('Price is required');
        } else if (isNaN(price)) {
            errors.push('Price must be a valid number');
        } else if (parseFloat(price) <= 0) {
            errors.push('Price must be greater than 0');
        }

        // Validate Quantity
        const quantity = document.getElementById('quantity').value.trim();
        if (!quantity && quantity !== '0') {
            errors.push('Quantity is required');
        } else if (!Number.isInteger(Number(quantity))) {
            errors.push('Quantity must be a whole number');
        } else if (parseInt(quantity) < 0) {
            errors.push('Quantity cannot be negative');
        }

        // Validate Category
        const category = document.getElementById('category').value;
        if (!category) {
            errors.push('Category is required');
        }

        // Validate Description
        const description = document.getElementById('description').value;
        if (description.length > 500) {
            errors.push('Description cannot exceed 500 characters');
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
            return false;
        }
    });

    // Clear old messages after 5 seconds
    window.addEventListener('load', function() {
        const message = document.querySelector('.message.show');
        if (message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, 5000);
        }
    });
</script>

</body>
</html>

<?php
$conn->close();
?>
