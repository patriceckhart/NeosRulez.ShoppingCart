$(function() {
    $('.quantityinput').change(function() {
        this.form.submit();
    });
});
$(function() {
    $('.delivery').change(function() {
        this.form.submit();
    });
});
$(function() {
    $('.custom-delivery :input').addClass("disabled");
    $(".custom-delivery :input").attr("disabled", true);
    $('#ecustomdelivery').change(function() {
        $('.custom-delivery :input').toggleClass("disabled")
        if ($('.custom-delivery :input').hasClass("disabled")) {
            $(".custom-delivery :input").attr("disabled", true);
            $('.custom-delivery').addClass("opacity");
        } else {
            $(".custom-delivery :input").attr("disabled", false);
            $('.custom-delivery').removeClass("opacity");
        }
    });
});
$('#gotostep2').click(function() {
    $('#step1').addClass("opacity");
    $('#step2').css('display','block');
    $('#gotostep2').css('display','none');
    $('#step1 :input').attr('disabled', true);
    $('.coupons').addClass("opacity");
    $('#couponcode').attr('disabled', true);
    $('.checkcode').attr('disabled', true);
});

$(function () {

    $('#checkcouponform').on('submit', function (e) {
        couponCode = $('#couponcode').val();
        $form = $(this);
        var path = $form.attr('action');
        e.preventDefault();
        $.ajax({
            type: 'post',
            url: path,
            data: $('#checkcouponform').serialize(),
            success: function (data) {
                var splitted = data.split(',');
                value = splitted[0];
                identifier = splitted[1];
                if(value>0) {
                    couponvalue = value;
                    sum = $('input[name="--neosrulez_shoppingcart-cart[fullprice]"').val();
                    newSum = sum-couponvalue;
                    $('#couponvalue').val(couponvalue);
                    $('input[name="--neosrulez_shoppingcart-cart[fullprice]"').val(newSum);
                    $('#couponidentifier').val(identifier);
                    $('h3.fullprice').text('€ '+newSum.toFixed(2).replace(".", ","));
                    $('#couponcode').removeClass('is-invalid');
                    $('#couponcode').addClass('is-valid');
                    $('.coupons').addClass("opacity");
                    $('#couponcode').attr('disabled', true);
                    $('.checkcode').attr('disabled', true);
                } else {
                    $('#couponcode').addClass('is-invalid');
                }
            }
        });
    });
});


function validateForm() {
    var firstname = $('#firstname').val();
    var lastname = $('#lastname').val();
    var address = $('#address').val();
    var zip = $('#zip').val();
    var city = $('#city').val();
    var phone = $('#phone').val();
    var email = $('#email').val();


    if (firstname == '' || lastname == '' || address == '' || zip == '' || city == '' || phone == '' || email == '') {
        $("#submitbtn").attr("disabled","disabled");
        $("#submitbtn").css("opacity","0.5");
    } else {
        $("#submitbtn").removeAttr("disabled");
        $("#submitbtn").css("opacity","1");
    }

    if (!isValidEmailAddress(email)) {
        $("#submitbtn").attr("disabled","disabled");
        $("#submitbtn").css("opacity","0.5");
    } else {
        $("#submitbtn").removeAttr("disabled");
        $("#submitbtn").css("opacity","1");
    }

    $('#phone').keydown( function( e )
    {
        return !(e.altKey || e.ctrlKey || e.shiftKey)
            && (
                e.keyCode >= 48 && e.keyCode <= 57 // 0 - 9
                || e.keyCode >= 96 && e.keyCode <= 105 // 0 - 9 NumPad
                || e.keyCode == 32 // Leerzeichen
                || e.keyCode == 187 // +
                || e.keyCode == 107 // + NumPad
                || e.keyCode == 8 // Backspace
                || e.keyCode == 46 // Entf
                || e.keyCode == 9 // Tab
            );
    } );

    $('#zip, #zip1').keydown( function( e )
    {
        return !(e.altKey || e.ctrlKey || e.shiftKey)
            && (
                e.keyCode >= 48 && e.keyCode <= 57 // 0 - 9
                || e.keyCode >= 96 && e.keyCode <= 105 // 0 - 9 NumPad
                || e.keyCode == 8 // Backspace
                || e.keyCode == 46 // Entf
                || e.keyCode == 9 // Tab
            );
    } );

    function isValidEmailAddress(emailAddress) {
        var pattern = new RegExp(/^(("[\w-+\s]+")|([\w-+]+(?:\.[\w-+]+)*)|("[\w-+\s]+")([\w-+]+(?:\.[\w-+]+)*))(@((?:[\w-+]+\.)*\w[\w-+]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][\d]\.|1[\d]{2}\.|[\d]{1,2}\.))((25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\.){2}(25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\]?$)/i);
        return pattern.test(emailAddress);
    };

    setTimeout(validateForm, 100);
}
$(function() {
    validateForm();
});
