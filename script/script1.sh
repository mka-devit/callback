#!/bin/bash

SCRIPT_NAME="script2.sh"
LOG_FILE="nohup.out"

while true; do
    # Проверка, запущен ли скрипт
    if pgrep -f "$SCRIPT_NAME" > /dev/null; then
        echo "$SCRIPT_NAME уже запущен. $(date '+%Y-%m-%d %H:%M:%S')" | tee -a $LOG_FILE
    else
        echo "Запуск $SCRIPT_NAME в $(date '+%Y-%m-%d %H:%M:%S')" | tee -a $LOG_FILE
        echo "$(date '+%Y-%m-%d %H:%M:%S')" >> $LOG_FILE
        nohup /path/to/$SCRIPT_NAME >> $LOG_FILE 2>&1 &
    fi
    
    # Ожидание перед следующей проверкой
    sleep 5  # Проверять каждые 60 секунд (измените по необходимости)
done
