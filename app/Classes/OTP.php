<?php

namespace App\Classes;

use App\Exceptions\ErrorException;
use Carbon\Carbon;

class OTP
{
    public function generateAndSend($user): bool
    {
        $code = rand(111111, 999999);

        $user->update([
            'otp' => $code,
            'otp_expired_at' => Carbon::parse(date('Y-m-d H:i:s'))->addMinutes(2),
        ]);

        return true;
    }

    public function verify($user, $otp): bool
    {
        $this->isExpired($user);

        if ($user->otp != $otp) {
            throw new ErrorException('OTP not matched.', 400);
        }

        $user->update(['otp' => null]);

        return true;
    }

    public function isExpired($user): bool
    {
        if (is_null($user->otp_expired_at) || $user->otp_expired_at < date('Y-m-d H:i:s')) {
            throw new ErrorException('OTP time is expired, please try again.', 400, ['retry' => true]);
        }

        return true;
    }
}
