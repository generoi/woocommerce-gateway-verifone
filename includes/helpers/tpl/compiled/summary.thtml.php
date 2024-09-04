<?php if(!isset($foreachEmptyValues)){$foreachEmptyValues = array();} ?><div id="verifone-summary-modal" class="verifone_summary_modal">

    <!-- Modal content -->
    <div class="modal-content">
        <div class="modal-header">
            <span class="close">&times;</span>
            <h2><?php echo htmlentities($context["header"], ENT_QUOTES); ?></h2>
        </div>
        <div class="modal-body">

            <table class="verifone_summary" cellspacing="0">
                <tbody>
                 <?php $isEmpty = empty($context["configurationData"]);  array_push($foreachEmptyValues, $isEmpty);  if(!$isEmpty)  foreach($context["configurationData"] as $foreach_current_key => $foreach_value){  $context["configuration"] = $foreach_value;   $context["current_key"] = $foreach_current_key; ?>
                <tr>
                    <td class="label"><?php echo htmlentities($context["configuration"]["label"], ENT_QUOTES); ?></td>
                    <td class="value">
                        <pre id="verifone-summary-<?php echo htmlentities($context["current_key"], ENT_QUOTES); ?>" class="strong"><?php echo htmlentities($context["configuration"]["value"], ENT_QUOTES); ?></pre>
                        <?php if(RuntimeVerifone::__parseVarHelper("configuration.has_desc",$context)): ?>
                        <p class="note">
                            <?php if(RuntimeVerifone::__parseVarHelper("configuration.has_desc_class",$context)): ?>
                                <span class="<?php echo htmlentities(RuntimeVerifone::__parseVarHelper("configuration.desc_class",$context), ENT_QUOTES); ?>">
                            <?php else: ?>
                                <span>
                            <?php endif; ?>
                                <?php echo htmlentities(RuntimeVerifone::__parseVarHelper("configuration.desc",$context), ENT_QUOTES); ?>
                                </span>
                        </p>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php } array_pop($foreachEmptyValues); ?>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <h3>&nbsp</h3>
        </div>
    </div>

</div>
