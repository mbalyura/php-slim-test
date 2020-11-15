<?php

namespace App;

class OrderValidator implements ValidatorInterface
{
    public function validate(array $order)
    {
        $errors = [];
        $blankErrorMessage = "Can't be blank";
        if (empty($order['paid'])) {
            $errors['paid'] = $blankErrorMessage;
        }
        if (empty($order['title'])) {
            $errors['title'] = $blankErrorMessage;
        }
        return $errors;
    }
}
