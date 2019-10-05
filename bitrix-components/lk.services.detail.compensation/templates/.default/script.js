$(function() {

	var errorMsg = {
		"email": 'Введите корректный e-mail',
		"postcode": 'Поле "Индекс" должно содержать 6 знаков',
		"contact": 'Необходимо указать контактные данные генерального директора, главного бухгалтера и контакт для связи. Должен быть указан только один основной контакт',
	};

    // форма Отмены заявки - заполняем причину
    $('body').on('keydown', '.layer[data-id=cancel] form textarea', function() {
        var btn = $(this).closest('form').find('.bk-button');
        if($(this).val() !== '') {
            $('.layer[data-id=cancel] .bk-button').prop('disabled', false);
        }
    });
    
    // форма Отмены заявки - отправляем форму
    $(document).on('click', '.layer[data-id=cancel] form .bk-button', function() {
        var form = $(this).closest('form');
        var reason = form.find('textarea');

        if($(reason).val() !== '') {
            $.ajax({
                url: '/ajax/order_cancel.php',
                dataType: 'json',
                data: {
                    id: $('#order_wrap').attr('data-id'),
                    reason: $('.layer[data-id=cancel] form textarea').val(),
                },
                beforeSend: function() {
                    $('.layer[data-id=cancel] > div').css('opacity', '0.3');
                },
                success: function(data) {
                    $('.layer[data-id=cancel] > div').css('opacity', '1');
                    if(data['status'] === 'ok') {

                        $.ajax({
                            url : '',
                            dataType : 'html',
                            data : {
                                'isAjax' : 'Y'
                            },
                            success: function(data) {
                                $('.service-detail').replaceWith(data);
                            }
                        });

                        $('.layer[data-id=cancel] .cancel_form').hide();
                        $('.layer[data-id=cancel] .cancel_success').show();
                        $('.cancel_window').hide();
                    } else {
                        console.log(data['error']);
                    }
                }
            });
        } else {
            $(reason).focus();
        }
        return false;
    });
    
    // форма Оценки - выбираем оценку
    $('body').on('click', '.layer[data-id=assess01] form .estimate-list img', function() {
        $('.layer[data-id=assess01] .bk-button').prop('disabled', false);
    });
    
    // форма Оценки - отправляем форму
    $('body').on('click', '.layer[data-id=assess01] form .bk-button', function() {
        var comment = $(this).closest('form').find('textarea').val();
        $.ajax({
            url: '/ajax/order_rate.php',
            dataType: 'json',
            data: {
                id: $('#order_wrap').attr('data-id'),
                comment: comment,
                rate: $('.layer[data-id=assess01] .estimate-list input[name=score]').val(),
            },
            beforeSend: function() {
                $('.layer[data-id=assess01] > div').css('opacity', '0.3');
            },
            success: function(data) {
                $('.layer[data-id=assess01] > div').css('opacity', '1');
                if(data['status'] == 'ok') {
                    $('.layer[data-id=assess01] .assess_form').hide();
                    $('.layer[data-id=assess01] .assess_success').show();
                    $('.open_rate_window').hide();
                } else {
                    console.log(data['error']);
                }
            }
        });
        return false;
    });

    $.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^]*)').exec(window.location.href);
        if (results === null){
            return null;
        } else{
            return results[1] || 0;
        }
    };

    if($.urlParam('SENDED') === 'Y') {
        window.Layer.open('crm-success');
    }

	var isGroupCompany = $("#is_group_company");
	if (typeof isGroupCompany !== "undefined" && isGroupCompany.length > 0) {
		isGroupCompany.on("click", function() {
			var nameGroupCompany = $("#name_group_company"),
				star = $("#is_group_company_field").find('.required-star'),
                checkboxGroupCompany;

			if (typeof nameGroupCompany !== "undefined" && nameGroupCompany.length > 0) {
				checkboxGroupCompany = $(this).find('input[type="checkbox"]');
				if (checkboxGroupCompany.is(':checked')) {
					nameGroupCompany.show();
					required(nameGroupCompany);
					star.show();
				} else {
					required(nameGroupCompany, true);
					nameGroupCompany.hide();
					star.hide();
				}
			}
		});
    }

	var isMatchedActualAddress = $("#is_matched_actual_address");
	if (isMatchedActualAddress.length > 0 && typeof isMatchedActualAddress !== 'undefined') {
		var checkboxMatchedActualAddress = isMatchedActualAddress.find('input[type="checkbox"]');

		checkboxMatchedActualAddress.on("click", function(ev) {
			var actualAddress = $("#actual_address").val();
			var legalAddress = $("#legal_address");

			if (legalAddress.length > 0 && typeof actualAddress !== 'undefined') {
				if (typeof actualAddress !== 'undefined' && actualAddress.length > 0 && $(this).is(':checked')) {
					legalAddress.val(actualAddress);
				} else if (!$(this).is(':checked')) {
					legalAddress.val("");
				} else {
					ev.preventDefault();
					return false;
				}
			}
		});
    }

	var industry = $("#industry");
	var automobileIndustry = $("#automobile_industry");
	if (industry.length > 0 && typeof industry !== 'undefined' && automobileIndustry.length > 0 && typeof automobileIndustry !== 'undefined') {
		industry.on("change", function () {
			if ($(this).val() === "0") {
				automobileIndustry.show();
				required(automobileIndustry);
			} else {
				required(automobileIndustry, true);
				automobileIndustry.hide();
			}
		});
	}

	var email = $('.field-email');
	if(email){
		email.each(function(index, el) {
			var input = $(el).find("input");

			input.inputmask("email");
			input.blur(function(){
				if($(this).val() !== "") {
					if(emailCheck($(this).val()) === true) {
						hideError(input);
						email.removeClass('error');
						input.removeClass('sf_error');
						return;
					}
				}

				if(!input.hasClass('sf_error')) {
					showError(input, errorMsg.email);
					input.addClass('sf_error');
					email.addClass('error');
				}
			});
		});
	}

	var fPhone = $(".field-phone");
	if (fPhone.length > 0) {
		fPhone.each(function (index, el) {
			var phone = $(el).find("input");
			phone.inputmask("mask", {"mask": arScorpOptions['THEME']['PHONE_MASK']});
		});
	}

	var postcode = $('.field-postcode');
	if(postcode){
		postcode.each(function(index, el){
			var input = $(el).find("input");
			defaultInputMask(input, "999999", postcode, errorMsg.postcode);
		});
	}

	$('.delete-contact').on('click', function () {
		var _this = $(this).parents('.app-table_tr');
		if (_this.length) {
			var contact_id = parseInt(_this.data('contact-id')),
				url = document.location.pathname;

			_this.addClass('removed-item');
			setTimeout(function () {
				_this.remove();
			},500);

			$.ajax({
				method: "POST",
				url: url,
				data: { "delete-contact": "Y", "step": 1, "contact_id": contact_id }
			});
		}
	});

	$('[data-contact-id] .app-table_td').on('click', function () {
		var _this = $(this).parent('.app-table_tr');

		if (parseInt(_this.data('contact-id')) && !$(this).hasClass('delete-contact')) {
			$('#button-contact-' + parseInt(_this.data('contact-id'))).click();
		}
	});

	$('#go-to-step-2, #go-to-step-3, a.item-menu-form').on('click', function () {
		var buttonContact = $("#button-contact"),
			itemContactCommunication = $('.app-table_tr[data-contact-position-communication]'),
			itemContactDirector = $('.app-table_tr[data-contact-position-director]'),
			itemContactsAccountant = $('.app-table_tr[data-contact-position-accountant]');
			itemContactsIsMain = $('.app-table_tr[data-contact-is-main]');

			console.log(itemContactsIsMain.length);

		if (itemContactCommunication.length === 0 || itemContactDirector.length === 0 || itemContactsAccountant.length === 0 || itemContactsIsMain.length !== 1) {
			if(!buttonContact.hasClass('sf_error')) {
				buttonContact.addClass('sf_error');
				showError(buttonContact, errorMsg.contact);
			}

			scrollToError();

			return false;
		}
	});

	$('.save-draft, #button-contact, .edit-contact').on('click', function () {
		$("[required]").prop("required", false);
	});

	$("#actual_address, #legal_address").suggestions({
		token: "268235c5c930c9255863911700b9bb5c400d1a71",
		type: "ADDRESS",
		hint: false,
		constraints: {
			locations: {
				"country": "*"
			},
		},
		onSelect: function(suggestion, changed) {
			var idTypeAddress = $(this).attr("id"),
				dataAddress,
				url = document.location.pathname;

			if(idTypeAddress === "actual_address") {
				dataAddress = {
					"ACTUAL": {
						"POSTCODE": suggestion.data.postal_code,
						"COUNTRY": suggestion.data.country,
						"REGION": suggestion.data.region_with_type,
						"CITY": suggestion.data.city_with_type,
						"LOCALITY": suggestion.data.settlement_with_type,
						"TYPE_STREET": suggestion.data.street_type,
						"STREET": suggestion.data.street,
						"HOUSE": suggestion.data.house,
						"BUILDING": suggestion.data.block,
						"OFFICE": suggestion.data.flat,
					}
				};
			} else {
				dataAddress = {
					"LEGAL": {
						"POSTCODE": suggestion.data.postal_code,
						"COUNTRY": suggestion.data.country,
						"REGION": suggestion.data.region_with_type,
						"CITY": suggestion.data.city_with_type,
						"LOCALITY": suggestion.data.settlement_with_type,
						"TYPE_STREET": suggestion.data.street_type,
						"STREET": suggestion.data.street,
						"HOUSE": suggestion.data.house,
						"BUILDING": suggestion.data.block,
						"OFFICE": suggestion.data.flat,
					}
				};
			}

			$.ajax({
				method: "POST",
				url: url,
				data: {
					"save-address": "Y",
					"step": 1,
					"ADDRESS": dataAddress
				}
			});

			$('#is_matched_actual_address').find("input").prop('checked', false);
		}
	});

	$('input[type=file]').change(function (e) {
		let name = e.target.files[0].name;
		let size = (e.target.files[0].size/1000).toFixed();
		let blockResult = $(this).closest('.field-item').find('.field_result');
		let blockReady = $(this).closest('.field_ready');
		blockReady.addClass('hide');
		blockResult.find('.doc_doc').html(name+", "+size+" kb <span class='desc'>"+name+", "+size+" kb</span>");
		blockResult.removeClass('hide');
	});

	$('button.replace_file').on('click', function () {
		$(this).closest('.field-item').find('.field_ready').find('input[type=file]').click();
	});

	$('label.remove_file').on('click', function () {
		let blockResult = $(this).closest('.field_result');
		let blockReady = $(this).closest('.field-item').find('.field_ready');
		blockResult.addClass('hide');
		blockReady.find('input[type=file]').val(null);
		blockReady.removeClass('hide');
	})
});

