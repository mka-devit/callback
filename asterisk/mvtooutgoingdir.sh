#!/bin/bash

# Переменные
SOURCE_DIR="/bash_call/callback/create"
DEST_DIR="/var/spool/asterisk/outgoing"
OWNER="asterisk:asterisk"
FILES_PER_BATCH=5
START_HOUR=9
END_HOUR=22

# Функция перемещения файлов
move_files() {
    files=($(ls $SOURCE_DIR))
    if [ ${#files[@]} -gt 0 ]; then
        echo "Найдено файлов: ${#files[@]}"

        # Перемещаем файлы по $FILES_PER_BATCH штук
        for file in "${files[@]:0:$FILES_PER_BATCH}"; do
            chown $OWNER "$SOURCE_DIR/$file"
            mv "$SOURCE_DIR/$file" "$DEST_DIR"
            echo "Перемещён файл: $file"
        done
    fi
}

# Основной цикл
while true; do
    # Получаем текущий час
    current_hour=$(date +"%H")

    # Проверяем, что текущее время в диапазоне 9:00 - 22:00
    if [ $current_hour -ge $START_HOUR ] && [ $current_hour -lt $END_HOUR ]; then
        move_files
    else
        echo "Вне времени работы. Ожидание..."
    fi

    sleep 10  # Пауза 10 секунд перед новой проверкой
done

