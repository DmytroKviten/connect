<?php
session_start();
$host = 'db'; 
$port = '3306';  
$dbname = 'sait'; 
$user = 'root'; 
$pass = 'root'; 
$dsn = "mysql:host=db;port=3306;dbname=sait;charset=utf8";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM reg WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['pass'])) {
            $_SESSION['user'] = $user['name']; 
            header("Location: ../index.php"); 
            exit;
        } else {
            
            echo "Неправильний логін або пароль.";
        }
    }
} catch (PDOException $e) {
    die("Помилка підключення до бази даних: " . $e->getMessage());
}
?>