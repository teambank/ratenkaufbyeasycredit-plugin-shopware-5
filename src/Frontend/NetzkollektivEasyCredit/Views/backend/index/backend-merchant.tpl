{block name="backend/base/header/css"}
    {$smarty.block.parent}
	<link rel="stylesheet" type="text/css" href="{link file='backend/_resources/merchant/css/app.css'}" />
    <style type="text/css">
    .easycredit--icon {
        background-image: url({link file='backend/_resources/merchant/img/ratenkauf-icon.svg'});
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
    

	<script type="text/javascript" src="{link file='backend/_resources/merchant/js/app.js'}"></script>
{/block}
