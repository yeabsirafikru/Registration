<?php
session_start();

$db = new SQLite3('users.db');

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    email TEXT UNIQUE,
    password TEXT,
    gender TEXT,
    age INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// LOGIN
if (isset($_POST['login'])) {
    $login_username = trim($_POST['login_username'] ?? '');
    $login_password = $_POST['login_password'] ?? '';
    
    if ($login_username && $login_password) {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bindValue(1, $login_username);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($user && password_verify($login_password, $user['password'])) {
            $_SESSION['logged_user'] = $user;
            header("Location: " . $_SERVER['PHP_SELF'] . "?login_success=1");
            exit;
        } else {
            $login_error = "Invalid login";
        }
    }
}
if (isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $age = $_POST['age'] ?? '';
    
    $errors = [];
    if (!$username) $errors[] = "Username required";
    if (!$email) $errors[] = "Email required";
    if (!$password) $errors[] = "Password required";
    if ($password != $confirm) $errors[] = "Passwords don't match";
    
    if (!$errors) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bindValue(1, $username);
        $stmt->bindValue(2, $email);
        $result = $stmt->execute();
        
        if ($result->fetchArray()) {
            $errors[] = "User exists";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password, gender, age) VALUES (?, ?, ?, ?, ?)");
            $stmt->bindValue(1, $username);
            $stmt->bindValue(2, $email);
            $stmt->bindValue(3, $hash);
            $stmt->bindValue(4, $gender);
            $stmt->bindValue(5, $age ?: null);
            
            if ($stmt->execute()) {
                $_SESSION['new_user'] = [
                    'username' => $username,
                    'email' => $email,
                    'gender' => $gender,
                    'age' => $age
                ];
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                exit;
            }
        }
    }
}
if (isset($_GET['success']) && isset($_SESSION['new_user'])) {
    $user = $_SESSION['new_user'];
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Registration Successful</h2>
    <p>Your details:</p>
    <table border="1">
        <tr><td>Username:</td><td><?= htmlspecialchars($user['username']) ?></td></tr>
        <tr><td>Email:</td><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><td>Gender:</td><td><?= htmlspecialchars($user['gender']) ?></td></tr>
        <tr><td>Age:</td><td><?= htmlspecialchars($user['age']) ?></td></tr>
    </table>
    <p><a href="<?= $_SERVER['PHP_SELF'] ?>">← Back</a></p>
</body>
</html>
<?php
    unset($_SESSION['new_user']);
    exit;
}

if (isset($_GET['login_success']) && isset($_SESSION['logged_user'])) {
    $user = $_SESSION['logged_user'];
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Login Successful</h2>
    <p>Welcome <?= htmlspecialchars($user['username']) ?>!</p>
    <p>Your details from database:</p>
    <table border="1">
        <tr><td>ID:</td><td><?= htmlspecialchars($user['id']) ?></td></tr>
        <tr><td>Username:</td><td><?= htmlspecialchars($user['username']) ?></td></tr>
        <tr><td>Email:</td><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><td>Gender:</td><td><?= htmlspecialchars($user['gender']) ?></td></tr>
        <tr><td>Age:</td><td><?= htmlspecialchars($user['age']) ?></td></tr>
        <tr><td>Created:</td><td><?= htmlspecialchars($user['created_at']) ?></td></tr>
    </table>
    <p><a href="<?= $_SERVER['PHP_SELF'] ?>">← Back</a> | 
       <a href="<?= $_SERVER['PHP_SELF'] ?>?logout=1">Logout</a></p>
</body>
</html>
<?php
    exit;
}
if (isset($_GET['logout'])) {
    unset($_SESSION['logged_user']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Test System</h2>
    
    <h3>Login (Test Database)</h3>
    <?php if (isset($login_error)): ?>
        <p style="color:red"><?= $login_error ?></p>
    <?php endif; ?>
    
    <form method="post">
        <input type="hidden" name="login" value="1">
        <p>Username: <input type="text" name="login_username" required></p>
        <p>Password: <input type="password" name="login_password" required></p>
        <p><input type="submit" value="Login"></p>
    </form>
    
    <hr>
    
    <h3>Register New User</h3>
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <p style="color:red"><?= $error ?></p>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <form method="post">
        <input type="hidden" name="register" value="1">
        <p>Username: <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required></p>
        <p>Email: <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required></p>
        <p>Password: <input type="password" name="password" required></p>
        <p>Confirm: <input type="password" name="confirm" required></p>
        <p>Gender: 
            <select name="gender">
                <option value="">Select</option>
                <option value="male" <?= ($gender ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                <option value="female" <?= ($gender ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                <option value="other" <?= ($gender ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
            </select>
        </p>
        <p>Age: <input type="number" name="age" value="<?= htmlspecialchars($age ?? '') ?>" min="1" max="150"></p>
        <p><input type="submit" value="Register"></p>
    </form>
    
    <hr>
    <h3>All Users in Database</h3>
    <?php
    $result = $db->query("SELECT id, username, email, gender, age FROM users ORDER BY id");
    if ($result) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Gender</th><th>Age</th></tr>";
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
            echo "<td>" . htmlspecialchars($row['age']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    ?>
</body>
</html>
