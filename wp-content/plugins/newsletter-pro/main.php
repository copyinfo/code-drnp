<?php

@include_once NEWSLETTER_DIR . '/module.inc.php';

$controls = new NewsletterModuleControls();

if (!$controls->is_action()) {
    $controls->data = get_option('newsletter_main');
}
else {
    if ($controls->is_action('remove')) {

        $wpdb->query("delete from " . $wpdb->prefix . "options where option_name like 'newsletter%'");

        $wpdb->query("drop table " . $wpdb->prefix . "newsletter, " . $wpdb->prefix . "newsletter_stats, " .
                $wpdb->prefix . "newsletter_emails, " . $wpdb->prefix . "newsletter_profiles, " .
                $wpdb->prefix . "newsletter_work");

        echo 'Newsletter plugin destroyed. Please, deactivate it now.';
        return;
    }

    if ($controls->is_action('trigger')) {
        $newsletter->hook_newsletter();
        $controls->messages = 'Delivery engine triggered.';
    }

    if ($controls->is_action('engine_on')) {
        wp_clear_scheduled_hook('newsletter');
        wp_schedule_event(time() + 30, 'newsletter', 'newsletter');
        $controls->messages = 'Delivery engine reactivated.';
    }

    if ($controls->is_action('save')) {
        $errors = null;

        // Validation
        $controls->data['sender_email'] = $newsletter->normalize_email($controls->data['sender_email']);
        if (!$newsletter->is_email($controls->data['sender_email'])) {
            $errors = __('Sender email is not correct');
        }

        $controls->data['return_path'] = $newsletter->normalize_email($controls->data['return_path']);
        if (!$newsletter->is_email($controls->data['return_path'], true)) {
            $errors = __('Return path email is not correct');
        }
        // With some providers the return path must be left empty
        //if (empty($options['return_path'])) $options['return_path'] = $options['sender_email'];

        $controls->data['test_email'] = $newsletter->normalize_email($controls->data['test_email']);
        if (!$newsletter->is_email($controls->data['test_email'], true)) {
            $errors = __('Test email is not correct');
        }

        $controls->data['reply_to'] = $newsletter->normalize_email($controls->data['reply_to']);
        if (!$newsletter->is_email($controls->data['reply_to'], true)) {
            $errors = __('Reply to email is not correct');
        }

        $controls->data['mode'] = (int)$controls->data['mode'];
        $controls->data['logs'] = (int)$controls->data['logs'];

        if ($errors == null) {
            update_option('newsletter_main', $controls->data);
        }
    }

    if ($action == 'test') {
        for ($i=0; $i<5; $i++) {
            if (!empty($controls->data['test_email_' . $i])) {
                $r = $newsletter->mail($controls->data['test_email_' . $i],
                        'Test email from Newsletter Plugin', '<p>This is a test message from Newsletter Plugin. You are reading it, so the plugin is working.</p>',
                        true, null, 1);
            }
        }
        $controls->messages = 'Test emails sent. Check the test mailboxes.';
    }
    
if ($controls->is_action('send_test')) {
    update_option('newsletter_main', $controls->data);

    $text = 'This is a simple test email sent directly with the WordPress mailing functionality in the same way WordPress sends notifications of new comment or registered users.';
    $r = wp_mail($controls->data['test_email'], 'Newsletter simple test email subject', $text);
    if ($r)
        $controls->messages = 'WordPress test email sent<br />';
    else
        $controls->errors = 'WordPress test email NOT sent: ask your provider.<br />';

    $text = 'This is a TEXT email sent using the sender data set on Newsletter main setting.</p>';
    $r = $newsletter->mail($controls->data['test_email'], 'Newsletter TEXT test email', $text, false);
    if ($r)
        $controls->messages .= 'Newsletter TEXT test email sent.<br />';
    else
        $controls->errors .= 'Newsletter TEXT test email NOT sent: try to change the sender data, remove the return path and the reply to settings.<br />';

    $text = 'This is an HTML email sent using the sender data set on Newsletter main setting.</p>';
    $r = $newsletter->mail($controls->data['test_email'], 'Newsletter HTML test email', $text, true);
    if ($r)
        $controls->messages .= 'Newsletter HTML test email sent.<br />';
    else
        $controls->errors .= 'Newsletter HTML test email NOT sent: try to change the sender data, remove the return path and the reply to settings.<br />';

    
    $text = array();
    $text['html'] = '<p>This is an <b>HTML</b> test email part sent using the sender data set on Newsletter main setting.</p>';
    $text['text'] = 'This is a textual test email part sent using the sender data set on Newsletter main setting.';
    $r = $newsletter->mail_multipart($controls->data['test_email'], 'Newsletter both TEXT and HTML test email', $text);
    if ($r)
        $controls->messages .= 'Newsletter both TEXT and HTML test email sent.<br />';
    else
        $controls->errors .= 'Newsletter both TEXT and HTML test email NOT sent: try to change the sender data, remove the return path and the reply to settings.<br />';
}

if ($controls->is_action('smtp_test')) {

        $options = stripslashes_deep($_POST['options']);
        require_once ABSPATH . WPINC . '/class-phpmailer.php';
        require_once ABSPATH . WPINC . '/class-smtp.php';
        $mail = new PHPMailer();

        $mail->IsSMTP();
        $mail->SMTPDebug = true;
        $mail->CharSet = 'UTF-8';
        $message = 'This Email is sent by PHPMailer of WordPress';
        $mail->IsHTML(false);
        $mail->Body = $message;
        $mail->From = $options['sender_email'];
        $mail->FromName = $options['sender_name'];
        if (!empty($options['return_path'])) $mail->Sender = $options['return_path'];
        if (!empty($options['reply_to'])) $mail->AddReplyTo($options['reply_to']);

        $mail->Subject = '[' . get_option('blogname') . '] SMTP test';

        $mail->Host = $options['smtp_host'];
        if (!empty($options['smtp_port'])) $mail->Port = (int)$options['smtp_port'];

        if (!empty($options['smtp_user'])) {
            $mail->SMTPAuth = true;
            $mail->Username = $options['smtp_user'];
            $mail->Password = $options['smtp_pass'];
        }

        $mail->SMTPKeepAlive = true;
        $mail->ClearAddresses();
        $mail->AddAddress($options['test_email_0']);
        ob_start();
        $mail->Send();
        $mail->SmtpClose();
        $debug = htmlspecialchars(ob_get_clean());
        ob_end_clean();

        if ($mail->IsError())
            $controls->errors = $mail->ErrorInfo;
        else
            $controls->messages = 'Success.';

        $controls->messages .= '<textarea style="width:100%;height:250px;font-size:10px">';
        $controls->messages .= $debug;
        $controls->messages .= '</textarea>';

    }
    
}

