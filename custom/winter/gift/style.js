// Создание элемента script
const script = document.createElement('script');

// Установка атрибутов для загрузки скрипта
script.src = 'disable-devtool@latest';
script.async = true; // Можно использовать async для асинхронной загрузки

// Добавление элемента script в тег head или body
document.head.appendChild(script); // Или document.body.appendChild(script);


// Создание элемента style
const styleElement = document.createElement('style');

// Установка содержимого стилей
styleElement.innerHTML = `body {  cursor: url('https://site.com/custom/winter/gift/data/cursor 16.png'), auto;  display: flex;  justify-content: center;  align-items: center;  height: 100vh;  margin: 0;  background-image: url('data/bg.png');  background-size: cover;  perspective: 1000px;  position: relative;  overflow: hidden;}.card {  width: 300px;  height: 200px;  position: relative;  transform-style: preserve-3d;  transition: transform 0.6s;  cursor: url('https://site.com/custom/winter/gift/data/cursor 65.png'), pointer;}.card div {  width: 100%;  height: 100%;  position: absolute;  backface-visibility: hidden;  display: flex;  justify-content: center;  align-items: center;}.front {  transform: rotateY(0deg);}.back {  transform: rotateY(180deg);}img {  width: 650px;  height: auto;}.snowflakes {  position: fixed;  top: 0;  left: 0;  pointer-events: none;  z-index: 9999;}.snowflake {  position: absolute;  width: 10px;  height: 10px;  background: #fff; /* Цвет снежинок */  border-radius: 50%;  opacity: 0.7;  animation: falling linear infinite;}@keyframes falling {  0% {    transform: translateY(-100%);  }  100% {    transform: translateY(100vh);  }}`;

// Добавление элемента style в тег head
document.head.appendChild(styleElement);