<?php
/**
 * Template Name: Contact Us
 * Template Post Type: page
 *
 * Assign this template to the "Contact Us" page in WordPress > Pages > Edit > Page Attributes.
 * Embed your HubSpot form shortcode in the page content, or hardcode the embed below.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>

<!-- Hero -->
<div class="contact-hero">
    <div class="container">
        <?php the_content(); ?>
    </div>
</div>

<!-- Info bar -->
<div class="contact-info-bar">
    <div class="container">
        <div class="contact-info-grid">
            <div class="contact-info-card">
                <div class="contact-info-icon">📞</div>
                <div>
                    <h4>Call Us</h4>
                    <a href="tel:<?php echo esc_html( get_theme_mod( 'mica_store_phone' ) ); ?>"><?php echo esc_html( get_theme_mod( 'mica_store_phone' ) ); ?></a>
                    <p><?php echo get_theme_mod( 'mica_store_hours' ); ?></p>
                </div>
            </div>
            <div class="contact-info-card">
                <div class="contact-info-icon">✉️</div>
                <div>
                    <h4>Email</h4>
                    <a href="mailto:<?php echo esc_html( get_theme_mod( 'mica_store_email' ) ); ?>"><?php echo esc_html( get_theme_mod( 'mica_store_email' ) ); ?></a>
                </div>
            </div>
            <div class="contact-info-card">
                <div class="contact-info-icon">🏪</div>
                <div>
                    <h4>Head Office</h4>
                    <p><?php echo esc_html( get_theme_mod( 'mica_address' ) ); ?></p>
                </div>
            </div>
            <!-- <div class="contact-info-card">
                <div class="contact-info-icon">⏰</div>
                <div>
                    <h4>Online Orders</h4>
                    <p>Order tracking & support<br>available 24/7 online</p>
                </div>
            </div> -->
        </div>
    </div>
</div>

<!-- Body: map + form -->
<div class="container">
    <div class="contact-body">

        <!-- Left: map + address -->
        <div>
            <div class="contact-map-wrap">
                <!--
                    Replace the src below with your actual Google Maps embed URL.
                    Go to Google Maps → Share → Embed a map → copy the iframe src.
                -->
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3580.8533171777294!2d28.155226075413996!3d-26.16890617709841!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1e95121ecd05d3a3%3A0xa051fcf51240b5b6!2sMica%20Hardware%20(Head%20Office)!5e0!3m2!1sen!2sza!4v1778071640451!5m2!1sen!2sza"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Mica Head Office location">
                </iframe>
            </div>

            <div class="contact-map-label">
                <span class="map-pin">📍</span>
                <div>
                    <h4>Mica Head Office</h4>
                    <p><?php echo esc_html( get_theme_mod( 'mica_address' ) ); ?></p>
                    <a class="map-link"
                       href="<?php echo esc_html( get_theme_mod( 'mica_google_map' ) ); ?>"
                       target="_blank" rel="noopener noreferrer">
                        Open in Google Maps →
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: HubSpot form card -->
        <div class="contact-form-card has-text-align-center">
            <h2 class="has-text-align-center">Send us a message</h2>
            <p class="form-subtitle">Fill in the form and we'll get back to you as soon as possible.</p>

            <div class="contact-form-btn-wrap">
                <button type="button" class="contact-form-btn" data-open-modal="contact-modal">
                    Send us a Message
                </button>
            </div>
            <div id="contact-modal" class="contact-modal-overlay" data-modal hidden>
                <div class="contact-modal-dialog">
                    <button class="contact-modal-close" data-close-modal aria-label="Close">&times;</button>
                    <iframe class="contact-modal-iframe"
                        src="https://micabrand.goreview.co.za/goreview/contact/#reviewform"
                        title="Contact Form"
                        loading="lazy">
                    </iframe>
                </div>
            </div>

            <h2 class="has-text-align-center">RETURNS ENQUIRIES​</h2>
            <p class="form-subtitle">For all return enquiries, please click on the button below and fill out the form and we'll get back to you as soon as possible..</p>

            <div class="return-form-btn-wrap">
                <button type="button" class="returns-form-btn" data-open-modal="returns-modal">
                    Returns Form
                </button>
            </div>

            <div id="returns-modal" class="returns-modal-overlay" data-modal hidden>
                <div class="returns-modal-dialog">
                    <h3 style="margin-bottom:var(--space-6);">Returns Form</h3>

                    <button class="returns-modal-close" data-close-modal aria-label="Close">&times;</button>

                    <div id="returns-form-container"></div>

                    <script src="//js-eu1.hsforms.net/forms/embed/v2.js"></script>
                    <script>
                    hbspt.forms.create({
                        portalId: "26047696",
                        formId: "7df69006-869c-4cee-a7ba-6bc90e75ac1b",
                        region: "eu1",
                        target: "#returns-form-container"
                    });
                    </script>
                </div>
            </div>

        </div>

    </div>

    <!-- FAQ Section -->
    <!-- <section class="contact-faq">
        <h2>Frequently Asked Questions</h2>
        <div class="faq-list">

            <?php
            $faqs = [
                [
                    'q' => 'How do I track my online order?',
                    'a' => 'Once your order is dispatched you will receive an email with a tracking number. You can also log into your account and visit the "Orders" section to see real-time status updates.',
                ],
                [
                    'q' => 'What is your return policy?',
                    'a' => 'We accept returns within 30 days of purchase on unopened, unused items in original packaging. Simply bring your proof of purchase to any Mica store or contact us to arrange a courier collection.',
                ],
                [
                    'q' => 'Can I collect my order in-store?',
                    'a' => 'Yes! Click &amp; Collect is available at all Mica stores nationwide. Select "Click &amp; Collect" at checkout, choose your preferred store, and we\'ll notify you when your order is ready — usually the same or next business day.',
                ],
                [
                    'q' => 'Do you offer trade accounts?',
                    'a' => 'Absolutely. We offer trade accounts for contractors, builders, and businesses. Fill in the "Trade Account" enquiry on this page or email trade@micashop.co.za and our team will get back to you within 24 hours.',
                ],
                [
                    'q' => 'How long does delivery take?',
                    'a' => 'Standard delivery is 5–7 business days across South Africa. Remote areas may take slightly longer. Express 2–3 day delivery is available at checkout for an additional fee.',
                ],
            ];

            foreach ( $faqs as $faq ) : ?>
            <details class="faq-item">
                <summary class="faq-question"><?php echo esc_html( $faq['q'] ); ?></summary>
                <div class="faq-answer"><?php echo wp_kses_post( $faq['a'] ); ?></div>
            </details>
            <?php endforeach; ?>

        </div>
    </section> -->

</div><!-- .container -->

<script>
(function () {
    function openModal(modal, triggerBtn) {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';

        const closeBtn = modal.querySelector('[data-close-modal]');
        if (closeBtn) closeBtn.focus();

        modal._triggerBtn = triggerBtn; // store reference for focus return
    }

    function closeModal(modal) {
        modal.hidden = true;
        document.body.style.overflow = '';

        if (modal._triggerBtn) {
            modal._triggerBtn.focus();
        }
    }

    // Open buttons
    document.querySelectorAll('[data-open-modal]').forEach(btn => {
        btn.addEventListener('click', function () {
            const modalId = btn.getAttribute('data-open-modal');
            const modal = document.getElementById(modalId);
            if (modal) openModal(modal, btn);
        });
    });

    // Close buttons
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', function () {
            const modal = btn.closest('[data-modal]');
            if (modal) closeModal(modal);
        });
    });

    // Click outside to close
    document.querySelectorAll('[data-modal]').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal(modal);
        });
    });

    // Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-modal]').forEach(modal => {
                if (!modal.hidden) closeModal(modal);
            });
        }
    });
})();

(function () {
    // Subject chip → populate a hidden HubSpot field named "subject"
    const chips = document.querySelectorAll('#subject-chips .subject-chip');
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');

            // Try to set the HubSpot field value once the form is ready
            var trySet = function () {
                var field = document.querySelector('#hs-form-target input[name="subject"], #hs-form-target select[name="subject"]');
                if (field) {
                    field.value = chip.dataset.subject;
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };
            trySet();
            // Retry after a short delay in case the HubSpot form hasn't initialised yet
            setTimeout(trySet, 800);
        });
    });
})();
</script>

<?php get_footer(); ?>
