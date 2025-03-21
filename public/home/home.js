// ----- Скрипт з index.php для зміни фону хедера -----
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
  
  // ----- Скрипт з script.js -----
  
  function playVideo(videoId) {
    var videos = document.querySelectorAll('video');
    videos.forEach(function(video) {
      video.pause();
      video.style.display = 'none';
    });
    var video = document.getElementById(videoId);
    if (video) {
      video.style.display = 'block';
      video.play();
    }
  }
  
  function checkAccess() {
    if (!localStorage.getItem('user')) {
      alert('Будь ласка, зареєструйтесь або увійдіть для доступу до демо.');
      return false;
    }
    return true;
  }
  
  // Перевірка, чи існує кнопка #demo
  const demoBtn = document.getElementById('demo');
  if (demoBtn) {
    demoBtn.addEventListener('click', function() {
      if (checkAccess()) {
        console.log('Демо доступне');
      }
    });
  }
  
  // При завантаженні сторінки
  window.addEventListener('DOMContentLoaded', (event) => {
    const user = localStorage.getItem('user');
    const loginBtn = document.getElementById('login');
    const registerBtn = document.getElementById('register');
  
    if (user) {
      if (loginBtn)  loginBtn.style.display = 'none';
      if (registerBtn) registerBtn.style.display = 'none';
  
      const userNameDisplay = document.createElement('span');
      userNameDisplay.textContent = `Вітаємо, ${user}`;
      userNameDisplay.className = 'user-name-display';
  
      // Ставимо кудись у .header (якщо така є)
      const header = document.querySelector('.header');
      if (header) {
        header.appendChild(userNameDisplay);
      }
    } else {
      // Якщо нема user в localStorage, відключимо DEMO
      if (demoBtn) {
        demoBtn.disabled = true;
      }
    }
  });
  