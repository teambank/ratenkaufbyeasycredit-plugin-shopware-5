{extends file='parent:frontend/index/index.tpl'}
{block name='frontend_index_header_meta_tags'}
    {$smarty.block.parent}

    {if isset($EasyCreditApiKey)}
    <meta name="easycredit-api-key" content="{$EasyCreditApiKey}" />
    {/if}

 
    <script type="module" src="https://ratenkauf.easycredit.de/api/resource/webcomponents/v3/easycredit-components/easycredit-components.esm.js"></script>
    <script nomodule src="https://ratenkauf.easycredit.de/api/resource/webcomponents/v3/easycredit-components/easycredit-components.js"></script>

{/block}
