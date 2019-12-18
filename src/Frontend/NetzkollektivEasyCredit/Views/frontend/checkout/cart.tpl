{extends file='parent:frontend/checkout/cart.tpl'}

{block name='frontend_basket_basket_is_empty'}
    {if $sBasketInfo}
		<div class="basket--info-messages">
			{include file="frontend/_includes/messages.tpl" type="error" content=$sBasketInfo}
		</div>
    {/if}

	{$smarty.block.parent}
{/block}
