<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8" />
  <title>CONNECT • Підключення пристрою</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Tailwind тільки для grid / flex / шрифтів -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            base:       '#0e0f11',
            surface:    '#1b1d22',
            glass:      'rgba(24,26,30,.4)',
            accent:     '#20e3b2',
            accentDark: '#129b80',
            muted:      '#9aa0ac',
          }
        }
      }
    }
  </script>

  <style>
    /* фон-градієнт -------------------------------------------------- */
    body::before{
      content:'';position:fixed;inset:0;z-index:-1;
      background:
        radial-gradient(circle at 30% 10%,rgba(32,227,178,.25),transparent 60%),
        radial-gradient(circle at 70% 90%,rgba(32,227,178,.15),transparent 60%),
        #0e0f11;
    }
    /* посилання сайдбару ------------------------------------------- */
    .nav-item{
      display:block;padding:.75rem 1rem;border-radius:.5rem;
      transition:.2s background-color;
    }
    .nav-item:hover,
    .nav-item:focus-visible{background-color:rgba(32,227,178,.1);}
    /* stylised select ---------------------------------------------- */
    .select{
      width:100%;color:#fff;font-size:1rem;line-height:1.5rem;
      background-color:#1b1d22;
      border:2px solid rgba(32,227,178,.4);
      padding:.5rem 2.5rem .5rem 1rem;
      border-radius:.75rem;
      transition:border-color .2s,box-shadow .2s;
      appearance:none;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='white' viewBox='0 0 24 24'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
      background-repeat:no-repeat;
      background-position:right .75rem center;
      background-size:.9rem;
    }
    .select:focus{
      border-color:#20e3b2;outline:none;
      box-shadow:0 0 0 2px rgba(32,227,178,.45);
    }
    .select option{background:#1b1d22;color:#e5e7eb;}
    .select option:disabled{color:#6b7280;}
    /* кнопка -------------------------------------------------------- */
    .btn{
      display:inline-flex;align-items:center;gap:.25rem;
      padding:.75rem 1.5rem;font-weight:600;border-radius:9999px;
      background:#20e3b2;color:#0e0f11;
      box-shadow:0 4px 18px rgba(32,227,178,.2);
      transition:.2s background-color;
    }
    .btn:hover       {background:#129b80;}
    .btn:disabled    {opacity:.25;pointer-events:none;}
  </style>
</head>

<body class="min-h-screen flex flex-col text-white font-sans antialiased selection:bg-accent/30">
  
 <!-- HEADER -->
<header
  class="fixed inset-x-0 top-0 h-16 px-6 flex items-center
         bg-glass/70 backdrop-blur shadow-md z-30">

  <!-- бургер -->
  <button id="burger" aria-label="Open the menu"
          class="p-2 mr-4 rounded hover:bg-white/10
                 focus-visible:outline focus-visible:outline-accent">
    <svg id="burgerIcon" class="w-7 h-7 stroke-white" fill="none"
         viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <line x1="3" y1="6"  x2="21" y2="6"/>
      <line x1="3" y1="12" x2="21" y2="12"/>
      <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
  </button>

  <!-- назва сайту (центрується завдяки flex-grow) -->
  <span class="flex-grow text-center text-lg font-bold tracking-wide select-none">
    CONNECT
  </span>

  <!-- посилання «Підключені пристрої» → тікає вправо завдяки ml-auto -->
  <a href="{{ route('devices.index') }}"
     class="ml-auto text-sm font-semibold text-white transition-colors
            hover:text-accent focus-visible:outline focus-visible:outline-accent">
     Connected&nbsp;devices
  </a>
</header>

  <!-- назва сайту -->
  <span class="text-lg font-bold tracking-wide select-none">
    CONNECT
  </span>
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

  <!-- MAIN -->
  <main class="flex-1 pt-20">
    <!-- опис -->
    <section id="about"
             class="px-6 py-16 text-center bg-glass/40 backdrop-blur
                    md:rounded-b-3xl shadow-xl max-w-6xl mx-auto">
      <h1 class="text-3xl md:text-5xl font-extrabold">
        Quick connect&nbsp;IoT device wizard
      </h1>
      <p class="mt-4 text-muted max-w-3xl mx-auto md:text-lg">
        Select a category, brand, and model, and the site will guide you through Wi-Fi settings and connect your device to the CONNECT cloud.
      </p>
    </section>

    <!-- селектори + превʼю -->
    <section id="setup"
             class="max-w-6xl mx-auto px-6 py-20 grid md:grid-cols-2 gap-8 items-start">
      <!-- селектори -->
      <div>
        <h2 class="text-2xl font-semibold mb-6">Select a device</h2>
        <div class="space-y-5">
          <div>
            <label class="block mb-1 text-sm text-muted" for="selCategory">Category</label>
            <select id="selCategory" class="select" aria-labelledby="selCategory"></select>
          </div>
          <div>
            <label class="block mb-1 text-sm text-muted" for="selBrand">Brand</label>
            <select id="selBrand" class="select" disabled aria-labelledby="selBrand"></select>
          </div>
          <div>
            <label class="block mb-1 text-sm text-muted" for="selModel">Model</label>
            <select id="selModel" class="select" disabled aria-labelledby="selModel"></select>
          </div>
        </div>
      </div>

      <!-- фото + кнопка -->
      <div class="flex flex-col items-center text-center md:mt-10 mt-12">
        <img id="deviceImg"
             src="/img/devices/placeholder.jpg"
             alt=""
             class="w-72 h-72 object-contain rounded-2xl border border-white/10 shadow-lg mb-6">

        <a id="btnNext" class="btn opacity-0 pointer-events-none" href="{{ route('setup') }}">
          Go to the settings
        </a>
      </div>
    </section>
  </main>

<!-- JS -->
<script>
/* ── дані пристроїв ─────────────────────────────── */
const DATA = {
  sockets: {
    label:'Sockets',
    brands:{
      shelly:{label:'Shelly',models:{'plus-plug-s':'Plus Plug S','plug-us':'Plug US'}},
      tapo  :{label:'TP-Link Tapo',models:{p100:'P100',p110:'P110'}},
      meross:{label:'Meross',models:{ms310:'MS310',mss210:'MSS210'}}
    }
  },
  lamps: {
    label:'Lamps',
    brands:{
      yeelight:{label:'Yeelight',models:{rgb:'RGB Bulb',w3:'W3 (Cool-White)'}},
      hue     :{label:'Philips Hue',models:{white:'White E27',color:'Color A60'}},
      nanoleaf:{label:'Nanoleaf',models:{a19:'A19',br30:'BR30'}}
    }
  },
  fridges:{
    label:'Fridges',
    brands:{
      samsung:{label:'Samsung',models:{familyhub:'Family Hub',bespoke:'Bespoke'}},
      lg     :{label:'LG',models:{instaview:'InstaView',thinQ:'ThinQ Smart'}}
    }
  }
};

/* ── DOM refs ───────────────────────────────────── */
const selCat   = document.getElementById('selCategory');
const selBrand = document.getElementById('selBrand');
const selModel = document.getElementById('selModel');
const img      = document.getElementById('deviceImg');
const btnNext  = document.getElementById('btnNext');

/* ── helpers ───────────────────────────────────── */
function fillSelect(el, obj, placeholder){
  el.innerHTML = '';
  el.disabled  = false;

  const ph = new Option(placeholder, '', true, true);
  ph.disabled = true;
  el.appendChild(ph);

  for (const key in obj){
    el.appendChild(new Option(obj[key].label || obj[key], key));
  }
}
function reset(el){ el.innerHTML = ''; el.disabled = true; }

function updatePreview(cat, brand, model){
  if (cat && brand && model){
    img.src = `/img/devices/${cat}-${brand}-${model}.jpg`;
    btnNext.classList.remove('opacity-0','pointer-events-none');
    btnNext.href = '/setup';
  }else{
    img.src = '/img/devices/placeholder.jpg';
    btnNext.classList.add('opacity-0','pointer-events-none');
    btnNext.removeAttribute('href');
  }
}

/* ── init ───────────────────────────────────────── */
fillSelect(selCat, DATA, '— select —');
reset(selBrand); reset(selModel);

let curCat = '', curBrand = '';

selCat.addEventListener('change', e => {
  curCat = e.target.value;
  fillSelect(selBrand, DATA[curCat].brands, '— виберіть —');
  reset(selModel);
  updatePreview(curCat);           // ← передаємо cat
});

selBrand.addEventListener('change', e => {
  curBrand = e.target.value;
  fillSelect(selModel, DATA[curCat].brands[curBrand].models, '— виберіть —');
  updatePreview(curCat, curBrand); // ← передаємо cat + brand
});

selModel.addEventListener('change', () =>
  updatePreview(curCat, curBrand, selModel.value)
);

/* ── бургер-меню ───────────────────────────────── */
const burger  = document.getElementById('burger');
const sidebar = document.getElementById('sidebar');
const icon    = document.getElementById('burgerIcon');

function toggleSidebar(){
  sidebar.classList.toggle('-translate-x-full');
  sidebar.classList.toggle('pointer-events-none');

  icon.innerHTML = ''; // очищаємо поточні <line>
  if (sidebar.classList.contains('-translate-x-full')){
    icon.insertAdjacentHTML('beforeend',
      '<line x1="3" y1="6"  x2="21" y2="6"/>'  +
      '<line x1="3" y1="12" x2="21" y2="12"/>' +
      '<line x1="3" y1="18" x2="21" y2="18"/>');
  }else{
    icon.insertAdjacentHTML('beforeend',
      '<line x1="6" y1="6"  x2="18" y2="18"/>' +
      '<line x1="6" y1="18" x2="18" y2="6"/>');
  }
}
burger.addEventListener('click', toggleSidebar);
</script>
</body>
</html>
