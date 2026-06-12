var initDropforUpdate = function(id, width, height) {
    $(".upload_image" + id).dropzone({
        paramName: "image",
        maxFilesize: 200,
        url: uploadUrl,
        timeout: 9000000,
        uploadMultiple: false,
        acceptedFiles: "image/*",
        previewTemplate: '<i class="hidden preview-image"></i>',
        parallelUploads: 1,
        success: function(file, data) {
            $(".upload_image" + id)
                .parents(".employee__profile-chnage")
                .find(".employee__profile-preview-box")
                .attr(
                    "style",
                    "background-image: url(" + data.thumb_image + ")"
                );
            $("#user_image").val(data.image);
            /*if($('#user_id').val() == 'false'){
            $(".kt-header__topbar-item--user").find('img').attr('src',data.thumb_image);
            $(".kt-user-card__avatar").find('img').attr('src',data.thumb_image);
            }*/
        },
        uploadprogress: function(file, progress, bytesent) {
            //var elm = $(this)[0].element;
            var _prt = $(".upload_image" + id).parent(".form-group");
            //_prt.find('#image-progress').html(parseInt(progress) + '%');
        },
        sending: function(file, xhr, formData) {
            var elm = $(this)[0].element;
            formData.append(
                "_token",
                $('meta[name="csrf-token"]').attr("content")
            );
            formData.append("width", width);
            formData.append("height", height);
            formData.append("id", $("#user_id").val());
        },
        error: function(file, data) {
            var elm = $(this)[0].element;
            var _prt = $(".upload_image" + id).parent(".employee__profile-chnage");
            //_prt.find('#image-progress').html('');
            _prt.find(".help-block").html("Image should be JPEG, PNG or JPG");
            jQuery.each(data.errors, function(i, _msg) {
                //_prt.find('#image-progress').html('');
                //toastr.error(_msg[0]);
                _prt.addClass("error");
                _prt.find(".help-block").html(_msg[0]);
            });
        }
    });
};

var initDropforUpdateDoc = function(id, pdf = true) {
    var _prt = $(".upload_doc" + id).parent(".form__input");
    _prt.find(".help-block").html("");
    //event.preventDefault();
    $(".upload_doc" + id).dropzone({
        paramName: "image",
        maxFilesize: 200,
        url: uploadDocUrl,
        timeout: 9000000,
        uploadMultiple: false,
        acceptedFiles: "image/*"+(pdf ? ",application/pdf" : ""),
        previewTemplate: '<i class="hidden preview-image"></i>',
        parallelUploads: 1,
        success: function(file, data) {
            $(".upload_doc" + id)
                .parent(".form__input")
                .find(".uploaded_doc")
                .attr(
                    "href",
                    data.thumb_image
                ).html(data.image).show();
                $(".upload_doc" + id)
                .parent(".form__input")
                .find(".doc_field")
                .val(data.image)
        },
        uploadprogress: function(file, progress, bytesent) {
            var _prt = $(".upload_doc" + id).parent(".form__input");
        },
        sending: function(file, xhr, formData) {
            var elm = $(this)[0].element;
            formData.append(
                "_token",
                $('meta[name="csrf-token"]').attr("content")
            );
            formData.append("pdf", pdf);
        },
        error: function(file, data) {
            var elm = $(this)[0].element;
            var _prt = $(".upload_doc" + id).parent(".form__input");
            _prt.find(".help-block").html("Image should be JPEG, PNG, JPG or PDF");
            jQuery.each(data.errors, function(i, _msg) {
                _prt.addClass("error");
                _prt.find(".help-block").html(_msg[0]);
            });
        }
    });
};

function removeAttachment(id, image){
    const link = $(".upload_doc" + id)
    .parent(".form__input")
    .find('.uploaded_doc').find('.'+image.split('.')[0]).remove();
    let _value = $(".upload_doc" + id)
    .parent(".form__input")
    .find('.doc_field').val()
    _value = JSON.parse(_value)

    let index = _value.indexOf(image);

    // Check if the value exists in the array
    if (index > -1) {
        _value.splice(index, 1); // Remove 1 element at the found index
    }

    $(".upload_doc" + id)
                    .parent(".form__input")
                    .find('.doc_field').val(JSON.stringify(_value))
    console.log('link', link)
}

var initDropforUpdateMultipleDoc = function(id, pdf = true) {
    var _prt = $(".upload_doc" + id).parent(".form__input");
    _prt.find(".help-block").html("");
    //event.preventDefault();
    $(".upload_doc" + id).dropzone({
        paramName: "image",
        maxFilesize: 200,
        url: uploadDocUrl,
        timeout: 9000000,
        uploadMultiple: false,
        acceptedFiles: "image/*"+(pdf ? ",application/pdf" : ""),
        previewTemplate: '<i class="hidden preview-image"></i>',
        parallelUploads: 1,
        success: function(file, data) {
            $(".upload_doc" + id)
                .parent(".form__input")
                .find(".uploaded_doc")
                .append(`
                    <div class="${(data.image).split('.')[0]}"><a href="${data.thumb_image}" target="_blank">${data.image}</a> <i class="fa fa-trash text-danger" onclick="removeAttachment('${id}', '${data.image}')"></i> <br></div>
                    `);
            let exists = $(".upload_doc" + id)
            .parent(".form__input")
            .find(".doc_field")
            .val();
            
            if(exists){
                exists = JSON.parse(exists)
                exists.push(data.image)
                    $(".upload_doc" + id)
                    .parent(".form__input")
                    .find(".doc_field")
                    .val(JSON.stringify(exists))
                }else{
                    $(".upload_doc" + id)
                    .parent(".form__input")
                    .find(".doc_field")
                    .val(JSON.stringify([data.image]))
                }
                    
        },
        uploadprogress: function(file, progress, bytesent) {
            var _prt = $(".upload_doc" + id).parent(".form__input");
        },
        sending: function(file, xhr, formData) {
            var elm = $(this)[0].element;
            formData.append(
                "_token",
                $('meta[name="csrf-token"]').attr("content")
            );
            formData.append("pdf", pdf);
        },
        error: function(file, data) {
            var elm = $(this)[0].element;
            var _prt = $(".upload_doc" + id).parent(".form__input");
            _prt.find(".help-block").html("Image should be JPEG, PNG, JPG or PDF");
            jQuery.each(data.errors, function(i, _msg) {
                _prt.addClass("error");
                _prt.find(".help-block").html(_msg[0]);
            });
        }
    });
};