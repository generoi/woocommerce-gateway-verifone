<?php if(!isset($foreachEmptyValues)){$foreachEmptyValues = array();} ?><html>
<body>
<form method="POST" id="pay" action="<?php echo htmlentities($context["action"], ENT_QUOTES); ?>"><?php echo htmlentities($context["redirectMessage"], ENT_QUOTES); ?>...
     <?php $isEmpty = empty($context["formData"]);  array_push($foreachEmptyValues, $isEmpty);  if(!$isEmpty)  foreach($context["formData"] as $foreach_current_key => $foreach_value){  $context["value"] = $foreach_value;   $context["current_key"] = $foreach_current_key; ?>
    <input type="hidden" name="<?php echo htmlentities($context["current_key"], ENT_QUOTES); ?>" value="<?php echo htmlentities($context["value"], ENT_QUOTES); ?>"/>
    <?php } array_pop($foreachEmptyValues); ?>
    <script type="text/javascript">document.getElementById("pay").submit();</script>
    <input type="submit"/>
</form>
</body>
</html>