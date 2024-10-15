#!/bin/bash

# Путь к конфигурационному файлу
CONFIG_FILE="/var/www/html/config.json"

# Лок-файл для предотвращения запуска нескольких экземпляров
LOCK_FILE="/tmp/move_files.lock"

# Функция для получения конфигурации
get_config() {
    local key=$1
    local value=$(jq -r ".${key}" "${CONFIG_FILE}")
    echo "${value}"
}

# Основной цикл работы скрипта
while true; do
    # Проверка на наличие лок-файла (для предотвращения запуска нескольких экземпляров)
    if [ -f "${LOCK_FILE}" ]; then
        echo "Скрипт уже запущен."
        exit 1
    fi

    # Создание лок-файла
    touch "${LOCK_FILE}"

    # Получение параметров из конфигурационного файла
    LIMIT=$(get_config 'limit')
    INTERVAL=$(get_config 'interval')
    DEST_DIR=$(get_config 'destination')

    # Вывод значений для отладки
    echo "LIMIT=${LIMIT}"
    echo "INTERVAL=${INTERVAL}"
    echo "DEST_DIR=${DEST_DIR}"

    # Проверка и создание директории назначения, если она не существует
    if [ ! -d "${DEST_DIR}" ]; then
        echo "Директория назначения ${DEST_DIR} не существует. Создание..."
        mkdir -p "${DEST_DIR}"
    fi

    # Проверка интервала
    if ! [[ "${INTERVAL}" =~ ^[0-9]+$ ]]; then
        echo "Ошибка: Параметр 'interval' должен быть положительным целым числом."
        rm -f "${LOCK_FILE}"
        exit 1
    fi

    # Путь к папке-источнику
    SOURCE_DIR="/var/www/html/create"

    # Перемещение файлов
    FILES_MOVED=0
    while [ "$(ls -1q "${SOURCE_DIR}" | wc -l)" -gt 0 ] && [ "${FILES_MOVED}" -lt "${LIMIT}" ]; do
        for FILE in "${SOURCE_DIR}"/*; do
            if [ -e "${FILE}" ]; then
                # Изменение владельца
                chown asterisk:asterisk "${FILE}"
                # Перемещение файла
                mv "${FILE}" "${DEST_DIR}"
                FILES_MOVED=$((FILES_MOVED + 1))
                # Прекращение перемещения после указанного количества файлов
                if [ "${FILES_MOVED}" -ge "${LIMIT}" ]; then
                    break
                fi
            fi
        done
        # Ожидание указанное время
        echo "Ожидание ${INTERVAL} секунд..."
        sleep "${INTERVAL}"
    done

    # Удаление лок-файла
    rm -f "${LOCK_FILE}"

    # Ожидание до следующего запуска (8 AM - 10 PM)
    CURRENT_HOUR=$(date +"%H")
    if [ "${CURRENT_HOUR}" -ge 20 ] || [ "${CURRENT_HOUR}" -lt 8 ]; then
        exit 0
    fi

    sleep 1
done
