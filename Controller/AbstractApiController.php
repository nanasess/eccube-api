<?php

/*
 * This file is part of the EccubeApi
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\EccubeApi\Controller;

use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Proxy\Proxy;
use Eccube\Application;
use Eccube\Entity\AbstractEntity;
use OAuth2\HttpFoundationBridge\Request as BridgeRequest;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * ApiController の抽象クラス.
 *
 * API の Controller クラスを作成する場合は、このクラスを継承します.
 *
 * @author Kentaro Ohkouchi
 * @author Kiyoshi Yamamura
 */
abstract class AbstractApiController
{
    private $errors = array();

    /**
     * API リクエストの妥当性を検証します.
     *
     * 認可リクエスト(AuthN)において、 $scope_required で指定した scope が認可されていない場合は false を返します.
     *
     * @param Application $app
     * @param string $scope_required API リクエストで必要とする scope
     * @return boolean 妥当と判定された場合 true
     */
    protected function verifyRequest(Application $app, Request $request, $scope_required = null)
    {
        return $app['oauth2.server.resource']->verifyResourceRequest(
            $this->createFromRequestWrapper($request),
            new BridgeResponse(),
            $scope_required
        );
    }

    /**
     * \OAuth2\HttpFoundationBridge\Response でラップしたレスポンスを返します.
     *
     * @param Application $app
     * @param mixed $data レスポンス結果のデータ
     * @param integer $statusCode 返却する HTTP Status コード
     * @return \OAuth2\HttpFoundationBridge\Response でラップしたレスポンス.
     */
    protected function getWrapperedResponseBy(Application $app, $data, $statusCode = 200)
    {
        $Response = $app['oauth2.server.resource']->getResponse();
        if (!is_object($Response)) {
            return $app->json($data, $statusCode);
        }
        $Response->setData($data);
        $Response->setStatusCode($statusCode);
        return $Response;
    }

    /**
     * エラー内容を追加します.
     *
     * $message が null の場合は、エラーコードに該当するエラーメッセージを返します.
     *
     * @param Application $app
     * @param string $code エラーコード
     * @param string $message エラーメッセージ
     * @return void
     */
    protected function addErrors(Application $app, $code, $message = null)
    {

        if (!$message) {
            $message = $app->trans($code);
            if ($message == $code) {
                // コードに該当するメッセージが取得できなかった場合、共通メッセージを表示
                $message =  $app->trans(100);
            }
        }

        $this->errors[] = array('code' => $code, 'message' => $message);
    }

    /**
     * エラーメッセージの配列を返します.
     *
     * @return array エラーメッセージの配列
     */
    protected function getErrors()
    {

        $errors = array();
        foreach ($this->errors as $error) {
            $errors[] = $error;
        }

        return array('errors' => $errors);
    }

    /**
     * エラーレスポンスを返します.
     *
     * @param Application $app
     * @param string $message エラーメッセージ
     * @param integer $statusCode 返却する HTTP Status コード
     * @return \OAuth2\HttpFoundationBridge\Response でラップしたレスポンス.

     */
    protected function getWrapperedErrorResponseBy(Application $app, $message = 'Not found', $statusCode = 404)
    {
        $this->addErrors($app, $statusCode, $message);
        return $this->getWrapperedResponseBy($app, $this->getErrors(), $statusCode);
    }

    /**
     * \OAuth2\HttpFoundationBridge\Request に Authorization ヘッダを付与します.
     *
     * Apache モジュール版の PHP で Authorization ヘッダが無視されてしまうのを回避するラッパーです.
     *
     * @see \OAuth2\HttpFoundationBridge\Request::createFromRequest()
     * @link https://github.com/EC-CUBE/eccube-api/issues/41
     * @link https://github.com/bshaffer/oauth2-server-php/issues/433
     */
    protected function createFromRequestWrapper(Request $request)
    {
        $BridgeRequest = BridgeRequest::createFromRequest($request);
        // XXX https://github.com/EC-CUBE/eccube-api/issues/41
        if (!$BridgeRequest->headers->has('Authorization') && function_exists('apache_request_headers')) {
            $all = apache_request_headers();
            if (array_key_exists('Authorization', $all) && isset($all['Authorization'])) {
                $BridgeRequest->headers->set('Authorization', $all['Authorization']);
            } elseif (array_key_exists('authorization', $all) && isset($all['authorization'])) {
                // ubuntu + Apache 2.4.x の環境で、キーが小文字になっている場合がある
                $BridgeRequest->headers->set('Authorization', $all['authorization']);
            }
        }
        return $BridgeRequest;
    }
}