function required(ob, remove = false) {
	var input = ob.find('input, select');
	var star = ob.find('.required-star');

    if(remove === true) {
	    input.prop("required", false);
	    star.hide();
    } else {
	    input.prop("required", true);
	    star.show();
    }
}

function inputUrlParam (name){
    var results = new RegExp('[\?&]' + name + '=([^]*)').exec(window.location.href);
    if (results === null){
        return null;
    } else{
        return results[1] || 0;
    }
}

function emailCheck(val) {
	var rex = /[^@]+@[^\.]+\..+/i;
	return rex.test(val);
}

function showError(input, mess) {
	input.after("<div class='field_error'>" + mess + "</div>");
}

function hideError(input) {
	input.siblings('.field_error').remove();
}

function defaultInputMask(field, mask, parent, msg) {
	var isRequired = field.attr("required");

    field.inputmask({
        placeholder: "",
        mask: mask,
        oncomplete: function () {
            hideError(field);
            parent.removeClass('error');
            field.removeClass('sf_error');
        },
        onincomplete: function () {
            if ((!field.hasClass('sf_error') && typeof isRequired !== 'undefined') || (!field.hasClass('sf_error'))) {
                showError(field, msg);
                field.addClass('sf_error');
                parent.addClass('error');
            }
        }
    });
}

function scrollToError () {
	try {
		$('html, body').animate({
			scrollTop: $(".field_error").offset().top
		}, 750);
	} catch ($e) {
		var block = this.block_list_el.querySelector(".field_error");
		var offsetTopBlock = block.offsetTop + block.scrollHeight;
		var counter = 0;
		var step = 1;
		var delay = 1000 / 50; // in 1 second 50 frames
		var timer = setInterval(function () {
			if (window.scrollY >= offsetTopBlock) {
				clearInterval(timer)
			} else {
				window.scrollTo(0, window.scrollY + counter);
				counter += step;
			}
		}, delay)
	}
};
