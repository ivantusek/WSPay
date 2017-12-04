{if $status=='ok'}
		<p>{l s='Your order has been completed.' mod='wspay'}</p>
		<br/><br/>{l s='For any question contact our' mod='wspay'}<a href="{$base_dir}contact-form.php">{l s='customer support' mod='wspay'}</a>.
{else}	
	<p class="warning">
	{l s='We noticed a problem with your order' mod='wspay'}
	</p>
		
		