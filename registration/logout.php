<?php
session_start();
session_unset(); // Очищує всі сесійні змінні
session_destroy(); // Знищує сесію
header("Location: ../index.php"); // Перенаправлення назад на головну сторінку
exit;
?>