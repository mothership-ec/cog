<?php if ($errors): ?>
    <ul>
        <?php foreach ($errors as $error): ?>
            <li><?php
                if (null === $error->getMessagePluralization()) {
                    echo $error->getMessageTemplate();
                } else {
                    echo $error->getMessageTemplate();
                }?></li>
        <?php endforeach; ?>
    </ul>
<?php endif ?>
