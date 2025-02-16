<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


class PriceTransformer implements DataTransformerInterface
{
    public function transform($price): ?float
    {
        if($value === null){
            return null;
        }

        return $value / 100;
    }

    public function reverseTransform($price): ?int
    {
        if(!is_numeric($value)){
            throw new TransformationFailedException("Invalid price number");
        }
        return (int) round($value * 100);
    }

}