<?php

include '../../../wp-load.php';

$action = $_REQUEST['a'];
if (empty($action)) return;

if (!isset($newsletter)) $newsletter = new Newsletter();

if ($action == 'o') {
    $wpdb->insert($wpdb->prefix . 'newsletter_stats',
            array(
                'email_id' => $_GET['ne'],
                'newsletter_id' => $_GET['ni'],
                'url' => '',
                'anchor' => ''
            )
    );
    header('Content-Type: image/gif');
    readfile(dirname(__FILE__) . '/1x1.gif');
    die();
}

if ($action == 'r') {
    $url = explode(';', base64_decode($_GET['nr']), 4);
    $wpdb->insert($wpdb->prefix . 'newsletter_stats',
            array(
                'email_id' => $url[0],
                'newsletter_id' => $url[1],
                'url' => $url[3],
                'anchor' => $url[2]
            )
    );
    header('Location: ' . $url[3]);
    die();
}

$user = $newsletter->check_user();

if ($user == null) {
    echo 'Subscriber not found, sorry.';
    die();
}

$options = get_option('newsletter', array());
$options_main = get_option('newsletter_main', array());
$message = null;

if ($action == 'c') {
    setcookie('newsletter', $user->id . '-' . $user->token, time() + 60 * 60 * 24 * 365, '/');
    $wpdb->query("update " . $wpdb->prefix . "newsletter set status='C' where id=" . $user->id);

    $newsletter->mail($user->email, $newsletter->replace($options['confirmed_subject'], $user), $newsletter->replace($options['confirmed_message'], $user));
    $newsletter->notify_admin($user, 'Newsletter subscription');

    $url = $options_main['url'];
    if (empty($url)) $url = get_option('home');
    
    header('Location: ' . $newsletter->add_qs($url, 'na=c&ni=' . $user->id . '&nt=' . $user->token, false));
    die();
}

if ($action == 'uc') {
    $wpdb->query($wpdb->prepare("update " . $wpdb->prefix . "newsletter set status='U' where id=%d and token=%s", $user->id, $user->token));
    setcookie("newsletter", "", time() - 3600);
    $newsletter->mail($user->email, $newsletter->replace($options['unsubscribed_subject'], $user), $newsletter->replace($options['unsubscribed_message'], $user));
    $newsletter->notify_admin($user, 'Newsletter cancellation');

    $url = $options_main['url'];
    if (empty($url)) $url = get_option('home');
    
    header('Location: ' . $newsletter->add_qs($url, 'na=uc&ni=' . $user->id . '&nt=' . $user->token, false));
    die();
}
?>
Unknown action.