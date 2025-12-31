<?php
/*
Template Name: Contact Page
*/
get_header();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - OHANA clinic</title>
  <link rel="stylesheet" href="contact.css">
 
  <div class="more-space"></div>
<body>

  <!-- Contact Header -->
  <section class="contact-header">
    <div class="text">
      <h2>THANK YOU FOR CONTACTING US!</h2>
      <h3>Get in Touch</h3>
      <p>
        OHANA clinic Services Co., Ltd. # No. 888, Russian Federation Blvd (110),<br>
        Sangkat Toeuk Thla, Khan Sen Sok, Phnom Penh, Cambodia.<br>
        <strong>Email:</strong> ohana@02hospital.com
      </p>
      <p><strong>Phone:</strong> +855 (0)23 991 000 / (0)12 991 000</p>
      <p><strong>Fax:</strong> +855 (0)23 986 992</p>
    </div>
    <img src="http://clinic_website.test/wp-content/uploads/2025/12/clinic_logo-Photoroom.png" alt="Contact Representative">
  </section>

  <!-- Contact Form -->
  <section class="contact-form-container">
    <h3>Thank you for contacting OHANA clinic.<br>
    Please use this form for inquiries about our services or for sharing your feedback.</h3>

    <form action="#" method="POST">
      <input type="text" name="fullname" placeholder="Full Name" required>

      <div class="form-group">
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="email" name="email" placeholder="Email Address" required>
      </div>

      <textarea name="message" placeholder="Message..." required></textarea>

      <button type="submit">SUBMIT →</button>
    </form>

    <p class="thank-note">
      Thanks for contacting OHANA clinic!<br>
      Our customer service will contact you back soon.
    </p>
  </section>

</body>
</html>


<?php get_footer(); ?>
