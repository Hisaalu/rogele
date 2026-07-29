<?php
// File: /lib/MobileMoney.php

class MobileMoney {
    
    private function getSslOption() {
        return (MTN_MOMO_ENVIRONMENT === 'production' || AIRTEL_MONEY_ENVIRONMENT === 'production');
    }

    public function requestMtnPayment($phone, $amount, $reference) {
        $formattedPhone = $this->formatPhoneNumber($phone);
        $token = $this->getMtnToken();
        
        if (!$token) {
            return ['success' => false, 'message' => 'Failed to obtain MTN API token.'];
        }

        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $reference)) {
            $bytes = random_bytes(16);
            $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40); 
            $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80); 
            $reference = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
        }

        $baseUrl = (MTN_MOMO_ENVIRONMENT === 'production') 
            ? 'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay' 
            : 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay';

        $headers = [
            'Authorization: Bearer ' . $token,
            'X-Reference-Id: ' . $reference,
            'X-Target-Environment: ' . MTN_MOMO_TARGET_ENV,
            'Ocp-Apim-Subscription-Key: ' . MTN_MOMO_PRIMARY_KEY,
            'X-Callback-Url: ' . MOBILE_MONEY_IPN_URL,
            'Content-Type: application/json'
        ];

        $payload = [
            'amount' => (string)number_format((float)$amount, 0, '.', ''),
            'currency' => MTN_MOMO_CURRENCY,
            'externalId' => (string)$reference,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $formattedPhone
            ],
            'payerMessage' => 'Subscription Payment',
            'payeeNote' => 'ROGELE Subscription'
        ];

        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getSslOption());
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 202) {
            return ['success' => true, 'reference' => $reference, 'provider' => 'mtn'];
        }

        error_log("MTN RequestToPay Failed [HTTP {$httpCode}]: " . $response);
        return [
            'success' => false, 
            'message' => 'MTN Payment request failed with HTTP code ' . $httpCode
        ];
    }

    private function getMtnToken() {
        $baseUrl = (MTN_MOMO_ENVIRONMENT === 'production') 
            ? 'https://proxy.momoapi.mtn.com/collection/token/' 
            : 'https://sandbox.momodeveloper.mtn.com/collection/token/';

        $credentials = base64_encode(MTN_MOMO_API_USER . ':' . MTN_MOMO_API_KEY);
        
        $headers = [
            'Authorization: Basic ' . $credentials,
            'Ocp-Apim-Subscription-Key: ' . MTN_MOMO_PRIMARY_KEY,
            'Content-Type: application/json'
        ];

        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['grant_type' => 'client_credentials']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getSslOption());

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            error_log("MTN Token Request Failed. HTTP: {$httpCode} | Response: " . $response);
            return null;
        }

        $result = json_decode($response, true);
        return $result['access_token'] ?? null;
    }

    public function checkMtnStatus($reference) {
        $token = $this->getMtnToken();
        if (!$token) return ['success' => false, 'message' => 'Token failed'];

        $baseUrl = (MTN_MOMO_ENVIRONMENT === 'production') 
            ? 'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay/' . $reference
            : 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/' . $reference;

        $headers = [
            'Authorization: Bearer ' . $token,
            'X-Target-Environment: ' . MTN_MOMO_TARGET_ENV,
            'Ocp-Apim-Subscription-Key: ' . MTN_MOMO_PRIMARY_KEY
        ];

        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getSslOption());
        
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        return [
            'success' => true,
            'status'  => strtoupper($result['status'] ?? 'PENDING'),
            'raw'     => $result
        ];
    }

    public function requestAirtelPayment($phone, $amount, $reference) {
        $formattedPhone = $this->formatPhoneNumber($phone);
        $token = $this->getAirtelToken();

        if (!$token) {
            return ['success' => false, 'message' => 'Failed to obtain Airtel API token.'];
        }

        $baseUrl = (AIRTEL_MONEY_ENVIRONMENT === 'production') 
            ? 'https://openapi.airtel.africa/merchant/v1/payments/' 
            : 'https://openapiuat.airtel.africa/merchant/v1/payments/';

        $headers = [
            'Content-Type: application/json',
            'Accept: */*',
            'X-Country: ' . AIRTEL_MONEY_COUNTRY,
            'X-Currency: ' . AIRTEL_MONEY_CURRENCY,
            'Authorization: Bearer ' . $token
        ];

        $payload = [
            'reference' => 'ROGELE Sub',
            'subscriber' => [
                'country' => AIRTEL_MONEY_COUNTRY,
                'currency' => AIRTEL_MONEY_CURRENCY,
                'msisdn' => (int)$formattedPhone
            ],
            'transaction' => [
                'amount' => $amount,
                'country' => AIRTEL_MONEY_COUNTRY,
                'currency' => AIRTEL_MONEY_CURRENCY,
                'id' => $reference
            ]
        ];

        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getSslOption());

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        
        if (isset($result['status']['success']) && $result['status']['success'] === true) {
            return ['success' => true, 'reference' => $reference, 'provider' => 'airtel'];
        }

        return ['success' => false, 'message' => $result['status']['message'] ?? 'Airtel Payment request failed.'];
    }

    public function checkAirtelStatus($reference) {
        $token = $this->getAirtelToken();
        if (!$token) return ['success' => false, 'message' => 'Token failed'];

        $baseUrl = (AIRTEL_MONEY_ENVIRONMENT === 'production') 
            ? 'https://openapi.airtel.africa/standard/v1/payments/' . $reference
            : 'https://openapiuat.airtel.africa/standard/v1/payments/' . $reference;

        $headers = [
            'Accept: */*',
            'X-Country: ' . AIRTEL_MONEY_COUNTRY,
            'X-Currency: ' . AIRTEL_MONEY_CURRENCY,
            'Authorization: Bearer ' . $token
        ];

        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getSslOption());

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        $status = strtoupper($result['data']['transaction']['status'] ?? 'PENDING');

        return [
            'success' => true,
            'status'  => ($status === 'SUCCESS' || $status === 'TS') ? 'SUCCESSFUL' : $status,
            'raw'     => $result
        ];
    }

    private function getAirtelToken() {
        $baseUrl = (AIRTEL_MONEY_ENVIRONMENT === 'production') 
            ? 'https://openapi.airtel.africa/auth/oauth2/token' 
            : 'https://openapiuat.airtel.africa/auth/oauth2/token';

        $headers = [
            'Content-Type: application/json',
            'Accept: */*'
        ];

        $payload = [
            'client_id' => AIRTEL_MONEY_CLIENT_ID,
            'client_secret' => AIRTEL_MONEY_CLIENT_SECRET,
            'grant_type' => 'client_credentials'
        ];

        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getSslOption());

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        return $result['access_token'] ?? null;
    }

    public function formatPhoneNumber($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            return '256' . substr($phone, 1);
        }
        if (substr($phone, 0, 3) === '256') {
            return $phone;
        }
        return '256' . $phone;
    }
}