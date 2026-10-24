<?php

namespace App\Services;

use App\Services\Sms\Drivers\AfroMessageDriver;

/** @deprecated Use SmsGatewayManager instead. Kept for backward compatibility. */
class AfroMessageService extends AfroMessageDriver {}
