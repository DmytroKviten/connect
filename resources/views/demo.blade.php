<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8" />
  <title>CONNECT • Demo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Tailwind + палітра -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            base   : '#0e0f11',
            surface: '#181a1e',
            glass  : 'rgba(24,26,30,.40)',
            accent : '#20e3b2',
            muted  : '#9aa0ac',
          }
        }
      }
    }
  </script>
</head>

<body class="bg-base text-white font-sans antialiased selection:bg-accent/30">
<div class="min-h-screen flex flex-col">
<link rel="manifest" href="/manifest.webmanifest">

  <!-- ── HEADER ────────────────────────────────────────────────────── -->
  <header class="fixed top-0 left-0 w-full h-16 bg-surface/80 backdrop-blur shadow-md
                 flex items-center justify-between px-6 z-30">
    <div class="flex items-center gap-3">
      <div class="w-3 h-3 rounded-full bg-accent animate-pulse"></div>
      <span class="font-semibold tracking-wide text-lg">CONNECT</span>
    </div>

    <button id="burger" class="p-2 rounded hover:bg-white/10 transition">
      <svg id="burger-icon" class="w-7 h-7 stroke-white" viewBox="0 0 24 24" fill="none"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6"  x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>
  </header>

  <!-- ── SIDEBAR (праворуч, під шапкою) ────────────────────────────── -->
  <aside id="sidebar"
         class="fixed top-16 right-0 bottom-0 w-72 bg-glass backdrop-blur shadow-lg
                border-l border-white/10 z-40
                transform translate-x-full transition-transform duration-300">

    <!-- кнопка закриття -->
    <button id="closeSidebar"
            class="absolute top-4 left-4 p-2 rounded hover:bg-white/10 transition">
      <svg class="w-6 h-6 stroke-white" viewBox="0 0 24 24" fill="none"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="6" y1="6"  x2="18" y2="18"/>
        <line x1="6" y1="18" x2="18" y2="6"/>
      </svg>
    </button>

    <nav class="pt-20 flex flex-col gap-1 px-6">
      <a href="#" class="nav-item">📊 Дашборд</a>
      <a href="#" class="nav-item">🔌 Пристрої</a>
      <a href="#" class="nav-item">⚙️ Налаштування</a>
      <a href="#" class="nav-item">❓ Підтримка</a>
    </nav>

    <p class="mt-auto px-6 py-6 text-xs text-muted">© 2025 CONNECT</p>
  </aside>

  <!-- ── MAIN ───────────────────────────────────────────────────────── -->
  <main class="flex-1 pt-16">

    <!-- HERO ---------------------------------------------------------- -->
    <section class="relative isolate overflow-hidden">
      <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))]
                  from-accent/40 via-transparent to-transparent"></div>

      <div class="max-w-5xl mx-auto px-6 py-24 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
          Підключіть <span class="text-accent">будь-який</span> IoT-пристрій<br>за декілька хвилин
        </h1>
        <p class="mt-6 text-muted md:text-lg max-w-2xl mx-auto">
          Майстер швидкого налаштування знайде пристрій, під’єднає до Wi-Fi і запустить керування в реальному часі.
        </p>

        <div class="mt-10 flex flex-wrap justify-center gap-4">
        <a href="/setup" class="btn-primary">Почати налаштування</a>
        </div>
      </div>
    </section>

    <!-- HOW IT WORKS --------------------------------------------------- -->
    <section class="px-6 py-20">
      <h2 class="text-3xl font-semibold mb-14 text-center">Як це працює?</h2>
      <div class="grid gap-10 md:grid-cols-3 max-w-6xl mx-auto">
        <div class="feature-card">
          <div class="feature-icon">1</div>
          <h3 class="feature-title">Скануємо мережу</h3>
          <p class="feature-text">Автоматично знаходимо сумісні пристрої у вашій мережі чи через AP.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">2</div>
          <h3 class="feature-title">Підключаємо Wi-Fi</h3>
          <p class="feature-text">Вибираєте SSID, вводите пароль — пристрій переходить у домашню мережу.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">3</div>
          <h3 class="feature-title">Керуєте та аналізуєте</h3>
          <p class="feature-text">Увімкнути, вимкнути, переглянути споживання, налаштувати автоматизації.</p>
        </div>
      </div>
    </section>

    <!-- DEVICE CARD ---------------------------------------------------- -->
    <section class="bg-surface/70 backdrop-blur py-20 px-6">
  <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-14">
    <div>
      <h2 class="text-2xl font-semibold mb-6">Ваш пристрій</h2>
      <ul class="space-y-3 text-muted">
        <li><strong class="text-white">IP:</strong>        <span id="ip">—</span></li>
        <li><strong class="text-white">Браузер:</strong>   <span id="br">—</span></li>
        <li><strong class="text-white">ОС:</strong>        <span id="os">—</span></li>
        <li><strong class="text-white">Екран:</strong>     <span id="scr">—</span></li>
        <li><strong class="text-white">Таймзона:</strong>  <span id="tz">—</span></li>
      </ul>
    </div>

    <div class="flex justify-center items-center">
      <div class="w-56 h-56 rounded-xl bg-white/5 border border-white/10
                  flex items-center justify-center text-muted text-sm">
        QR CODE
      </div>
    </div>
  </div>