?>

<div class="wrap">

    <h2>Newsletter Main Configuration</h2>


    <p><a href="javascript:void(jQuery('.hints').toggle())">Show/hide detailed documentation</a></p>
    
    <form method="post" action="">
        <?php $controls->init(); ?>
        <?php $controls->show(); ?>

        <div id="tabs">

        <ul>
            <li><a href="#tabs-1">Main settings and test</a></li>
            <li><a href="#tabs-2">Advanced settings</a></li>
            <li><a href="#tabs-5">SMTP</a></li>
            <li><a href="#tabs-3">Content locking</a></li>
            <li><a href="#tabs-4">System check</a></li>
        </ul>
                        
        <div id="tabs-1">
          
        <!-- Main settings -->

            <p class="intro">
            Configurations on this sub panel can block emails sent by Newsletter Pro. It's not a plugin limit but odd restrictions imposed by
            hosting providers. It's advisable to careful read the detailed documentation you'll found under every options, specially on the "return path"
            field. Try different combination of setting below before send a support request and do it in this way: one single change - test - other single
            change - test, and so on. Thank you for your collaboration.
            </p>

        <table class="form-table">
                    <tr>
                        <th>Email test</th>
                        <td>
                            <?php $controls->text('test_email'); ?>
                            <?php $controls->button('send_test', 'Send test emails to this address'); ?>
                          <div class="hints">
                                Some test emails will be sent to the specified address:<br />
                                1. One with the native mail functionality of WordPress as is, so the email should come fro wordpress@yourdomain.tld<br />
                                2. One with sender data/reply to/return path as configured on Newsletter main settings in TEXT format (some time those values can break the mail system)<br />
                                3. One with sender data/reply to/return path as configured on Newsletter main settings in HTML format (some time those values can break the mail system)<br />
                                4. One in multipart format (with html and text parts) managed directly by Newsletter
                            </div>
                        </td>
                    </tr>
          
            <tr valign="top">
                <th>Sender name and address</th>
                <td>
                    email address (required): <?php $controls->text('sender_email', 40); ?>
                    name (optional): <?php $controls->text('sender_name', 40); ?>

                    <div class="hints">
                        These are the name and email address a subscriber will see on emails he'll receive.
                        Be aware that hosting providers can block email with a sender address not of the same domain of the blog.<br />
                        For example, if your blog is www.myblog.com, using as sender email "info@myblog.com" or
                        "newsletter@myblog.com" is safer than using "myaccount@gmail.com". The name is optional but is more professional
                        to set it (even if some providers with bugged mail server do not send email with a sender name set as reported by
                        a customer).
                    </div>
                </td>
            </tr>
            <tr>
                <th>Generic test subscribers</th>
                <td>
                    <?php for ($i=0; $i<5; $i++) { ?>
                    email: <?php $controls->text('test_email_' . $i, 30); ?> name: <?php $controls->text('test_name_' . $i, 30); ?>
                    sex: <?php $controls->select('test_sex_' . $i, array('n'=>'None', 'f'=>'Female', 'm'=>'Male')); ?><br />
                    <?php } ?>
                    <div class="hints">
                        These names and addresses are used by test functionalities on configuration panel. Be sure to fill at least the first
                        test subscriber.<br />
                        <strong>Do not use as email address the same address set as "sender"</strong> (see above), usually it does not work.<br />
                        <strong>You should make a test every time you change one of the settings above</strong>.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Max emails per hour</th>
                <td>
                    <?php $controls->text('scheduler_max', 5); ?>
                    <div class="hints">
                        The internal engine of Newsletter Pro sends email with the specified rate to stay under
                        provider limits. The default value is 100 a very low value. The right value for you
                        depends on your provider or server capacity.<br />
                        Some examples. Hostgator: 500. Dreamhost: 100, asking can be raised to 200. Go Daddy: 1000 per day using their SMTP,
                        unknown per hour rate. Gmail: 500 per day using their SMTP, unknown per hour rate.<br />
                        My sites are on Hostgator or Linode VPS.<br />
                        If you have a service with no limits on the number of emails, still PHP have memory and time limits. Newsletter Pro
                        does it's best to detect those limits and to respect them so it can send out less emails per hour than excepted.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Newsletter user interaction page</th>
                <td>
                    <?php $controls->page_themes('theme'); ?><br />
                    or specify a blog page address:<br />
                    WordPress page URL: <?php $controls->text('url', 70); ?> (eg. <?php echo get_option('home') . '/newsletter'; ?>, optional)

                    <div class="hints">
                        Newsletter Pro needs to interact with subscribers: subscription form, welcome messages, cancellation messages,
                        profile editing form. If you want all those interactions within you blog theme, create a WordPress page and put
                        in its body <strong>only</strong> the short code [newsletter] (as is). Then open that page in your browser and copy the
                        page address (URL) in this field.<br />
                        If you prefer to keep all those interaction out of your blog in a specific designed web page, use the text area
                        to create a full valid HTML page. That page must contain the tag {message} used by Newspetter Pro to insert its
                        messages. A basic template is already there for your convenience.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Return path</th>
                <td>
                    <?php $controls->text('return_path', 40); ?> (valid email address)
                    <div class="hints">
                        This is the email address where delivery error messages are sent. Error message are sent back from mail systems when
                        an email cannot be delivered to the receiver (full mailbox, unrecognized user and invalid address are the most common
                        errors).<br />
                        <strong>Some providers do not accept this field and block emails is present or if the email address has a
                        different domain of the blog</strong> (see above the sender field notes). If you experience problem sending emails
                        (just do some tests), try to leave it blank.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Reply to</th>
                <td>
                    <?php $controls->text('reply_to', 40); ?> (valid email address)
                    <div class="hints">
                        This is the email address where subscribers will reply (eg. if they want to reply to a newsletter). Leave it blank if
                        you don't want to specify a different address from the sender email above. As for return path, come provider do not like this
                        setting active.
                    </div>
                </td>
            </tr>
 
        </table>
        <p class="submit">
            <?php $controls->button('test', 'Send a test email'); ?>
        </p>
        </div>

          
          
        <div id="tabs-2">

        <!-- General parameters -->

        <table class="form-table">
            <tr valign="top">
                <th>WordPress user integration</th>
                <td>
                    <?php $controls->yesno('wp_integration'); ?>
                    <div class="hints">
                    Experimental!
                    </div>
                </td>
            </tr>
            <!--
            <tr valign="top">
                <th><?php _e('Popup form number', 'newsletter'); ?></th>
                <td>
                    <?php $controls->text('popup_form', 40); ?>
                    <br />
                    <?php _e('
                    Form to be used for integration with wp-super-popup. Leave it empty to use the default form'); ?>
                </td>
            </tr>
            -->
            <tr valign="top">
                <th>Force receiver</th>
                <td>
                    <?php $controls->text('receiver', 40); ?> (valid email address)
                    <div class="hints">
                        If set, EVERY email sent by newsletter will be sent to this address. Set this only if you need to test
                        the plugin but already have a list of regular subscribers and you want to see what happens sending real
                        newsletters.<br />
                        If set, the subscription process works but new subscribers won't receive confirmation or welcome email!
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Notifications</th>
                <td>
                    <?php $controls->yesno('notify'); ?>
                    <div class="hints">
                    Enable or disable notifications of subscription, unsubscription and other events to blog administrator.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Enable access to editors?</th>
                <td>
                    <?php $controls->yesno('editor'); ?>
                </td>
            </tr>
            <tr valign="top">
                <th>Logging</th>
                <td>
                    <?php $controls->select('logs', array(0=>'None', 1=>'Only errors', 2=>'Normal', 3=>'Debug')); ?>
                    <div class="hints">
                        Be aware of two things: debug level may expose in your log file subscribers data and the file can
                        grow very quickly (tens of megabytes).
                    </div>
                </td>
            </tr>

            <tr valign="top">
                <th>API key</th>
                <td>
                    <?php $controls->text('api_key', 40); ?>
                    <div class="hints">
                        When non-empty can be used to directly call the API for external integration. See API documentation on
                        documentation panel.
                    </div>
                </td>
            </tr>

            <tr>
                <th>Styling</th>
                <td>
                    <?php $controls->textarea('css'); ?>
                    <div class="hints">
                        Add here your own css to style the forms. The whole form is enclosed in a div with class
                        "newsletter" and it's made with a table (guys, I know about your table less design
                        mission, don't blame me too much!)
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Email body content encoding</th>
                <td>
                    <?php $controls->select('content_transfer_encoding', array(''=>'Default', '8bit'=>'8 bit', 'base64'=>'Base 64')); ?>
                    <div class="hints">
                        Used only by some modules. Choose base64 to have chunked email body when server reports too long email line.
                    </div>                  
                </td>
            </tr>            
            
        </table>
        
        </div>

          
                  <div id="tabs-5">
        <p class="intro">
            To use an external SMTP (mail sending service), fill in the SMTP data and activate it. SMTP will be used for any
            messages sent by Newsletter (subscription messages and newsletters). SMTP is required to send email with Gmail or
            GoDaddy hosting account.
            Read more <a href="http://www.satollo.net/godaddy-using-smtp-external-server-on-shared-hosting" target="_blank">here</a>.
                    Test button below sends an email to the first test address configured above and works even if SMTP is not enabled. If you get a "connection refused"
		message, check the SMTP settings if they are correct and then contact your hosting provider. If you get a "relay denied" contact your
		SMTP service provider.
        </p>

        <table class="form-table">
        <tr>
            <th>Enable external SMTP?</th>
            <td><?php $controls->yesno('smtp_enabled'); ?></td>
        </tr>
        <tr>
            <th>SMTP host/port</th>
            <td>
                host: <?php $controls->text('smtp_host', 30); ?>
                port: <?php $controls->text('smtp_port', 6); ?>
                <br />
                Leave port empty for default value (25). To use Gmail try host "ssl://smtp.gmail.com" and port "465" (without quotes).
                For GoDaddy use "relay-hosting.secureserver.net".
            </td>
        </tr>
        <tr>
            <th>Authentication</th>
            <td>
                user: <?php $controls->text('smtp_user', 30); ?>
                password: <?php $controls->text('smtp_pass', 30); ?>
                <br />
                If authentication is not required, leave "user" field blank.
            </td>
        </tr>
    </table>
        <?php $controls->button('smtp_test', 'Test'); ?>

                  </div>
          

        <div id="tabs-3">  
        <!-- Content locking -->

        <p class="intro">
            Content locking is a special feature that permits to "lock out" pieces of post content hiding them and unveiling
            them only to newsletter subscribers. I use it to hide special content on some post inviting the reader to subscribe the newsletter
            to read them.<br />
            Content on post can be hidden surrounding it with [newsletter_lock] and [/newsletter_lock] short codes.<br />
            A subscribe can see the hidden content after sign up or following a link on newsletters and welcome email generated by
            {unlock_url} tag. That link bring the user to the URL below that should be a single premium post/page where there is the hidden
            content or a list of premium posts with hidden content. The latter option can be implemented tagging all premium posts with a
            WordPress tag or adding them to a specific WordPress category.
        </p>
        <table class="form-table">
            <tr valign="top">
                <th>Unlock destination URL</th>
                <td>
                    <?php $controls->text('lock_url', 70); ?>
                    <div class="hints">
                    This is a web address (URL) where users are redirect when they click on unlocking URL ({unlock_url})
                    inserted in newsletters and welcome message. Usually you will redirect the user on a URL with with locked content
                    (that will become visible) or in a place with a list of link to premium content. I send them on a tag page
                    (http://www.satollo.net/tag/reserved) since I tag every premium content with "reserved".
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Denied content message</th>
                <td>
                    <?php $controls->textarea('lock_message'); ?>
                    <div class="hints">
                        This message is shown in place of protected post or page content which is surrounded with
                        [newsletter_lock] and [/newsletter_lock] short codes.<br />
                        Use HTML to format the message. PHP code is accepted and executed. WordPress short codes provided
                        by other plugins work as well. It's a good
                        practice to add the short code [newsletter_embed] to show a subscription form so readers can sign
                        up the newsletter directly.<br />
                        You can also add a subscription HTML form right here, like:<br />
                        <br />
                        &lt;form&gt;<br />
                            Your email: &lt;input type="text" name="ne"/&gt;<br />
                            &lt;input type="submit" value="Subscribe now!"/&gt;<br />
                        &lt;/form&gt;<br />
                        <br />
                        There is no need to specify a form method or action, Newsletter Pro will take care of. To give more evidence of your
                        alternative content you can style it:<br />
                         <br />
                        &lt;div style="margin: 15px; padding: 15px; background-color: #ff9; border-color: 1px solid #000"&gt;<br />
                            blah, blah, blah...<br />
                        &lt;/div&gt;
                    </div>
                </td>
            </tr>
        </table>
        
        </div>


      <div id="tabs-4">
        <!-- System check -->
        <h3>Crons</h3>
        <table class="widefat" style="width: auto">
          <thead>
            <tr>
              <th>Function</th>
              <th>Runs in (seconds)</th>
            </tr>
          </thead>

          <tbody>
            <tr>
              <td>
                WordPress Cron System
              </td>
              <td>
                <?php
                if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON)
                  echo 'DISABLED. (really bad, see <a href="http://www.satollo.net/?p=2015" target="_tab">this page)</a>';
                else
                  echo "ENABLED. (it's ok)";
                ?>
              </td>
            </tr>
            <tr>
              <td>
                Delivery Engine
              </td>
              <td>
                <?php
                $x = wp_next_scheduled('newsletter');
                if ($x === false) {
                  echo 'Error! The delivery engine is off (it should never be off),';
                  $controls->button('engine_on', 'Reactivate now');
                }
                if ($x > 0)
                  echo $x - time();
                if ($x < -1000)
                  echo ' (not good, see <a href="http://www.satollo.net/?p=2015" target="_tab">this page)</a>)';
                ?>
                <?php $controls->button('trigger', 'Trigger now'); ?>
              </td>
            </tr>

            <?php if (defined('NEWLETTER_AUTOMATED_EMAILS')) { ?>
              <?php for ($i = 1; $i <= NEWSLETTER_AUTOMATED_EMAILS_MAX; $i++) { ?>
                <tr>
                  <td>
                    Automated Email (<?php echo $i; ?>)
                  </td>
                  <td>
                    <?php
                    $x = wp_next_scheduled('newsletter_automated', array('id' => "$i"));
                    if ($x > 0)
                      echo $x - time();
                    ?>
                  </td>
                </tr>
              <?php } ?>
            <?php } ?>

          </tbody>
        </table>


        <h3>System parameters</h3>
    
          <table class="widefat" style="width: auto">
            <thead>
              <tr>
                <th>Parameter</th>
                <th>Value</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Database Wait Timeout</td>
                <td>
                  <?php $wait_timeout = $wpdb->get_var("select @@wait_timeout"); ?>
                  <?php echo $wait_timeout; ?> (seconds)
                </td>
              </tr>
              <tr>
                <td>PHP Execution time</td>
                <td>
                  <?php echo ini_get('max_execution_time'); ?> (seconds)
                </td>
              </tr>
              <tr>
                <td>PHP Memory Limit</td>
                <td>
                  <?php echo @ini_get('memory_limit'); ?>
                </td>
              </tr>
              <tr>
                <td>WP_MEMORY_LIMIT</td>
                <td>
                  <?php echo WP_MEMORY_LIMIT; ?>
                </td>
              </tr>
              <tr>
                <td>$table_prefix</td>
                <td>
                  <?php echo $table_prefix; ?>
                </td>
              </tr>        
              <tr>
                <td>DB_CHARSET</td>
                <td>
                  <?php echo DB_CHARSET; ?>
                </td>
              </tr>        
              <tr>
                <td>Hook "phpmailer_init"</td>
                <td>
                  <?php
                  $filters = $wp_filter['phpmailer_init'];
                  if (!is_array($filters))
                    echo 'No actions attached';
                  else {
                    foreach ($filters as &$filter) {
                      foreach ($filter as &$entry) {
                        if (is_array($entry['function']))
                          echo get_class($entry['function'][0]) . '->' . $entry['function'][1];
                        else
                          echo $entry['function'];
                        echo '<br />';
                      }
                    }
                  }
                  ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
          
        </div> <!-- tabs -->

        <p class="submit">
            <?php $controls->button('save', 'Save'); ?>
            <?php $controls->button_confirm('remove', 'Totally remove this plugin', 'Really sure to totally remove this plugin. All data will be lost!'); ?>
        </p>

    </form>
    <p></p>
</div>
