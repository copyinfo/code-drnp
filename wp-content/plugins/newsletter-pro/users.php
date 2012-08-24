<?php

@include_once 'commons.php';

$options = stripslashes_deep($_POST['options']);
$options_lists = get_option('newsletter_profile');
$options_profile = get_option('newsletter_profile');
$options_main = get_option('newsletter_main');

$lists = array();
for ($i=1; $i<=NEWSLETTER_LIST_MAX; $i++)
{
    $lists[''.$i] = '(' . $i . ') ' . $options_lists['list_' . $i];
}

if ($action == 'resend') {
    $user = $newsletter->get_user($options['subscriber_id']);
    $opts = get_option('newsletter');
    $newsletter->mail($user->email, $newsletter->replace($opts['confirmation_subject'], $user), $newsletter->replace($opts['confirmation_message'], $user));
}

if ($action == 'resend_welcome') {
    $user = $newsletter->get_user($options['subscriber_id']);
    $opts = get_option('newsletter');
    $newsletter->mail($user->email, $newsletter->replace($opts['confirmed_subject'], $user), $newsletter->replace($opts['confirmed_message'], $user));
}

if ($action == 'resend_all') {
    $list = $wpdb->get_results("select * from " . $wpdb->prefix . "newsletter where status='S'");
    $opts = get_option('newsletter');

    if ($list) {
        foreach ($list as $user) {
            $newsletter->mail($user->email, $newsletter->replace($opts['confirmation_subject'], $user), $newsletter->replace($opts['confirmation_message'], $user));
        }
    }
}

if ($action == 'remove') {
    $wpdb->query($wpdb->prepare("delete from " . $wpdb->prefix . "newsletter where id=%d", $options['subscriber_id']));
    unset($options['subscriber_id']);
}

if ($action == 'remove_unconfirmed') {
    $wpdb->query("delete from " . $wpdb->prefix . "newsletter where status='S'");
}

if ($action == 'remove_unsubscribed') {
    $wpdb->query("delete from " . $wpdb->prefix . "newsletter where status='U'");
}

if ($action == 'remove_bounced') {
    $wpdb->query("delete from " . $wpdb->prefix . "newsletter where status='B'");
}

if ($action == 'confirm_all') {
    $wpdb->query("update " . $wpdb->prefix . "newsletter set status='C'");
}

if ($action == 'remove_all') {
    $wpdb->query("delete from " . $wpdb->prefix . "newsletter");
}

if ($action == 'list_add') {
    $wpdb->query("update " . $wpdb->prefix . "newsletter set list_" . $options['list'] . "=1");
}

if ($action == 'list_remove') {
    $wpdb->query("update " . $wpdb->prefix . "newsletter set list_" . $options['list'] . "=0");
}

if ($action == 'list_delete') {
    $wpdb->query("delete from " . $wpdb->prefix . "newsletter where list_" . $options['list'] . "<>0");
}

if ($action == 'status') {
    newsletter_set_status($options['subscriber_id'], $options['subscriber_status']);
}


if ($action == 'feed_all') {
    $wpdb->query("update " . $wpdb->prefix . 'newsletter set feed=1 where feed<>2');
}

if ($action == 'feed_cancel') {
    $wpdb->query("update " . $wpdb->prefix . 'newsletter set feed=0');
}

if ($action == 'list_manage') {
    if ($options['list_action'] == 'move') {
        echo 'move';
        $wpdb->query("update " . $wpdb->prefix . 'newsletter set list_' . $options['list_1'] . '=0, list_' . $options['list_2'] . '=1' .
                ' where list_' . $options['list_1'] . '=1');
    }

    if ($options['list_action'] == 'add') {
        $wpdb->query("update " . $wpdb->prefix . 'newsletter set list_' . $options['list_2'] . '=1' .
                ' where list_' . $options['list_1'] . '=1');
    }
}


