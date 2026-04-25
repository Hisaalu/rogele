<?php
// File: /views/contact/send-contact.php
$pageTitle = 'Contact Us | ROGELE';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Contact Section -->
<div class="contact-page">
    <section class="contact-section">
        <div class="container">
            <div class="section-header">
                <h1>Contact Us</h1>
                <p>
                    Have questions? We'd love to hear from you. 
                    Send us a message and we'll respond as soon as possible.
                </p>
            </div>

            <div class="contact-grid">
                <div class="contact-info">
                    <div class="info-card">
                        <i class="fas fa-envelope"></i>
                        <h3>Email</h3>
                        <p>info@raysofgrace.ac.ug</p>
                        <p>nelson.hisaalu@gmail.com</p>
                    </div>
                    
                    <div class="info-card">
                        <i class="fas fa-phone-alt"></i>
                        <h3>Phone</h3>
                        <p>+256 786 764 239</p>
                        <p>+256 751 719 313</p>
                    </div>
                    
                    <div class="info-card">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>Address</h3>
                        <p>Njeru, Uganda</p>
                    </div>
                    
                    <div class="info-card">
                        <i class="fas fa-clock"></i>
                        <h3>Working Hours</h3>
                        <p>Mon-Fri: 8:00 AM - 5:00 PM</p>
                        <p>Saturday: 9:00 AM - 1:00 PM</p>
                    </div>
                </div>
                
                <div class="contact-form-wrapper">
                    <div id="alertMessage" style="display: none;"></div>
                    
                    <form id="contactForm" class="contact-form" action="https://formspree.io/f/xnneyjlj" method="POST">
                        <input type="hidden" name="_subject" value="New Contact Message from ROGELE">
                        <input type="hidden" name="_replyto" id="_replyto">
                        <input type="hidden" name="_next" value="<?php echo BASE_URL; ?>/contact?success=true">
                        
                        <input type="text" name="_gotcha" style="display:none">
                        
                        <div class="form-group">
                            <input type="text" name="name" id="name" placeholder="Your Name" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" name="email" id="email" placeholder="Your Email" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="text" name="subject" id="subject" placeholder="Subject" required>
                        </div>
                        
                        <div class="form-group">
                            <textarea name="message" id="message" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn-send" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                        
                        <div id="formMessage" style="display: none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.contact-section {
    padding: 60px 20px;
    background: #F8FAFC;
    min-height: calc(100vh - 200px);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.section-header {
    text-align: center;
    margin-bottom: 50px;
}

.section-header h1 {
    font-size: 2.5rem;
    color: #7f2677;
    margin-bottom: 15px;
}

.section-header p {
    color: black;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

.contact-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 40px;
}

.contact-info {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.info-card {
    background: white;
    padding: 30px 20px;
    border-radius: 16px;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #E2E8F0;
}

.info-card:hover {
    transform: translateY(-5px);
    border-color: #f06724;
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.1);
}

.info-card i {
    font-size: 2rem;
    color: #8B5CF6;
    margin-bottom: 15px;
}

.info-card h3 {
    color: #7f2677;
    font-size: 1.1rem;
    margin-bottom: 10px;
}

.info-card p {
    color: black;
    font-size: 0.9rem;
    margin: 5px 0;
}

.contact-form-wrapper {
    background: white;
    padding: 40px;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border: 1px solid #E2E8F0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #f06724;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.btn-send {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #7f2677, #9b3a8f);
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-send:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(127, 38, 119, 0.3);
}

.btn-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

#alertMessage {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

#alertMessage.success {
    background: #F0FDF4;
    color: #166534;
    border: 1px solid #BBF7D0;
}

#alertMessage.error {
    background: #FEF2F2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}

#formMessage {
    margin-top: 15px;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
}

#formMessage.success {
    background: #F0FDF4;
    color: #166534;
    border: 1px solid #BBF7D0;
}

#formMessage.error {
    background: #FEF2F2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}

.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 992px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .contact-info {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .contact-section {
        padding: 40px 20px;
    }
    
    .section-header h1 {
        font-size: 2rem;
    }
    
    .contact-info {
        grid-template-columns: 1fr;
    }
    
    .contact-form-wrapper {
        padding: 25px;
    }
}

@media (max-width: 480px) {
    .section-header h1 {
        font-size: 1.8rem;
    }
    
    .info-card {
        padding: 20px;
    }
    
    .btn-send {
        font-size: 0.9rem;
    }
}
</style>

<script>
const FORMSPREE_ENDPOINT = 'https://formspree.io/f/xnneyjlj';

// Get DOM elements
const form = document.getElementById('contactForm');
const submitBtn = document.getElementById('submitBtn');
const messageDiv = document.getElementById('formMessage');
const alertMessage = document.getElementById('alertMessage');

// Check for URL parameters (for redirect success message)
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('success') === 'true') {
    showAlert('Thank you for your message! We\'ll get back to you soon.', 'success');
    window.history.replaceState({}, document.title, window.location.pathname);
}

// Function to show alert messages
function showAlert(message, type) {
    alertMessage.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> <span>${message}</span>`;
    alertMessage.className = type;
    alertMessage.style.display = 'flex';
    
    setTimeout(() => {
        alertMessage.style.display = 'none';
    }, 5000);
}

// Set the _replyto field to the user's email for easier reply
document.getElementById('email').addEventListener('change', function() {
    document.getElementById('_replyto').value = this.value;
});

// Handle form submission
form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    const originalText = submitBtn.innerHTML;
    
    // Disable button and show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    submitBtn.disabled = true;
    messageDiv.style.display = 'none';
    
    try {
        // Send to Formspree
        const response = await fetch(FORMSPREE_ENDPOINT, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> Thank you! Your message has been sent successfully.';
            messageDiv.className = 'success';
            messageDiv.style.display = 'block';
            
            form.reset();
            
            document.getElementById('_replyto').value = '';
            
            messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        } else {
            const data = await response.json();
            let errorMessage = 'Sorry, there was an error sending your message. Please try again.';
            
            if (data.errors) {
                errorMessage = data.errors.map(error => error.message).join(', ');
            }
            
            throw new Error(errorMessage);
        }
    } catch (error) {
        console.error('Form submission error:', error);
        
        messageDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${error.message || 'Network error. Please check your connection and try again.'}`;
        messageDiv.className = 'error';
        messageDiv.style.display = 'block';
        
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

function validateForm() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const subject = document.getElementById('subject').value.trim();
    const message = document.getElementById('message').value.trim();
    
    if (!name || !email || !subject || !message) {
        return false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        return false;
    }
    
    return true;
}

const inputs = ['name', 'email', 'subject', 'message'];
inputs.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
        field.addEventListener('input', function() {
            if (this.value.trim()) {
                this.style.borderColor = '#E2E8F0';
            }
        });
    }
});

// Prevent double submission
let isSubmitting = false;
form.addEventListener('submit', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        return false;
    }
    
    if (!validateForm()) {
        e.preventDefault();
        showAlert('Please fill in all fields correctly.', 'error');
        return false;
    }
    
    isSubmitting = true;
    
    // Reset after 3 seconds (in case of network issues)
    setTimeout(() => {
        isSubmitting = false;
    }, 3000);
});

console.log('Contact form initialized with Formspree integration');
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>