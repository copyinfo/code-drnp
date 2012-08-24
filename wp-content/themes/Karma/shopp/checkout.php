<form action="<?php shopp('checkout','url'); ?>" method="post" class="checkout-form commentsblock" id="checkout">
<?php shopp('checkout','cart-summary'); ?>

<?php if (shopp('cart','hasitems')): ?>
	<?php shopp('checkout','function'); ?>
	
		<?php if (shopp('customer','notloggedin')): ?>
		<div>
			<label for="login">Login to Your Account</label>
			<span><?php shopp('customer','account-login','size=20&title=Login'); ?><label for="account-login">Email</label></span>
			<span><?php shopp('customer','password-login','size=20&title=Password'); ?><label for="password-login">Password</label></span>
			<span><?php shopp('customer','login-button','context=checkout&value=Login'); ?></span>
		</div>
		<?php endif; ?>
		
			<h3>Contact Information</h3>
			
			<table cellpadding="0" width="80%" cellspacing="0" border="0">
			<tr>
			<td>
			<label for="firstname" class="name">First Name</label>
			</td>
			<td><label for="lastname" class="name">Last Name</label>
			</td>
			</tr>
			<tr>
			<td>
			<?php shopp('checkout','firstname','required=true&minlength=2&size=8&title=First Name');
			?>
			</td>
			<td>
			<?php shopp('checkout','lastname','required=true&minlength=3&size=14&title=Last Name'); ?></td>
			</tr>
			<tr>
			<td>
			<label for="company">Organization</label>
			</td>
			<td><label for="phone"  class='phone'>Phone Number</label></td>
			</tr>
			<tr>
			<td>
			<?php shopp('checkout','company','size=22&title=Company/Organization'); ?></td>
			<td><?php shopp('checkout','phone','format=phone&size=15&title=Phone&class=phone'); ?></td>
			</tr>
			<tr>
			<td colspan="2">
			<label for="email" class='grunion-field-label email'>Email Address</label>
			</td>
			</tr>
			<tr>
			<td colspan="2">
			<?php shopp('checkout','email','required=true&format=email&size=30&title=Email'); ?></td>
			</tr></table>
		
		<?php if (shopp('customer','notloggedin')): ?>
		<table cellpadding="0" width="80%" cellspacing="0" border="0">
			<tr>
			<td>
			<span><?php shopp('checkout','password','required=true&format=passwords&size=16&title=Password'); ?>
			<label for="password">Password</label></span>
			<span><?php shopp('checkout','confirm-password','required=true&format=passwords&size=16&title=Password Confirmation'); ?>
			<label for="confirm-password">Confirm Password</label></span>
		</td>
			</tr></table>
		<?php endif; ?>
		
		<?php if (shopp('cart','needs-shipped')): ?>
			<div class="half" id="billing-address-fields">
		<?php else: ?>
			
		<?php endif; ?>
			<br /><h3>Billing Address</h3>
			<table cellpadding="0" width="80%" cellspacing="0" border="0">
			<tr>
			<td>
				<label for="billing-name" class="firstname">Name</label>
				</td>
			<td><label for="billing-address" class="firstname">Street Address</label></td>
			</tr>
			<tr><td>
				<?php shopp('checkout','billing-name','required=false&title=Bill to'); ?>
			</td>
			<td>
				
				<?php shopp('checkout','billing-address','required=true&title=Billing street address'); ?>
			</td>
			</tr>
			<tr><td>
				<label for="billing-xaddress" class="firstname">Address Line 2</label></td>
			<td><label for="billing-city" class="firstname">City</label>
			</td>
			</tr>
			<tr><td>
			<?php shopp('checkout','billing-xaddress','title=Billing address line 2'); ?>
			</td><td><?php shopp('checkout','billing-city','required=true&title=City billing address'); ?>
			</td></tr>	
			<tr><td>
				<label for="billing-state" class="firstname">State / Province</label>
			</td><td>	
			<label for="billing-postcode" class="firstname">Postal / Zip Code</label>
			</td></tr>	
			<tr><td>
				<?php shopp('checkout','billing-state','required=true&title=State/Provice/Region billing address'); ?>
			</td><td>				
				<?php shopp('checkout','billing-postcode','required=true&title=Postal/Zip Code billing address'); ?>
			</td></tr>	
			<tr><td colspan="2">
				<label for="billing-country" class="firstname">Country</label>
			</td></tr>
			<tr><td colspan="2">
				<?php shopp('checkout','billing-country','required=true&title=Country billing address'); ?>
			</td></tr></table>
		<?php if (shopp('cart','needs-shipped')): ?>
			<div class="inline"><?php shopp('checkout','same-shipping-address'); ?></div>
			</div>
			<div class="half right" id="shipping-address-fields">
				<h3>Shipping Address</h3>
				<table cellpadding="0" width="80%" cellspacing="0" border="0">
			<tr>
			<td><label for="shipping-address" class="firstname">Name</label></td>
			<td><label for="shipping-address" class="firstname">Street Address</label></td>
			</tr><tr>
			<td><?php shopp('checkout','shipping-name','required=false&title=Ship to'); ?></td>
			<td><?php shopp('checkout','shipping-address','required=true&title=Shipping street address'); ?></td>
			</tr><tr>
			<td><label for="shipping-xaddress" class="firstname">Address Line 2</label></td>
			<td><label for="shipping-city" class="firstname">City</label></td>
			</tr><tr>
			<td><?php shopp('checkout','shipping-xaddress','title=Shipping address line 2'); ?></td>
			<td><?php shopp('checkout','shipping-city','required=true&title=City shipping address'); ?>
			</td>
			</tr><tr>
			<td><label for="shipping-state" class="firstname">State / Province</label></td>
			<td><label for="shipping-postcode" class="firstname">Postal / Zip Code</label></td>
			</tr><tr>
			<td><?php shopp('checkout','shipping-state','required=true&title=State/Provice/Region shipping address'); ?></td>
			<td><?php shopp('checkout','shipping-postcode','required=true&title=Postal/Zip Code shipping address'); ?></td>
			</tr><tr>		
			<tr><td colspan="2"><label for="shipping-country" class="firstname">Country</label>	
			</td></tr>
			<tr><td colspan="2"><?php shopp('checkout','shipping-country','required=true&title=Country shipping address'); ?></tr></table>	
			</div>
		<?php endif; ?>
		<?php if (shopp('checkout','billing-localities')): ?>
			<div class="half locale hidden">
				<div>
				<?php shopp('checkout','billing-locale'); ?>
				<label for="billing-locale" class="firstname">Local Jurisdiction</label>
				</div>
			</div>
		<?php endif; ?>
		
		<div>
			<?php shopp('checkout','payment-options'); ?>
			<?php shopp('checkout','gateway-inputs'); ?>
		</div>
		<?php if (shopp('checkout','card-required')): ?>
		<br />
			<h3>Payment Information</h3>
			<table cellpadding="0" width="80%" cellspacing="0" border="0">
			<tr>
			<td colspan="2">
			<label for="billing-card" class="firstname">Credit/Debit Card Number</label></td>
			</tr>
			<tr>
			<td colspan="2"><?php shopp('checkout','billing-card','required=true&size=30&title=Credit/Debit Card Number'); ?></td>
			</tr>
			<tr>
			<td><label for="billing-cardexpires-mm" class="firstname">Expiration Month</label></td>
			<td><label for="billing-cardexpires-yy" class="firstname">Expiration Year</label></td>
			</tr>
			<tr>
			<td><?php shopp('checkout','billing-cardexpires-mm','size=4&required=true&minlength=2&maxlength=2&title=Card\'s 2-digit expiration month&style=width:50px'); ?> </td>
			<td><?php shopp('checkout','billing-cardexpires-yy','size=4&required=true&minlength=2&maxlength=2&title=Card\'s 2-digit expiration year&style=width:50px'); ?></td>
			</tr>
			<tr>
			<td><label for="billing-cardtype" class="firstname">Card Type</label></td>
			<td><label for="billing-cardholder" class="firstname">Name on Card</label></td>
			</tr>
			<tr>
			<td><?php shopp('checkout','billing-cardtype','required=true&title=Card Type'); ?></td>
			<td><?php shopp('checkout','billing-cardholder','required=true&size=30&title=Card Holder\'s Name'); ?></td>
			</tr>
			<tr>
			<td colspan="2"><label for="billing-cvv">Card Code Verification (CCV)</label></td></tr>
			<tr>
			<td>
			<?php shopp('checkout','billing-cvv','size=7&minlength=3&maxlength=4&title=Card\'s security code (3-4 digits on the back of the card)'); ?></td></tr>
			</table>
		<?php if (shopp('checkout','billing-xcsc-required')): // Extra billing security fields ?>
		<table cellpadding="0" width="80%" cellspacing="0" border="0">
			<tr>
			<td colspan="2"><label for="billing-xcsc-start" class="firstname">Start Date</label></td>
			</tr>
			<tr>
		<td colspan="2"><?php shopp('checkout','billing-xcsc','input=start&size=7&minlength=5&maxlength=5&title=Card\'s start date (MM/YY)'); ?></td>
			</tr>
			<tr>
		<td colspan="2"><label for="billing-xcsc-issue" class="firstname">Issue #</label></td></tr>
		<tr>
		<td colspan="2"><?php shopp('checkout','billing-xcsc','input=issue&size=7&minlength=3&maxlength=4&title=Card\'s issue number'); ?></td>
			</tr></table>
		</div>
		<?php endif; ?>

		<?php endif; ?>
		
		<div>
		<div class="inline"><label for="marketing" class="firstname"><?php shopp('checkout','marketing'); ?> 3 month FREE Introductory fundraising tips eNewsletter
		</label></div>
		</div>
	
	<div id="submit_order" style="width:157px;"><input type="image" name="process" src="/images/submit-order.png" value="button" />
	</div>

<?php endif; ?>
</form>
