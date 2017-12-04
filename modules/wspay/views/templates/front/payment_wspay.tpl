
{capture name=path}{l s='Credit Card/WSPay' mod='wspay'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='wspay'}</h2>

{assign var='current_step' value='payment'}

{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

<h3>{l s='You have chosen to pay by WSPay credit card payment.' mod='wspay'}</h3>

<form name="pay" action="https://form.wspay.biz/test/Authorization.aspx" method="POST">
   	<input type="hidden" name="ShopID" value="{$shopid}"/>
    	<input type="hidden" name="TotalAmount" value="{$check_total}"/>
    	<input type="hidden" name="Signature" value="{$signature}"/>
    	<input type="hidden" name="ShoppingCartID"  value="{$cart_order_id}"/>
    	<input type="hidden" name="ReturnURL" value="http://localhost/prestashop/index.php?controller=return"/>
	<input type="hidden" name="CancelURL" value="http://localhost/prestashop/index.php?controller=order"/>
	<input type="hidden" name="ReturnErrorURL" value="http://localhost/prestashop/index.php?controller=return-error"/>
    			
   
	<input type="hidden" name="CustomerFirstName" value="{$ship_name}" />
	<input type="hidden" name="CustomerLastName" value="{$ship_lastname}" />
	<input type="hidden" name="CustomerAddress" value="{$ship_street_address}" />
	<input type="hidden" name="CustomerEmail" value="{$email}" />
	<input type="hidden" name="CustomerPhone" value="{$phone}" />
	<input type="hidden" name="CustomerCountry" value=" {$ship_country}" />
	<input type="hidden" name="CustomerCity" value=" {$ship_city}" />
	<input type="hidden" name="CustomerZIP" value=" {$ship_zip}" />
	
	
	

    <p>&nbsp;</p>
    <p>
		{l s='Here is a short summary of your order:' mod='wspay'}
	</p>
	<p style="margin-top:20px;">
		- {l s='The total amount of your order is' mod='wspay'}
            {if $override_currency == 0}
                <span id="amount_{$currency->id}" class="price">{convertPriceWithCurrency price=$total currency=$currency}</span>
            {else}
                <span id="amount_{$override_currency->id}" class="price">{convertPriceWithCurrency price=$total currency=$override_currency}</span>
            {/if}
			{if $use_taxes == 1}
			{l s='(tax incl.)' mod='wspay'}
			{/if}
	</p>
   
    <p>
        {l s='You will be redirected to WSPay to complete your payment.' mod='wspay'}
        <br /><br />
        <b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='wspay'}.</b>
    </p>
    <p class="cart_navigation">
        <input type="submit" name="submit" value="{l s='I confirm my order' mod='wspay'}" class="exclusive_large" />
        <a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Other payment methods' mod='wspay'}</a>
    </p>
</form>
{/if}
