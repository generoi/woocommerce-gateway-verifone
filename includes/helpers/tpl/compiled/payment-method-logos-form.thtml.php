<?php if(!isset($foreachEmptyValues)){$foreachEmptyValues = array();} ?><div class="verifone-payment">
    <p class="verifone-payment-select-message"><?php echo htmlentities($context["messages"]["selectMethod"], ENT_QUOTES); ?></p>

	<div class="verifone-payment-methods-list-selection verifone-payment-methods-saved">
		<?php $isEmpty = empty($context["paymentMethods"]["saved"]);  array_push($foreachEmptyValues, $isEmpty);  if(!$isEmpty)  foreach($context["paymentMethods"]["saved"] as $foreach_current_key => $foreach_value){  $context["paymentMethod"] = $foreach_value;   $context["current_key"] = $foreach_current_key; ?>
			<?php $hidden = $context["paymentMethod"]["type"] === 'all' && count($context["paymentMethods"]["saved"]) > 1; ?>
			<div class="verifone-payment-method-logo verifone-payment-method-<?php echo htmlentities($context["paymentMethod"]["code"], ENT_QUOTES); ?>" <?php if ($hidden) { echo 'style="display:none;"'; } ?>>
				<input type="radio" id="verifone-payment-method-<?php echo htmlentities($context["paymentMethod"]["code"], ENT_QUOTES); ?>" name="verifone-payment-method" value="<?php echo htmlentities($context["paymentMethod"]["code"], ENT_QUOTES); ?>" data-type="<?php echo htmlentities($context["paymentMethod"]["type"], ENT_QUOTES); ?>"/>
				<label for="verifone-payment-method-<?php echo htmlentities($context["paymentMethod"]["code"], ENT_QUOTES); ?>">
					<?php echo htmlentities($context["paymentMethod"]["displayName"], ENT_QUOTES); ?>
				</label>
			</div>
		<?php } array_pop($foreachEmptyValues); ?>
	</div>

	<div class="verifone-payment-methods-list-selection verifone-payment-method-logos">
		<?php $isEmpty = empty($context["paymentMethods"]["methods"]);  array_push($foreachEmptyValues, $isEmpty);  if(!$isEmpty)  foreach($context["paymentMethods"]["methods"] as $foreach_current_key => $foreach_value){  $context["paymentMethod"] = $foreach_value;   $context["current_key"] = $foreach_current_key; ?>
			<?php $hidden = $context["paymentMethod"]["type"] === 'all' && count($context["paymentMethods"]["methods"]) > 1; ?>
			<div class="verifone-payment-method-logo verifone-payment-method-<?php echo htmlentities($context["paymentMethod"]["code"], ENT_QUOTES); ?>" <?php if ($hidden) { echo 'style="display:none;"'; } ?>>
				<input type="radio" id="verifone-payment-method-<?php echo htmlentities($context["paymentMethod"]["code"], ENT_QUOTES); ?>" name="verifone-payment-method" value="<?php echo htmlentities($context["paymentMethod"]["code"], ENT_QUOTES); ?>" data-type="<?php echo htmlentities($context["paymentMethod"]["type"], ENT_QUOTES); ?>"/>
				<label for="verifone-payment-method-<?php echo htmlentities($context["paymentMethod"]["code"], ENT_QUOTES); ?>">
					<img src="<?php echo htmlentities($context["paymentMethod"]["logo"], ENT_QUOTES); ?>" alt="<?php echo htmlentities($context["paymentMethod"]["displayName"], ENT_QUOTES); ?>"/>
				</label>
			</div>
		<?php } array_pop($foreachEmptyValues); ?>
	</div>

    <?php if($context["allowCC"]): ?>
    <div class="verifone-save-payment-method-wrapper">
        <input type="checkbox" id="verifone-save-payment-method" name="verifone-save-payment-method"/>
        <label for="verifone-save-payment-method">
            <span><?php echo htmlentities($context["messages"]["rememberMethod"], ENT_QUOTES); ?></span>
        </label>
        <span class="verifone-payment-saved-info"><?php echo htmlentities($context["messages"]["rememberMeInfo"], ENT_QUOTES); ?></span>
    </div>
    <?php endif; ?>
</div>
<div class="verifone-payment-message">
    <p><?php echo htmlentities($context["messages"]["redirectMessage"], ENT_QUOTES); ?></p>
</div>
