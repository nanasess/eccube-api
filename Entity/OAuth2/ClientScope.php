<?php

/*
 * This file is part of the EccubeApi
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\EccubeApi\Entity\OAuth2;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClientScope
 *
 * @ORM\Table(name="plg_oauth2_client_scope")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\EccubeApi\Repository\OAuth2\ClientScopeRepository")
 */
class ClientScope extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="client_id", type="integer", nullable=false, options={"unsigned":false})
     * @ORM\Id
     */
    private $client_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="scope_id", type="integer", nullable=false, options={"unsigned":false})
     */
    private $scope_id;

    /**
     * @var \Plugin\EccubeApi\Entity\OAuth2\Client
     *
     * @ORM\ManyToOne(targetEntity="Plugin\EccubeApi\Entity\OAuth2\Client")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     * })
     */
    private $Client;

    /**
     * @var \Plugin\EccubeApi\Entity\OAuth2\Scope
     *
     * @ORM\ManyToOne(targetEntity="Plugin\EccubeApi\Entity\OAuth2\Scope")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scope_id", referencedColumnName="id")
     * })
     */
    private $Scope;


    /**
     * Set client_id
     *
     * @param integer $clientId
     * @return ClientScope
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get client_id
     *
     * @return integer
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set scope_id
     *
     * @param integer $scopeId
     * @return ClientScope
     */
    public function setScopeId($scopeId)
    {
        $this->scope_id = $scopeId;

        return $this;
    }

    /**
     * Get scope_id
     *
     * @return integer
     */
    public function getScopeId()
    {
        return $this->scope_id;
    }

    /**
     * Set Client
     *
     * @param \Plugin\EccubeApi\Entity\OAuth2\Client $client
     * @return ClientScope
     */
    public function setClient(\Plugin\EccubeApi\Entity\OAuth2\Client $client = null)
    {
        $this->Client = $client;

        return $this;
    }

    /**
     * Get Client
     *
     * @return \Plugin\EccubeApi\Entity\OAuth2\Client
     */
    public function getClient()
    {
        return $this->Client;
    }

    /**
     * Set Scope
     *
     * @param \Plugin\EccubeApi\Entity\OAuth2\Scope $scope
     * @return ClientScope
     */
    public function setScope(\Plugin\EccubeApi\Entity\OAuth2\Scope $scope = null)
    {
        $this->Scope = $scope;

        return $this;
    }

    /**
     * Get Scope
     *
     * @return \Plugin\EccubeApi\Entity\OAuth2\Scope
     */
    public function getScope()
    {
        return $this->Scope;
    }
}
