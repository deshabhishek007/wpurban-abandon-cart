( function() {

	tinymce.PluginManager.add('abandoncart_pro_css', function(editor, url) {

	
    editor.addButton( 'abandoncart_pro_css', {
        type: 'menubutton',
        text: false,
        icon: "abandoncart_email_variables_css_pro",
        menu: [
               {
                   text: 'Cart Button',
                   value: '{{cart.button}}',
                   onclick: function() {
                       editor.insertContent(
                    		'<table cellspacing="0" cellpadding="0" border="0" style="border-collapse:separate!important;border-radius:3px;background-color:#557da1; width:auto;" class="abandoned_cart_button">'
               				+'<tbody>'
               				+'<tr>'
               				+'<td valign="middle" align="center" style="font-family:Arial;font-size:16px;padding:15px">'
               				+'<a style="font-weight:bold;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#f5f5f5;word-wrap:break-word" title="View My Cart" href="{{cart.link}}" target="_blank">View My Cart</a></td>'
               				+'</tr></tbody></table>'
               				+'<br> <br>'   
                       
                       );
                   }
               },
               {
                   text: 'Checkout Button',
                   value: '{{checkout.button}}',
                   onclick: function() {
                       editor.insertContent( 
                    		   '<table cellspacing="0" cellpadding="0" border="0" style="border-collapse:separate!important;border-radius:3px;background-color:#557da1; width:auto;" class="abandoned_checkout_button">'
               				+'<tbody>'
               				+'<tr>'
               				+ '<td valign="middle" align="center" style="font-family:Arial;font-size:16px;padding:15px">'
               				+'<a style="font-weight:bold;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#f5f5f5;word-wrap:break-word" title="Take Me To Checkout" href="{{checkout.link}}" target="_blank">Take Me To Checkout</a> </td>'
               				+'</tr></tbody></table>'
               				+'<br> <br>'   
                       );
                   }
               }
               
           ]
    });

});

})();