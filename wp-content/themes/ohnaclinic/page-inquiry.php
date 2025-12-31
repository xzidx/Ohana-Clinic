<?php
/*
Template Name: Inquiry Page
*/
get_header();
?>
<div class="more-space"></div>
 <div class="main">
        <h1>Get in touch with our lovely team</h1>
        <span>We're here to help.To get in touch with us,please fill out the information below. </span>
 </div>
       <div class="contact-form-section">
    <h2>Contact Us</h2>
    
    <form action="#">
        <div class="contact-grid">
            <div class="form-group">
                <span class="form-icon"><i class="fa-solid fa-user"></i></span>
                <div style="width: 100%;">
                    <label>Complete Name</label>
                    <input type="text" placeholder="Please provide your complete name">
                </div>
            </div>

            <div class="form-group">
                <span class="form-icon"><i class="fa-solid fa-message"></i></span>
                <div style="width: 100%;">
                    <label>Email</label>
                    <input type="email" placeholder="Please provide your email">
                </div>
            </div>

            <div class="form-group full-width">
                <span class="form-icon"><i class="fa-solid fa-file-pen"></i></span>
                <div style="width: 100%;">
                    <label>Message</label>
                    <textarea placeholder="Please provide your messages here.."></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="submit-btn">Send Messages</button>
    </form>
</div>
<?php get_footer(); ?>
