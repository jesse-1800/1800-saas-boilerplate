<?php use App\Models\Products; ?>
<?php use App\Models\CostModel; ?>

<table class="summary-table">
    <thead>
    <tr>
        <th>Product</th>
        <th>Qty.</th>
        <th>Subtotal</th>
    </tr>
    </thead>

    <!--PRODUCTS AND ACCESSORIES-->
    <tbody>
    <?php
    $is_leasing = CostModel::is_leasing($proposal);
    $is_purchasing = CostModel::is_purchasing($proposal);
    $rows_offset = count($proposal->cost_addons) + 1;
    $rowspan = $is_leasing ? count($proposal->cart_items) + $rows_offset : 1;

    # We have to minus the addons that are "one-time" only
    foreach ($proposal->cost_addons as $addon) {
        if ($addon->charge_type == 'one-time') $rowspan--;
    }

    # We need to sort the addons so that the one-time addons are at the bottom
    usort($proposal->cost_addons, function($a, $b) {
        if ($a->charge_type == $b->charge_type) return 0;
        return ($a->charge_type == 'one-time') ? 1 : -1;
    });
    ?>
    <?php foreach ($proposal->cart_items as $cart_index => $cart_item): ?>
        <?php $product = (object)Products::find_product($cart_item->product->id) ?>
        <tr>
            <td class="pa-1 pb-2">
                <div class="d-flex align-center">
                    <img width="50" height="50" src="<?= Products::product_image($product) ?>" alt="">
                    <span><?= $product->name?> (Lease)</span>
                </div>
                <!--Accessories-->
                <?php $attachments = Products::attachments($cart_item) ?>
                <ul class="mt-1 ml-3">
                    <?php foreach ($attachments as $attachment): ?>
                        <li><?= Products::find_accessory($attachment->accessory_id)->name ?></li>
                    <?php endforeach ?>

                    <!--Prints Cost/Included-->
                    <?php if ($proposal->is_global_print_cost==0): ?>
                        <!--Only show this if show_prints_cost is YES-->
                        <?php if ($proposal->show_prints_cost==1): ?>
                            <li>Monthly Included Prints: <?=$cart_item->print_cost->black_prints_included?> B/W, <?=$cart_item->print_cost->color_prints_included?> Color</li>
                            <li>Overage Prints: $<?=$cart_item->print_cost->black_overage_cost?> per B/W, $<?=$cart_item->print_cost->black_overage_cost?> per Color</li>
                        <?php endif ?>
                    <?php endif ?>
                </ul>
            </td>
            <td class="text-center"><?=$cart_item->product->qty?></td>
            <?php if ($cart_index === 0): ?>
                <td class="text-right cost-column" rowspan="<?=$rowspan?>">
                    <p>$<?=CostModel::grand_total_monthly($proposal,$proposal->lease_term_offered)?> / month</p>
                </td>
            <?php endif ?>
        </tr>
    <?php endforeach ?>

    <!--Maintenance and Service-->
    <tr>
        <td colspan="2">
            <p><span class="mdi mdi-printer-pos-wrench-outline"></span> Maintenance & Service</p>
            <!--Show included prints if its GLOBAL-->
            <?php if ($proposal->is_global_print_cost==1): ?>
                <!--Only show this if show_prints_cost is YES-->
                <?php if ($proposal->show_prints_cost==1): ?>
                    <ul class="mt-1 ml-1">
                        <!--Prints Cost/Included-->
                        <li>Monthly Included Prints: <?=$proposal->global_print_cost->black_prints_included?> B/W, <?=$proposal->global_print_cost->color_prints_included?> Color</li>
                        <li>Overage Prints: $<?=$proposal->global_print_cost->black_overage_cost?> per B/W, $<?=$proposal->global_print_cost->black_overage_cost?> per Color</li>
                    </ul>
                <?php endif ?>
            <?php endif ?>
        </td>
        <!--TD is filled by rowspan-->
    </tr>

    <!--Cost Addons-->
    <?php foreach ($proposal->cost_addons as $addon): ?>
        <tr>
            <td colspan="2">
                <span class="mdi mdi-puzzle-plus-outline"></span>
                <?=$addon->name?>
            </td>
            <!--TD is filled by rowspan-->
            <!--This TD below only show if $addon->charge_type == 'one-time'-->
            <?php if ($addon->charge_type=='one-time'): ?>
                <td class="text-right cost-column">
                    <p>$<?=number_format(CostModel::single_addon_cost($proposal,$addon), 2)?> One-time</p>
                </td>
            <?php endif ?>
        </tr>
    <?php endforeach ?>
    </tbody>
    <!--END of main tbody from Products-->

    <!--Grand Totals-->
    <tfoot>
        <!--One-time payment grand total-->
        <?php if (CostModel::grand_total_one_time($proposal) > 0): ?>
            <tr class="selected">
                <td class="font-weight-bold" colspan="2">Upfront Total (One-Time)</td>
                <td class="cost-column">$<?=number_format(CostModel::grand_total_one_time($proposal), 2)?> One-time</td>
            </tr>
        <?php endif ?>

        <!--The offered lease term-->
        <tr class="selected">
            <td colspan="2" class="font-weight-bold" onclick="window.ToggleLeaseOptions()">Monthly Total (Recurring)
                <?php if ($proposal->show_term_options==1 && $is_leasing): ?>
                    <i id="term-indicator" class="mdi mdi-chevron-down"></i>
                <?php endif ?>
            </td>
            <td class="text-right cost-column">
                <p class="font-weight-bold">
                    $<?=CostModel::grand_total_monthly($proposal,$proposal->lease_term_offered)?> / month
                </p>
            </td>
        </tr>

        <!--Lease term options-->
        <?php if ($proposal->show_term_options==1): ?>
            <?php foreach ($proposal->lease_factor_provider->lease_factors as $factor):?>
                <?php $is_selected_term = $proposal->lease_term_offered==$factor->term ?>
                <tr class="lease-term-options d-none <?=$is_selected_term? 'selected':''?>">
                    <td colspan="2">
                        <?= $factor->term?>-Month Term <?=$is_selected_term ? '<b>(Recommended)</b>':''?>
                    </td>
                    <td class="text-right cost-column">
                        <p>$<?=CostModel::grand_total_monthly($proposal,$factor->term)?> / month</p>
                    </td>
                </tr>
            <?php endforeach ?>
        <?php endif ?>
    </tfoot>
</table>