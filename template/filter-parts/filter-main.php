<form method="post">
    <div class="gr-window">
        <div class="gr-window__title">Список категорий товара:</div>
        <div class="gr-filter__tags-list">
            <?php
                $categories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => false,
                ));

                if (!empty($categories) && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        printf( '<label><input type="checkbox" name="goods-cat" value="%s">%s</label>', esc_attr($category->slug), esc_html($category->name) );
                    }
                } else {
                    echo "<h2>Не найдены категории</h2><br><p>Проверьте наличие категорий товаров</p>";
                }
            ?>
        </div>
        <div class="gr-filter__title">Поиск по значению поля:</div>
        <div class="gr-filter__fields">
            <label><input type="text" name="goods-name" value="" placeholder="Введите значение">Название товара (целиком или частично)</label>
            <?php 
                /*<label><input type="text" name="goods-price-max" value="" placeholder="Введите значение">Цена товара (до)</label>
                <label><input type="checkbox" name="zero-prise" checked>Показать товары без цены</label>*/
            ?>
        </div>

        <?php submit_button('Поиск товаров', 'button-primary', ''); ?>
    </div>
</form>
