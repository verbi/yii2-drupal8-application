<?php

namespace verbi\yii2Drupal8Application;

use Yii;
use yii\helpers\Url;
use yii\base\InvalidRouteException;
use verbi\yii2Drupal8Application\helpers\Request;
use verbi\yii2Drupal8Application\helpers\Response;
use verbi\yii2Drupal8Application\helpers\Session;
use verbi\yii2Drupal8Application\helpers\User;
use verbi\yii2Drupal8Application\helpers\ErrorHandler;
use verbi\yii2Drupal8Application\exceptions\NotFoundHttpException;

/**
 * Application is the base class for all drupal8 application classes.
 *
 *
 * @property ErrorHandler $errorHandler The error handler application component. This property is read-only.
 * @property string $homeUrl The homepage URL.
 * @property Request $request The request component. This property is read-only.
 * @property Response $response The response component. This property is read-only.
 * @property Session $session The session component. This property is read-only.
 * @property User $user The user component. This property is read-only.
 *
 * @author Philip Verbist <philip.verbist@gmail.com>
 * @since 2.0
 */
class Application extends \yii\web\Application {

    public $layout;
    public $bootstrap = ['log'];
    public $params = [
        'adminEmail' => 'admin@example.com',
    ];

    /**
     * @inheritdoc
     */
    protected function bootstrap() {
        $request = $this->getRequest();
        Yii::setAlias('@webroot', dirname($request->getScriptFile()));
        Yii::setAlias('@web', $request->getBaseUrl());
        parent::bootstrap();
    }

    public function run() {
        ob_start();
        parent::run();
        ob_end_clean();
        return $this->getResponse();
    }

    /**
     * Handles the specified request.
     * @param Request $request the request to be handled
     * @return Response the resulting response
     * @throws NotFoundHttpException if the requested route is invalid
     */
    public function handleRequest($request) {
        if (empty($this->catchAll)) {
            try {
                list ($route, $params) = $request->resolve();
            } catch (UrlNormalizerRedirectException $e) {
                $url = $e->url;
                if (is_array($url)) {
                    if (isset($url[0])) {
                        // ensure the route is absolute
                        $url[0] = '/' . ltrim($url[0], '/');
                    }
                    $url += $request->getQueryParams();
                }
                return $this->getResponse()->redirect(Url::to($url, $e->scheme), $e->statusCode);
            }
        } else {
            $route = $this->catchAll[0];
            $params = $this->catchAll;
            unset($params[0]);
        }
        try {
            Yii::trace("Route requested: '$route'", __METHOD__);
            $this->requestedRoute = $route;
            $result = $this->runAction($route, $params);
            if ($result instanceof Response) {
                return $result;
            } else {
                $response = $this->getResponse();
                if ($result !== null) {
                    $response->data = $result;
                }
                return $response;
            }
        } catch (InvalidRouteException $e) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'), $e->getCode(), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function coreComponents() {
        return array_merge(parent::coreComponents(), [
            'request' => [
                'class' => Request::className(),
                'cookieValidationKey' => 'so-bLQoWlm1IIiChzihcjtzMZnsbSQ0M',
            ],
            'response' => ['class' => Response::className()],
            'session' => ['class' => Session::className()],
            'user' => [
                'class' => User::className(),
                'identityClass' => 'app\models\User',
                'enableAutoLogin' => true,
            ],
            'errorHandler' => [
                'class' => ErrorHandler::className(),
                'errorAction' => 'site/error',
            ],
            'cache' => [
                'class' => 'yii\caching\FileCache',
            ],
            'mailer' => [
                'class' => 'yii\swiftmailer\Mailer',
                // send all mails to a file by default. You have to set
                // 'useFileTransport' to false and configure a transport
                // for the mailer to send real emails.
                'useFileTransport' => true,
            ],
            'log' => [
                'class' => 'yii\log\Dispatcher',
                'traceLevel' => YII_DEBUG ? 3 : 0,
                'targets' => [
                    [
                        'class' => 'yii\log\FileTarget',
                        'levels' => ['error', 'warning'],
                    ],
                ],
            ],
            'db' => [
                'class' => 'yii\db\Connection',
                'dsn' => 'mysql:host=localhost;dbname=yii2basic',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8',
            ],
        ]);
    }

}
