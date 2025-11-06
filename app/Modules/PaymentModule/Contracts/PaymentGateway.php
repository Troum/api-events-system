<?php

namespace App\Modules\PaymentModule\Contracts;

use Illuminate\Http\Request;

interface PaymentGateway
{
    public function createPayment(float $amount, string $description, array $metadata = []): PaymentResponse;

    public function handleCallback(Request $request): PaymentStatus;
}

