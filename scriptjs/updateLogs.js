// scriptjs/updateLogs.js
function updateLogs() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'monitoring.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('logs-container').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}

// Обновляем логи каждые 2 секунды
setInterval(updateLogs, 2000);
