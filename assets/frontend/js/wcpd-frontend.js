jQuery(function ($) {

    if ($("form.cart").hasClass('grouped_form')) {
        $("form.cart.grouped_form .single_add_to_cart_button").on("click", function() {
            var productData = []; // Array to store product data
    
            $('.woocommerce-grouped-product-list-item').each(function(i, e) {
                var productId = $(this).attr('id').replace('product-', '');
                var quantity = parseInt($(this).find('.quantity input').val());
        
                if (isNaN(quantity) || quantity <= 0) {
                    quantity = 0; // Set quantity to 0 if invalid
                }
        
                productData.push({
                    productId: productId,
                    quantity: quantity
                });
            });
        
            // Send AJAX request to add products to cart
            $.ajax({
                url: wcpd.ajaxurl, // WordPress AJAX endpoint URL
                type: 'POST',
                data: {
                    action: 'add_grouped_products_to_cart',
                    product_data: productData,
                    nonce: wcpd.nonce
                },
                success: function(response) {
                    $('#primary').prepend(`<div class="woocommerce-message" role="alert">
                    <a href="${wcpd.cart_url}" class="button wc-forward">View cart</a> Product has been added to your cart.</div>`);
                    $(document.body).trigger('wc_fragment_refresh');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error adding products to cart:', errorThrown);
                }
            });
        });
    }

    if ( 'yes' == wcpd.ajax_cart ) { //if ajax add to cart on archive page.        
        $(document).ajaxSend(function(event, jqxhr, settings) {            
            if (settings.url.indexOf('?wc-ajax=add_to_cart') != -1) {
                jqxhr.abort();
            }
        });        
    }

    $(document).on('click', '.close-wcpd-rest-modal', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        $(`a[data-product_id="${id}"]`).removeClass('loading');
        $('.wcpd-error-modal-container').fadeOut("200");
        $('.wcpd-modal-container').fadeOut("200");
        $('body').removeClass('wcpd-modal-open');
    });

    //sitewide disclaimer
    var data = {
        action: wcpd.sitewide_disclaimer,
        cookie_activation: wcpd.sitewide_cookie_activation,
        cookie_duration: wcpd.sitewide_cookie_duration,
        nonce: wcpd.nonce
    };

    $.post( wcpd.ajaxurl, data, function( success ) {        
        if ( '' != success ) {                 
            $('body').addClass('wcpd-modal-open');
            $('.wcpd-modal-container').fadeIn('200').css('display', 'flex');
            $('#wcpd-modal-content').html( success );
        }
    });


    //for single product page
    $("form.cart").submit(function(e) {

        if($("form.cart").hasClass('grouped_form')){
            return;
        }

        e.preventDefault();
        var form = $(this);
        var variation_id = '';
        var product_id = '';
        var quantity = form.find('input[name="quantity"]').val();

        if ( form.hasClass('variations_form') ) {
            product_id = form.find('input[name="add-to-cart"]').val();
            variation_id = form.find('input[name="variation_id"]').val();
        } else {
            product_id = form.find('button[name="add-to-cart"]').val();
        }       

        var data = {
            action: wcpd.simple_product_disclaimer,
            cookie_activation: wcpd.global_cookie_activation,
            cookie_duration: wcpd.global_cookie_duration,
            product_id: product_id,
            variation_id: variation_id,
            quantity: quantity,
            nonce: wcpd.nonce            
        };

        $.post( wcpd.ajaxurl, data, function( success ) {            
            if ( '' != success ) {                 
                $('body').addClass('wcpd-modal-open');
                $('.wcpd-modal-container').fadeIn('200').css('display', 'flex');
                $('#wcpd-modal-content').html( success );
            } else {                
                if ( variation_id != '' ) {
                    add_to_cart_simple_product(product_id, variation_id, quantity, form);
                } else {
                    add_to_cart_simple_product(product_id, '', quantity, form);
                }
            }
        });
        
    });

    // for simple type products on archive page
    $(document).on( 'click', '.product_type_simple.add_to_cart_button, .product_type_simple.ajax_add_to_cart', function( e ) {
       
        if( $('.wp-block-button__link').length > 0  && $('body').hasClass('woocommerce') && $('body').hasClass('archive')){
            e.preventDefault();
        }

        if ( 'no' == wcpd.ajax_cart ) {            
            e.preventDefault();
        }
        
        var add_to_cart_btn = $(this);
        var product_id = add_to_cart_btn.data('product_id');
        var quantity = 1;
        
        
        var data = {
            action: wcpd.simple_product_disclaimer,
            cookie_activation: wcpd.global_cookie_activation,
            cookie_duration: wcpd.global_cookie_duration,
            product_id: product_id,
            quantity: quantity,
            nonce: wcpd.nonce
        };
        
        $.post( wcpd.ajaxurl, data, function( success ) {            
            if ( '' != success ) {                
                $('body').addClass('wcpd-modal-open');
                $('.wcpd-modal-container').fadeIn('200').css('display', 'flex');
                $('#wcpd-modal-content').html( success );
            } else {
                woo_add_to_cart(product_id, quantity, add_to_cart_btn);
            }
        });
    });

    $(document).on( 'click', '.wcpd_accept', function(e) {
        e.preventDefault();        
        var flag_age = false;
        var flag_terms = false;
        var current = $(this);
        var age = '';
        var req_age = '';
        var parent = current.parents('.wcpd-modal-content');
        var type = parent.find('#wcpd_disclaimer_type').val();
        var product_id = parent.find('#wcpd_product_id').val();
        var disclaimer_id = parent.find('#wcpd_disclaimer_id').val();
        var quantity = parent.find('#wcpd_product_quantity').val();
        var add_to_cart_btn = $(`a[data-product_id=${product_id}]`);      
        var form = $('form.cart');

        if ( $('.wcpd_age_verification_wrapper').find('.wcpd-error').length > 0 ) {
            $('.wcpd_age_verification_wrapper .wcpd-error').remove();
        }

        if ( $('.wcpd_terms_condition_wrapper').find('.wcpd-error').length > 0 ) {
            $('.wcpd_terms_condition_wrapper .wcpd-error').remove();
        }

        if ( parent.find('#wcpd_age_verification').length > 0 ) {
            age = $.trim( parent.find('#wcpd_age_verification').val() );
            req_age = parent.find('#wcpd_age_verification').data('min');
            if ( age >= req_age ) {
                flag_age = true;
            } else {
                flag_age = false;
                if ( $('.wcpd_age_verification_wrapper').find('.wcpd-error').length <= 0 ) {
                    $('.wcpd_age_verification_wrapper').append(`<span class="wcpd-error">${wcpd.age_error_txt}</span>`);
                }
            }
        } else {
            flag_age = true;
        }

        if ( parent.find('#wcpd_terms_condition').length > 0 ) {
            if( parent.find('#wcpd_terms_condition').is(':checked') ) {
                flag_terms = true;
            } else {
                flag_terms = false;
                if ( $('.wcpd_terms_condition_wrapper').find('.wcpd-error').length <= 0 ) {
                    $('.wcpd_terms_condition_wrapper').append(`<span class="wcpd-error">${wcpd.terms_error_txt}</span>`);
                }
            }
        } else {
            flag_terms = true;
        }

        if ( flag_age && flag_terms ) {

            $('.wcpd-modal-loader').fadeIn('200');

            // if cookie setting is enable for sitewide
            if ( type == 'sitewide' && wcpd.sitewide_cookie_activation == 'enabled' ) {
                var cookie_data = {
                    action: wcpd.add_cookies,
                    nonce: wcpd.nonce,
                    type: type,                    
                    cookie_duration: wcpd.sitewide_cookie_duration
                };

                $.post( wcpd.ajaxurl, cookie_data, function ( response ) {

                });
            }

            // if cookie setting is enable for general or specific
            if ( type != 'sitewide' && wcpd.global_cookie_activation == 'enabled' ) {
                var cookie_data = {
                    action: wcpd.add_cookies,
                    nonce: wcpd.nonce,
                    type: type,
                    product_id: product_id,              
                    cookie_duration: wcpd.global_cookie_duration
                };

                $.post( wcpd.ajaxurl, cookie_data, function ( response ) {

                });
            }

            if ( type == 'sitewide' ) {
                $('.wcpd-error-modal-container').fadeOut("200");
                $('.wcpd-modal-container').fadeOut("200");
                $('body').removeClass('wcpd-modal-open');
                return false;
            }

            if ( wcpd.is_product ) {
                if ( form.hasClass('variations_form') ) { 
                    var variation_id = form.find('input[name="variation_id"]').val();
                    add_to_cart_simple_product(product_id, variation_id, quantity, form);
                } else {
                    add_to_cart_simple_product(product_id, '', quantity, form);      
                }

            } else {
               
                woo_add_to_cart( product_id, quantity, add_to_cart_btn);        
            }
        }
    });


    function woo_add_to_cart(product_id, quantity, $this='') {
        var data = {
            action: 'woocommerce_add_to_cart',
            product_id: product_id,
            quantity: quantity,
            nonce: wcpd.nonce
        };

        // Ajax action
        $.post( wcpd.ajaxurl, data, function( response ) {            
            if ( !response ) {
                return;
            }

            $('.close-wcpd-rest-modal').trigger('click');

            var this_page = window.location.toString();

            this_page = this_page.replace('add-to-cart', 'added-to-cart');

            if ( response.error && response.product_url ) {
                window.location = response.product_url;
                return false;
            }

            // Redirect to cart option
            if ( wcpd.cart_redirect === 'yes' ) {
                window.location = wcpd.cart_url;
                return;
            } else {

                if ( $this == '' ) {
                    location.reload();
                    return;
                }

                $this.removeClass('loading');
                fragments = response.fragments;
                cart_hash = response.cart_hash;
                // Block fragments class
                if (fragments) {
                    $.each(fragments, function(key, value) {
                        $(key).addClass('updating');
                    });
                }

                // Changes button classes
                $this.addClass('added');
                // View cart text
                if ( !wcpd.is_cart && $this.parent().find('.added_to_cart').size() === 0 ) {
                    $this.after(' <a href="' + wcpd.cart_url + '" class="added_to_cart wc-forward" title="' +
                    wcpd.i18n_view_cart + '">' + wcpd.i18n_view_cart + '</a>');
                }

                // Replace fragments
                if (fragments) {
                    $.each(fragments, function(key, value) {
                        $(key).replaceWith(value);
                    });
                }
                // Unblock
                $('.widget_shopping_cart, .updating').stop(true).css('opacity', '1').unblock();
                // Cart page elements
                $('.shop_table.cart').load(this_page + ' .shop_table.cart:eq(0) > *', function() {
                    $('div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)').addClass('buttons_added').append('<input type="button" value="+" id="add1" class="plus" />').prepend('<input type="button" value="-" id="minus1" class="minus" />');
                    $('.shop_table.cart').stop(true).css('opacity', '1').unblock();
                    $('body').trigger('cart_page_refreshed');
                });

                $('.cart_totals').load(this_page + ' .cart_totals:eq(0) > *', function() {
                    $('.cart_totals').stop(true).css('opacity', '1').unblock();
                });                
                
                // Trigger event so themes can refresh other areas
                $('body').trigger('added_to_cart', [fragments, cart_hash]);
            }
        });
    }

    function add_to_cart_simple_product( product_id='', variation_id='', quantity='', form) {
        var data = {
            action: 'wcpd_product_add_to_cart',
            product_id: product_id,
            variation_id: variation_id,
            quantity: quantity,
            form_serialized: form.serialize(),
            nonce: wcpd.nonce,

        };

        jQuery.post(wcpd.ajaxurl, data, function (response) {

            if ( response.error && response.product_url ) {
                window.location = response.product_url;
                return false;
            }

            $('.close-wcpd-rest-modal').trigger('click');

            if ( response.fragments ) {
                                
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);                

                //added for block theme compatibility
                $(document.body).trigger('wc_fragment_refresh');
                
                if ( wcpd.cart_redirect === 'yes' ) {
                    window.location = wcpd.cart_url;
                    return;
                } else {
                    if ( $('.woocommerce-message').length > 0 ) {
                        $('.woocommerce-message').remove();
                    }
                    $('#primary').prepend(`<div class="woocommerce-message" role="alert">
                            <a href="${wcpd.cart_url}" class="button wc-forward">View cart</a> Product has been added to your cart.</div>`);
                }
                
            }
        });
    }

});
