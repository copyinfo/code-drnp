<?php
@include_once 'commons.php';

$options = get_option('newsletter_forms');

if (isset($_POST['save'])) {
    $options = stripslashes_deep($_POST['options']);
    update_option('newsletter_forms', $options);
}
?>

<div class="wrap">

    <h2>Newsletter Forms</h2>
    <p>
        On this version of Newsletter Pro, custom forms can be coded directly on subscription page or on widgets.
    </p>
    <p>
        If you still want to code the forms here (may be to centralize them) do not add the &lt;form&gt; tag
        since it is added automatically.
    </p>
    <p>
        To recall a form with PHP you can use:
    </p>
    <p>
        <code>&lt;?php newsletter_form(1); ?&gt;</code>
    </p>
    <p>
        where "1" is the form number (so it can be from 1 to 10). The form will be echoed.
    </p>

<form method="post" action="">

    <table class="form-table">
        <?php for ($i=1; $i<=10; $i++) { ?>
        <tr valign="top">
            <th>Form <?php echo $i; ?></th>
            <td>
                <textarea cols="70" width="100%" style="width:100%;font-family:monospace" rows="7" wrap="off" name="options[form_<?php echo $i; ?>]"><?php echo htmlspecialchars($options['form_' . $i])?></textarea>
                <br />
                <input class="button" type="submit" name="save" value="Save"/>
            </td>
        </tr>
            <?php } ?>
    </table>

    <h3>Examples</h3>
    <p>Those are examples of forms, you can copy and paste that code as starting point.</p>

    <p><strong>Simple standard form</strong></p>
    <pre style="font-family:monospace"><?php echo htmlspecialchars(
        '
    <table cellspacing="3" cellpadding="3" border="0" width="50%">
        <tr><td>Your name</td><td><input type="text" name="nn" size="30"/></td></tr>
        <tr><td>Your email</td><td><input type="text" name="ne" size="30"/></td></tr>
        <tr><td colspan="2" style="text-align: center"><input type="submit" value="Subscribe me"/></td></tr>
    </table>'
        ); ?></pre>

    <p><strong>Form asking "sex"</strong></p>
    <pre style="font-family:monospace"><?php echo htmlspecialchars(
        '
    <table cellspacing="3" cellpadding="3" border="0" width="50%">
        <tr><td>Your name</td><td><input type="text" name="nn" size="30"/></td></tr>
        <tr><td>Your email</td><td><input type="text" name="ne" size="30"/></td></tr>
        <tr><td>You are</td><td><select name="nx]"><option value="m">Male</option><option value="f">Female</option></select></td></tr>
        <tr><td colspan="2" style="text-align: center"><input type="submit" value="Subscribe me"/></td></tr>
    </table>
'
        ); ?></pre>
   
</form>


</div>