<?php

/*
 * This file is part of the EccubeApi
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\EccubeApi\Tests\Web;

use Eccube\Common\Constant;
use Eccube\Tests\Mock\CsrfTokenMock;
use Plugin\EccubeApi\Tests\AbstractEccubeApiTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;


class AbstractEccubeApiWebTestCase extends AbstractEccubeApiTestCase
{

    /**
     * User をログインさせてHttpKernel\Client を返す.
     *
     * @param UserInterface $User ログインさせる User
     * @return Symfony\Component\HttpKernel\Client
     */
    public function loginTo(UserInterface $User)
    {
        $firewall = 'admin';
        $role = array('ROLE_ADMIN');
        if ($User instanceof \Eccube\Entity\Customer) {
            $firewall = 'customer';
            $role = array('ROLE_USER');
        }
        $token = new UsernamePasswordToken($User, null, $firewall, $role);

        $this->app['session']->set('_security_' . $firewall, serialize($token));
        $this->app['session']->save();

        $cookie = new Cookie($this->app['session']->getName(), $this->app['session']->getId());
        $this->client->getCookieJar()->set($cookie);
        return $this->client;
    }

    /**
     * AccessToken を生成して返す.
     *
     * @param UserInterface $User ログインさせる User
     */
    protected function doAuthorized($UserInfo, $Client, $scope_granted = '')
    {
        foreach(explode(' ', $scope_granted) as $scope) {
            $this->addClientScope($Client, $scope);
        }
        $token = sha1(openssl_random_pseudo_bytes(100));

        $this->app['eccube.repository.oauth2.access_token']->setAccessToken($token, 'test-client-id', $UserInfo->getId(), time() + 3600, $scope_granted);

        $AccessToken = $this->app['eccube.repository.oauth2.access_token']->getAccessToken($token);
        return $AccessToken;
    }
}
