<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connect - IoT Solutions</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('home/home.css') }}">
</head>
<body class="bg-gray-50">

  <!-- Header -->
  <header id="main-header" class="fixed w-full top-0 z-50 bg-transparent transition-colors duration-300">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <div class="flex items-center space-x-4">
        <img src="/home/image/krest.png" alt="Connect Logo" class="h-16 w-16">
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

      <!-- Auth buttons -->
      <div class="flex items-center space-x-4">
        @auth
        <a href="{{ route('demo') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">DEMO</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Вийти</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">LOG IN</a>
          <a href="{{ route('register') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">REGISTER</a>
        @endauth
      </div>
    </div>
  </header>

  <!-- Hero -->
  <section class="relative min-h-screen overflow-hidden">
    <div class="absolute inset-0 z-0">
      <video autoplay loop muted class="w-full h-full object-cover">
        <source src="{{ asset('home/video/videofon.mp4') }}" type="video/mp4">
        Ваш браузер не підтримує відео.
      </video>
      <div class="absolute inset-0" style="background: rgba(0, 0, 0, 0.75);"></div>
    </div>
    <div class="relative z-10 flex flex-col items-center justify-center min-h-screen text-center px-4">
      <h1 class="text-5xl md:text-6xl font-bold text-white mt-8 mb-4 animate-fadeIn">CONNECT</h1>
      <h2 class="text-2xl md:text-4xl text-white mt-4 mb-6 animate-fadeIn delay-200">
        Revolutionize Your IoT Ecosystem
      </h2>
      <p class="max-w-xl md:text-2xl text-white mt-4 mb-8 animate-fadeIn delay-400">
        Experience unparalleled device integration, actionable analytics, and intuitive visualization—
        all designed to empower your digital transformation and drive innovation
      </p>
      <a href="#features" class="px-8 py-4 bg-blue-600 text-white rounded-full font-semibold hover:bg-blue-700 transition animate-fadeIn delay-600">
        Learn More
      </a>
    </div>
  </section>

  <!-- Features -->
  <section id="features" class="py-16 bg-gray-100">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Features</h2>
      <p class="text-gray-600 mb-8">Details about features...</p>
    </div>
  </section>

  <!-- Call-to-Action -->
  <section id="cta" class="py-16 bg-gradient-to-r from-blue-700 to-blue-900 text-white">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-4xl font-bold mb-4 animate-fadeIn">Ready to Transform Your Business?</h2>
      <p class="text-xl mb-8 animate-fadeIn delay-200">
        Join us now and take your IoT solutions to the next level.
      </p>
      <a href="{{ route('register') }}" class="px-8 py-4 bg-green-600 text-white rounded-full font-semibold hover:bg-green-700 transition animate-fadeIn delay-400">
        Get Started
      </a>
    </div>
  </section>

  <!-- Testimonials -->
  <section id="testimonials" class="py-16 bg-white">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-gray-800 text-center mb-8 animate-fadeIn">What Our Clients Say</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition animate-fadeIn delay-200">
          <p class="text-gray-700 italic mb-4">"This platform revolutionized our IoT strategy."</p>
          <h3 class="text-xl font-semibold text-gray-800">John Doe</h3>
          <p class="text-gray-600">CEO, TechCorp</p>
        </div>
        <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition animate-fadeIn delay-400">
          <p class="text-gray-700 italic mb-4">"A game-changer in device management."</p>
          <h3 class="text-xl font-semibold text-gray-800">Jane Smith</h3>
          <p class="text-gray-600">CTO, Innovate Inc.</p>
        </div>
        <div class="bg-gray-100 p-6 rounded-lg shadow hover:shadow-xl transition animate-fadeIn delay-600">
          <p class="text-gray-700 italic mb-4">"Comprehensive features make IoT management seamless."</p>
          <h3 class="text-xl font-semibold text-gray-800">Alex Johnson</h3>
          <p class="text-gray-600">COO, Smart Solutions</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contact" class="bg-gray-800 text-gray-300 py-8">
    <div class="container mx-auto px-4 text-center">
      <p>&copy; {{ date('Y') }} Connect. All rights reserved.</p>
    </div>
  </footer>

  <!-- JS -->
  <script src="{{ asset('home/home.js') }}"></script>

</body>
</html>
