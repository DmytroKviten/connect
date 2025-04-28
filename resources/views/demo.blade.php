<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>DEMO - Підключення пристрою</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-500 to-indigo-700 min-h-screen text-white flex flex-col">

  <!-- Заголовок -->
  <section class="text-center py-12">
    <h1 class="text-5xl font-bold mb-4">Підключення до вашого IoT-пристрою</h1>
    <p class="text-xl">Швидке підключення та управління пристроями в реальному часі</p>
  </section>

  <!-- Як працює система -->
  <section class="container mx-auto px-6 py-10 grid md:grid-cols-3 gap-8">
    <div class="text-center">
      <div class="text-6xl mb-4">🔌</div>
      <h2 class="text-2xl font-semibold mb-2">Підключіть пристрій</h2>
      <p>Зʼєднайте ваш IoT-пристрій з мережею та підготуйте його до роботи.</p>
    </div>

    <div class="text-center">
      <div class="text-6xl mb-4">➕</div>
      <h2 class="text-2xl font-semibold mb-2">Додайте у систему</h2>
      <p>Введіть дані пристрою у платформу та зареєструйте його.</p>
    </div>

    <div class="text-center">
      <div class="text-6xl mb-4">📱</div>
      <h2 class="text-2xl font-semibold mb-2">Керуйте</h2>
      <p>Керуйте пристроями через зручний веб-інтерфейс з будь-якої точки світу.</p>
    </div>
  </section>

  <!-- Інструкція -->
  <section class="bg-white text-gray-800 py-12">
    <div class="container mx-auto px-6 text-center">
      <h2 class="text-4xl font-bold mb-8">Інструкція для підключення</h2>

      <div class="grid md:grid-cols-2 gap-8 items-center">
        <div class="space-y-4 text-left">
          <p><strong>IP-адреса пристрою:</strong> 192.168.1.100</p>
          <p><strong>API Token:</strong> 8dfh38dh93hd839hf8h3f</p>
          <p><strong>Порт:</strong> 8080</p>
        </div>

        <div class="flex justify-center">
          <!-- Просто квадрат замість QR-коду (можна буде підставити справжній) -->
          <div class="w-40 h-40 bg-gray-300 flex items-center justify-center text-gray-700">
            QR CODE
          </div>
        </div>
      </div>

      <div class="mt-8">
        <button class="px-8 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition">Почати підключення</button>
      </div>
    </div>
  </section>

  <!-- Статус пристрою -->
  <section class="container mx-auto px-6 py-12 text-center">
    <h2 class="text-4xl font-bold mb-6">Стан пристрою</h2>
    <div class="bg-white text-gray-800 p-6 rounded-lg inline-block">
      <p class="text-2xl font-semibold mb-2">Статус: <span class="text-green-600">Online</span></p>
      <p class="text-gray-600">Оновлення в реальному часі буде доступне у майбутніх версіях</p>
    </div>
  </section>

  <!-- Повернутися -->
  <div class="text-center pb-12">
    <a href="{{ url('/') }}" class="px-6 py-3 bg-white text-blue-600 rounded-full font-bold hover:bg-gray-100 transition">На головну</a>
  </div>

</body>
</html>
