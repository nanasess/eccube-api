<?php

/*
 * This file is part of the EccubeApi
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\EccubeApi\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OAuth2AuthorizationType extends AbstractType
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO
        $builder
            ->add('client_id', HiddenType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('redirect_uri', HiddenType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('response_type', HiddenType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('state', HiddenType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('scope', HiddenType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('nonce', HiddenType::class, array(
                'constraints' => array(
                    // new Assert\NotBlank(),
                ),
            ))
            ->add('authorized', HiddenType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ));
    }

    public function getBlockPrefix()
    {
        return 'oauth_authorization';
    }
}
