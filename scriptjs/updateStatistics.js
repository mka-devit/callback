// scriptjs/updateStatistics.js
function updateStatistics() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'update_stats.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var stats = JSON.parse(xhr.responseText);
            document.querySelector('.statistics-section p:nth-child(2)').innerText = 'Загружено: ' + stats.lastLogFileLineCount;
            document.getElementById('status').innerText = stats.status; // Обновляем статус
            // Убедитесь, что элемент с id 'scriptStatus' существует
            if (document.getElementById('scriptStatus')) {
                document.getElementById('scriptStatus').innerText = stats.scriptStatus; // Обновляем статус скрипта
            }
        }
    };
    xhr.send();
}

// Автоматическое обновление статистики каждые 5 секунд
setInterval(updateStatistics, 5000);
