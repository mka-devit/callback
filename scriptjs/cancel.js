// scriptjs/cancel.js
function cancelCreation() {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "cancel.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById("status").innerText = "Создание файлов отменено.";
            showNotification("Создание файлов отменено.");
            updateStatistics(); // Обновляем статистику после отмены
        } else if (xhr.status != 200) {
            console.error('Произошла ошибка при отмене операции.');
        }
    };
    xhr.send("action=cancel");
}
