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
        $name = $_POST['name'] ?? '';
        $password = $_POST['password'] ?? '';

        $login = filter_var($_POST['login'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $stmt = $pdo->prepare("SELECT * FROM reg WHERE login = ?");
        $stmt->execute([$login]);

        if ($stmt->rowCount() > 0) {
            echo "Користувач з таким логіном вже існує.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
            $stmt = $pdo->prepare("INSERT INTO reg (login, name, pass) VALUES (?, ?, ?)");
            $stmt->execute([$login, $name, $hashed_password]);
        
            
            $_SESSION['registration_success'] = 'Користувача успішно зареєстровано! Будь ласка, увійдіть.';
        
           
            header("Location: Logi.php");
            exit;
        }
    }

   
    $_SESSION['user'] = $name; 

} catch (PDOException $e) {
    die("Помилка підключення до бази даних: " . $e->getMessage());
}
?>

