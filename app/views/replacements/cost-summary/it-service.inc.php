<?php use App\Models\Products; ?>
<?php use App\Models\CostModel; ?>

<table class="summary-table">
    <thead>
    <tr>
        <th>Item</th>
        <th>Qty.</th>
        <th>Subtotal</th>
    </tr>
    </thead>

    <!--PRODUCTS AND ACCESSORIES-->
    <tbody>
        <?php foreach ($proposal->it_service_items as $cart_item): ?>
        <!--IF Tiered IT Service-->
        <?php if(!empty($cart_item->tier)): ?>
            <tr>
                <td>
                    <p><?=$cart_item->tier->name?></p>
                    <ul>
                        <?php foreach ($cart_item->items as $line_item): ?>
                            <li><small><?=$line_item->name?></small></li>
                        <?php endforeach ?>
                    </ul>
                </td>
                <td class="text-center">1</td>
                <td class="text-right">
                    <p>$<?=number_format(CostModel::it_svc_tier_subtotal($cart_item),2)?></p>
                    <small class="text-capitalize"><?=$cart_item->tier->charge_type?></small>
                </td>
            </tr>
        <?php endif ?>

        <!--IF Standalone IT Service-->
        <?php if(empty($cart_item->tier)): ?>
            <?php foreach ($cart_item->items as $line_item): ?>
                <tr>
                    <td><?=$line_item->name?></td>
                    <td class="text-center"><?=$line_item->quantity?></td>
                    <td class="text-right">
                        <p>$<?=number_format(CostModel::it_svc_line_item_subtotal($line_item),2)?></p>
                        <small class="text-capitalize"><?=$line_item->charge_type?></small>
                    </td>
                </tr>
            <?php endforeach ?>
        <?php endif ?>
    <?php endforeach ?>
    </tbody>

    <tfoot>
        <!--ONE-TIME GRAND TOTAL-->
        <?php if (CostModel::it_svc_one_time_grand_total($proposal)>0): ?>
            <tr class="selected">
                <td class="font-weight-bold" colspan="2">
                    <p>Upfront Total (One-time)</p>
                </td>
                <td class="text-right cost-column">
                    <p class="font-weight-bold">
                        $<?=number_format(CostModel::it_svc_one_time_grand_total($proposal),2)?>
                    </p>
                </td>
            </tr>
        <?php endif ?>

        <!--RECURRING GRAND TOTAL-->
        <tr class="selected">
            <td class="font-weight-bold" colspan="2">
                <p>Grand Total (Recurring)</p>
            </td>
            <td class="text-right cost-column">
                <p class="font-weight-bold">
                    $<?=number_format(CostModel::it_svc_recurring_grand_total($proposal),2)?>
                </p>
            </td>
        </tr>
    </tfoot>

</table>