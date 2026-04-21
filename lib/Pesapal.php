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
            $this->apiBaseUrl = 'https://pay.pesapal.com';
        } else {
            $this->apiBaseUrl = 'https://cybqa.pesapal.com';
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
            ]);
            
            error_log("[PesaPal Auth] URL: " . $url);
            error_log("[PesaPal Auth] Consumer Key: " . substr($this->consumerKey, 0, 10) . "...");
            
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
            
            error_log("[PesaPal Auth] HTTP Code: " . $httpCode);
            error_log("[PesaPal Auth] Response: " . $response);
            
            curl_close($ch);
            
            if ($curlError) {
                error_log("[PesaPal Auth] CURL Error: " . $curlError);
                return null;
            }
            
            $data = json_decode($response, true);
            
            if ($httpCode == 200) {
                if (isset($data['token'])) {
                    error_log("[PesaPal Auth] Token obtained successfully");
                    return $data['token'];
                }
                if (isset($data['access_token'])) {
                    error_log("[PesaPal Auth] Access token obtained successfully");
                    return $data['access_token'];
                }
            }
            
            error_log("[PesaPal Auth] Failed to get token. Response: " . print_r($data, true));
            return null;
            
        } catch (Exception $e) {
            error_log("[PesaPal Auth] Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Submit payment request to Pesapal v3
     */
    public function submitPayment($paymentData) {
        try {
            error_log("[PesaPal] Starting submitPayment");
            error_log("[PesaPal] Payment data: " . print_r($paymentData, true));
            
            // Get access token
            $token = $this->getAccessToken();
            if (!$token) {
                error_log("[PesaPal] Failed to get access token");
                return ['error' => true, 'message' => 'Failed to authenticate with PesaPal.'];
            }
            
            error_log("[PesaPal] Got access token: " . substr($token, 0, 20) . "...");
            
            // Get IPN ID
            $ipnId = $this->getIpnId($token);
            if (!$ipnId) {
                error_log("[PesaPal] Failed to get IPN ID, but continuing");
            } else {
                error_log("[PesaPal] Got IPN ID: " . $ipnId);
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
            
            error_log("[PesaPal] Submit order URL: " . $this->apiBaseUrl . '/api/Transactions/SubmitOrderRequest');
            error_log("[PesaPal] Post data: " . json_encode($postData));
            
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
            
            error_log("[PesaPal] Response HTTP Code: " . $httpCode);
            error_log("[PesaPal] Response: " . $response);
            
            curl_close($ch);
            
            if ($curlError) {
                error_log("[PesaPal] CURL Error: " . $curlError);
                return ['error' => true, 'message' => 'Connection error: ' . $curlError];
            }
            
            $result = json_decode($response, true);
            error_log("[PesaPal] Decoded response: " . print_r($result, true));
            
            if (isset($result['redirect_url'])) {
                error_log("[PesaPal] Success! Redirect URL: " . $result['redirect_url']);
                return [
                    'success' => true,
                    'redirect_url' => $result['redirect_url'],
                    'tracking_id' => $result['order_tracking_id'] ?? ''
                ];
            }
            
            if (isset($result['error'])) {
                $errorMsg = is_array($result['error']) ? ($result['error']['message'] ?? json_encode($result['error'])) : $result['error'];
                error_log("[PesaPal] Error in response: " . $errorMsg);
                return ['error' => true, 'message' => 'PesaPal Error: ' . $errorMsg];
            }
            
            error_log("[PesaPal] Unexpected response format");
            return ['error' => true, 'message' => 'Payment submission failed - Invalid response from server'];
            
        } catch (Exception $e) {
            error_log("[PesaPal] Exception: " . $e->getMessage());
            return ['error' => true, 'message' => 'Payment processing error: ' . $e->getMessage()];
        }
    }

    /**
     * Helper Method to register IPN URL and get IPN ID
     */
    public function getIpnId($token) {
        try {
            $url = $this->apiBaseUrl . '/api/URLSetup/RegisterIPN';
            $postData = json_encode([
                'url' => PESAPAL_IPN_URL,
                'ipn_notification_type' => 'GET'
            ]);
            
            error_log("[PesaPal] Registering IPN URL: " . PESAPAL_IPN_URL);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            error_log("[PesaPal] IPN Registration Response Code: " . $httpCode);
            error_log("[PesaPal] IPN Registration Response: " . $response);
            
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if (isset($result['ipn_id'])) {
                error_log("[PesaPal] IPN Registered Successfully. ID: " . $result['ipn_id']);
                return $result['ipn_id'];
            } else {
                error_log("[PesaPal] IPN Registration Failed. Response: " . print_r($result, true));
                return null;
            }
            
        } catch (Exception $e) {
            error_log("[PesaPal] IPN Registration Exception: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Query payment status
     */
    public function queryPaymentStatus($orderTrackingId) {
        try {
            error_log("[PesaPal Query] Starting for tracking ID: " . $orderTrackingId);
            
            // Get access token
            $token = $this->getAccessToken();
            if (!$token) {
                error_log("[PesaPal Query] Failed to get access token");
                return ['success' => false, 'message' => 'Failed to authenticate', 'status' => 'ERROR'];
            }
            
            // FIX: Add /v3/ to the URL
            $url = $this->apiBaseUrl . '/v3/api/Transactions/GetTransactionStatus?order_tracking_id=' . urlencode($orderTrackingId);
            error_log("[PesaPal Query] URL: " . $url);
            
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
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            error_log("[PesaPal Query] HTTP Code: " . $httpCode);
            error_log("[PesaPal Query] Response: " . $response);
            
            if ($curlError) {
                return ['success' => false, 'message' => 'CURL error: ' . $curlError, 'status' => 'ERROR'];
            }
            
            $result = json_decode($response, true);
            
            // Check for successful response
            if ($result && isset($result['payment_status_description'])) {
                return [
                    'success' => true,
                    'status' => $result['payment_status_description'],
                    'amount' => $result['amount'] ?? 0,
                    'payment_method' => $result['payment_method'] ?? '',
                    'raw' => $result
                ];
            }
            
            // Check for error
            if ($result && isset($result['error'])) {
                $errorMsg = is_array($result['error']) ? ($result['error']['message'] ?? json_encode($result['error'])) : $result['error'];
                return ['success' => false, 'message' => $errorMsg, 'status' => 'ERROR'];
            }
            
            return ['success' => false, 'message' => 'Query failed - invalid response', 'status' => 'UNKNOWN'];
            
        } catch (Exception $e) {
            error_log("[PesaPal Query] Exception: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'status' => 'ERROR'];
        }
    }
}