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
            $apiKey = (string) env('SMS_MRAM_API_KEY', '');
            $senderId = (string) env('SMS_MRAM_SENDERID', '');
            if ($apiKey === '' || $senderId === '') {
                return 'SMS API not configured';
            }

            return null;
        }

        if ($gateway === 'gennet') {
            $token = (string) env('SMS_GENNET_API_TOKEN', '');
            $sid = (string) env('SMS_GENNET_SID', '');
            if ($token === '' || $sid === '') {
                return 'SMS API not configured';
            }

            return null;
        }

        $token = (string) env('SMS_SSL_API_TOKEN', '');
        if ($token === '') {
            $token = (string) env('SMS_API_TOKEN', '');
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
            $apiKey = (string) env('SMS_MRAM_API_KEY', '');
            $senderId = (string) env('SMS_MRAM_SENDERID', '');

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
            $apiToken = (string) env('SMS_GENNET_API_TOKEN', '');
            $sid = (string) env('SMS_GENNET_SID', '');

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
            $token = (string) env('SMS_API_TOKEN', '');
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
}
