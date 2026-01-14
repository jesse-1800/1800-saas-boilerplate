<?php use App\Models\Products; ?>
<?php use App\Models\CostModel; ?>

<?php if ($proposal->category == 'copier-lease'): ?>

    <!--Copier Lease Proposal-->
    <?php if (CostModel::is_leasing($proposal)): ?>
        <?php include 'cost-summary/leasing.inc.php' ?>
    <?php else: ?>
        <?php include 'cost-summary/purchase.inc.php' ?>
    <?php endif ?>

<?php else: ?>
    <!--IT Service Proposal-->
    <?php include 'cost-summary/it-service.inc.php' ?>
<?php endif ?>
