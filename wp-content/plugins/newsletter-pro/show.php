<?php

include '../../../wp-load.php';

list($id, $token) = explode('-', $_REQUEST['uid'], 2);

$user = $wpdb->get_row($wpdb->prepare("select * from " . $wpdb->prefix . "newsletter where id=%d and token=%s", $id, $token));

if (!$user) {
    echo 'Subscriber not found, sorry.';
    //die();
}

setcookie("newsletter", "", time() - 3600);
$email = $wpdb->get_row($wpdb->prepare("select * from " . $wpdb->prefix . "newsletter_emails where id=%d", $_GET['eid']));

$message = $newsletter->replace($message, $user);

if (stripos($email->message, '<html') === false) {
    echo '<html><body bgcolor="#666666"><div style="background-color:#fff; padding: 20px; width: 600px; margin: auto;">';
    echo $email->message;
    echo '</div></body></html>';
}
else echo $email->message;
?>
