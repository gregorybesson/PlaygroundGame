/**
 * Cristi Citea (http://zend-form-generator.123easywebsites.com/)
 *
 * @link      https://github.com/patrioticcow/Zend-Form for the canonical source repository
 * @copyright Copyright (c) 2012 Cristi Citea
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Form_Generator
 */

//$('#generate_form')
var editLineText = function editLineText (id, type){

    var liId = $('#'+id);
    var liClass = $('.'+id);

    var requiredEdit    = liId.find('input[name="required"]').val() == 'false' ? 'no' : 'yes';
    var labelEdit       = liId.find('.main_label').html();
    var pli = liId.find('.form_input').attr('placeholder');
    var placeholderEdit = pli != 'undefined' ? pli : liId.find('input[name="placeholder_text"]').val();

    if(type == 'line_number' || type == 'line_phone'){
        placeholderEdit = liId.find('.form_number').attr('placeholder');
    }

    if(type == 'line_paragraph'){
        placeholderEdit = liId.find('.form_paragraph').attr('placeholder');
    }

    //right side
    //general
    var nameEdit        = liId.find('input[name="input_name"]').val();
    var classEdit       = liId.find('input[name="class"]').val();
    var idEdit          = liId.find('input[name="id"]').val();
    var minEdit         = liId.find('input[name="min"]').val();
    var maxEdit         = liId.find('input[name="max"]').val();

    //line_date
    var dateMinEdit     = liId.find('input[name="date_min"]').val();
    var dateMaxEdit     = liId.find('input[name="date_max"]').val();
    var dateMinVEdit    = liId.find('input[name="date_min_validate"]').val();
    var dateMaxVEdit    = liId.find('input[name="date_miax_validate"]').val();
    var localeEdit      = liId.find('input[name="locale"]').val();
    var dateFormatEdit  = liId.find('input[name="date_format"]').val();

    //line_line_number
    var validationEdit  = liId.find('input[name="validation"]').val();
    var html5Edit       = liId.find('input[name="html5"]').val();
    var numMinEdit      = liId.find('input[name="min_str"]').val();
    var numMaxEdit      = liId.find('input[name="max_str"]').val();

    //line_password_verify
    var tokenEdit       = liId.find('input[name="token"]').val();

    //line_email
    var invalidTypeEdit = liId.find('input[name="invalid_type"]').val();
    var isEmptyTypeEdit = liId.find('input[name="is_empty_type"]').val();

    //line_checkbox and line_radio
    var numberIdEdit    = liId.find('input[name="label_class"]').val();
    var numberClassEdit = liId.find('input[name="label_id"]').val();
    //TODO : get values from the checkbox

    //line_radio
    //TODO : get values from the radio

    //line_dropdown
    var notinarrayEdit = liId.find('input[name="number_notinarray"]').val();

    //line_upload
    var filesizeMinEdit          = liId.find('input[name="upload_filesize_min"]').val();
    var filesizeMaxEdit          = liId.find('input[name="upload_filesize_max"]').val();
    var filefilesizeMinEdit      = liId.find('input[name="upload_filefilessize_min"]').val();
    var filefilesizeMaxEdit      = liId.find('input[name="upload_filefilessize_max"]').val();
    var filecounMinEdit          = liId.find('input[name="upload_filecount_min"]').val();
    var filecounMaxEdit          = liId.find('input[name="upload_filecount_max"]').val();
    var fileexcludeextensionEdit = liId.find('input[name="upload_fileexcludeextension"]').val();
    var filemimetypeEdit         = liId.find('input[name="upload_filemimetype"]').val();
    var fileexcludemimetypeEdit  = liId.find('input[name="upload_fileexcludemimetype"]').val();
    var fileexistsEdit           = liId.find('input[name="upload_fileexists"]').val();
    var fileextensionEdit        = liId.find('input[name="upload_fileextension"]').val();
    var minheightEdit            = liId.find('input[name="upload_fileimagesize_minheight"]').val();
    var maxheightEdit            = liId.find('input[name="upload_fileimagesize_maxheight"]').val();
    var minwidthEdit             = liId.find('input[name="upload_fileimagesize_minwidth"]').val();
    var maxwidthEdit             = liId.find('input[name="upload_fileimagesize_maxwidth"]').val();
    var fileiscompressedEdit     = liId.find('input[name="upload_fileiscompressed"]').val();
    var fileisimageEdit          = liId.find('input[name="upload_fileisimage"]').val();
    var filewordcountMinEdit     = liId.find('input[name="upload_filewordcount_min"]').val();
    var filewordcountMaxEdit     = liId.find('input[name="upload_filewordcount_max"]').val();

    if(type == 'line_text'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);
    }

    if(type == 'line_date'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);

        liClass.find('input[name="date_min"]').val(dateMinEdit);
        liClass.find('input[name="date_max"]').val(dateMaxEdit);
        liClass.find('input[name="date_min_validate"]').val(dateMinVEdit);
        liClass.find('input[name="date_max_validate"]').val(dateMaxVEdit);
        liClass.find('select[name="locale"]').val(localeEdit);
        liClass.find('input[name="date_format"]').val(dateFormatEdit);
    }

    if(type == 'line_number'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);

        liClass.find('select[name="valid_type"]').val(validationEdit);
        var html5PLaceholder = liClass.find('input[name="number_html5"]');
        html5Edit == 1 ? html5PLaceholder.prop('checked', true) : html5PLaceholder.prop('checked', false);
        liClass.find('input[name="number_min_str"]').val(numMinEdit);
        liClass.find('input[name="number_max_str"]').val(numMaxEdit);
    }

    if(type == 'line_phone'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);
    }

    if(type == 'line_password'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);
    }

    if(type == 'line_password_verify'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);

        liClass.find('input[name="token"]').val(tokenEdit);
    }

    if(type == 'line_email'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);

        liClass.find('input[name="invalid_type"]').val(invalidTypeEdit);
        liClass.find('input[name="is_empty_type"]').val(isEmptyTypeEdit);
    }

    if(type == 'line_paragraph'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);
    }

    if(type == 'line_checkbox'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);

        liClass.find('input[name="number_label_class"]').val(numberIdEdit);
        liClass.find('input[name="number_label_id"]').val(numberClassEdit);
    }

    if(type == 'line_radio'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);

        liClass.find('input[name="number_label_class"]').val(numberIdEdit);
        liClass.find('input[name="number_label_id"]').val(numberClassEdit);
    }

    if(type == 'line_dropdown'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);

        liClass.find('input[name="number_notinarray"]').val(notinarrayEdit);
    }

    if(type == 'line_upload'){
        defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit);

        liClass.find('input[name="filesize_min"]').val(filesizeMinEdit);
        liClass.find('input[name="filesize_max"]').val(filesizeMaxEdit);
        liClass.find('input[name="filefilessize_min"]').val(filefilesizeMinEdit);
        liClass.find('input[name="filefilessize_max"]').val(filefilesizeMaxEdit);
        liClass.find('input[name="filecount_min"]').val(filecounMinEdit);
        liClass.find('input[name="filecount_max"]').val(filecounMaxEdit);
        liClass.find('input[name="fileexcludeextension"]').val(fileexcludeextensionEdit);
        liClass.find('input[name="filemimetype"]').val(filemimetypeEdit);
        liClass.find('input[name="fileexcludemimetype"]').val(fileexcludemimetypeEdit);
        liClass.find('input[name="filemimetype"]').val(fileexistsEdit);
        liClass.find('input[name="fileexists"]').val(fileextensionEdit);
        liClass.find('input[name="fileimagesize_minheight"]').val(minheightEdit);
        liClass.find('input[name="fileimagesize_maxheight"]').val(maxheightEdit);
        liClass.find('input[name="fileimagesize_minwidth"]').val(minwidthEdit);
        liClass.find('input[name="fileimagesize_maxwidth"]').val(maxwidthEdit);
        liClass.find('input[name="fileiscompressed"]').val(fileiscompressedEdit);
        liClass.find('input[name="fileisimage"]').val(fileisimageEdit);
        liClass.find('input[name="filewordcount_min"]').val(filewordcountMinEdit);
        liClass.find('input[name="filewordcount_max"]').val(filewordcountMaxEdit);
    }

    console.log(liId);
    console.log(liClass);

}

var defaultValues = function defaultValues(liClass, labelEdit, nameEdit, placeholderEdit, requiredEdit, minEdit, maxEdit, classEdit, idEdit){
    liClass.find('input[name="text_label"]').val(labelEdit),
    liClass.find('input[name="input_name"]').val(nameEdit),
    liClass.find('input[name="placeholder_text"]').val(placeholderEdit),
    liClass.find('select[name="required"]').val(requiredEdit),
    liClass.find('input[name="number_min"]').val(minEdit),
    liClass.find('input[name="number_max"]').val(maxEdit),
    liClass.find('input[name="number_class"]').val(classEdit),
    liClass.find('input[name="number_id"]').val(idEdit);
};
