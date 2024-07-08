<?php
function gr_custom_notice($message = 'no message!', $type = 'warning') {
    $valid_types = array('error', 'warning', 'success', 'info');
    
    if (!in_array($type, $valid_types)) {
        $type = 'warning'; // Устанавливаем значение по умолчанию, если тип некорректный
    }

    $class = 'notice notice-' . $type . ' is-dismissible';
    ?>
    <div class="<?php echo esc_attr($class); ?>">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php
}