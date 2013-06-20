<?php if(isset($attr['data-translation-key'])): ?>
<div class="help"><?php echo $view['translator']->trans($attr['data-translation-key']) ?></div>
<?php endif; ?>
