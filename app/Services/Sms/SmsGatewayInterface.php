<?php

namespace App\Services\Sms;

interface SmsGatewayInterface
{
    /**
     * Send an SMS message.
     *
     * @return array{acknowledge: string, configured: bool, response: array}
     *                                                                       acknowledge: 'success' | 'error'
     *                                                                       configured: true if credentials are present (false means permanent config failure, not retryable)
     */
    public function sendSms(string $to, string $message): array;
}
