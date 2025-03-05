<?php
session_start();
if (isset($_SESSION['registration_success'])) {
    echo '<script>alert("'.$_SESSION['registration_success'].'")</script>';
    unset($_SESSION['registration_success']);
}
?>



<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="intro" id="intro">
<div class="form-wrapper">
  
  <h2>Вхід</h2>
  <form action="login.php" method="post">
    <input type="text" class="form-control" name="login" placeholder="Введіть логін" required><br>
    <input type="password" class="form-control" name="password" placeholder="Введіть пароль" required><br>
    <button type="submit">Увійти</button>
  </form>
</div>

</form>
  </form>
</div>

<script src="three.min.js"></script>
<script src="vanta.net.min.js"></script>
<script src="app.js"></script>




</body>
</html>