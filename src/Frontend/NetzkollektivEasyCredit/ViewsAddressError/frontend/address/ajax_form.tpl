{extends file="parent:frontend/address/ajax_form.tpl"}

{* Error messages *}
{block name="frontend_address_error_messages"}
    {$smarty.block.parent}
    <div class="address-editor--errors">
    {include file="frontend/register/error_message.tpl" error_messages=$error_messages}
    </div>
{/block}

{block name="frontend_address_form_fieldset_customer_type"}
    <p><strong>Ich möchte die folgende Adresse als Rechnungs- und Lieferadresse verwenden:</strong></p>
    {$smarty.block.parent}
{/block}

{block name="frontend_address_action_button_send"}{/block}
{block name="frontend_address_action_button_save_as_new"}{/block}

