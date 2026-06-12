<?php
// File: /controllers/PageController.php

class PageController {
    public function privacyPolicy() {
        $hideFooter = false;
        $pageTitle = 'Privacy Policy | ROGELE';
        
        require_once __DIR__ . '/../views/privacy-policy.php';
    }
    
    public function termsOfService() {
        $hideFooter = false;
        $pageTitle = 'Terms of Service | ROGELE';
        require_once __DIR__ . '/../views/terms-of-service.php';
    }
    
    public function about() {
        $hideFooter = false;
        $pageTitle = 'About Us | ROGELE';
        require_once __DIR__ . '/../views/about.php';
    }
    
    public function contact() {
        $hideFooter = false;
        $pageTitle = 'Contact Us | ROGELE';
        require_once __DIR__ . '/../views/contact.php';
    }
}