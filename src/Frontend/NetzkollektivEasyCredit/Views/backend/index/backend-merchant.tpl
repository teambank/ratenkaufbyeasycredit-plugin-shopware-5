{block name="backend/base/header/css"}
    {$smarty.block.parent}

    <style type="text/css">
    .easycredit--icon {
        background-image: url({link file='backend/_resources/img/ratenkaufbyeasycredit-icon.svg'});
        background-repeat: no-repeat;
        background-size: contain;
    }
    </style>
{/block}

{block name="backend/base/header/javascript"}
    {$smarty.block.parent}

    {literal}<script>{/literal}
    window.ratenkaufbyeasycreditOrderManagementConfig = {$easyCreditConfig};
    {literal}</script>{/literal}
    

	<script type="text/javascript" src="{link file='backend/_resources/merchant/easycredit-merchant.min.js'}"></script>
{/block}
