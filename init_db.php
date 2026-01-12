<?php
try {
    $database = new SQLite3('users.db');
    
    $query = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        gender TEXT,
        age INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($database->exec($query)) {
        echo "Database and table created successfully!<br>";
        echo "Database file: users.db<br>";
        echo "Table: users created with columns: id, username, email, password, gender, age, created_at";
    } else {
        echo "Error creating table.";
    }
    
    $database->close();
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
