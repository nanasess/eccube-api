<?php

/*
 * This file is part of the EccubeApi
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Plugin\EccubeApi\ServiceProvider;

use Eccube\Application;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\EccubeApi\Form\Type\EccubeApiConfigType;
use Plugin\EccubeApi\Form\Type\ApiClientType;
use Symfony\Component\Yaml\Yaml;
use Pimple\Container;
use Pimple\ServiceProviderInterface;


class EccubeApiServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
         // メッセージ登録
        $app->extend('translator', function ($translator) use ($app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());
            $file = __DIR__ . '/../Resource/locale/message_api.' . $app['locale'] . '.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }
            return $translator;
         });

        // load config
        $conf = $app['config'];
        $app->overwrite('config', function () use ($conf) {
            $confarray = array();

            $constant_file = __DIR__.'/../Resource/config/constant.yml';
            if (file_exists($constant_file)) {
                $config_yml = Yaml::parse(file_get_contents($constant_file));
                if (isset($config_yml)) {
                    $confarray = array_replace_recursive($confarray, $config_yml);
                }
            }

            return array_replace_recursive($conf, $confarray);
        });

        // ログファイル設定
        $app['monolog.EccubeApi'] = function ($app) {

            $logger = new $app['monolog.logger.class']('plugin.EccubeApi');

            $file = $app['config']['root_dir'].'/app/log/EccubeApi.log';
            $RotateHandler = new RotatingFileHandler($file, $app['config']['log']['max_files'], Logger::INFO);
            $RotateHandler->setFilenameFormat(
                'EccubeApi_{date}',
                'Y-m-d'
            );

            $logger->pushHandler(
                new FingersCrossedHandler(
                    $RotateHandler,
                    new ErrorLevelActivationStrategy(Logger::INFO)
                )
            );

            return $logger;
        };


        // roting

        // api
        $c = $app['controllers_factory'];

        // product_category
        $c->get('/product_category/product_id/{product_id}/category_id/{category_id}',
                'Plugin\EccubeApi\Controller\EccubeApiCRUDController::findProductCategory')
            ->bind('api_operation_find_product_category')
            ->assert('product_id', '\d+')->assert('category_id', '\d+');
        $c->post('/product_category', 'Plugin\EccubeApi\Controller\EccubeApiCRUDController::createProductCategory')
            ->bind('api_operation_create_product_category');
        $c->put('/product_category/product_id/{product_id}/category_id/{category_id}',
                'Plugin\EccubeApi\Controller\EccubeApiCRUDController::updateProductCategory')
            ->bind('api_operation_update_product_category')
            ->assert('product_id', '\d+')->assert('category_id', '\d+');

        // payment_option キーのみのテーブルなので get と post のみ
        $c->get('/payment_option/delivery_id/{delivery_id}/payment_id/{payment_id}',
                'Plugin\EccubeApi\Controller\EccubeApiCRUDController::findPaymentOption')
            ->bind('api_operation_find_payment_option')
            ->assert('delivery_id', '\d+')->assert('payment_id', '\d+');
        $c->post('/payment_option', 'Plugin\EccubeApi\Controller\EccubeApiCRUDController::createPaymentOption')
            ->bind('api_operation_create_payment_option');

        // block_position
        $c->get('/block_position/page_id/{page_id}/target_id/{target_id}/block_id/{block_id}',
                'Plugin\EccubeApi\Controller\EccubeApiCRUDController::findBlockPosition')
            ->bind('api_operation_find_block_position')
            ->assert('page_id', '\d+')->assert('target_id', '\d+')->assert('block_id', '\d+');
        $c->post('/block_position', 'Plugin\EccubeApi\Controller\EccubeApiCRUDController::createBlockPosition')
            ->bind('api_operation_create_block_position');
        $c->put('/block_position/page_id/{page_id}/target_id/{target_id}/block_id/{block_id}',
                'Plugin\EccubeApi\Controller\EccubeApiCRUDController::updateBlockPosition')
            ->bind('api_operation_update_block_position')
            ->assert('page_id', '\d+')->assert('target_id', '\d+')->assert('block_id', '\d+');


        $c->get('/{table}', 'Plugin\EccubeApi\Controller\EccubeApiCRUDController::findAll')->bind('api_operation_findall')->assert('table', '\w+');
        $c->post('/{table}', 'Plugin\EccubeApi\Controller\EccubeApiCRUDController::create')->bind('api_operation_create')->assert('table', '\w+');
        $c->get('/{table}/{id}', 'Plugin\EccubeApi\Controller\EccubeApiCRUDController::find')->bind('api_operation_find')->assert('table', '\w+')->assert('id', '\d+');
        $c->delete('/{table}/{id}', 'Plugin\EccubeApi\Controller\EccubeApiCRUDController::delete')->bind('api_operation_delete')->assert('table', '\w+')->assert('id', '\d+');
        $c->put('/{table}/{id}', 'Plugin\EccubeApi\Controller\EccubeApiCRUDController::update')->bind('api_operation_put')->assert('table', '\w+')->assert('id', '\d+');

        $c->match('/products', 'Plugin\EccubeApi\Controller\EccubeApiController::products')->bind('api_products');
        $c->get('/products/{id}', 'Plugin\EccubeApi\Controller\EccubeApiController::productsDetail')->bind('api_products_detail')->assert('id', '\d+');

        // 認証sample
        $c->get('/productsauthsample/{id}', 'Plugin\EccubeApi\Controller\EccubeApiSampleController::productsDetail')->bind('api_products_auth_sample')->assert('id', '\d+');
        $app->mount($app['config']['api.endpoint'].'/'.$app['config']['api.version'], $c);

        // Swagger 関連
        $s = $app['controllers_factory'];
        $s->match('/swagger-ui', 'Plugin\EccubeApi\Controller\EccubeApiController::swaggerUI')->bind('swagger_ui');
        $s->match('/api-docs', 'Plugin\EccubeApi\Controller\EccubeApiController::swagger')->bind('swagger_yml');
        $s->match('/o2c', 'Plugin\EccubeApi\Controller\EccubeApiController::swaggerO2c')->bind('swagger_o2c');
        $app->mount('/'.$app['config']['api.endpoint'], $s);

        // 認証関連
        $ep = $app['controllers_factory'];
        $ep->match('/OAuth2/'.$app['config']['api.version'].'/token', 'Plugin\EccubeApi\Controller\OAuth2\OAuth2Controller::token')->bind('oauth2_server_token');
        $ep->match('/OAuth2/'.$app['config']['api.version'].'/tokeninfo', 'Plugin\EccubeApi\Controller\OAuth2\OAuth2Controller::tokeninfo')->bind('oauth2_server_tokeninfo');
        $ep->match('/OAuth2/'.$app['config']['api.version'].'/userinfo', 'Plugin\EccubeApi\Controller\OAuth2\OAuth2Controller::userInfo')->bind('oauth2_server_userinfo');
        $ep->match('/'.trim($app['config']['admin_route'], '/').'/OAuth2/'.$app['config']['api.version'].'/authorize', 'Plugin\EccubeApi\Controller\OAuth2\OAuth2Controller::authorize')->bind('oauth2_server_admin_authorize');
        $ep->match('/'.trim($app['config']['admin_route'], '/').'/OAuth2/'.$app['config']['api.version'].'/authorize/{code}', 'Plugin\EccubeApi\Controller\OAuth2\OAuth2Controller::authorizeOob')->assert('code', '\w+')->bind('oauth2_server_admin_authorize_oob');

        $ep->match('/mypage/OAuth2/'.$app['config']['api.version'].'/authorize', 'Plugin\EccubeApi\Controller\OAuth2\OAuth2Controller::authorize')->bind('oauth2_server_mypage_authorize');
        $ep->match('/mypage/OAuth2/'.$app['config']['api.version'].'/authorize/{code}', 'Plugin\EccubeApi\Controller\OAuth2\OAuth2Controller::authorizeOob')->assert('code', '\w+')->bind('oauth2_server_mypage_authorize_oob');
        $app->mount('/', $ep);

        // APIクライアント設定画面
        $m = $app['controllers_factory'];
        $m->match('/setting/system/member/{member_id}/api', 'Plugin\EccubeApi\Controller\ApiClientController::lists')->assert('member_id', '\d+')->bind('admin_api_lists');
        $m->match('/setting/system/member/{member_id}/api/{client_id}/edit', 'Plugin\EccubeApi\Controller\ApiClientController::edit')->assert('member_id', '\d+')->assert('client_id', '\d+')->bind('admin_setting_system_client_edit');
        $m->delete('/setting/system/member/{member_id}/api/{client_id}/delete', 'Plugin\EccubeApi\Controller\ApiClientController::delete')->assert('member_id', '\d+')->assert('client_id', '\d+')->bind('admin_setting_system_client_delete');
        $m->match('/setting/system/member/{member_id}/api/new', 'Plugin\EccubeApi\Controller\ApiClientController::newClient')->assert('member_id', '\d+')->bind('admin_setting_system_client_new');
        $app->mount('/'.trim($app['config']['admin_route'], '/').'/', $m);

        $c = $app['controllers_factory'];
        $c->match('/api', 'Plugin\EccubeApi\Controller\ApiClientController::lists')->bind('mypage_api_lists');
        $c->match('/api/{client_id}/edit', 'Plugin\EccubeApi\Controller\ApiClientController::edit')->assert('client_id', '\d+')->bind('mypage_api_client_edit');
        $c->delete('/api/{client_id}/delete', 'Plugin\EccubeApi\Controller\ApiClientController::delete')->assert('client_id', '\d+')->bind('mypage_api_client_delete');
        $c->match('/api/new', 'Plugin\EccubeApi\Controller\ApiClientController::newClient')->bind('mypage_api_client_new');
        $app->mount('/mypage/', $c);

        // Form Extension
        $app->extend('form.types', function ($types) use ($app) {
            $types[] = new ApiClientType($app['config']);
            return $types;
        });

        // Repository
        $app['eccube.repository.oauth2.client'] = function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\EccubeApi\Entity\OAuth2\Client');
        };
        $app['eccube.repository.oauth2.openid.userinfo'] = function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\EccubeApi\Entity\OAuth2\OpenID\UserInfo');
        };
        $app['eccube.repository.oauth2.authorization_code'] = function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\EccubeApi\Entity\OAuth2\AuthorizationCode');
        };
        $app['eccube.repository.oauth2.refresh_token'] = function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\EccubeApi\Entity\OAuth2\RefreshToken');
        };
        $app['eccube.repository.oauth2.access_token'] = function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\EccubeApi\Entity\OAuth2\AccessToken');
        };
        $app['eccube.repository.oauth2.openid.public_key'] = function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\EccubeApi\Entity\OAuth2\OpenID\PublicKey');
        };
        $app['eccube.repository.oauth2.scope'] = function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\EccubeApi\Entity\OAuth2\Scope');
        };
        $app['eccube.repository.oauth2.clientscope'] = function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\EccubeApi\Entity\OAuth2\ClientScope');
        };

        // OAuth2 GrantType
        $app['oauth2.openid.granttype.authorization_code'] = function () use ($app) {
            return new \OAuth2\OpenID\GrantType\AuthorizationCode(
                $app['eccube.repository.oauth2.authorization_code']
            );
        };
        $app['oauth2.granttype.refresh_token'] = function () use ($app) {
            return new \OAuth2\GrantType\RefreshToken(
                $app['eccube.repository.oauth2.refresh_token'],
                array(
                    'always_issue_new_refresh_token' => true,
                    // 'unset_refresh_token_after_use' => false
                )
            );
        };
        $app['oauth2.granttype.client_credential'] =  function () use ($app) {
            return new \OAuth2\GrantType\ClientCredentials(
                $app['eccube.repository.oauth2.client']
            );
        };

        // OAuth2 ResponseType
        $app['oauth2.responsetype.authorization_code'] = function () use ($app) {
            return new \OAuth2\OpenID\ResponseType\AuthorizationCode(
                $app['eccube.repository.oauth2.authorization_code'],
                array('enforce_redirect' => true)
            );
        };
        $app['oauth2.responsetype.access_token'] = function () use ($app) {
            return new \OAuth2\ResponseType\AccessToken(
                $app['eccube.repository.oauth2.access_token'],
                $app['eccube.repository.oauth2.refresh_token']
            );
        };
        $app['oauth2.openid.responsetype.id_token'] = function () use ($app) {
            return new \OAuth2\OpenID\ResponseType\IdToken(
                $app['eccube.repository.oauth2.openid.userinfo'],
                $app['eccube.repository.oauth2.openid.public_key'],
                array('issuer' => rtrim($app->url('homepage'), '/'))
            );
        };
        $app['oauth2.openid.responsetype.id_token_token'] = function () use ($app) {
            return new \OAuth2\OpenID\ResponseType\IdTokenToken(
                $app['oauth2.responsetype.access_token'],
                $app['oauth2.openid.responsetype.id_token']
            );
        };
        $app['oauth2.openid.responsetype.code_id_token'] = function () use ($app) {
            return new \OAuth2\OpenID\ResponseType\CodeIdToken(
                $app['oauth2.responsetype.authorization_code'],
                $app['oauth2.openid.responsetype.id_token']
            );
        };

        // OAuth2 Server
        $oauth2_config = $app['config']['oauth2'];
        // $oauth2_config['issuer'] = rtrim($app->url('homepage'), '/'); XXX homepage が取れない
        $oauth2_config['issuer'] = 'issuer';
        // Authorization Endpoint 用
        $app['oauth2.server.authorization'] = function () use ($app, $oauth2_config) {
            $grantTypes = array(
                'authorization_code' => $app['oauth2.openid.granttype.authorization_code'],
                'refresh_token' => $app['oauth2.granttype.refresh_token']
            );
            $responseTypes = array(
                'token' => $app['oauth2.responsetype.access_token'],
                'code' => $app['oauth2.responsetype.authorization_code'],
                'id_token' => $app['oauth2.openid.responsetype.id_token'],
                'id_token token' => $app['oauth2.openid.responsetype.id_token_token'],
                'code id_token' => $app['oauth2.openid.responsetype.code_id_token']
            );
            $server = new \OAuth2\Server(array(
                'client_credentials' => $app['eccube.repository.oauth2.client'],
                'authorization_code' => $app['eccube.repository.oauth2.authorization_code'],
                'user_claims' => $app['eccube.repository.oauth2.openid.userinfo'],
                'access_token'       => $app['eccube.repository.oauth2.access_token'],
                'refresh_token' => $app['eccube.repository.oauth2.refresh_token'],
                'scope' => $app['eccube.repository.oauth2.scope'],
            ), $oauth2_config, $grantTypes, $responseTypes);
            $server->addStorage($app['eccube.repository.oauth2.openid.public_key'], 'public_key');
            return $server;
        };

        // Token Endpoint 用
        $app['oauth2.server.token'] = function () use ($app, $oauth2_config) {

            $grantTypes = array(
                'authorization_code' => $app['oauth2.openid.granttype.authorization_code'],
                'refresh_token' => $app['oauth2.granttype.refresh_token']
            );

            $responseTypes = array(
                'token' => $app['oauth2.responsetype.access_token'],
                'id_token' => $app['oauth2.openid.responsetype.id_token'],
                'id_token token' => $app['oauth2.openid.responsetype.id_token_token'],
            );

            $server = new \OAuth2\Server(array(
                'client_credentials' => $app['eccube.repository.oauth2.client'],
                'user_claims' => $app['eccube.repository.oauth2.openid.userinfo'],
                // 'user_credentials'   => $userStorage,
                'access_token'       => $app['eccube.repository.oauth2.access_token'],
                'refresh_token' => $app['eccube.repository.oauth2.refresh_token'],
                'authorization_code' => $app['eccube.repository.oauth2.authorization_code'],
                'scope' => $app['eccube.repository.oauth2.scope'],
            ), $oauth2_config, $grantTypes, $responseTypes);
            $server->addStorage($app['eccube.repository.oauth2.openid.public_key'], 'public_key');
            return $server;
        };

        // Resource 用
        $app['oauth2.server.resource'] = function () use ($app, $oauth2_config) {
            $grantTypes = array(
                'authorization_code' => $app['oauth2.openid.granttype.authorization_code'],
                'client_credentials' => $app['oauth2.granttype.client_credential']
            );

            $responseTypes = array(
                'token' => $app['oauth2.responsetype.access_token'],
                'code' => $app['oauth2.responsetype.authorization_code'],
                'id_token' => $app['oauth2.openid.responsetype.id_token'],
                'id_token token' => $app['oauth2.openid.responsetype.id_token_token'],
                'code id_token' => $app['oauth2.openid.responsetype.code_id_token']
            );

            $server = new \OAuth2\Server(array(
                'client_credentials' => $app['eccube.repository.oauth2.client'],
                'user_claims' => $app['eccube.repository.oauth2.openid.userinfo'],
                'access_token' => $app['eccube.repository.oauth2.access_token'],
                'authorization_code' => $app['eccube.repository.oauth2.authorization_code'],
            ), $oauth2_config, $grantTypes, $responseTypes);
            return $server;
        };

        // Service

    }
}
