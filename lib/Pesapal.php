<?php
// File: /lib/Pesapal.php

class Pesapal {
    private $consumerKey;
    private $consumerSecret;
    private $environment;
    private $apiBaseUrl;
    
    public function __construct() {
        $this->consumerKey = PESAPAL_CONSUMER_KEY;
        $this->consumerSecret = PESAPAL_CONSUMER_SECRET;
        $this->environment = PESAPAL_ENVIRONMENT;
        
        if ($this->environment == 'production') {
            $this->apiBaseUrl = 'https://pay.pesapal.com/v3';
        } else {
            $this->apiBaseUrl = 'https://cybqa.pesapal.com/pesapalv3';
        }
    }
    
    /**
     * Get OAuth token for v3 API
     */
    public function getAccessToken() {
        try {
            $url = $this->apiBaseUrl . '/api/Auth/RequestToken';
            
            $postData = json_encode([
                'consumer_key' => $this->consumerKey,
                'consumer_secret' => $this->consumerSecret
            ], JSON_UNESCAPED_SLASHES);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return null;
            }
            
            if ($httpCode == 200) {
                $data = json_decode($response, true);
                if (isset($data['token'])) {
                    return $data['token'];
                }
                if (isset($data['access_token'])) {
                    return $data['access_token'];
                }
                error_log("Token not found in response: " . print_r($data, true));
            } else {
                error_log("Auth failed with HTTP code: " . $httpCode);
            }
            
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Submit payment request to Pesapal v3
     */
    public function submitPayment($paymentData) {
        try {
            // Get access token
            $token = $this->getAccessToken();
            $ipnId = $this->getIpnId($token);
            if (!$token) {
                return ['error' => true, 'message' => 'Failed to authenticate with PesaPal.'];
            }
            
            // Clean phone number
            $phone = preg_replace('/\s+/', '', $paymentData['phone']);
            if (substr($phone, 0, 1) !== '0' && strlen($phone) == 9) {
                $phone = '0' . $phone;
            }
            
            // Prepare payment data for v3 API
            $postData = [
                'amount' => (float)$paymentData['amount'],
                'currency' => PESAPAL_CURRENCY,
                'description' => $paymentData['description'],
                'id' => $paymentData['reference'],
                'callback_url' => PESAPAL_CALLBACK_URL,
                'notification_id' => $ipnId,
                'billing_address' => [
                    'email_address' => $paymentData['email'],
                    'phone_number' => $phone,
                    'first_name' => $paymentData['first_name'],
                    'last_name' => $paymentData['last_name'],
                    'country_code' => 'UG'
                ]
            ];
            
            $url = $this->apiBaseUrl . '/api/Transactions/SubmitOrderRequest';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return ['error' => true, 'message' => 'Connection error: ' . $curlError];
            }
            
            $result = json_decode($response, true);
            
            if (isset($result['redirect_url'])) {
                return [
                    'success' => true,
                    'redirect_url' => $result['redirect_url'],
                    'tracking_id' => $result['order_tracking_id'] ?? ''
                ];
            }
            
            if (isset($result['error'])) {
                $errorMsg = is_array($result['error']) ? ($result['error']['message'] ?? json_encode($result['error'])) : $result['error'];
                return ['error' => true, 'message' => 'PesaPal Error: ' . $errorMsg];
            }
            
            return ['error' => true, 'message' => 'Payment submission failed - Invalid response from server'];
            
        } catch (Exception $e) {
            return ['error' => true, 'message' => 'Payment processing error: ' . $e->getMessage()];
        }
    }

    /**
     * Helper Method to register IPN URL and get IPN ID 
     */
    public function getIpnId($token) {
        $url = $this->apiBaseUrl . '/api/URLSetup/RegisterIPN';
        $postData = json_encode([
            'url' => PESAPAL_IPN_URL,
            'ipn_notification_type' => 'GET'
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        return $result['ipn_id'] ?? null;
    }
    
    /**
     * Query payment status
     */
    public function queryPaymentStatus($orderTrackingId) {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                return ['error' => true, 'message' => 'Failed to authenticate'];
            }
            
            $url = $this->apiBaseUrl . '/api/Transactions/GetTransactionStatus?order_tracking_id=' . urlencode($orderTrackingId);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if (isset($result['payment_status_description'])) {
                return [
                    'success' => true,
                    'status' => $result['payment_status_description'],
                    'amount' => $result['amount'] ?? 0,
                    'payment_method' => $result['payment_method'] ?? ''
                ];
            }
            
            return ['error' => true, 'message' => 'Query failed'];
            
        } catch (Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }
}