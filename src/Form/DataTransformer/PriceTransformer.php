<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


class PriceTransformer implements DataTransformerInterface
{
    public function transform($price): ?float
    {
        if($price === null){
            return null;
        }

        return $price / 100;
    }

    public function reverseTransform($price): ?int
    {
        if(!is_numeric($price)){
            throw new TransformationFailedException("Invalid price number");
        }
        return (int) round($price * 100);
    }

}