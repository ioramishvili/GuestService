# Guest Service Microservice

## Описание
Этот микросервис предназначен для управления данными о гостях. Он предоставляет API для выполнения CRUD операций (создание, чтение, обновление, удаление) над сущностью "Гость". Микросервис реализован на PHP.

Технический стек:
- PHP (Yii2 Framework)
- MySQL
- Docker
- Nginx

### Установка
Убедитесь, что на вашем компьютере установлены последние версии Docker и Docker Compose.

1. **Склонируйте репозиторий:**
   ```bash
   git clone git@github.com:ioramishvili/GuestService.git
   cd GuestService
   ```

2. **Создайте файл `.env` в корне проекта и укажите необходимые переменные окружения:**
   ```
   DB_ROOT_PASSWORD=root_password
   DB_NAME=guest_service
   DB_USERNAME=user
   DB_PASSWORD=password
   TZ=Europe/Moscow
   ```

3. **Постройте Docker образы и запустите контейнеры:**
   ```bash
   docker-compose build
   docker-compose up -d
   ```

4. **Установите зависимости внутри контейнера `php-fpm`:**
   ```bash
   docker-compose exec php-fpm composer install
   ```

5. **Создайте документацию API:**
   ```bash
   docker-compose exec php-fpm apidoc -i /www/modules/api/controllers/ -o /www/docs/api
   ```
6. **Инициализируйте базу данных:**
   ```bash
   docker-compose exec php-fpm php yii migrate/up
   ```
7. **Доступ к документации API:**
   Откройте [http://localhost/docs/api/](http://localhost/docs/api/) в вашем веб-браузере для просмотра документации.

### API Документация
API поддерживает следующие запросы:
- **GET /api/guest/** - Получить список гостей
- **GET /api/guest/{id}** - Получить информацию о конкретном госте по ID
- **POST /api/guest/** - Создать нового гостя
- **PUT /api/guest/{id}** - Обновить информацию о госте по ID
- **DELETE /api/guest/{id}** - Удалить гостя по ID

### Сущность "Гость"
Каждый гость имеет следующие атрибуты:
- `id` (уникальный идентификатор)
- `first_name` (имя, обязательное поле)
- `last_name` (фамилия, обязательное поле)
- `email` (электронная почта, уникальное поле)
- `phone` (телефон, уникальное поле, обязательное поле)
- `country` (страна, опциональное поле, при создании используется формат ISO 3166-1 alpha-2)
- Если страна не указана, она определяется по телефонному коду (например, +7 - Россия).

#### Примеры запросов
- **Создание гостя:**
  ```bash
  curl -X POST http://localhost/api/guest/ -H "Content-Type: application/json" -d '{"first_name": "John", "last_name": "Doe", "email": "john.doe@example.com", "phone": "+79999999999"}'
  ```

- **Получение списка гостей:**
  ```bash
  curl -X GET http://localhost/api/guest/
  ```

### Дополнительная информация
В ответах сервера присутствуют следующие заголовки:
- `X-Debug-Time` - время выполнения запроса в миллисекундах
- `X-Debug-Memory` - количество используемой памяти в Кб
