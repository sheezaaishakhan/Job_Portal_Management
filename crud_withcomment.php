<?php
// ===== DATABASE CONNECT KAR RAHE HAIN =====
$conn = new mysqli("localhost", "root", "", "student_management");

// ===== TABLE CREATE KAR RAHE HAIN (AGAR PEHLE SE NA HO) =====
$conn->query("CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,   // auto id generate hogi
    name VARCHAR(100),                   // student ka name
    phone VARCHAR(15)                    // student ka phone
)");

$msg = ""; // message show karne ke liye variable

// ===== ADD STUDENT =====
if (isset($_POST['add'])) {
    $name = $_POST['name'];     // form se name le rahe hain
    $phone = $_POST['phone'];   // form se phone le rahe hain

    // database me insert kar rahe hain
    $conn->query("INSERT INTO students (name, phone) VALUES ('$name', '$phone')");
    
    $msg = "Student Add ho gaya!";
}

// ===== DELETE STUDENT =====
if (isset($_GET['del'])) {
    // jis id ko delete karna hai usko URL se le rahe hain
    $conn->query("DELETE FROM students WHERE id=".$_GET['del']);
    
    $msg = "Student Delete ho gaya!";
}

// ===== EDIT KE LIYE DATA NIKALNA =====
$edit = null; // default null
if (isset($_GET['edit'])) {
    // jis id ko edit karna hai uska data fetch kar rahe hain
    $res = $conn->query("SELECT * FROM students WHERE id=".$_GET['edit']);
    $edit = $res->fetch_assoc(); // data array me aa jata hai
}

// ===== UPDATE STUDENT =====
if (isset($_POST['update'])) {
    // update query chal rahi hai
    $conn->query("UPDATE students SET 
        name='".$_POST['name']."',
        phone='".$_POST['phone']."'
        WHERE id=".$_POST['id']);
    
    $msg = "Student Update ho gaya!";
}

// ===== SARA DATA FETCH KARNA =====
$data = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<body>

<h2>Student CRUD</h2>

<!-- ===== MESSAGE SHOW KAR RAHE HAIN ===== -->
<p><?php echo $msg; ?></p>

<!-- ===== FORM (ADD / UPDATE DONO KE LIYE) ===== -->
<form method="post">

    <!-- hidden id update ke liye use hoti hai -->
    <input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">
    
    <!-- name input -->
    Name: 
    <input type="text" name="name" 
           value="<?php echo $edit['name'] ?? ''; ?>" required>

    <!-- phone input -->
    Phone: 
    <input type="text" name="phone" 
           value="<?php echo $edit['phone'] ?? ''; ?>" required>

    <!-- agar edit mode hai to update button warna add -->
    <?php if ($edit): ?>
        <button name="update">Update</button>
    <?php else: ?>
        <button name="add">Add</button>
    <?php endif; ?>
</form>

<hr>

<!-- ===== TABLE ME DATA SHOW KAR RAHE HAIN ===== -->
<table border="1">

<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Phone</th>
    <th>Action</th>
</tr>

<?php while($row = $data->fetch_assoc()): ?>
<tr>
    <!-- har row ka data show -->
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['phone']; ?></td>

    <td>
        <!-- edit button -->
        <a href="?edit=<?php echo $row['id']; ?>">Edit</a>

        <!-- delete button -->
        <a href="?del=<?php echo $row['id']; ?>">Delete</a>
    </td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>