/**
 * Cristi Citea (http://zend-form-generator.123easywebsites.com/)
 *
 * @link      https://github.com/patrioticcow/Zend-Form for the canonical source repository
 * @copyright Copyright (c) 2012 Cristi Citea
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Form_Generator
 */

$(document).ready(function()
{
    /**
     * add_form_text
     */
	$(document).on("click", "#add_form_text", function(e){
		e.preventDefault();
		addForm($('#add_form_data'), 'input', 'form_input', 'text');
	});

    /**
     * add_form_url
     */
	$(document).on("click", "#add_form_url", function(e){
		e.preventDefault();
		addForm($('#add_form_data'), 'input', 'form_input', 'url');
	});

    /**
     * add_form_datye
     */
	$(document).on("click", "#add_form_date", function(e){
		e.preventDefault();
		addForm($('#add_form_data'), 'input', 'form_input', 'date');
	});

    /**
     * add_form_password
     */
    $(document).on("click", "#add_form_password", function(e){
        e.preventDefault();
        addForm($('#add_form_data'), 'input', 'form_input', 'password');
    });

    /**
     * add_form_password_verify
     */
    $(document).on("click", "#add_form_password_verify", function(e){
        e.preventDefault();
        addForm($('#add_form_data'), 'input', 'form_input', 'password_verify');
    });

    /**
     * add_form_email
     */
    $(document).on("click", "#add_form_email", function(e){
        e.preventDefault();
        addForm($('#add_form_data'), 'input', 'form_input', 'email');
    });

    /**
     * add_form_number
     */
    $(document).on("click", "#add_form_number", function(e){
		e.preventDefault();
		addForm($('#add_form_data'), 'input', 'form_number', 'number');
	});

    /**
     * add_form_phone
     */
    $(document).on("click", "#add_form_phone", function(e){
		e.preventDefault();
		addForm($('#add_form_data'), 'input', 'form_data', 'number');
	});

    /**
     * add_form_paragraph
     */
	$(document).on("click", "#add_form_paragraph", function(e){
		e.preventDefault();
		addForm($('#add_form_data'), 'textarea', 'form_paragraph', 'paragraph');
	});

    /**
     * add_form_fileupload
     */
    $(document).on("click", "#add_form_fileupload", function(e){
        e.preventDefault();
        addForm($('#add_form_data'), 'input', 'form_input', 'fileupload');
    });

    /**
     * add_form_checkbox
     */
	$(document).on("click", "#add_form_checkbox", function(e){
		e.preventDefault();
		addForm($('#add_form_data'), 'input', 'form_checkbox', 'checkbox');
	});

    $(document).on("click", "button:contains('remove')", function(e){
        e.preventDefault();
        $(this).parent('li').remove();

        var remIdTemp = $(this).attr('id');
        var mainCheckbox = $('code').text();
        $('#' + mainCheckbox + ' #check_' + remIdTemp).remove();
    });

    $(document).on("click", "button:contains('set default')", function(e){
        e.preventDefault();
        var defId = $(this).attr('id').replace(/[^\d.]/g, "");
        var mainCheckbox = $('code').text();

        $('#' + mainCheckbox).find('input').removeAttr('checked');

        $('#' + mainCheckbox).find('#form_checkbox_' + defId).attr({'checked':'checked'})
        .attr({'type':'checkbox'}); // don't know why it doesn't work without this yet
    });

    $(document).on("click", "#add_new_checkbox_field", function(e){
        e.preventDefault();
        var mainCheckbox = $('code').text();
        var prevLi = $(this).parent('ul').children('li').last();
        var prevId = prevLi.find("button:contains('remove')").attr('id').replace(/[^\d.]/g, "");
        var nextIdNr = parseFloat(prevId) + 1;
        var nextId = 'rem' + nextIdNr; //rem1
        var nextIdNext = 'check_rem' + nextIdNr;

        // clone in the edit side
        prevLi.clone()
            .insertAfter(prevLi)
            .find(".input-small").attr({'name':nextId})
            .end()
            .find("button:contains('remove')").attr({'id':nextId})
            .end()
            .find("button:contains('set default')").attr({'id':'def' + nextIdNr});

        // clone in the view side
        $('#' + mainCheckbox)
            .find('span').last()
            .clone().insertBefore('#' + mainCheckbox + ' .delete_li')
            .attr({'id':nextIdNext})
            .find('input').attr({'id': 'form_checkbox_' + nextIdNr});

        $("button:contains('remove')").removeClass("disabled");
        $("button:contains('remove')").prop("disabled", false);
    });

    /**
     * add_form_multiple_choices
     */
    $(document).on("click", "#add_form_radio", function(e){
        e.preventDefault();
        addForm($('#add_form_data'), 'input', 'form_radio', 'radio');
    });

    $(document).on("click", "button:contains('remove')", function(e){
        e.preventDefault();
        $(this).parent('li').remove();

        var remIdTemp = $(this).attr('id');
        var mainRadio = $('code').text();
        $('#' + mainRadio + ' #radio_' + remIdTemp).remove();
    });

    $(document).on("click", "button:contains('set default')", function(e){
        e.preventDefault();
        var defId = $(this).attr('id').replace(/[^\d.]/g, "");
        var mainRadio = $('code').text();

        $('#' + mainRadio).find('input').removeAttr('checked');
        $('#' + mainRadio).find('#form_radio_' + defId).attr({'checked':'checked'});
    });

    $(document).on("click", "#add_new_radio_field", function(e){
        e.preventDefault();
        var mainRadio = $('code').text();
        var prevLi = $(this).parent('ul').children('li').last();
        var prevId = prevLi.find("button:contains('remove')").attr('id').replace(/[^\d.]/g, "");
        var nextIdNr = parseFloat(prevId) + 1;
        var nextId = 'rem' + nextIdNr; //rem1
        var nextIdNext = 'radio_rem' + nextIdNr;

        // clone in the edit side
        prevLi.clone()
            .insertAfter(prevLi)
            .find(".input-small").attr({'name':nextId})
            .end()
            .find("button:contains('remove')").attr({'id':nextId})
            .end()
            .find("button:contains('set default')").attr({'id':'def' + nextIdNr});

        // clone in the view side
        $('#' + mainRadio)
            .find('span').last()
            .clone().insertBefore('#' + mainRadio + ' .delete_li')
            .attr({'id':nextIdNext})
            .find('input').attr({'id': 'form_radio_' + nextIdNr});
        
        $("button:contains('remove')").removeClass("disabled");
        $("button:contains('remove')").prop("disabled", false);
    });

    /**
     * add_form_dropdown
     */
    $(document).on("click", "#add_form_dropdown", function(e){
        e.preventDefault();
        addForm($('#add_form_data'), 'input', 'form_dropdown', 'dropdown');
    });

    $(document).on("click", "button:contains('remove')", function(e){
        e.preventDefault();
        
        // if only one remove button is left, disable it
        if($("button:contains('remove')").length == 1) {
            $("button:contains('remove')").addClass("disabled");
            $("button:contains('remove')").prop("disabled", true);
        }

        $(this).parent('li').remove();

        var remIdTemp = $(this).attr('id');
        var mainRadio = $('code').text();
        $('#' + mainRadio + ' #dropdown_' + remIdTemp).remove();
    });

    $(document).on("click", "button:contains('set default')", function(e){
        e.preventDefault();
        var defId = $(this).attr('id').replace(/[^\d.]/g, "");
        var mainRadio = $('code').text();

        $('#' + mainRadio).find('option').removeAttr('selected');
        $('#dropdown_rem' + defId).attr({'selected':'selected'});
    });

    $(document).on("click", "#add_new_dropdown_field", function(e){
        e.preventDefault();
        var mainRadio = $('code').text();
        var prevLi = $(this).parent('ul').children('li').last();
        var prevId = prevLi.find("button:contains('remove')").attr('id').replace(/[^\d.]/g, "");
        var nextIdNr = parseFloat(prevId) + 1;
        var nextId = 'rem' + nextIdNr; //rem1
        var nextIdNext = 'dropdown_rem' + nextIdNr;

        // clone in the edit side
        prevLi.clone()
            .insertAfter(prevLi)
            .find(".input-small").attr({'name':nextId})
            .end()
            .find("button:contains('remove')").attr({'id':nextId})
            .end()
            .find("button:contains('set default')").attr({'id':'def' + nextIdNr});

        // clone in the view side
        $('#' + mainRadio)
            .find('option').last()
            .clone().appendTo('#' + mainRadio + ' select')
            .attr({'id':nextIdNext});

        $("button:contains('remove')").removeClass("disabled");
        $("button:contains('remove')").prop("disabled", false);
    });

    /**
     * add_form_credit_card
     */
    $(document).on("click", "#add_form_credit_card", function(e){
        e.preventDefault();
        addForm($('#add_form_data'), 'input', 'form_input', 'credit_card');
    });

    /**
     * add_form_hidden
     */
    $(document).on("click", "#add_form_hidden", function(e){
        e.preventDefault();
        addForm($('#add_form_data'), 'input', 'form_input', 'hidden');
    });

    /**
     * edit individual field form preferences
     * @param formId
     * @param fieldType
     * @param formClass
     */
	var addForm = function addForm(formId, fieldType, formClass, specificType){

		var formField = formId.attr('class');
		var formJson = formId.serializeFormJSON();
		var formSel = $('#' + formField).find('.' + formClass);
		var formReq = $('#' + formField + ' input[name="required"]');

        // set label text
		$('#' + formField + ' label.main_label').html(formJson.text_label);

        // set placeholder text
		formSel.attr("placeholder", formJson.placeholder_text);

        // set hidden required
        formJson.required === 'yes' ? formReq.attr("value", "true") : formReq.attr("value", "false");

        // set token
        if(specificType === 'password_verify'){
            $('#' + formField + ' input[name="token"]').attr("value", formJson.token);
        }

        // set input name
        $('#' + formField + ' input[name="input_name"]').attr("value", formJson.input_name);

        // set hidden min
        $('#' + formField + ' input[name="min"]').attr("value", formJson.number_min);

        // set hidden max
        $('#' + formField + ' input[name="max"]').attr("value", formJson.number_max);

        // set hidden class
        $('#' + formField + ' input[name="class"]').attr("value", formJson.number_class);

        // set hidden id
        $('#' + formField + ' input[name="id"]').attr("value", formJson.number_id);

        // set hidden label class
        $('#' + formField + ' input[name="label_class"]').attr("value", formJson.number_label_class);

        // set hidden label id
        $('#' + formField + ' input[name="label_id"]').attr("value", formJson.number_label_id);

        // set email
        if(specificType === 'email'){
            $('#' + formField + ' input[name="invalid_type"]').attr("value", formJson.invalid_type);
            $('#' + formField + ' input[name="is_empty_type"]').attr("value", formJson.is_empty_type);
        }

        // set credit card
        if(specificType === 'credit_card'){
            $('#' + formField + ' input[name="institutes"]').attr("value", formJson.institutes);
        }

        // set hidden
        if(specificType === 'hidden'){
            $('#' + formField + ' input[name="value"]').attr("value", formJson.input_value);
        }

        // set date
        if(specificType === 'date'){
            $('#' + formField + ' input[name="date_min_validate"]').attr("value", formJson.date_min_validate);
            $('#' + formField + ' input[name="date_max_validate"]').attr("value", formJson.date_max_validate);
            $('#' + formField + ' input[name="date_min"]').attr("value", formJson.date_min);
            $('#' + formField + ' input[name="date_max"]').attr("value", formJson.date_max);
            $('#' + formField + ' input[name="locale"]').attr("value", formJson.locale);
            $('#' + formField + ' input[name="date_format"]').attr("value", formJson.date_format);
        }

        // transfer text content form the edit side to the main form side
        if(specificType === 'checkbox'){
            $.each(formJson, function(index, value) {
                $('#' + formField + ' #check_' + index).find('label.checkbox').text(value);
            });
        }

        if(specificType === 'radio'){
            $.each(formJson, function(index, value) {
                $('#' + formField + ' #radio_' + index).find('label.radio').text(value);
            });
        }

        if(specificType === 'fileupload'){
            $('#' + formField + ' input[name="filesize_min"]').attr("value", formJson.upload_filesize_min);
            $('#' + formField + ' input[name="filesize_max"]').attr("value", formJson.upload_filesize_max);
            $('#' + formField + ' input[name="filefilessize_min"]').attr("value", formJson.upload_filefilessize_min);
            $('#' + formField + ' input[name="filefilessize_max"]').attr("value", formJson.upload_filefilessize_max);
            $('#' + formField + ' input[name="filecount_min"]').attr("value", formJson.upload_filecount_min);
            $('#' + formField + ' input[name="filecount_max"]').attr("value", formJson.upload_filecount_max);
            $('#' + formField + ' input[name="fileextension"]').attr("value", formJson.upload_fileextension);
            $('#' + formField + ' input[name="fileexcludeextension"]').attr("value", formJson.upload_fileexcludeextension);
            $('#' + formField + ' input[name="filemimetype"]').attr("value", formJson.upload_filemimetype);
            $('#' + formField + ' input[name="fileexcludemimetype"]').attr("value", formJson.upload_fileexcludemimetype);
            $('#' + formField + ' input[name="fileexists"]').attr("value", formJson.upload_fileexists);
            $('#' + formField + ' input[name="fileimagesize_minheight"]').attr("value", formJson.upload_fileimagesize_minheight);
            $('#' + formField + ' input[name="fileimagesize_maxheight"]').attr("value", formJson.uploadfileimagesize_maxheight);
            $('#' + formField + ' input[name="fileimagesize_minwidth"]').attr("value", formJson.upload_fileimagesize_minwidth);
            $('#' + formField + ' input[name="fileimagesize_maxwidth"]').attr("value", formJson.upload_fileimagesize_maxwidth);
            $('#' + formField + ' input[name="fileiscompressed"]').attr("value", formJson.upload_fileiscompressed);
            $('#' + formField + ' input[name="fileisimage"]').attr("value", formJson.upload_fileisimage);
            $('#' + formField + ' input[name="filewordcount_min"]').attr("value", formJson.upload_filewordcount_min);
            $('#' + formField + ' input[name="filewordcount_max"]').attr("value", formJson.upload_filewordcount_max);
        }

        if(specificType === 'number'){
            var html5 = formJson.number_html5 ? 1 : 0;

            $('#' + formField + ' input[name="validation"]').attr("value", formJson.valid_type);
            $('#' + formField + ' input[name="html5"]').attr("value", html5);
            $('#' + formField + ' input[name="min_str"]').attr("value", formJson.number_min_str);
            $('#' + formField + ' input[name="max_str"]').attr("value", formJson.number_max_str);
        }

        if(specificType === 'dropdown'){
            $.each(formJson, function(index, value) {
                $('#' + formField).find(' #dropdown_' + index).html(value);
            });

            $('#' + formField + ' input[name="number_notinarray"]').attr("value", formJson.number_notinarray);
            console.log(formField);
        }

        $('#' + formField).css({'background-color' : '#87ffc1'}).animate({backgroundColor: '#ffffff'},{duration:1000});
	};

});
