#!/bin/bash

# Переменные
SOURCE_DIR="/var/www/html/create"
DEST_DIR="/var/www/html/syuda"
OWNER="asterisk:asterisk"
FILES_PER_BATCH=5
STATUS_FILE="/var/www/html/asterisk/status.txt"

# Цикл проверки статуса и ожидания, если played=no
check_status() {
    while true; do
        status=$(cat $STATUS_FILE)
        
        if [ "$status" == "played=no" ]; then
            echo "Скрипт приостановлен (played=no), проверка статуса..."
            sleep 5  # Проверяем статус каждые 5 секунд
        else
            echo "Статус изменен на played=yes, продолжение работы..."
            break
        fi
    done
}

# Функция перемещения файлов с постоянной проверкой статуса
move_files() {
    files=($(ls $SOURCE_DIR))
    
    if [ ${#files[@]} -gt 0 ]; then
        echo "Найдено файлов: ${#files[@]}"
        
        for file in "${files[@]:0:$FILES_PER_BATCH}"; do
            # Проверяем статус перед каждым перемещением файла
            status=$(cat $STATUS_FILE)
            if [ "$status" == "played=no" ]; then
                echo "Статус изменен на played=no во время работы, остановка скрипта."
                check_status  # Снова ждем, пока статус не станет played=yes
            fi

            # Продолжаем перемещать файлы, если played=yes
            chown $OWNER "$SOURCE_DIR/$file"
            mv "$SOURCE_DIR/$file" "$DEST_DIR"
            echo "Перемещён файл: $file"
            sleep 1  # Ждем 1 секунду между перемещениями
        done
    fi
}

# Основной цикл скрипта
while true; do
    check_status  # Ожидание, пока статус станет played=yes
    move_files  # Перемещение файлов с проверкой статуса
done
