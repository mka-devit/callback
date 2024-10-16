// scriptjs/updateRemainingFiles.js
function updateRemainingFiles() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'update_stats.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var stats = JSON.parse(xhr.responseText);
            // Обновляем только количество оставшихся файлов
            document.querySelector('.statistics-section p:nth-child(3)').innerText = 'Осталось: ' + stats.createDirectoryFileCount;
        }
    };
    xhr.send();
}

// Автоматическое обновление количества оставшихся файлов каждые 1 секунду
setInterval(updateRemainingFiles, 1000);
