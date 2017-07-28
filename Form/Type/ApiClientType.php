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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ApiClientType extends AbstractType
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('app_name', TextType::class, array(
                'label' => 'アプリケーション名',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => 255)),
                ),
            ))
            ->add('redirect_uri', TextType::class, array(
                'label' => 'redirect_uri',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('max' => 2000)),
                ),
            ))
            ->add('client_identifier', TextType::class, array(
                'label' => 'Client ID',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => 80,
                    ))
                ),
            ))
            ->add('client_secret', TextType::class, array(
                'label' => 'Client secret',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => 80,
                    ))
                ),
            ))
            ->add('Scopes', EntityType::class, array(
                'label' => 'scope',
                'choice_label' => 'label',
                'choice_value' => 'scope',
                'choice_name' => 'scope',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'required' => false,
                'class' => 'Plugin\EccubeApi\Entity\OAuth2\Scope'
            ))
            ->add('public_key', TextareaType::class, array(
                'label' => 'id_token 公開鍵',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => 2000,
                    ))
                ),
            ))
            ->add('encryption_algorithm', TextType::class, array(
                'label' => 'id_token 暗号化アルゴリズム',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => 100,
                    ))
                ),
            ))

            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plugin\EccubeApi\Entity\OAuth2\Client',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'api_client';
    }
}
