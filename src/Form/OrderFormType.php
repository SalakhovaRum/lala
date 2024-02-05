<?php

// src/Form/OrderFormType.php

namespace App\Form;

use App\Entity\ShopOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', HiddenType::class)
            ->add('sessionId', HiddenType::class)
            ->add(
                'userName',
                TextType::class,
                [
                    'label' => 'Имя',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Введите ваше имя',
                        ]),
                        new Length([
                            'min' => 2,
                            'minMessage' => 'Имя должно содержать минимум {{ limit }} символа',
                        ]),
                    ],
                ]
            )
            ->add(
                'userEmail',
                EmailType::class,
                [
                    'label' => 'Email',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Введите ваш email',
                        ]),
                        // здесь можете добавить другие ограничения для email, если необходимо
                    ],
                ]
            )
            ->add(
                'userPhone',
                TextType::class,
                [
                    'label' => 'Телефон',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Введите ваш телефон',
                        ]),
                        new Regex([
                            'pattern' => '/^8\d{10}$/',
                            'message' => 'Телефонный номер должен начинаться с 8 и содержать 11 цифр',
                        ]),
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'Сохранить',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ShopOrder::class,
            ]
        );
    }
}