if ($action == 'search') {
    $list = newsletter_search($options['search_text'], $options['search_status'], $options['search_order'], $options['search_list'], $options['search_link'], $options['search_limit']);
}
else {
    $list = array();
}

$nc = new NewsletterControls($options);
$nc->errors($errors);
$nc->messages($messages);

?>
<script type="text/javascript">
    function newsletter_remove(f, id)
    {
        f.elements["options[subscriber_id]"].value = id;
        f.submit();
    }

    function newsletter_set_status(f, id, status)
    {
        f.elements["options[subscriber_id]"].value = id;
        f.elements["options[subscriber_status]"].value = status;
        f.submit();
    }

    function newsletter_resend(f, id)
    {
        if (!confirm("<?php _e('Resend the subscription confirmation email?', 'newsletter'); ?>")) return;
        f.elements["options[subscriber_id]"].value = id;
        f.submit();
    }

    function newsletter_resend_welcome(f, id)
    {
        if (!confirm("<?php _e('Resend the welcome email?', 'newsletter'); ?>")) return;
        f.elements["options[subscriber_id]"].value = id;
        f.submit();
    }
</script>

<div class="wrap">
    <h2>Newsletter Subscribers Management</h2>

    <p><a href="admin.php?page=newsletter-pro/users-edit.php&amp;id=0" class="button">Create a new user</a></p>

    <h3>Statistics</h3>
                
    <form method="post" action="">
        <?php $nc->init(); ?>
            
        <table class="widefat" style="width: 300px;">
            <thead><tr><th>Status</th><th>Total</th><th>Actions</th></thead>
            <tr valign="top">
                <td>Total stored emails</td>
                <td>
                    <?php echo $wpdb->get_var("select count(*) from " . $wpdb->prefix . "newsletter"); ?>
                </td>
                <td>
                    <?php $nc->button_confirm('remove_all', 'Delete all', 'Are you SURE you want to remove ALL subscribers?'); ?>
                </td>
            </tr>            
            <tr>
                <td>Confirmed</td>
                <td>
                    <?php echo $wpdb->get_var("select count(*) from " . $wpdb->prefix . "newsletter where status='C'"); ?>
                </td>
                <td nowrap>
                </td>
            </tr>
            <tr>
                <td>Not confirmed</td>
                <td>
                    <?php echo $wpdb->get_var("select count(*) from " . $wpdb->prefix . "newsletter where status='S'"); ?>
                </td>
                <td nowrap>
                    <?php $nc->button_confirm('remove_unconfirmed', 'Delete all not confirmed', 'Are you sure you want to delete ALL not confirmed subscribers?'); ?>
                    <?php $nc->button_confirm('confirm_all', 'Confirm all', 'Are you sure you want to mark ALL subscribers as confirmed?'); ?>
                    <?php $nc->button_confirm('resend_all', 'Resend confirmation to unconfirmed', 'Are you sure you want to resend a confirmation email to every unconfirmed subscriber?'); ?>                    
                </td>
            </tr>
            <tr>
                <td>Subscribed to feed by mail</td>
                <td nowrap>
                    <?php echo $wpdb->get_var("select count(*) from " . $wpdb->prefix . "newsletter where status='C' and feed=1"); ?>
                </td>
                <td nowrap>
                    <?php $nc->button_confirm('feed_all', 'Subscribe all to feed by mail', 'Are you sure you want to mark ALL subscribers to receive feeds?'); ?>
                    <?php $nc->button_confirm('feed_cancel', 'Cancel all from feed by mail', 'Are you sure you want to cancel ALL subscribers from feed by mail service?'); ?>
                </td>
            </tr>
            <tr>
                <td>Unsubscribed</td>
                <td>
                    <?php echo $wpdb->get_var("select count(*) from " . $wpdb->prefix . "newsletter where status='U'"); ?>
                </td>
                <td>
                    <?php $nc->button_confirm('remove_unsubscribed', 'Delete all unsubscribed', 'Are you sure you want to delete ALL unsubscribed?'); ?>
                </td>
            </tr>
        </table>    
        <p><a href="admin.php?page=newsletter-pro/users-stats.php">See other statistic data and graphs</a></p>


        <h3>Massive actions</h3>
        <table class="form-table">
            <tr>
                <th>Lists</th>
                <td>
                    List <?php $nc->select('list', $lists); ?>:
                    <?php $nc->button_confirm('list_add', 'Add all to it', 'Are you sure?'); ?>
                    <?php $nc->button_confirm('list_remove', 'Cancel all from it', 'Are you sure?'); ?>
                    <?php $nc->button_confirm('list_delete', 'Delete subscribers of it', 'Are you really sure you want to delete those user from your database?'); ?>
                    <br /><br />
                    <?php $nc->select('list_action', array('move'=>'Move', 'add'=>'Add')); ?>
                    all subscribers of list <?php $nc->select('list_1', $lists); ?>
                    to list <?php $nc->select('list_2', $lists); ?>
                    <?php $nc->button_confirm('list_manage', 'Go!', 'Are you sure?'); ?>
                    <div class="hints">
                        Lists <strong>are not</strong> group of users, but options subscribers can have set or not. If you choose to <strong>delete</strong> users in a list, they will be
                        <strong>physically deleted</strong> from the database (no way back).
                    </div>
                </td>
            </tr>
            <tr>
                <th>Follow up</th>
                <td>
                    Actually cannot be managed from here
                </td>
            </tr>
        </table>
    </form>

    <form id="channel" method="post" action="">
        <?php $nc->init(); ?>
        <input type="hidden" name="options[subscriber_id]"/>
        <input type="hidden" name="options[subscriber_status]"/>

        <?php
            $tmp = $wpdb->get_results($wpdb->prepare("select distinct newsletter, url from " . $wpdb->prefix . "newsletter_stats order by newsletter,url"));
            $links = array(''=>'Unfiltered');
            foreach ($tmp as $t) {
                $links[$t->newsletter . '|' . $t->url] = $t->newsletter . ': ' . substr($t->url, 0, min(strlen($t->url), 50)) . '...';
            }
        ?>


        <h3>Search</h3>
        <table class="form-table">
            <tr valign="top">
                <td>
                    text: <?php $nc->text('search_text', 50); ?> (partial name, email, ...)<br />
                    <?php $nc->select('search_status', array(''=>'Any status', 'C'=>'Confirmed', 'S'=>'Not confirmed', 'U'=>'Unsubscribed', 'B'=>'Bounced')); ?>
                    <?php
                        $order_fields = array('id'=>'Order by id', 'email'=>'Order by email', 'name'=>'Order by name');
                        for ($i=1; $i<20; $i++) {
                            if ($options_profile['profile_' . $i] == '') continue;
                            $order_fields['profile_' . $i] = $options_profile['profile_' . $i];
                        }
                    ?>
                    <?php $nc->select('search_order', $order_fields); ?>

                    <?php $nc->select('search_list', $lists, 'Any'); ?>
                    <?php $nc->select('search_limit', array(100=>'Max 100 results', '1000'=>'Max 1000 result', ''=>'No limit')); ?>
                    <?php $nc->button('search', 'Search'); ?>
                    <br />
                    <?php _e('show clicks', 'newsletter'); ?>:&nbsp;<?php $nc->yesno('search_clicks'); ?>
                    who&nbsp;clicked:&nbsp;&nbsp;
                    <?php $nc->select('search_link', $links); ?>

                    <div class="hints">
                    Press without filter to show all. Max 100 results will be shown. Use export panel to get all subscribers.
                    </div>
                </td>
            </tr>
        </table>


