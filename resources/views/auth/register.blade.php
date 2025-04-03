<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: radial-gradient(ellipse at bottom, #1b2735 0%, #090a0f 100%);
      color: white;
    }
    canvas {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 0;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden">

  <!-- Зоряний фон -->
  <div class="absolute inset-0 z-0">
    <canvas id="stars" class="w-full h-full"></canvas>
  </div>

  <!-- Форма реєстрації -->
  <form action="{{ url('/register') }}" method="POST" class="z-10 bg-white p-8 rounded shadow w-96 text-black">
    @csrf
    <h2 class="text-2xl font-bold mb-6 text-center">Реєстрація</h2>

    @if($errors->any())
      <div class="mb-4 text-red-600">
        {{ $errors->first() }}
      </div>
    @endif

    <input type="text" name="name" placeholder="Ім’я" class="w-full p-2 border rounded mb-4" value="{{ old('name') }}" required>
    <input type="email" name="email" placeholder="Email" class="w-full p-2 border rounded mb-4" value="{{ old('email') }}" required>
    <input type="password" name="password" placeholder="Пароль" class="w-full p-2 border rounded mb-4" required>
    <input type="password" name="password_confirmation" placeholder="Підтвердження пароля" class="w-full p-2 border rounded mb-4" required>

    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Зареєструватися</button>
  </form>

  <!-- Анімація зоряного фону -->
  <script>
    const canvas = document.getElementById('stars');
    const ctx = canvas.getContext('2d');
    let width, height;
    let stars = [];

    function resize() {
      width = canvas.width = window.innerWidth;
      height = canvas.height = window.innerHeight;
    }

    window.addEventListener('resize', resize);
    resize();

    for (let i = 0; i < 200; i++) {
      stars.push({
        x: Math.random() * width,
        y: Math.random() * height,
        radius: Math.random() * 1.5,
        velocity: Math.random() * 0.5 + 0.2
      });
    }

    function animate() {
      ctx.clearRect(0, 0, width, height);
      for (let star of stars) {
        ctx.beginPath();
        ctx.arc(star.x, star.y, star.radius, 0, 2 * Math.PI);
        ctx.fillStyle = 'white';
        ctx.fill();
        star.y += star.velocity;
        if (star.y > height) {
          star.y = 0;
          star.x = Math.random() * width;
        }
      }
      requestAnimationFrame(animate);
    }

    animate();
  </script>

</body>
</html>
