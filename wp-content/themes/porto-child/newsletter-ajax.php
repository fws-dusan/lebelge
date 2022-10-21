<?php
require_once('Mailer.php');

function newsletter_emails()
{
    $mailer = new Mailer();
    $errors = [];

    if (empty($_POST['email'])) {
        $errors['email'] = 'Email is required.';

        wp_send_json_success([
            'success' => false,
            'message' => $errors['email'],
        ]);
    }

    if (get_page_by_title($_POST['email'], OBJECT, 'email_signup')) {
        wp_send_json_success([
            'success' => false,
            'message' => 'Email is already in use.',
        ]);
    } else {
        $id = wp_insert_post([
            'post_type' => 'email_signup',
            'post_status' => 'publish',
            'post_title' => $_POST['email'],
        ]);

        // send email to user
        $to = $_POST['email'];
        $subject = 'Welcome! Take 15% off Your First Order!!';
        $headerCaption = 'Welcome';
        $mailData = ['headerCaption' => 'Thanks for signing up!', 'form' => ''];
        $mailer->send($to, $subject, 'customer-welcome', ['Data' => $mailData]);

        wp_send_json_success([
            'success' => true,
            'message' => "Thank you for subscribing! Please check your email where you'll receive your one time discount code",
        ]);
    }
}

add_action('wp_ajax_newsletter_emails', 'newsletter_emails');
add_action('wp_ajax_nopriv_newsletter_emails', 'newsletter_emails');
