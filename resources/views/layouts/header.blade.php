<header class="fixed inset-x-0 top-0 h-16 px-6 flex items-center bg-glass/70 backdrop-blur shadow-md z-30">
  <!-- бургер -->
  <button id="burger" aria-label="Open the menu"
          class="p-2 mr-4 rounded hover:bg-white/10 focus-visible:outline focus-visible:outline-accent">
    <svg id="burgerIcon" class="w-7 h-7 stroke-white" fill="none"
         viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <line x1="3" y1="6"  x2="21" y2="6"/>
      <line x1="3" y1="12" x2="21" y2="12"/>
      <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
  </button>

  <span class="flex-grow text-center text-lg font-bold tracking-wide select-none">
    CONNECT
  </span>

  <a href="{{ route('devices.index') }}"
     class="ml-auto text-sm font-semibold text-white transition-colors hover:text-accent focus-visible:outline focus-visible:outline-accent">
    Connected&nbsp;devices
  </a>
</header>

<!-- SIDEBAR -->
<aside id="sidebar"
       class="fixed left-0 top-20 bottom-20 w-64 -translate-x-full pointer-events-none
              bg-glass backdrop-blur border-r border-white/10 rounded-r-xl p-6
              transition-transform duration-300 z-40 shadow-lg">
  <nav class="flex flex-col gap-3">
    <a href="#setup" class="nav-item" onclick="toggleSidebar()">⚙️ Налаштування</a>
    <a href="#about" class="nav-item" onclick="toggleSidebar()">ℹ️ Про сайт</a>
  </nav>
</aside>

<!-- ВСТАВЛЕНИЙ JS -->
<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const burgerIcon = document.getElementById('burgerIcon');
    const isOpen = !sidebar.classList.contains('-translate-x-full');

    if (isOpen) {
      sidebar.classList.add('-translate-x-full');
      sidebar.classList.remove('pointer-events-auto');
      sidebar.classList.add('pointer-events-none');
    } else {
      sidebar.classList.remove('-translate-x-full');
      sidebar.classList.remove('pointer-events-none');
      sidebar.classList.add('pointer-events-auto');
    }

    // Зміна іконки (опційно)
    burgerIcon.classList.toggle('open');
  }

  document.getElementById('burger').addEventListener('click', toggleSidebar);
</script>
