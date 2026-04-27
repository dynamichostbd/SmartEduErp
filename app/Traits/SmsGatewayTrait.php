<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait SmsGatewayTrait
{
    protected function smsGatewayName(): string
    {
        $gateway = 'sslwireless';

        try {
            if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'sms_gateway')) {
                $v = DB::table('site_settings')->value('sms_gateway');
                $gateway = is_string($v) && trim($v) !== '' ? trim($v) : $gateway;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $gateway = strtolower((string) $gateway);
        if (!in_array($gateway, ['sslwireless', 'gennet', 'mram'], true)) {
            return 'sslwireless';
        }

        return $gateway;
    }

    protected function smsGatewayConfigError(): ?string
    {
        $gateway = $this->smsGatewayName();

        if ($gateway === 'mram') {
            $apiKey = (string) env('SMS_MRAM_API_KEY', 'R700003667bf4438073693.96702578');
            $senderId = (string) env('SMS_MRAM_SENDERID', '8809601014859');
            if ($apiKey === '' || $senderId === '') {
                return 'SMS API not configured';
            }

            return null;
        }

        if ($gateway === 'gennet') {
            $token = (string) env('SMS_GENNET_API_TOKEN', '$2y$12$rMJmXiIL.rCWo/iTfaQDy.shIUR/Fm/umZi7fptV42wL/fXXoT52O');
            $sid = (string) env('SMS_GENNET_SID', '8809612777555');
            if ($token === '' || $sid === '') {
                return 'SMS API not configured';
            }

            return null;
        }

        $token = (string) env('SMS_SSL_API_TOKEN', '');
        if ($token === '') {
            $token = (string) env('SMS_API_TOKEN', '9knet9jt-be80iqtz-t4d8gowh-kbk3il17-l0yx4lcf');
        }

        if ($token === '') {
            return 'SMS API not configured';
        }

        return null;
    }

    protected function sendSmsViaGateway(string $msisdn, string $message): bool
    {
        $gateway = $this->smsGatewayName();

        if ($gateway === 'mram') {
            $baseUrl = (string) env('SMS_MRAM_BASE_URL', 'https://sms.mram.com.bd/smsapi');
            $apiKey = (string) env('SMS_MRAM_API_KEY', 'R700003667bf4438073693.96702578');
            $senderId = (string) env('SMS_MRAM_SENDERID', '8809601014859');

            if ($apiKey === '' || $senderId === '') {
                return false;
            }

            $contacts = $this->normalizeContacts($msisdn, true);
            if (count($contacts) === 0) {
                return false;
            }

            $res = Http::post($baseUrl, [
                'api_key' => $apiKey,
                'type' => 'text',
                'contacts' => implode(',', $contacts),
                'senderid' => $senderId,
                'msg' => $message,
            ]);

            return $res->successful();
        }

        if ($gateway === 'gennet') {
            $baseUrl = (string) env('SMS_GENNET_BASE_URL', 'https://isms.gennet.com.bd/api/v3/send-sms');
            $apiToken = (string) env('SMS_GENNET_API_TOKEN', '$2y$12$rMJmXiIL.rCWo/iTfaQDy.shIUR/Fm/umZi7fptV42wL/fXXoT52O');
            $sid = (string) env('SMS_GENNET_SID', '8809612777555');

            if ($apiToken === '' || $sid === '') {
                return false;
            }

            $contacts = $this->normalizeContacts($msisdn, true);
            if (count($contacts) === 0) {
                return false;
            }

            $res = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($baseUrl, [
                'api_token' => $apiToken,
                'sid' => $sid,
                'msisdn' => implode(',', $contacts),
                'sms' => $message,
                'csms_id' => Str::random(8) . time(),
            ]);

            return $res->successful();
        }

        $baseUrl = (string) env('SMS_SSL_BASE_URL', '');
        if ($baseUrl === '') {
            $baseUrl = (string) env('SMS_BASE_URL', 'https://smsplus.sslwireless.com/api/v3/send-sms');
        }

        $token = (string) env('SMS_SSL_API_TOKEN', '');
        if ($token === '') {
            $token = (string) env('SMS_API_TOKEN', '9knet9jt-be80iqtz-t4d8gowh-kbk3il17-l0yx4lcf');
        }

        $sid = (string) env('SMS_SSL_SID', '');
        if ($sid === '') {
            $sid = (string) env('SMS_SID', 'DYNAMICNONMASK');
        }

        if ($token === '') {
            return false;
        }

        $contacts = $this->normalizeContacts($msisdn, false);
        if (count($contacts) === 0) {
            return false;
        }

        $res = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($baseUrl, [
            'api_token' => $token,
            'sid' => $sid,
            'msisdn' => implode(',', $contacts),
            'sms' => $message,
            'csms_id' => Str::random(8) . time(),
        ]);

        return $res->successful();
    }

    protected function normalizeContacts(string $msisdn, bool $prefix88): array
    {
        $parts = array_map('trim', preg_split('/[\s,]+/', $msisdn) ?: []);
        $parts = array_values(array_filter($parts, fn ($x) => $x !== ''));

        $out = [];
        foreach ($parts as $p) {
            $n = $p;
            $n = preg_replace('/^\+/', '', $n);
            if (str_starts_with($n, '88')) {
                $n = substr($n, 2);
            }
            if ($n === '') {
                continue;
            }

            $out[] = $prefix88 ? ('88' . $n) : $n;
        }

        return $out;
    }

    protected function smsTemplate(string $sms_type, array $params = [], $user = null): ?string
    {
        $otp = (string) ($params['otp'] ?? '');
        $password = (string) ($params['password'] ?? '');
        $invoice_id = (string) ($params['invoice_id'] ?? '');
        $date = !empty($params['date']) ? date('D, d F Y', strtotime((string) $params['date'])) : date('D, d F Y');

        $template = DB::table('sms_templates')
            ->where('sms_type', $sms_type)
            ->where('sending_status', 1)
            ->first();

        if (!$template || empty($template->sms_body)) {
            return null;
        }

        $sms_body = (string) $template->sms_body;

        if ($user) {
            $sms_body = str_replace('[_Student_Name_]', (string) ($user->name ?? ''), $sms_body);
            $sms_body = str_replace('[_Mobile_]', (string) ($user->mobile ?? ''), $sms_body);
            $sms_body = str_replace('[_Email_]', (string) ($user->email ?? ''), $sms_body);
            $sms_body = str_replace('[_College_Roll_]', (string) ($user->college_roll ?? ''), $sms_body);
        }

        $sms_body = str_replace('[_Password_]', $password, $sms_body);
        $sms_body = str_replace('[_Date_]', $date, $sms_body);
        $sms_body = str_replace('[_OTP_]', $otp, $sms_body);
        $sms_body = str_replace('[_Invoice_ID_]', $invoice_id, $sms_body);

        return $sms_body;
    }
}