<h3>Search results</h3>

<?php if (empty($list)) { ?>
<p>No search results (or you did not search at all)</p>
<?php } ?>


<?php if (!empty($list)) { ?>

<table class="widefat">
    <thead>
<tr>
    <th>Id</th>
    <th><?php _e('Email', 'newsletter'); ?>/<?php _e('Name', 'newsletter'); ?></th>
    <th>Profile</th>
    <th><?php _e('Status', 'newsletter'); ?></th>
    <th>Lists</th>
    <th><?php _e('Actions', 'newsletter'); ?></th>
    <th><?php _e('Profile', 'newsletter'); ?></th>
    <?php if ($options['search_clicks'] == 1) { ?>
    <th><?php _e('Clicks', 'newsletter'); ?></th>
    <?php } ?>
</tr>
    </thead>
    <?php foreach($list as $s) { ?>
<tr class="<?php echo ($i++%2==0)?'alternate':''; ?>">

<td>
    <?php echo $s->id; ?>
</td>

<td>
    <a href="admin.php?page=newsletter-pro/users-edit.php&amp;id=<?php echo $s->id; ?>"><?php echo $s->email; ?><br /><?php echo $s->name; ?> <?php echo $s->surname; ?></a>
</td>

<td>
    <small>
    <?php
    for ($i=1; $i<20; $i++) {
        if ($options_profile['profile_' . $i] == '') continue;
        echo $options_profile['profile_' . $i];
        echo ':';
        $key = 'profile_' . $i;
        echo htmlspecialchars($s->$key);
        echo '<br />';
    }
    ?>
    </small>
</td>

<td>
    <small>
        <?php
        switch ($s->status) {
            case 'S': echo 'NOT CONFIRMED'; break;
            case 'C': echo 'CONFIRMED'; break;
            case 'U': echo 'UNSUBSCRIBED'; break;
            case 'B': echo 'BOUNCED'; break;
        }
        ?>
        <br />
        Feed: <?php echo ($s->feed!=1?'NO':'YES'); ?><br />
        Follow Up: <?php echo ($s->followup!=1?'NO':'YES'); ?> (<?php echo $s->followup_step; ?>)
    </small>
</td>

<td>
    <small>
        <?php
        for ($i=1; $i<=NEWSLETTER_LIST_MAX; $i++) {
            $l = 'list_' . $i;
            if ($s->$l == 1) echo $lists['' . $i] . '<br />';
        }
        ?>
    </small>
</td>

<td>
    <?php $nc->button('remove', 'Remove', 'newsletter_remove(this.form,' . $s->id . ')'); ?>
    <?php $nc->button('status', 'Confirm', 'newsletter_set_status(this.form,' . $s->id . ',\'C\')'); ?>
    <?php $nc->button('status', 'Unconfirm', 'newsletter_set_status(this.form,' . $s->id . ',\'S\')'); ?>
    <?php $nc->button('resend', 'Resend confirmation', 'newsletter_resend(this.form,' . $s->id . ')'); ?>
    <?php $nc->button('resend_welcome', 'Resend welcome', 'newsletter_resend_welcome(this.form,' . $s->id . ')'); ?>
</td>
<td><small>
        date: <?php echo $s->created; ?><br />
        <?php
        $query = $wpdb->prepare("select name,value from " . $wpdb->prefix . "newsletter_profiles where newsletter_id=%d", $s->id);
        $profile = $wpdb->get_results($query);
        foreach ($profile as $field) {
            echo htmlspecialchars($field->name) . ': ' . htmlspecialchars($field->value) . '<br />';
        }
        echo 'Token: ' . $s->token;
?>
</small></td>

<?php if ($options['search_clicks'] == 1) { ?>
    <td><small>
    <?php
    $clicks = $wpdb->get_results($wpdb->prepare("select * from " . $wpdb->prefix . "newsletter_stats where newsletter_id=%d order by newsletter", $s->id));
    foreach ($clicks as &$click) {
    ?>
    <?php echo $click->newsletter; ?>: <?php echo $click->url; ?><br />
    <?php } ?>
    </small></td>
<?php } ?>

</tr>
<?php } ?>
</table>
<?php } ?>
    </form>
</div>
