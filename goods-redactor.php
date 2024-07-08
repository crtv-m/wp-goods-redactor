<?php
/**
* Plugin Name: Goods Redactor
* Plugin URI: 
* Description: 
* Version: 0.13
* Requires at least: 5.6
* Requires PHP: 8.1.29
* Author: krtv-m
* Text Domain: GR
* Domain Path: /languages
* Copyright 2024 - goods redactor
*/

require_once 'template/message.php';
require_once 'template/list-config.php';


// Подклчение скриптов
function gr_enqueue_admin_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('wp-util');
    wp_enqueue_script('common');
    wp_enqueue_script('wp-a11y');
    wp_enqueue_script('jquery-ui');
    wp_enqueue_style('common'); // Cтили админки
    wp_enqueue_style('wp-admin'); // Подключаем стили админки
    wp_enqueue_style('wp-components'); // Подключаем компоненты WP
    wp_enqueue_style('gr-style', plugin_dir_url(__FILE__) . 'vendors/gr-style.css');
}
add_action('admin_enqueue_scripts', 'gr_enqueue_admin_scripts');


// Добавление пункта в меню
add_action('admin_menu', 'gr_add_menu');
function gr_add_menu() {
    add_menu_page(
        __('Goods Redactor', 'GR'), // Название страницы
        __('GoodsRedactor', 'GR'), // Название меню
        'manage_options', // Уровень доступа
        'goods-redactor', // Слаг страницы
        'gr_page_content', // Функция вывода содержимого страницы
        'dashicons-edit', // Иконка меню (можно выбрать другую)
        20 // Позиция меню
    );
}


// Вывод сообщений из сессии
session_start();
if (!empty($_SESSION['gr_success_messages'])) {
    foreach ($_SESSION['gr_success_messages'] as $message) {
        gr_custom_notice($message, 'success');
    }
    unset($_SESSION['gr_success_messages']); // Очистка всех сообщений из сессии
}


// Проверка настроек WooCommerce
function gr_check_woocommerce_setup() {
    if (class_exists('WooCommerce') && function_exists('wc_get_page_id')) {
        return true;
    }
    return false;
}


// Обработчик формы редактирования
function gr_handle_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'gr_form_submission') {
        
        // Проверка nonce для безопасности
        if (!check_admin_referer('gr_form_submission_nonce')) {
            wp_die('Nonce verification failed! Please try again.');
            error_log('Nonce verification failed for gr_form_submission_nonce.');
        }

        ob_start();

        $new_category = isset($_POST['new_category']) ? sanitize_text_field($_POST['new_category']) : '';
        $sale_price = isset($_POST['sale_price']) ? sanitize_text_field($_POST['sale_price']) : '';
        $product_ids = isset($_POST['product_ids']) ? (array) $_POST['product_ids'] : array();

        // Создаю новую категорию, если указана
        if (!empty($new_category)) {
            $existing_category = get_term_by('name', $new_category, 'product_cat');

            if ($existing_category) {
                $category_id = $existing_category->term_id;
            } else {
                $category_id = wp_insert_term($new_category, 'product_cat');
                if (is_wp_error($category_id)) {
                    $_SESSION['gr_success_message'][] = 'Ошибка при создании категории: ' . $category_id->get_error_message();
                    error_log('Error create category: ' . $category_id->get_error_message());
                    return;
                }
                $category_id = $category_id['term_id'];
            }

            $counter = 0;
            foreach ($product_ids as $product_id) {
                wp_set_post_terms($product_id, array($category_id), 'product_cat', true);
                $counter++;
            }
            $_SESSION['gr_success_message'][] = 'Добавлена новая категория для товаров (' . $counter . 'шт)';
        }

        // Обновляю акционную цену товаров, если указана
        if (!empty($sale_price)) {
            $counter = 0;
            foreach ($product_ids as $product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $product->set_sale_price($sale_price);
                    $product->save();
                }
                $counter++;
            }
            $_SESSION['gr_success_message'][] = 'Изменена акционная цена для товаров (' . $counter . 'шт) на ' . $sale_price;
        }

        ob_end_clean();
        
        // Перенаправление обратно на страницу плагина
        $redirect_url = add_query_arg('updated', 'true', wp_get_referer());
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('admin_post_gr_form_submission', 'gr_handle_form_submission');


if (gr_check_woocommerce_setup()) {

    // Содержимое страницы плагина
    function gr_page_content() {
        echo '<div class="wrap"><h1>' . esc_html__('Goods Redactor', 'GR') . '</h1></div>';
        echo "<h2>Шаг 1</h2><p class='title-description'>Укажите параметры выборки товаров:</p>";
        require_once plugin_dir_path(__FILE__) . 'template/filter-parts/filter-main.php';

        echo "<h2>Шаг 2</h2><p class='title-description'>Выберите отдельные товары:</p>";
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        $list_table = new GR_Products_List_Table();
        $list_table->prepare_items();
        $list_table->display();

        echo "<h2>Шаг 3</h2><p class='title-description'>Параметры массового редактирования:</p>";
        require_once plugin_dir_path(__FILE__) . 'template/edit-parts/editing-main.php';
        wp_nonce_field('gr_form_submission_nonce');
        echo '<input type="hidden" name="action" value="gr_form_submission">';
        submit_button('Записать изменения', 'button-primary', '');
        echo "</form>";
    }

} else {

    echo "<h3>" . esc_html__('Установите и активируйте плагин "woocommerce" - это необходимо для работы плагина!', 'GR') . "</h3>";

}