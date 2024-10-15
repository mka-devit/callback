function showNotification(message) {
    const notification = document.getElementById('notification');
    notification.innerText = message;
    notification.style.display = 'block';

    setTimeout(() => {
        notification.style.display = 'none';
    }, 4000);  // Уведомление исчезнет через 4 секунды
}

function cancelCreation() {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "cancel.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById("status").innerText = "Создание файлов отменено.";
            showNotification("Создание файлов отменено.");
        }
    };
    xhr.send("action=cancel");
}

function startCall() {
    fetch('start_call.php')
        .then(response => response.text())
        .then(data => {
            showNotification(data);  // Предполагается, что функция showNotification уже существует
        })
        .catch(error => {
            showNotification('Ошибка при запуске обзвона: ' + error);
        });
}

function updateStatistics() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'update_stats.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var stats = JSON.parse(xhr.responseText);
            document.querySelector('.statistics-section p:nth-child(2)').innerText = 'Загружено: ' + stats.lastLogFileLineCount;
            document.querySelector('.statistics-section p:nth-child(3)').innerText = 'Осталось: ' + stats.createDirectoryFileCount;
            document.getElementById('status').innerText = stats.status; // Обновляем статус
        }
    };
    xhr.send();
}

// Автоматическое обновление статистики каждые 5 секунд
setInterval(updateStatistics, 5000); // 5000 миллисекунд = 5 секунд
