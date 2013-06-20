<div>
    <?php echo $view['form']->label($form) ?>
    <?php echo $view['form']->errors($form) ?>
    <?php echo $view['form']->widget($form) ?>
    <?php echo $view['form']->block($form, 'form_help') ?>
</div>
