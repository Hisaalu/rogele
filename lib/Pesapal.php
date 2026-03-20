<?php
// File: /lib/Pesapal.php

class Pesapal {
    private $consumerKey;
    private $consumerSecret;
    private $environment;
    private $apiUrl;
    private $queryUrl;
    private $ipnRegisterUrl;
    
    public function __construct() {
        $this->consumerKey = PESAPAL_CONSUMER_KEY;
        $this->consumerSecret = PESAPAL_CONSUMER_SECRET;
        $this->environment = PESAPAL_ENVIRONMENT;
        $this->apiUrl = PESAPAL_API_URL;
        $this->queryUrl = PESAPAL_QUERY_URL;
        $this->ipnRegisterUrl = PESAPAL_IPN_REGISTER_URL;
    }
    
    /**
     * Get OAuth signature
     */
    private function getOAuthSignature($url, $params, $method = 'POST') {
        $consumer = new OAuthConsumer($this->consumerKey, $this->consumerSecret);
        $signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();
        
        $req = OAuthRequest::from_consumer_and_token($consumer, null, $method, $url, $params);
        $req->sign_request($signatureMethod, $consumer, null);
        
        return $req->to_header();
    }
    
    /**
     * Register IPN (Instant Payment Notification)
     */
    public function registerIPN() {
        $params = array(
            'url' => PESAPAL_IPN_URL,
            'ipn_notification_type' => 'GET'
        );
        
        $signature = $this->getOAuthSignature($this->ipnRegisterUrl, $params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ipnRegisterUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($signature, "Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
    
    /**
     * Submit payment request to Pesapal
     */
    public function submitPayment($paymentData) {
        // Clean phone number for display
        $phone = preg_replace('/\s+/', '', $paymentData['phone']);
        
        // Prepare payment parameters
        $params = array(
            'oauth_callback' => PESAPAL_CALLBACK_URL,
            'pesapal_merchant_reference' => $paymentData['reference'],
            'pesapal_transaction_type' => 'MERCHANT',
            'pesapal_total_amount' => $paymentData['amount'],
            'pesapal_currency' => PESAPAL_CURRENCY,
            'pesapal_description' => $paymentData['description'],
            'pesapal_first_name' => $paymentData['first_name'],
            'pesapal_last_name' => $paymentData['last_name'],
            'pesapal_email' => $paymentData['email'],
            'pesapal_phone_number' => $phone,
            'pesapal_shipping_phone' => $phone,
            'pesapal_shipping_address1' => 'Kampala, Uganda'
        );
        
        $signature = $this->getOAuthSignature($this->apiUrl, $params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($signature, "Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Parse response
        parse_str($response, $result);
        
        if (isset($result['pesapal_redirect_url'])) {
            return [
                'success' => true,
                'redirect_url' => $result['pesapal_redirect_url'],
                'tracking_id' => $result['pesapal_transaction_tracking_id'] ?? ''
            ];
        }
        
        error_log("Pesapal Error Response: " . $response);
        return ['error' => true, 'message' => 'Payment submission failed'];
    }
    
    /**
     * Query payment status
     */
    public function queryPaymentStatus($pesapalTrackingId) {
        $params = array(
            'pesapal_merchant_reference' => '', // Not required for status query
            'pesapal_transaction_tracking_id' => $pesapalTrackingId
        );
        
        $signature = $this->getOAuthSignature($this->queryUrl, $params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->queryUrl . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($signature, "Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        parse_str($response, $result);
        
        if (isset($result['pesapal_response_data'])) {
            $data = explode(',', $result['pesapal_response_data']);
            return [
                'success' => true,
                'status' => $data[0] ?? 'UNKNOWN',
                'payment_method' => $data[1] ?? '',
                'amount' => $data[2] ?? 0
            ];
        }
        
        return ['error' => true, 'message' => 'Query failed'];
    }
    
    /**
     * Verify payment using merchant reference
     */
    public function verifyPayment($merchantReference) {
        $params = array(
            'pesapal_merchant_reference' => $merchantReference,
            'pesapal_transaction_tracking_id' => ''
        );
        
        $signature = $this->getOAuthSignature($this->queryUrl, $params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->queryUrl . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($signature, "Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        parse_str($response, $result);
        
        if (isset($result['pesapal_response_data'])) {
            $data = explode(',', $result['pesapal_response_data']);
            return [
                'success' => true,
                'status' => $data[0] ?? 'UNKNOWN',
                'payment_method' => $data[1] ?? '',
                'amount' => $data[2] ?? 0,
                'tracking_id' => $data[3] ?? ''
            ];
        }
        
        return ['error' => true, 'message' => 'Verification failed'];
    }
}

// OAuth Classes (required for Pesapal)
class OAuthConsumer {
    public $key;
    public $secret;
    
    function __construct($key, $secret, $callback_url = NULL) {
        $this->key = $key;
        $this->secret = $secret;
        $this->callback_url = $callback_url;
    }
    
    function __toString() {
        return "OAuthConsumer[key=$this->key,secret=$this->secret]";
    }
}

class OAuthSignatureMethod_HMAC_SHA1 {
    function get_name() {
        return "HMAC-SHA1";
    }
    
    function build_signature($request, $consumer, $token) {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;
        
        $key_parts = array(
            $consumer->secret,
            ($token) ? $token->secret : ""
        );
        
        $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
        $key = implode('&', $key_parts);
        
        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }
}

class OAuthRequest {
    private $parameters;
    private $http_method;
    private $http_url;
    public $base_string;
    
    function __construct($http_method, $http_url, $parameters = null) {
        @$parameters or $parameters = array();
        $this->parameters = $parameters;
        $this->http_method = $http_method;
        $this->http_url = $http_url;
    }
    
    public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters = null) {
        $defaults = array(
            "oauth_version" => "1.0",
            "oauth_nonce" => self::generate_nonce(),
            "oauth_timestamp" => self::generate_timestamp(),
            "oauth_consumer_key" => $consumer->key
        );
        
        if ($token)
            $defaults['oauth_token'] = $token->key;
        
        $parameters = array_merge($defaults, (array)$parameters);
        return new OAuthRequest($http_method, $http_url, $parameters);
    }
    
    public function sign_request($signature_method, $consumer, $token) {
        $this->set_parameter("oauth_signature_method", $signature_method->get_name());
        $signature = $this->build_signature($signature_method, $consumer, $token);
        $this->set_parameter("oauth_signature", $signature);
    }
    
    public function build_signature($signature_method, $consumer, $token) {
        $signature = $signature_method->build_signature($this, $consumer, $token);
        return $signature;
    }
    
    public function set_parameter($name, $value) {
        $this->parameters[$name] = $value;
    }
    
    public function get_parameter($name) {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }
    
    public function get_signature_base_string() {
        $parts = array(
            $this->http_method,
            $this->http_url,
            $this->get_signable_parameters()
        );
        
        $parts = OAuthUtil::urlencode_rfc3986($parts);
        return implode('&', $parts);
    }
    
    public function get_signable_parameters() {
        $params = $this->parameters;
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }
        
        return OAuthUtil::build_http_query($params);
    }
    
    public function to_header() {
        $out = 'Authorization: OAuth ';
        $total = array();
        foreach ($this->parameters as $k => $v) {
            if (substr($k, 0, 5) != "oauth") continue;
            if (is_array($v)) {
                throw new Exception('Arrays not supported in headers');
            }
            $out .= OAuthUtil::urlencode_rfc3986($k) . '="' . OAuthUtil::urlencode_rfc3986($v) . '", ';
        }
        return substr_replace($out, '', -2);
    }
    
    private static function generate_timestamp() {
        return time();
    }
    
    private static function generate_nonce() {
        return md5(microtime() . mt_rand());
    }
}

class OAuthUtil {
    public static function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input);
        } else if (is_scalar($input)) {
            return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($input)));
        } else {
            return '';
        }
    }
    
    public static function build_http_query($params) {
        if (!$params) return '';
        
        $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
        $values = OAuthUtil::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);
        
        uksort($params, 'strcmp');
        
        $pairs = array();
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                natsort($value);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        
        return implode('&', $pairs);
    }
}
?>