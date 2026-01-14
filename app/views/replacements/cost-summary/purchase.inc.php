<?php use App\Models\Products; ?>
<?php use App\Models\CostModel; ?>


<table class="summary-table">
    <thead>
    <tr>
        <th>Product</th>
        <th>Section</th>
        <th>Qty.</th>
        <th>Subtotal</th>
    </tr>
    </thead>

    <!--PRODUCTS AND ACCESSORIES-->
    <tbody>
    <?php foreach ($proposal->cart_items as $cart_item): ?>
        <?php $product = (object)Products::find_product($cart_item->product->id) ?>
        <tr>
            <td class="pa-1 pb-2">
                <div class="d-flex align-center">
                    <img width="50" height="50" src="<?= Products::product_image($product) ?>" alt="">
                    <span><?= $product->name?> <?=CostModel::is_leasing($proposal) ?'(Lease)':'(Purchase)'?></span>
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
            <td class="text-center">
                <?php if (CostModel::is_leasing($proposal)): ?>
                    Recurring Payment
                <?php else: ?>
                    One-Time Payment
                <?php endif ?>
            </td>
            <td class="text-center"><?=$cart_item->product->qty?></td>
            <td class="text-right cost-column">
                <p>$<?=number_format(CostModel::single_equipment_total_cost($proposal,$cart_item),2)?></p>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>

    <!--Maintenance and Service-->
    <tbody>
    <tr>
        <td>
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
        <td class="text-center">
            <?php if (CostModel::is_global_prints($proposal)): ?>
                <?=CostModel::charge_type_alias($proposal->global_print_cost->charge_type)?>
            <?php else: ?>
                <?=CostModel::charge_type_alias($proposal->cart_items[0]->print_cost->charge_type)?>
            <?php endif ?>
        </td>
        <td class="text-center">1</td>
        <td class="text-right cost-column">
            <?php if (CostModel::is_global_prints($proposal)): ?>
                <p>$<?=CostModel::global_print_cost($proposal)?></p>
            <?php else: ?>
                <!--This is always MONTHLY cause we're embedding-->
                <!--the 'one-time' into each copier's subtotal-->
                <p>$<?=CostModel::per_copier_print_cost($proposal)?></p>
            <?php endif ?>
        </td>
    </tr>
    </tbody>

    <!--Cost Addons-->
    <tbody>
    <?php foreach ($proposal->cost_addons as $addon): ?>
        <tr>
            <td>
                <span class="mdi mdi-puzzle-plus-outline"></span>
                <?=$addon->name?></td>
            <td class="text-center">
                <?=CostModel::charge_type_alias($addon->charge_type)?>
            </td>
            <td class="text-center"><?= $addon->qty ?></td>
            <td class="text-right cost-column">
                <p>$<?=number_format(CostModel::single_addon_cost($proposal,$addon), 2)?></p>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>

    <!--One-time payment-->
    <tfoot>
    <!--One-time/Upfront payment-->
    <?php if (CostModel::grand_total_one_time($proposal) > 0): ?>
        <tr class="selected">
            <td class="font-weight-bold" colspan="3">Upfront Total (One-Time)</td>
            <td class="cost-column">$<?=number_format(CostModel::grand_total_one_time($proposal), 2)?></td>
        </tr>
    <?php endif ?>

    <!--The offered lease term-->
    <?php if (CostModel::is_leasing($proposal) || CostModel::grand_total_monthly($proposal,$proposal->lease_term_offered) > 0): ?>
        <tr class="selected">
            <td class="font-weight-bold" colspan="3" onclick="window.ToggleLeaseOptions()">Monthly Total (Recurring)
                <?php if ($proposal->show_term_options==1 && CostModel::is_leasing($proposal)): ?>
                    <i id="term-indicator" class="mdi mdi-chevron-down"></i>
                <?php endif ?>
            </td>
            <td class="text-right cost-column">
                <p class="font-weight-bold">
                    $<?=CostModel::grand_total_monthly($proposal,$proposal->lease_term_offered)?>
                </p>
            </td>
        </tr>
    <?php endif ?>

    <!--Lease term options-->
    <?php if ($proposal->show_term_options==1 && CostModel::is_leasing($proposal)): ?>
        <?php foreach ($proposal->lease_factor_provider->lease_factors as $factor):?>
            <tr class="lease-term-options d-none <?=$proposal->lease_term_offered==$factor->term?'selected':''?>">
                <td colspan="3"><?= $factor->term?>-Month Term <?= $proposal->lease_term_offered == $factor->term ? '<b>(Recommended)</b>' : '' ?></td>
                <td class="td-cost cost-column">
                    <p>$<?=CostModel::grand_total_monthly($proposal,$factor->term)?></p>
                </td>
            </tr>
        <?php endforeach ?>
    <?php endif ?>
    </tfoot>

</table>