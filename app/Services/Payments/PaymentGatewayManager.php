<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Services\Payments\Drivers\ChapaDriver;
use App\Services\Payments\Drivers\TelebirrDriver;
use Illuminate\Support\Manager;

class PaymentGatewayManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return PaymentMethod::Chapa->value;
    }

    public function createChapaDriver(): PaymentGatewayInterface
    {
        return new ChapaDriver();
    }

    public function createTelebirrDriver(): PaymentGatewayInterface
    {
        return new TelebirrDriver();
    }
}
