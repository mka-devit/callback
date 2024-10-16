// scriptjs/buttons.js
document.getElementById('stopButton').onclick = function() {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'stop.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            updateStatistics(); // Обновляем статистику после изменения статуса
        } else if (xhr.status != 200) {
            console.error('Произошла ошибка при изменении статуса.'); // Логируем ошибку в консоль
        }
    };
    xhr.send();
};

document.getElementById('cancelButton').onclick = function() {
    if (confirm("Вы уверены, что хотите отменить операцию? Это удалит все файлы и очистит логи.")) {
        cancelCreation(); // Вызываем функцию отмены из cancel.js
    }
};
