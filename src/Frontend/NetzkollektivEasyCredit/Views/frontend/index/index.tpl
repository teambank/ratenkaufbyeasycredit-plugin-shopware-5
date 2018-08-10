{extends file='parent:frontend/index/index.tpl'}
{block name='frontend_index_header_meta_tags'}
    {$smarty.block.parent}

    {if isset($EasyCreditApiKey)}
    <meta name="easycredit-api-key" content="{$EasyCreditApiKey}" />
    {/if}
{/block}
