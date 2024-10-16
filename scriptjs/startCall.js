// scriptjs/startCall.js
function startCall() {
    fetch('start_call.php')
        .then(response => response.text())
        .then(data => {
            showNotification(data);
        })
        .catch(error => {
            showNotification('Ошибка при запуске обзвона: ' + error);
        });
}
