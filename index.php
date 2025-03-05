<?php
session_start();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connect - IoT Solutions</title>
  <!-- Підключення Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Анімація появи (fadeIn) */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    .animate-fadeIn {
      animation: fadeIn 1s forwards;
    }
    .delay-200 { animation-delay: 0.2s; }
    .delay-400 { animation-delay: 0.4s; }
    .delay-600 { animation-delay: 0.6s; }
  </style>
</head>
<body class="bg-gray-50">
  <!-- Header: спочатку прозора, а при скролі набуває синього кольору -->
  <header id="main-header" class="fixed w-full top-0 z-50 bg-transparent transition-colors duration-300">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <div class="flex items-center space-x-4">
        <img src="image/krest.png" alt="Connect Logo" class="h-16 w-16">
        <h1 class="text-3xl font-bold text-white">Connect</h1>
      </div>
      <nav class="hidden md:block">
        <ul class="flex space-x-6">
          <li><a href="#" class="text-white text-3xl hover:text-blue-300 font-medium">HOME</a></li>
          <li><a href="#features" class="text-white text-3xl hover:text-blue-300 font-medium">PRODUCTS</a></li>
          <li><a href="#demo" class="text-white text-3xl hover:text-blue-300 font-medium">COMPANY</a></li>
          <li><a href="#contact" class="text-white text-3xl hover:text-blue-300 font-medium">SERVICES</a></li>
        </ul>
      </nav>
      <div class="flex items-center space-x-4">
        <?php if (isset($_SESSION['user'])): ?>
          <span class="text-white"><?php echo htmlspecialchars($_SESSION['user']); ?></span>
          <a href="IOTsystem/IOT.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">DEMO</a>
          <a href="registration/logout.php" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Вийти</a>
        <?php else: ?>
          <a href="Registration/logi.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">LOG IN</a>
          <a href="Registration/reg.html" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">REGISTER</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Hero Section: повноекранна секція з відео як фоном та текстовим блоком -->
  <section class="relative min-h-screen overflow-hidden">
    <!-- Фонове відео з оверлеєм для затемнення -->
    <div class="absolute inset-0 z-0">
      <video autoplay loop muted class="w-full h-full object-cover">
        <source src="image/videofon.mp4" type="video/mp4">
        Ваш браузер не підтримує відео.
      </video>
      <!-- Оверлей для затемнення відео -->
      <div class="absolute inset-0" style="background: rgba(0, 0, 0, 0.75);"></div>
    </div>
    <!-- Контент поверх відео -->
    <div class="relative z-10 flex flex-col items-center justify-center min-h-screen text-center px-4">
      <h1 class="text-5xl md:text-6xl font-bold text-white mt-8 mb-4 animate-fadeIn">CONNECT</h1>
      <h2 class="text-2xl md:text-4xl text-white mt-4 mb-6 animate-fadeIn delay-200">
        Revolutionize Your IoT Ecosystem
      </h2>
      <p class="max-w-xl md:text-2xl text-white mt-4 mb-8 animate-fadeIn delay-400">
        Experience unparalleled device integration, actionable analytics, and intuitive visualization—all designed to empower your digital transformation and drive innovation
      </p>
      <a href="#features" class="px-8 py-4 bg-blue-600 text-white rounded-full font-semibold hover:bg-blue-700 transition animate-fadeIn delay-600">
        Learn More
      </a>
    </div>
  </section>

  <!-- Додаткова секція: Our Features -->
  <section id="features" class="py-16 bg-gray-100">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Features</h2>
      <p class="text-gray-600 mb-8">Details about features...</p>
    </div>
  </section>

  <!-- Call-to-Action Section -->
  <section id="cta" class="py-16 bg-gradient-to-r from-blue-700 to-blue-900 text-white">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-4xl font-bold mb-4 animate-fadeIn">Ready to Transform Your Business?</h2>
      <p class="text-xl mb-8 animate-fadeIn delay-200">Join us now and take your IoT solutions to the next level.</p>
      <a href="Registration/reg.html" class="px-8 py-4 bg-green-600 text-white rounded-full font-semibold hover:bg-green-700 transition animate-fadeIn delay-400">
        Get Started
      </a>
    </div>
  </section>

  <!-- Testimonials Section -->
  <section id="testimonials" class="py-16 bg-white">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-gray-800 text-center mb-8 animate-fadeIn">What Our Clients Say</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Testimonial Card 1 -->
        <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition animate-fadeIn delay-200">
          <p class="text-gray-700 italic mb-4">"This platform revolutionized our IoT strategy. Exceptional performance and usability!"</p>
          <h3 class="text-xl font-semibold text-gray-800">John Doe</h3>
          <p class="text-gray-600">CEO, TechCorp</p>
        </div>
        <!-- Testimonial Card 2 -->
        <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition animate-fadeIn delay-400">
          <p class="text-gray-700 italic mb-4">"A game-changer in device management. The analytics provided are second to none."</p>
          <h3 class="text-xl font-semibold text-gray-800">Jane Smith</h3>
          <p class="text-gray-600">CTO, Innovate Inc.</p>
        </div>
        <!-- Testimonial Card 3 -->
        <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition animate-fadeIn delay-600">
          <p class="text-gray-700 italic mb-4">"The intuitive interface and comprehensive features make IoT management seamless."</p>
          <h3 class="text-xl font-semibold text-gray-800">Alex Johnson</h3>
          <p class="text-gray-600">COO, Smart Solutions</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contact" class="bg-gray-800 text-gray-300 py-8">
    <div class="container mx-auto px-4 text-center">
      <p>&copy; <?php echo date("Y"); ?> Connect. All rights reserved.</p>
    </div>
  </footer>

  <!-- JavaScript для зміни фону шапки при скролі -->
  <script>
    window.addEventListener("scroll", function() {
      const header = document.getElementById("main-header");
      if (window.scrollY > 50) {
        header.classList.remove("bg-transparent");
        header.classList.add("bg-blue-600", "shadow-md");
      } else {
        header.classList.remove("bg-blue-600", "shadow-md");
        header.classList.add("bg-transparent");
      }
    });
  </script>
</body>
</html>
