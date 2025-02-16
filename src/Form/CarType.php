<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\DataTransformer\PriceTransformer;

class CarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('price', MoneyType::class, [
            'currency' => 'EUR',
            'scale' => 2,
            'divisor' => 100,
        ]);

        $builder->get('price')->addModelTransformer(new PriceTransformer());
    }
}