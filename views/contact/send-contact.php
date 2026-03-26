<?php
// File: /views/contact/send-contact.php
$pageTitle = 'Contact Us - Rays of Grace';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="section-header">
            <h1>Contact Us</h1>
            <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>

        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="contact-info">
                <div class="info-card">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p>info@raysofgrace.ac.ug</p>
                    <p>rogele@raysofgrace.ac.ug</p>
                </div>
                
                <div class="info-card">
                    <i class="fas fa-phone-alt"></i>
                    <h3>Phone</h3>
                    <p>+256 778 086 883</p>
                    <p>+256 707 610 551</p>
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
            
            <!-- Contact Form -->
            <div class="contact-form-wrapper">
                <?php if (isset($_SESSION['contact_success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $_SESSION['contact_success']; unset($_SESSION['contact_success']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['contact_error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $_SESSION['contact_error']; unset($_SESSION['contact_error']); ?></span>
                    </div>
                <?php endif; ?>
                
                <form id="contactForm" class="contact-form" method="POST" action="<?php echo BASE_URL; ?>/send-contact">
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

<style>
/* Contact Section */
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

/* Contact Grid */
.contact-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 40px;
}

/* Contact Info Cards */
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

/* Contact Form */
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
    background: linear-gradient(135deg, #7f2677);
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

.btn-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
}

.btn-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Alert Messages */
.alert {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #F0FDF4;
    color: #166534;
    border: 1px solid #BBF7D0;
}

.alert-error {
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
const form = document.getElementById('contactForm');
const submitBtn = document.getElementById('submitBtn');
const messageDiv = document.getElementById('formMessage');

form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    submitBtn.disabled = true;
    messageDiv.style.display = 'none';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/send-contact', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Get the response as text first
        const text = await response.text();
        console.log('Raw response:', text);
        
        // Try to parse as JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('JSON parse error:', e);
            result = { success: false, message: 'Server error: Invalid response format' };
        }
        
        if (result.success) {
            messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + result.message;
            messageDiv.className = 'success';
            messageDiv.style.display = 'block';
            form.reset();
        } else {
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + result.message;
            messageDiv.className = 'error';
            messageDiv.style.display = 'block';
        }
        
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    } catch (error) {
        console.error('Fetch error:', error);
        messageDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error: ' + error.message;
        messageDiv.className = 'error';
        messageDiv.style.display = 'block';
        
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>