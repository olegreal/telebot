Для работы бота надо установить webhook.
Для этого нужно послать запрос в телеграм следующего формата
https://api.telegram.org/bot{BOT_TOKEN}/setwebhook?url={BOT_URL}

{BOT_TOKEN} - вместо этого нужно подставить реальный токен бота ( https://t.me/botfather - бот раздающий токены)
{BOT_URL} - полный URL файла bot.php 


также в файле config.php нужно указать параметры соединения с базой данных, токен и имя бота ( TELEGRAM_BOT_TOKEN , TELEGRAM_BOT_NAME )
и путь к каталогу со скриптами (SCRIPT_URL_PREFIX)