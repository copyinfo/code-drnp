<?php if (shopp('cart','hasitems')): ?>
<form id="cart" action="<?php shopp('cart','url'); ?>" method="post">
<?php shopp('cart','function'); ?>
<table class="cart">
	<tr>
		<th scope="col" class="item"><h5>Cart Items</h5></th>
		<th scope="col"><h5>Quantity</h5></th>
		<th scope="col" class="money"><h5>Item Price</h5></th>
		<th scope="col" class="money"><h5>Item Total</h5></th>
	</tr>

	<?php while(shopp('cart','items')): ?>
		<tr>
			<td>
				<a href="<?php shopp('cartitem','url'); ?>"><?php shopp('cartitem','name'); ?></a>
				<?php shopp('cartitem','options'); ?>
				<?php shopp('cartitem','addons-list'); ?>
				<?php shopp('cartitem','inputs-list'); ?>
			</td>
			<td><?php shopp('cartitem','quantity','input=text'); ?>
				<?php shopp('cartitem','remove','input=button'); ?></td>
			<td class="money"><?php shopp('cartitem','unitprice'); ?></td>
			<td class="money"><?php shopp('cartitem','total'); ?></td>
		</tr>
	<?php endwhile; ?>

	<?php while(shopp('cart','promos')): ?>
		<tr><td colspan="4" class="money"><?php shopp('cart','promo-name'); ?><strong><?php shopp('cart','promo-discount',array('before' => '&nbsp;&mdash;&nbsp;')); ?></strong></td></tr>
	<?php endwhile; ?>

	<tr class="totals">
		<td colspan="2" rowspan="5">
			<?php if (shopp('cart','needs-shipping-estimates')): ?>
			<small>Estimate shipping &amp; taxes for:</small>
			<?php shopp('cart','shipping-estimates'); ?>
			<?php endif; ?>
			<?php shopp('cart','promo-code'); ?>
		</td>
		<th scope="row">Subtotal</th>
		<td class="money"><?php shopp('cart','subtotal'); ?></td>
	</tr>
	<?php if (shopp('cart','hasdiscount')): ?>
	<tr class="totals">
		<th scope="row">Discount</th>
		<td class="money">-<?php shopp('cart','discount'); ?></td>
	</tr>
	<?php endif; ?>
	<?php if (shopp('cart','needs-shipped')): ?>
	<tr class="totals">
		<th scope="row"><?php shopp('cart','shipping','label=Estimated Shipping'); ?></th>
		<td class="money"><?php shopp('cart','shipping'); ?></td>
	</tr>
	<?php endif; ?>
	<tr class="totals">
		<th scope="row"><?php shopp('cart','tax','label=Tax'); ?></th>
		<td class="money"><?php shopp('cart','tax'); ?></td>
	</tr>
	<tr class="totals total">
		<th scope="row">Total</th>
		<td class="money"><?php shopp('cart','total'); ?></td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td style="float:right;"><div id="updatebutton"><input type="image" name="update" src="/images/update-subtotal.png" value="button"></div></td>
	</tr>
</table>

<big>
	<a href="<?php shopp('cart','referrer'); ?>" class="ka_button small_button small_silver"><span>&laquo; Continue Shopping</span></a>
	<a href="<?php shopp('checkout','url'); ?>" class="right ka_button small_button small_cherry"><span>Proceed to Checkout &raquo;</span></a>
</big>

</form>

<?php else: ?>
	<p class="warning">There are currently no items in your shopping cart.</p>
	<p><a href="<?php shopp('catalog','url'); ?>" class="ka_button small_button small_silver"><span>&laquo; Continue Shopping</span></a></p>
<?php endif; ?>