</section>

    <!-- STATUS --------------------------------------------------------- -->
    <section class="py-20 px-6 text-center">
      <h2 class="text-2xl font-semibold mb-6">Стан пристрою</h2>
      <div class="inline-flex items-center gap-3 px-8 py-5 rounded-xl
                  bg-glass backdrop-blur shadow-lg">
        <span class="w-3 h-3 rounded-full bg-green-400 animate-pulse"></span>
        <span class="text-lg">Online</span>
      </div>
      <p class="text-muted mt-3 text-sm">Оновлення в реальному часі — незабаром</p>
    </section>
  </main>
</div>

<!-- ── UTILITIES ─────────────────────────────────────────────────────── -->
<style>
  .btn-primary { @apply px-6 py-3 rounded-lg font-semibold bg-accent hover:bg-accent/80 transition; }
  .btn-outline { @apply px-6 py-3 rounded-lg font-semibold border border-accent text-accent hover:bg-accent hover:text-base transition; }
  .nav-item    { @apply px-4 py-3 rounded-lg hover:bg-accent/10 transition font-medium; }
  .feature-card{ @apply flex flex-col items-center text-center space-y-4; }
  .feature-icon{ @apply w-14 h-14 flex items-center justify-center rounded-full bg-accent/10 text-accent font-bold text-xl; }
  .feature-title{@apply text-xl font-semibold; }
  .feature-text { @apply text-muted max-w-xs; }
</style>

<!-- ── JS: BURGER LOGIC ─────────────────────────────────────────────── -->
<script>
  const burger       = document.getElementById('burger');
  const sidebar      = document.getElementById('sidebar');
  const closeBtn     = document.getElementById('closeSidebar');
  const burgerIcon   = document.getElementById('burger-icon');

  function toggleSidebar() {
    sidebar.classList.toggle('translate-x-full');

    // ≡ ↔ ✕
    burgerIcon.innerHTML = sidebar.classList.contains('translate-x-full')
      ? '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>'
      : '<line x1="6" y1="6" x2="18" y2="18"/><line x1="6" y1="18" x2="18" y2="6"/>';
  }

  burger.addEventListener('click', toggleSidebar);
  // кнопка-хрестик усередині
  document.getElementById('closeSidebar').addEventListener('click', toggleSidebar);
</script>


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
(async () => {
  /* IP із бекенду */
  let ip = '—';
  try {
    const r = await axios.get('/api/client');
    ip = r.data.ip ?? '—';
  } catch {}

  /* Дані з браузера */
  const ua   = navigator.userAgent;
  const scr  = `${window.screen.width}×${window.screen.height}`;
  const tz   = Intl.DateTimeFormat().resolvedOptions().timeZone ?? '—';

  const brRe = /(Firefox|Edg|Chrome|Safari)\/([\d.]+)/;
  const osRe = /\(([^)]+)\)/;

  const br = (ua.match(brRe)||[]).slice(1,3).join(' ') || 'Невідомо';
  const os = (ua.match(osRe)||[])[1] ?? 'Невідомо';

  /* Вставляємо у DOM */
  document.getElementById('ip').textContent  = ip;
  document.getElementById('br').textContent  = br;
  document.getElementById('os').textContent  = os;
  document.getElementById('scr').textContent = scr;
  document.getElementById('tz').textContent  = tz;
})();
</script>

@section('scripts')
<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker
           .register('/service-worker.js')
           .catch(err => console.error('SW fail', err));
}
</script>
@endsection

</body>
</html>
