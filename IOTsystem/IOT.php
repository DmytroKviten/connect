<?php
// index.php
?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IoT Connect — Демонстрація</title>
  <!-- Підключення Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Додаткові стилі для анімації кнопки-сканування */
    .planet-button {
      width: 200px;
      height: 200px;
      background: radial-gradient(circle at 30% 30%, #00f3ff, #000);
      border-radius: 50%;
      border: 3px solid #00f3ff;
      box-shadow: 0 0 40px rgba(0, 243, 255, 0.3);
      cursor: pointer;
      transition: transform 0.3s, box-shadow 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
    }

    .planet-button::before {
      content: "🌀";
      font-size: 4rem;
      filter: drop-shadow(0 0 10px #00f3ff);
    }

    .planet-button:hover {
      transform: scale(1.1) rotate(15deg);
      box-shadow: 0 0 60px rgba(0, 243, 255, 0.5);
    }
  </style>
</head>
<body class="flex flex-col min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 text-white">
  <!-- Шапка (Header) -->
  <header class="bg-black/30 backdrop-blur-md py-4">
    <div class="container mx-auto flex justify-between items-center px-4">
      <!-- Логотип і назва -->
      <div class="flex items-center space-x-3">
      <img src="/image/logo.png" alt="Логотип" class="h-20 w-200 hidden md:block">
      </div>
      <!-- Меню навігації -->
      <nav>
      <ul class="flex space-x-10">
  <li>
    <a href="#" class="text-xl font-semibold text-[#00f3ff] hover:text-[#00c4ff] px-4 py-2">
      Головна
    </a>
  </li>
  <li>
    <a href="#about" class="text-xl font-semibold text-[#00f3ff] hover:text-[#00c4ff] px-4 py-2">
      Про нас
    </a>
  </li>
  <li>
    <a href="#contact" class="text-xl font-semibold text-[#00f3ff] hover:text-[#00c4ff] px-4 py-2">
      Контакти
    </a>
  </li>
</ul>
      </nav>
      <!-- Кнопка "Повернутися на сайт" -->
      <div>
        <a href="/" class="bg-[#00f3ff] text-black px-4 py-2 rounded hover:bg-[#00c4ff]">
          Повернутися на сайт
        </a>
      </div>
    </div>
  </header>

  <!-- Основний контент -->
  <main class="flex-grow container mx-auto my-12 px-4">
    <div class="text-center">
      <h2 class="text-3xl font-bold mb-4">Моніторинг пристроїв IoT</h2>
      <p class="mb-8">Скануйте, керуйте та моніторте свої пристрої в режимі реального часу.</p>
      <!-- Кнопка-сканування -->
      <button id="scanButton" class="planet-button"></button>
      <p class="mt-6 text-2xl text-[#00f3ff] font-bold">Сканувати пристрої</p>
    </div>
  </main>

  <!-- Модальне вікно для відображення результатів сканування -->
  <div id="scanModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-gray-800 p-6 rounded-lg shadow-lg w-11/12 md:w-1/2">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Результати сканування</h3>
        <button id="closeModal" class="text-[#00f3ff] text-2xl">&times;</button>
      </div>
      <div id="modalContent">
        <p>Пошук пристроїв...</p>
      </div>
    </div>
  </div>

  <!-- Футер (Footer) -->
  <footer class="bg-black/30 backdrop-blur-md py-4">
    <div class="container mx-auto text-center">
      <small class="opacity-70">
        <a href="#" class="text-[#00f3ff] hover:text-[#00c4ff]">Космічний протокол</a> • 
        <a href="#" class="text-[#00f3ff] hover:text-[#00c4ff]">Галактичні правила</a>
      </small>
    </div>
  </footer>

  <!-- JavaScript для роботи модального вікна та сканування -->
  <script>
    document.addEventListener("DOMContentLoaded", function(){
      const scanButton = document.getElementById('scanButton');
      const scanModal = document.getElementById('scanModal');
      const closeModal = document.getElementById('closeModal');
      const modalContent = document.getElementById('modalContent');

      // Обробка кліку на кнопку сканування
      scanButton.addEventListener('click', function(){
        scanModal.classList.remove('hidden');
        modalContent.innerHTML = '<p>Пошук пристроїв...</p>';

        // Імітація затримки сканування (2 секунди)
        setTimeout(() => {
          const devices = [
            { name: "Пристрій 1", ip: "192.168.1.10" },
            { name: "Пристрій 2", ip: "192.168.1.11" },
            { name: "Пристрій 3", ip: "192.168.1.12" }
          ];

          let content = '<p class="mb-4">Знайдено пристрої:</p><ul class="list-disc pl-5">';
          devices.forEach(device => {
            content += `<li>${device.name} (IP: ${device.ip})</li>`;
          });
          content += '</ul>';
          modalContent.innerHTML = content;
        }, 2000);
      });

      // Закриття модального вікна
      closeModal.addEventListener('click', function(){
        scanModal.classList.add('hidden');
      });
    });
  </script>
</body>
</html>