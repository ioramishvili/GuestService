<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\models\Guest;

class GuestController extends BaseController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @api {get} /guest Получить список гостей
     * @apiName GetGuests
     * @apiGroup Guest
     * @apiDescription Получение списка гостей по фильтрам с поддержкой пагинации.
     *
     * @apiHeader {String} x-partner-token Токен аутентификации партнера ("test-token" по умолчанию).
     *
     * @apiParam {String} [email] Фильтр по email гостя.
     * @apiParam {String} [phone] Фильтр по телефону гостя.
     * @apiParam {String} [country] Фильтр по стране гостя.
     * @apiParam {Number} [page=1] Номер страницы для пагинации.
     * @apiParam {Number} [pageSize=10] Количество записей на странице.
     *
     * @apiSuccess {Object[]} guests Список гостей.
     * @apiSuccess {Number} guests.id Уникальный идентификатор гостя.
     * @apiSuccess {String} guests.first_name Имя гостя.
     * @apiSuccess {String} guests.last_name Фамилия гостя.
     * @apiSuccess {String} guests.email Email гостя.
     * @apiSuccess {String} guests.phone Телефон гостя.
     * @apiSuccess {String} guests.country Страна гостя.
     * @apiSuccess {Number} totalCount Общее количество гостей.
     * @apiSuccess {Number} pageCount Общее количество страниц.
     * @apiSuccess {Number} currentPage Текущая страница.
     * @apiSuccess {Number} perPage Количество записей на странице.
     *
     * @apiSuccessExample {json} Успешный ответ:
     * HTTP/1.1 200 OK
     * {
     *   "totalCount": 25,
     *   "pageCount": 3,
     *   "currentPage": 1,
     *   "perPage": 10,
     *   "guests": [
     *     {
     *       "id": 1,
     *       "first_name": "Иван",
     *       "last_name": "Иванов",
     *       "email": "ivanov@example.com",
     *       "phone": "+79123456789",
     *       "country": "Россия"
     *     },
     *     {
     *       "id": 2,
     *       "first_name": "Петр",
     *       "last_name": "Петров",
     *       "email": "petrov@example.com",
     *       "phone": "+79123456780",
     *       "country": "Россия"
     *     }
     *     // другие гости...
     *   ]
     * }
     */
    public function actionIndex(): array
    {
        $email = Yii::$app->request->getQueryParam('email');
        $phone = Yii::$app->request->getQueryParam('phone');
        $country = Yii::$app->request->getQueryParam('country');
        $page = Yii::$app->request->getQueryParam('page', 1);
        $pageSize = Yii::$app->request->getQueryParam('pageSize', 10);

        $query = Guest::find()
            ->andFilterWhere(['like', 'email', $email])
            ->andFilterWhere(['like', 'phone', $phone])
            ->andFilterWhere(['country' => $country]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
                'page' => $page - 1,
            ],
        ]);

        return [
            'totalCount' => $dataProvider->getTotalCount(),
            'pageCount' => $dataProvider->getPagination()->getPageCount(),
            'currentPage' => $dataProvider->getPagination()->getPage() + 1,
            'perPage' => $dataProvider->getPagination()->getPageSize(),
            'guests' => $dataProvider->getModels(),
        ];
    }



    /**
     * @api {get} /api/guest/:id Получить информацию о госте
     * @apiName GetGuest
     * @apiGroup Guest
     * @apiDescription Получение информации о конкретном госте по ID.
     *
     * @apiHeader {String} x-partner-token Токен аутентификации партнера ("test-token" по умолчанию).
     *
     * @apiParam {Number} id Уникальный идентификатор гостя.
     *
     * @apiSuccess {Number} id Уникальный идентификатор гостя.
     * @apiSuccess {String} first_name Имя гостя.
     * @apiSuccess {String} last_name Фамилия гостя.
     * @apiSuccess {String} email Email гостя.
     * @apiSuccess {String} phone Телефон гостя.
     * @apiSuccess {String} country Страна гостя.
     *
     * @apiSuccessExample {json} Успешный ответ:
     * HTTP/1.1 200 OK
     * {
     *   "id": 1,
     *   "first_name": "Иван",
     *   "last_name": "Иванов",
     *   "email": "ivanov@example.com",
     *   "phone": "+79123456789"
     * }
     *
     * @apiError NotFound Гость не найден.
     * @apiErrorExample {json} Ошибка 404:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Гость с ID 1 не найден."
     * }
     */
    public function actionView(int $id): Guest
    {
        $guest = Guest::findOne($id);
        if ($guest === null) {
            throw new NotFoundHttpException("Гость с ID $id не найден.");
        }

        return $guest;
    }

    /**
     * @api {post} /api/guest Создать нового гостя
     * @apiName CreateGuest
     * @apiGroup Guest
     * @apiDescription Создание нового гостя.
     *
     * @apiHeader {String} x-partner-token Токен аутентификации партнера ("test-token" по умолчанию).
     *
     * @apiBody {String} first_name Имя гостя.
     * @apiBody {String} last_name Фамилия гостя.
     * @apiBody {String} email Email гостя.
     * @apiBody {String} phone Телефон гостя.
     * @apiBody {String} [country] Страна гостя в формате ISO 3166-1 alpha-2.
     *
     * @apiSuccess (201) {Number} id Уникальный идентификатор созданного гостя.
     * @apiSuccess (201) {String} first_name Имя гостя.
     * @apiSuccess (201) {String} last_name Фамилия гостя.
     * @apiSuccess (201) {String} email Email гостя.
     * @apiSuccess (201) {String} phone Телефон гостя.
     * @apiSuccess (201) {String} country Страна гостя.
     *
     * @apiExample {curl} Пример запроса:
     * curl -X POST /api/guest \
     * -H "Content-Type: application/json" \
     * -d '{
     *   "first_name": "Алексей",
     *   "last_name": "Алексеев",
     *   "email": "alekseev@example.com",
     *   "phone": "+79123456781",
     *   "country": "RU"
     * }'
     *
     * @apiSuccessExample {json} Успешный ответ:
     * HTTP/1.1 201 Created
     * {
     *   "id": 3,
     *   "first_name": "Алексей",
     *   "last_name": "Алексеев",
     *   "email": "alekseev@example.com",
     *   "phone": "+79123456781",
     *   "country": "Россия"
     * }
     *
     * @apiError BadRequest Некорректные данные.
     * @apiErrorExample {json} Ошибка 400:
     * HTTP/1.1 400 Bad Request
     * {
     *   "message": "Некорректные данные."
     * }
     */
    public function actionCreate()
    {
        $guest = new Guest();
        $guest->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($guest->save()) {
            Yii::$app->response->setStatusCode(201);
            return $guest;
        }

        if (!$guest->hasErrors()) {
            throw new ServerErrorHttpException('Не удалось создать гостя по неизвестным причинам.');
        }

        return $guest;
    }

    /**
     * @api {put} /api/guest/:id Обновить информацию о госте
     * @apiName UpdateGuest
     * @apiGroup Guest
     * @apiDescription Обновление информации о существующем госте.
     *
     * @apiHeader {String} x-partner-token Токен аутентификации партнера ("test-token" по умолчанию).
     *
     * @apiParam {Number} id Уникальный идентификатор гостя.
     * @apiBody {String} [first_name] Имя гостя.
     * @apiBody {String} [last_name] Фамилия гостя.
     * @apiBody {String} [email] Email гостя.
     * @apiBody {String} [phone] Телефон гостя.
     * @apiBody {String} [country] Страна гостя в формате ISO 3166-1 alpha-2.
     *
     * @apiSuccess {Number} id Уникальный идентификатор гостя.
     * @apiSuccess {String} first_name Имя гостя.
     * @apiSuccess {String} last_name Фамилия гостя.
     * @apiSuccess {String} email Email гостя.
     * @apiSuccess {String} phone Телефон гостя.
     * @apiSuccess (201) {String} country Страна гостя.
     *
     * @apiSuccessExample {json} Успешный ответ:
     * HTTP/1.1 200 OK
     * {
     *   "id": 1,
     *   "first_name": "Иван",
     *   "last_name": "Иванов",
     *   "email": "ivanov@example.com",
     *   "phone": "+79123456789"
     * }
     *
     * @apiError NotFound Гость не найден.
     * @apiErrorExample {json} Ошибка 404:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Гость с ID 1 не найден."
     * }
     */
    public function actionUpdate(int $id): Guest
    {
        $guest = Guest::findOne($id);
        if ($guest === null) {
            throw new NotFoundHttpException("Гость с ID $id не найден.");
        }

        $guest->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($guest->save()) {
            return $guest;
        }

        if (!$guest->hasErrors()) {
            throw new ServerErrorHttpException('Не удалось обновить гостя по неизвестным причинам.');
        }

        return $guest;
    }

    /**
     * @api {delete} /api/guest/:id Удалить гостя
     * @apiName DeleteGuest
     * @apiGroup Guest
     * @apiDescription Удаление гостя по уникальному идентификатору.
     *
     * @apiHeader {String} x-partner-token Токен аутентификации партнера ("test-token" по умолчанию).
     *
     * @apiParam {Number} id Уникальный идентификатор гостя.
     *
     * @apiSuccessExample {json} Успешный ответ:
     * HTTP/1.1 204 No Content
     *
     * @apiError NotFound Гость не найден.
     * @apiErrorExample {json} Ошибка 404:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Гость с ID 1 не найден."
     * }
     */
    public function actionDelete(int $id): Response
    {
        $guest = Guest::findOne($id);
        if ($guest === null) {
            throw new NotFoundHttpException("Гость с ID $id не найден.");
        }

        if ($guest->delete() === false) {
            throw new ServerErrorHttpException('Не удалось удалить гостя по неизвестным причинам.');
        }

        Yii::$app->response->setStatusCode(204);
        return Yii::$app->response;
    }
}
