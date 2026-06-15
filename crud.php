<?php
$conn = new mysqli("localhost", "root", "", "student_management");

// Create table
$conn->query("CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    phone VARCHAR(15)
)");

$msg = "";

// ADD
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];

    $conn->query("INSERT INTO students (name, phone) VALUES ('$name', '$phone')");
    $msg = "Added!";
}

// DELETE
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM students WHERE id=".$_GET['del']);
    $msg = "Deleted!";
}

// GET DATA FOR EDIT
$edit = null;
if (isset($_GET['edit'])) {
    $res = $conn->query("SELECT * FROM students WHERE id=".$_GET['edit']);
    $edit = $res->fetch_assoc();
}

// UPDATE
if (isset($_POST['update'])) {
    $conn->query("UPDATE students SET 
        name='".$_POST['name']."',
        phone='".$_POST['phone']."'
        WHERE id=".$_POST['id']);
    $msg = "Updated!";
}

// FETCH ALL
$data = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<body>

<h2>Student CRUD</h2>
<p><?php echo $msg; ?></p>

<form method="post">
    <input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">
    
    Name: <input type="text" name="name" value="<?php echo $edit['name'] ?? ''; ?>" required>
    Phone: <input type="text" name="phone" value="<?php echo $edit['phone'] ?? ''; ?>" required>

    <?php if ($edit): ?>
        <button name="update">Update</button>
    <?php else: ?>
        <button name="add">Add</button>
    <?php endif; ?>
</form>

<hr>

<table border="1">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Phone</th>
    <th>Action</th>
</tr>

<?php while($row = $data->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['phone']; ?></td>
    <td>
        <a href="?edit=<?php echo $row['id']; ?>">Edit</a>
        <a href="?del=<?php echo $row['id']; ?>">Delete</a>
    </td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>