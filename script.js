function playVideo(videoId) {
    // Зупиняємо всі відео та встановлюємо їх видимість як 'none'
    var videos = document.querySelectorAll('video');
    videos.forEach(function(video) {
      video.pause();
      video.style.display = 'none';
    });

 // відтворюємо відео, яке відповідає натиснутій кнопці
var video = document.getElementById(videoId);
video.style.display = 'block';
video.play();
}


function checkAccess() {
    if (!localStorage.getItem('user')) {
        alert('Будь ласка, зареєструйтесь або увійдіть для доступу до демо.');
        return false;
    }
    return true;
}

document.getElementById('demo').addEventListener('click', function() {
    if (checkAccess()) {
        // Тут код для відображення демо, якщо користувач авторизований
        console.log('Демо доступне');
    }
});

window.addEventListener('DOMContentLoaded', (event) => {
    const user = localStorage.getItem('user');
    if (user) {
        document.getElementById('login').style.display = 'none';
        document.getElementById('register').style.display = 'none';
        // Створення нового елемента для відображення імені користувача
        const userNameDisplay = document.createElement('span');
        userNameDisplay.textContent = `Вітаємо, ${user}`;
        userNameDisplay.className = 'user-name-display';
        document.querySelector('.header').appendChild(userNameDisplay);
    } else {
        document.getElementById('demo').disabled = true; // Відключити кнопку DEMO, якщо користувач не залогінений
    }
});