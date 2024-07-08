<?php wp_nonce_field('gr_form_submission_nonce'); ?>
<input type="hidden" name="action" value="gr_form_submission">
<div class="gr-window">
    <div class="gr-filter__title">Список изменений:</div>
    <div class="gr-filter__fields">
        <label>
            <input type="text" name="new_category" placeholder="Новая категория">
            Создать новую категорию и добавить выбранные товары
        </label>
        <label>
            <input type="text" name="sale_price" placeholder="Новая цена">
            Изменить акционную цену выбранных товаров
        </label>
    </div>
</div>