{extends file='parent:frontend/listing/listing.tpl'}

{block name="frontend_listing_list_inline"}
    {block name='frontend_index_easycredit_marketing_card'}
        {if (($EasyCreditMarketingCard && !$sSearchResults) || ($EasyCreditMarketingCardSearch && $sSearchResults)) && $sPage == 1}
            <easycredit-box-listing position="{$EasyCreditMarketingCardSettingsPosition}" src="{$EasyCreditMarketingCardSettingsMedia}" class="product--box box--{$productBoxLayout}"></easycredit-box-listing>
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}