<?php if(!isset($foreachEmptyValues)){$foreachEmptyValues = array();} ?><div class="verifone-payment">
    <label for="verifone-payment-method"><?php echo htmlentities($context["messages"]["selectMethod"], ENT_QUOTES); ?></label>
    <select id="verifone-payment-method" name="verifone-payment-method">
         <?php $isEmpty = empty($context["paymentMethods"]);  array_push($foreachEmptyValues, $isEmpty);  if(!$isEmpty)  foreach($context["paymentMethods"] as $foreach_current_key => $foreach_value){  $context["paymentMethod"] = $foreach_value;   $context["current_key"] = $foreach_current_key; ?>
        <option value="<?php echo htmlentities($context["paymentMethod"]["code"], ENT_QUOTES); ?>" data-type="<?php echo htmlentities($context["paymentMethod"]["type"], ENT_QUOTES); ?>"><?php echo htmlentities($context["paymentMethod"]["displayName"], ENT_QUOTES); ?></option>
        <?php } array_pop($foreachEmptyValues); ?>
    </select>

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