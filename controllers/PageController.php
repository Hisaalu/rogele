<?php
// File: /controllers/PageController.php

class PageController {
    
    /**
     * Display privacy policy page
     */
    public function privacyPolicy() {
        $hideFooter = false;
        $pageTitle = 'Privacy Policy | ROGELE';
        
        require_once __DIR__ . '/../views/privacy-policy.php';
    }
    
    /**
     * Display terms of service page
     */
    public function termsOfService() {
        $hideFooter = false;
        $pageTitle = 'Terms of Service | ROGELE';
        require_once __DIR__ . '/../views/terms-of-service.php';
    }
    
    /**
     * Display about page
     */
    public function about() {
        $hideFooter = false;
        $pageTitle = 'About Us | ROGELE';
        require_once __DIR__ . '/../views/about.php';
    }
    
    /**
     * Display contact page
     */
    public function contact() {
        $hideFooter = false;
        $pageTitle = 'Contact Us | ROGELE';
        require_once __DIR__ . '/../views/contact.php';
    }
}