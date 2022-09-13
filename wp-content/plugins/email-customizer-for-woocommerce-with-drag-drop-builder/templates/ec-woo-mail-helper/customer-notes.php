<?php

if (!defined('ABSPATH')) {
    exit;
}

foreach ($customer_notes as $note) {
    ?>
    <div class="customer-note-comment-content">
        <?php echo $note->comment_content; ?>
    </div>
    <?php
}
?>