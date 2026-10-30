<?php

namespace App\Support;

use RuntimeException;

/**
 * An order that can't be placed or changed as asked (out of stock, coupon
 * used up…). The message is safe to show the customer; $field is the form
 * field it belongs to.
 */
class OrderException extends RuntimeException
{
    public function __construct(string $message, public readonly string $field = 'order')
    {
        parent::__construct($message);
    }
}
