{extends file='parent:frontend/index/index.tpl'}
{block name='frontend_index_header_meta_tags'}
    {$smarty.block.parent}

    {if isset($EasyCreditApiKey)}
    <meta name="easycredit-api-key" content="{$EasyCreditApiKey}" />
    {/if}

    {if $EasyCreditWidget}
    <meta name="easycredit-widget-active" content="true" />
    {/if}

    <script type="module" src="https://ratenkauf.easycredit.de/api/resource/webcomponents/v3/easycredit-components/easycredit-components.esm.js"></script>
    <script nomodule src="https://ratenkauf.easycredit.de/api/resource/webcomponents/v3/easycredit-components/easycredit-components.js"></script>

{/block}

{block name="frontend_index_body_classes"}
    {$smarty.block.parent}

    {if $EasyCreditMarketingBar}
        easycredit-box-top
    {/if}
{/block}

{block name='frontend_index_after_body'}
    {$smarty.block.parent}

    {block name='frontend_index_easycredit_marketing_bar'}
        {if $EasyCreditMarketingBar}
            <easycredit-box-top></easycredit-box-top>
        {/if}
    {/block}
{/block}

{block name='frontend_index_footer'}
    {$smarty.block.parent}

    {block name='frontend_index_easycredit_marketing_modal'}
        {if $EasyCreditMarketingModal}
            <easycredit-box-modal is-open="{$EasyCreditMarketingModalIsOpen}" snooze-for="{$EasyCreditMarketingModalSettingsSnoozeFor}" delay="{$EasyCreditMarketingModalSettingsDelay}" src="{$EasyCreditMarketingModalSettingsMedia}"></easycredit-box-modal>
        {/if}
    {/block}

    {block name='frontend_index_easycredit_marketing_flashbox'}
        {if $EasyCreditMarketingFlashbox}
            <easycredit-box-flash is-open="false" src="{$EasyCreditMarketingFlashboxSettingsMedia}"></easycredit-box-flash>
        {/if}
    {/block}
{/block}
