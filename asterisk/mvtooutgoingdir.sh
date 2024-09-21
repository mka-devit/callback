#!/bin/bash

# Переменные
SOURCE_DIR="/bash_call/callback/create"
DEST_DIR="/var/spool/asterisk/outgoing"
OWNER="asterisk:asterisk"
FILES_PER_BATCH=5

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
    move_files
    sleep 10  # Пауза 10 секунд перед новой проверкой
done

