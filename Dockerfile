# Используем базовый образ с PHP и Apache
FROM php:8.2-apache

# Устанавливаем необходимые пакеты
RUN apt-get update && apt-get install -y \
    cron \
    jq \
    nano \
    chrony \
    tzdata \
    && docker-php-ext-install mysqli

# Настраиваем часовой пояс Ташкента (UTC+5)
ENV TZ=Asia/Tashkent

# Создаём пользователя и группу asterisk
RUN groupadd -r asterisk && useradd -r -g asterisk asterisk

# Копируем файлы проекта в папку /var/www/html контейнера
COPY . /var/www/html/

# Делаем скрипты исполняемыми
RUN chmod +x /var/www/html/script/script1.sh && \
    chmod +x /var/www/html/script/script2.sh

# Добавляем задачи в crontab для пользователя root
RUN echo "0 8 * * * /var/www/html/script/script1.sh" >> /etc/crontab && \
    echo "0 22 * * * pkill -f script1.sh" >> /etc/crontab

# Открываем порт 80 для доступа к веб-серверу
EXPOSE 80

# Настройка chrony для синхронизации с сервером времени
RUN echo "server pool.ntp.org iburst" >> /etc/chrony/chrony.conf

# Запуск cron, chrony и Apache в фоновом режиме
CMD service chrony start && service cron start && apache2-foreground
