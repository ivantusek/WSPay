
<h2>  {l s= 'We noticed error in your transaction and credi card!' sprintf=$shop_name mod='wspay'} </h2>

<p>&nbsp;</p>
    <p>
		{l s='Here is a short summary of your error:' mod='wspay'}
	</p>
	<p style="margin-top:20px;">
		- {l s='Your car number is not corect!' mod='wspay'}
	</p>
   
    <p>
      -  {l s='Your credit card is expired!' mod='wspay'}
        <br /><br />
        <b>{l s='Please check your credit card! ' mod='wspay'}.</b>
    </p>
    <p class="cart_navigation">
    
    	<div class="buttons"><a class="btn btn-default button button-medium" href="{$base_dir}" title="{l s='Home'}"><span><i class="icon-chevron-left left"></i>{l s='Home page'}</span></a></div> <br>  
        <a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Other payment methods' mod='wspay'}</a>
        
        