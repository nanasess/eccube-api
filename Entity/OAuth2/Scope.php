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
 * Scope
 *
 * @ORM\Table(name="plg_oauth2_scope")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\EccubeApi\Repository\OAuth2\ScopeRepository")
 */
class Scope extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=80, unique=true)
     */
    private $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=80, nullable=true)
     */
    private $label;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_default", type="boolean")
     */
    private $is_default;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_flg", type="smallint", options={"unsigned":true, "default":0})
     */
    private $customer_flg = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="member_flg", type="smallint", options={"unsigned":true, "default":1})
     */
    private $member_flg = '1';

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Plugin\EccubeApi\Entity\OAuth2\ClientScope", mappedBy="Scope")
     */
    private $ClientScope;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ClientScope = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set scope
     *
     * @param string $scope
     * @return Scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return Scope
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set is_default
     *
     * @param boolean $isDefault
     * @return Scope
     */
    public function setDefault($isDefault)
    {
        $this->is_default = $isDefault;

        return $this;
    }

    /**
     * Get is_default
     *
     * @return boolean
     */
    public function getDefault()
    {
        return $this->is_default;
    }

    public function isDefault()
    {
        return $this->is_default;
    }

    /**
     * Set customer_flg
     *
     * @param integer $customerFlg
     * @return Scope
     */
    public function setCustomerFlg($customerFlg)
    {
        $this->customer_flg = $customerFlg;

        return $this;
    }

    /**
     * Get customer_flg
     *
     * @return integer
     */
    public function getCustomerFlg()
    {
        return $this->customer_flg;
    }

    /**
     * Set member_flg
     *
     * @param integer $memberFlg
     * @return Scope
     */
    public function setMemberFlg($memberFlg)
    {
        $this->member_flg = $memberFlg;

        return $this;
    }

    /**
     * Get member_flg
     *
     * @return integer
     */
    public function getMemberFlg()
    {
        return $this->member_flg;
    }

    /**
     * Add ClientScope
     *
     * @param \Plugin\EccubeApi\Entity\OAuth2\ClientScope $clientScope
     * @return Scope
     */
    public function addClientScope(\Plugin\EccubeApi\Entity\OAuth2\ClientScope $clientScope)
    {
        $this->ClientScope[] = $clientScope;

        return $this;
    }

    /**
     * Remove ClientScope
     *
     * @param \Plugin\EccubeApi\Entity\OAuth2\ClientScope $clientScope
     */
    public function removeClientScope(\Plugin\EccubeApi\Entity\OAuth2\ClientScope $clientScope)
    {
        $this->ClientScope->removeElement($clientScope);
    }

    /**
     * Get ClientScope
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClientScope()
    {
        return $this->ClientScope;
    }
}
