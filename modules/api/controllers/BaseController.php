<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class BaseController extends Controller
{
    private int $startMemory;
    private float $startTime;

    private const EXPECTED_PARTNER_TOKEN = 'test-token';

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        return $behaviors;
    }

    /**
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     */
    public function beforeAction($action): bool
    {
        $request = Yii::$app->request;
        $partnerToken = $request->headers->get('x-partner-token');

        if ($partnerToken !== self::EXPECTED_PARTNER_TOKEN) {
            throw new UnauthorizedHttpException();
        }

        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();

        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        $executionTime = (microtime(true) - $this->startTime) * 1000;
        $memoryUsage = (memory_get_usage() - $this->startMemory) / 1024;

        Yii::$app->response->headers->set('X-Debug-Time', $executionTime);
        Yii::$app->response->headers->set('X-Debug-Memory', $memoryUsage);

        return parent::afterAction($action, $result);
    }
}