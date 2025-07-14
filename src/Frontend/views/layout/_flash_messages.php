<?php
// /src/Frontend/views/layout/_flash_messages.php

if (!empty($flash_messages)) {
    foreach ($flash_messages as $message) {
        $type = e($message['type'] ?? 'info');
        $text = e($message['message'] ?? 'Notification');

        $alertClass = '';
        $icon = 'info';
        switch ($type) {
            case 'success':
                $alertClass = 'alert-success';
                $icon = 'check_circle';
                break;
            case 'error':
                $alertClass = 'alert-error';
                $icon = 'error';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                $icon = 'warning';
                break;
            case 'info':
            default:
                $alertClass = 'alert-info';
                break;
        }
        ?>
        <div role="alert" class="alert <?= $alertClass ?> shadow-lg mb-4 animate-fade-in-down">
            <span class="material-icons"><?= $icon ?></span>
            <span><?= $text ?></span>
            <button class="btn btn-sm btn-ghost" onclick="this.parentElement.remove()">
                <span class="material-icons">close</span>
            </button>
        </div>
        <?php
    }
}
?>