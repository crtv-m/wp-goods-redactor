<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class GR_Products_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct(array(
            'singular' => __('Product', 'GR'),
            'plural'   => __('Products', 'GR'),
            'ajax'     => false
        ));
    }

    public function get_columns() {
        $columns = array(
            'cb'         => '<input type="checkbox"/>',
            'image'      => __('Image', 'GR'),
            'name'       => __('Name', 'GR'),
            'price'      => __('Price', 'GR'),
            'sale_price' => __('Sale Price', 'GR'),
            'category'   => __('Category', 'GR'),
        );
        return $columns;
    }

    protected function column_cb($item) {
        return sprintf('<input type="checkbox" name="product_ids[]" value="%s" />', $item['ID']);
    }

    protected function column_name($item) {
        $edit_link = get_edit_post_link($item['ID']);
        return sprintf('<a href="%s">%s</a>', $edit_link, $item['name']);
    }

    protected function column_image($item) {
        $product_id = $item['ID'];
        $product = wc_get_product($product_id);
        if ($product) {
            $image_id = $product->get_image_id();
            if ($image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                if ($image_url) {
                    return '<img src="' . esc_url($image_url) . '" alt="Product Image" width="50" height="50" />';
                }
            }
        }
        return '';
    }

    protected function column_price($item) {
        $product_id = $item['ID'];
        $product = wc_get_product($product_id);
        if ($product) {
            $regular_price = $product->get_regular_price();
            return wc_price($regular_price);
        }
        return '';
    }

    protected function column_sale_price($item) {
        $product_id = $item['ID'];
        $product = wc_get_product($product_id);
        if ($product) {
            $sale_price = $product->get_sale_price();
            if ($sale_price) {
                return wc_price($sale_price);
            } else {
                return __('No Sale', 'GR');
            }
        }
        return '';
    }

    protected function get_sortable_columns() {
        return array(
            'name'     => array('name', true),
            'price'    => array('price', false),
            'category' => array('category', false)
        );
    }

    public function prepare_items() {
        $per_page = 25;

        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $current_page = $this->get_pagenum();
        $total_items = count($this->get_products_data());
        $data = array_slice($this->get_products_data(), (($current_page - 1) * $per_page), $per_page);

        $this->items = $data;

        $_SESSION['gr_success_message'][] = '123';

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    private function get_products_data() {
        $args = array(
            'post_type'        => 'product',
            'posts_per_page'   => -1,
            'suppress_filters' => false,
        );

        if (!empty($_GET['orderby'])) {
            $args['orderby'] = $_GET['orderby'];
            $args['order']   = $_GET['order'];
        }

        if (!empty($_POST['goods-name'])) {
            $args['s'] = sanitize_text_field($_POST['goods-name']);
            echo "Поиск по запросу ->'" . $args['s'] . "'";
        }

        if (!empty($_POST['goods-cat'])) {
            $goods_cat = $_POST['goods-cat'];
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => $goods_cat,
                    //'operator' => 'AND',
                ),
            );
        }
       
        $products = get_posts($args);
        $data = array();

        foreach ($products as $product) {
            $product_id = $product->ID;
            $product_obj = wc_get_product($product_id);
            if ($product_obj) {
                $data[] = array(
                    'ID'         => $product_id,
                    'name'       => $product->post_title,
                    'price'      => $product_obj->get_regular_price(),
                    'category'   => implode(', ', wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'))),
                    'sale_price' => $product_obj->get_sale_price()
                );
            }
        }

        return $data;
    }

    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'price':
            case 'category':
            case 'sale_price':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }
}
