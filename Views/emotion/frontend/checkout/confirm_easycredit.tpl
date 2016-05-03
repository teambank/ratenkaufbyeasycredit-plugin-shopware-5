{* Shopware 5.x.x *}
{block name="frontend_checkout_confirm_tos_panel"}
    {$smarty.block.parent}

    {include file="frontend/common/redemption.tpl"}
{/block}

{* Shopware 4.x.x *}
{block name="frontend_checkout_confirm_shipping"}
    {$smarty.block.parent}

    {include file="frontend/common/redemption.tpl"}
{/block}
