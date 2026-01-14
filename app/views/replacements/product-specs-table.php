<table class="product-details">
    <tbody>
    <?php
    use App\Models\Products;
    $is_phone = $find_product->standard_function === 'Phone';
    $is_wide_format = $find_product->standard_function === 'Wide-Format';
    ?>

    <?php if (!$is_phone): ?>
        <tr>
            <td>Paper Size</td>
            <td><?= $find_product->copier_type ?? '-' ?></td>
        </tr>
    <?php endif; ?>

    <tr>
        <td>Manufacturer</td>
        <td><?=Products::product_brand_name($find_product->manufacturer_id)?></td>
    </tr>
    <tr>
        <td>Copier Type</td>
        <td><?= $find_product->standard_function ?? '-' ?></td>
    </tr>
    <tr>
        <td>Model Number</td>
        <td><?= $find_product->model_number ?? '-' ?></td>
    </tr>

    <?php if (!$is_phone): ?>
        <tr>
            <td>Color/Monochrome</td>
            <td><?= $find_product->copier_color ?? '-' ?></td>
        </tr>
    <?php endif; ?>

    <?php if (!empty($find_product->speed_color) && !$is_phone && !$is_wide_format): ?>
        <tr>
            <td>Speed (Color)</td>
            <td><?= $find_product->speed_color ?> ppm</td>
        </tr>
    <?php endif; ?>

    <?php if (!empty($find_product->speed_black) && !$is_phone && !$is_wide_format): ?>
        <tr>
            <td>Speed (Black)</td>
            <td><?= $find_product->speed_black ?> ppm</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
