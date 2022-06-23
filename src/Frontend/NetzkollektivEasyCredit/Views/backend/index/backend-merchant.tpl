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
    
    <script type="module" src="https://ratenkauf.easycredit.de/api/resource/webcomponents/v3/easycredit-components/easycredit-components.esm.js"></script>
    <script nomodule src="https://ratenkauf.easycredit.de/api/resource/webcomponents/v3/easycredit-components/easycredit-components.js"></script>
{/block}
