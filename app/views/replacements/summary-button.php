<?php if ($proposal->status == 'accepted'): ?>
<table class="accepted-proposal-table">
    <tr>
        <td><i class="mdi mdi-account-outline"></i> Accepted by:</td>
        <td><?=$proposal->accepted_by?></td>
    </tr>
    <tr>
        <td><i class="mdi mdi-calendar"></i> Date:</td>
        <td><?=$proposal->accepted_date?></td>
    </tr>
    <tr>
        <td><i class="mdi mdi-briefcase-outline"></i> Company:</td>
        <td><?=$proposal->company_name?></td>
    </tr>
    <tr>
        <td><i class="mdi mdi-signature-text"></i> Signature:</td>
        <td class="pa-0"><img height="50" src="<?=$proposal->accepted_signature?>"></td>
    </tr>
</table>

<?php else: ?>
    <button title="Accept Proposal" onclick="window.AcceptProposal()" class="accept-proposal-btn v-btn v-btn--elevated text-white text-h6 px-6 py-4 rounded-pill">
        <i class="mdi mdi-file-sign mr-2"></i> Accept Proposal
    </button>
<?php endif ?>
