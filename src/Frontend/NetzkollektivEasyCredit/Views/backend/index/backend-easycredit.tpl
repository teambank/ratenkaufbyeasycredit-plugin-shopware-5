{block name="backend/base/header/css"}
    {$smarty.block.parent}

    <style type="text/css">
    .easycredit--icon {
        background-image: url({link file='backend/_resources/images/easycredit-icon.svg'});
        background-repeat: no-repeat;
        background-size: contain;
    }
    </style>
    <link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/easycredit-config.css'}" />
{/block}

{block name="backend/base/header/javascript"}
    {$smarty.block.parent}

    {literal}<script>{/literal}
    window.ratenkaufbyeasycreditOrderManagementConfig = {$easyCreditConfig};
    {literal}</script>{/literal}
    
    <script type="module" src="https://invoice.easycredit-ratenkauf-webcomponents.pages.dev/easycredit-components/easycredit-components.esm.js"></script>
{/block}
