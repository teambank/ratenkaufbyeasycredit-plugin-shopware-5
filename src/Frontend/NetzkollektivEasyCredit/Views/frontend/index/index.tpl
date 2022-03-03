{extends file='parent:frontend/index/index.tpl'}
{block name='frontend_index_header_meta_tags'}
    {$smarty.block.parent}

    {if isset($EasyCreditApiKey)}
    <meta name="easycredit-api-key" content="{$EasyCreditApiKey}" />
    {/if}

    <script type="module" src="{link file='frontend/_public/src/js/easycredit-components/easycredit-components/easycredit-components.esm.js'}"></script>
    <script nomodule src="{link file='frontend/_public/src/js/easycredit-components/easycredit-components/easycredit-components.js'}"></script>
{/block}
