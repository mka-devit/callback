# Путь к файлу со списком номеров
PHONE_NUMBERS_FILE="spisok"

# Временная директория для создания .call файлов
TEMP_DIR="outcoloing"

# Конечная директория для размещения .call файлов
OUTGOING_DIR="/var/spool/asterisk/outgoing/"

# Количество строк для чтения за раз
BATCH_SIZE=20

# Проверка наличия файла со списком номеров
if [ ! -f "$PHONE_NUMBERS_FILE" ]; then
  echo "Файл с номерами не найден!"
  exit 1
fi

# Создание временной директории, если не существует
mkdir -p "$TEMP_DIR"

# Функция для создания .call файла
create_call_file() {
  local phone_number=$1
  local temp_call_file="$TEMP_DIR/$phone_number.call"

  echo "Channel: Local/$phone_number@indebtedness-notify/n" > "$temp_call_file"
  echo "CallerID: 781500000" >> "$temp_call_file"
  echo "MaxRetries: 1" >> "$temp_call_file"
  echo "RetryTime: 21600" >> "$temp_call_file"
  echo "WaitTime: 30" >> "$temp_call_file"
  echo "Application: Playback" >> "$temp_call_file"
  echo "Data: neutral/notify/freelinkuzru" >> "$temp_call_file"
  echo "AlwaysDelete: Yes" >> "$temp_call_file"

  # Перемещение файла в конечную папку
  chown asterisk:asterisk "$temp_call_file"
  mv "$temp_call_file" "$OUTGOING_DIR"
}

# Чтение файла по 20 строк за раз
while true; do
  # Читаем очередные 20 строк
  lines=$(head -n $BATCH_SIZE "$PHONE_NUMBERS_FILE")

  # Если строки кончились - выходим
  if [ -z "$lines" ]; then
    echo "Обработка завершена."
    break
  fi

  # Проход по строкам и создание .call файлов
  echo "$lines" | while IFS= read -r phone_number; do
    echo "Обрабатываем номер: $phone_number"  # Выводим текущий номер
    create_call_file "$phone_number"
  done

  # Удаляем прочитанные строки из файла
  sed -i "1,${BATCH_SIZE}d" "$PHONE_NUMBERS_FILE"

  # Ожидаем 40 секунд перед следующей итерацией
  sleep 40
done


