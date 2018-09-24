{extends file="parent:frontend/address/ajax_editor.tpl"}

{block name="frontend_address_editor_modal_body"}
    {$smarty.block.parent}

    <div class="panel--footer">
        <button class="btn is--primary address--form-submit" data-submit-form="frmAddresses" data-value="update" data-checkFormIsValid="false" data-preloader-button="true">weiter zur Ratenauswahl</button>
    </div>
{/block}
