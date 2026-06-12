var updateProfile = function (_form) {
    event.preventDefault();

        if (CKEDITOR.instances.description) {
        CKEDITOR.instances.description.updateElement();
    }

    var frm = jQuery(_form);
    var btn = frm.find(".saveBtn");
    axios({
            method: 'post',
            url: frm.attr('action'),
            data: frm.serialize(),
            onUploadProgress: function (progressEvent) {
                startLoader(btn);
            }
        })
        .then(function (response) {
            console.log(response.data)
            if (response.data.redirect) {
                $(window).scrollTop(0);
                frm.find('.form-control').removeClass('error');
                frm.find('.help-block').html('');
                if (response.data.success) {
                    toastr.success(response.data.message);
                    $('input').attr('readonly', true);
                    $('select').attr('disabled', true);
                    endLoader(btn);
                    btn.hide();

                    if(response.data.redirect){
                        window.location.href = response.data.redirect
                    }

                    if (Dropzone.instances.length > 0) {
                        Dropzone.instances.forEach(dz => dz.destroy())
                        Dropzone.instances.forEach(dz => dz.destroy())
                        Dropzone.instances.forEach(dz => dz.destroy())
                    }
                    $('#editBtn').removeClass('invisible');
                    $('#updateProfilePic').hide();
                    $('#updateProfilePic').removeClass('upload_image_profile');
                    $('#updateProfilePic').removeClass('dz-clickable');
                } else {
                    toastr.error(response.data.message);
                    endLoader(btn);
                }
            }
        })
        .catch(function (error) {
            endLoader(btn);
            if (error.response) {
                if (error.response.status == "419") {
                   toastr.error('Page session expired');
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                }
                var errors = error.response.data.errors;
                frm.find('.form-control').removeClass('error');
                frm.find('.help-block').html('');
                var checkFirstEle = 0;
                jQuery.each(errors, function (i, _msg) {
                    var el = frm.find("[name=" + i + "]");
                    if (checkFirstEle == 0) {
                        el.focus();
                        checkFirstEle++;
                    }
                    el.addClass('error');
                    el.parents('.form__input').find('.help-block').html(_msg[0]);
                });
            }
        });
    return false;
};

var updateProfiles = function (_form) {
    event.preventDefault();

       

    var frm = jQuery(_form);
    var btn = frm.find(".saveBtn");
    axios({
            method: 'post',
            url: frm.attr('action'),
            data: frm.serialize(),
            onUploadProgress: function (progressEvent) {
                startLoader(btn);
            }
        })
        .then(function (response) {
            console.log(response.data)
            if (response.data.redirect) {
                $(window).scrollTop(0);
                frm.find('.form-control').removeClass('error');
                frm.find('.help-block').html('');
                if (response.data.success) {
                    toastr.success(response.data.message);
                    $('input').attr('readonly', true);
                    $('select').attr('disabled', true);
                    endLoader(btn);
                    btn.hide();

                    if(response.data.redirect){
                        window.location.href = response.data.redirect
                    }

                    if (Dropzone.instances.length > 0) {
                        Dropzone.instances.forEach(dz => dz.destroy())
                        Dropzone.instances.forEach(dz => dz.destroy())
                        Dropzone.instances.forEach(dz => dz.destroy())
                    }
                    $('#editBtn').removeClass('invisible');
                    $('#updateProfilePic').hide();
                    $('#updateProfilePic').removeClass('upload_image_profile');
                    $('#updateProfilePic').removeClass('dz-clickable');
                } else {
                    toastr.error(response.data.message);
                    endLoader(btn);
                }
            }
        })
        .catch(function (error) {
            endLoader(btn);
            if (error.response) {
                if (error.response.status == "419") {
                   toastr.error('Page session expired');
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                }
                var errors = error.response.data.errors;
                frm.find('.form-control').removeClass('error');
                frm.find('.help-block').html('');
                var checkFirstEle = 0;
                jQuery.each(errors, function (i, _msg) {
                    var el = frm.find("[name=" + i + "]");
                    if (checkFirstEle == 0) {
                        el.focus();
                        checkFirstEle++;
                    }
                    el.addClass('error');
                    el.parents('.form__input').find('.help-block').html(_msg[0]);
                });
            }
        });
    return false;
};

var updateProfileimg = function (_form) {
    
    event.preventDefault();
    var frm = jQuery(_form);
    var btn = frm.find(".saveBtn");

    // Create FormData instead of serialize() for multipart
    var formData = new FormData(frm[0]);

    newImages.forEach(file => {
        formData.append('images[]', file);
    });

        formData.append('removed_images', JSON.stringify(removedOldImages));


    axios({
        method: 'post',
        url: frm.attr('action'),
        data: formData,
        headers: {
            'Content-Type': 'multipart/form-data' // Important for file upload
        },
        onUploadProgress: function (progressEvent) {
            startLoader(btn);
        }
    })
    .then(function (response) {
        console.log(response.data)
        if (response.data.redirect) {
            $(window).scrollTop(0);
            frm.find('.form-control').removeClass('error');
            frm.find('.help-block').html('');
            if (response.data.success) {
                toastr.success(response.data.message || 'Saved successfully!', "Success", {
                    closeButton:true,
                    progressBar:true,
                    timeOut:5000
                });
                $('input').attr('readonly', true);
                $('select').attr('disabled', true);
                endLoader(btn);
                btn.hide();

                if(response.data.redirect){
                    window.location.href = response.data.redirect
                }

                if (Dropzone.instances.length > 0) {
                    Dropzone.instances.forEach(dz => dz.destroy());
                }
                $('#editBtn').removeClass('invisible');
                $('#updateProfilePic').hide();
                $('#updateProfilePic').removeClass('upload_image_profile');
                $('#updateProfilePic').removeClass('dz-clickable');
            } else {
                toastr.error(response.data.message);
                endLoader(btn);
            }
        }
    })
    .catch(function (error) {
        endLoader(btn);
       
        if (error.response) {
            if (error.response.status == 419) {
                toastr.error('Page session expired');
                setTimeout(function () {
                    location.reload();
                }, 2000);
                return;
            }

            // Laravel validation errors
            var errors = error.response.data.errors;
            if(errors){
                Object.values(errors).flat().forEach(msg => {
                    toastr.error(msg, "Validation Error", {
                        closeButton:true,
                        progressBar:true,
                        timeOut:5000
                    });
                });
            } else if(error.response.data.message){
                toastr.error(error.response.data.message || 'Something went wrong');
            }
        } else {
            toastr.error('Something went wrong');
        }
    });

    return false;
};

var updateProfileimgs = function (_form) {
    event.preventDefault();

    var frm = jQuery(_form);
    var btn = frm.find(".saveBtn");

    // Create FormData instead of serialize() for multipart
    var formData = new FormData(frm[0]);

 

    axios({
        method: 'post',
        url: frm.attr('action'),
        data: formData,
        headers: {
            'Content-Type': 'multipart/form-data' // Important for file upload
        },
        onUploadProgress: function (progressEvent) {
            startLoader(btn);
        }
    })
    .then(function (response) {
        console.log(response.data)
        if (response.data.redirect) {
            $(window).scrollTop(0);
            frm.find('.form-control').removeClass('error');
            frm.find('.help-block').html('');
            if (response.data.success) {
                 toastr.success(response.data.message || 'Saved successfully!', "Success", {
                    closeButton:true,
                    progressBar:true,
                    timeOut:5000
                });
                $('input').attr('readonly', true);
                $('select').attr('disabled', true);
                endLoader(btn);
                btn.hide();

                if(response.data.redirect){
                    window.location.href = response.data.redirect
                }

                if (Dropzone.instances.length > 0) {
                    Dropzone.instances.forEach(dz => dz.destroy());
                }
                $('#editBtn').removeClass('invisible');
                $('#updateProfilePic').hide();
                $('#updateProfilePic').removeClass('upload_image_profile');
                $('#updateProfilePic').removeClass('dz-clickable');
            } else {
                toastr.error(response.data.message);
                endLoader(btn);
            }
        }
    })
    .catch(function (error) {
        endLoader(btn);
         if (error.response) {
            if (error.response.status == 419) {
                toastr.error('Page session expired');
                setTimeout(function () {
                    location.reload();
                }, 2000);
                return;
            }

            // Laravel validation errors
            var errors = error.response.data.errors;
            if(errors){
                Object.values(errors).flat().forEach(msg => {
                    toastr.error(msg, "Validation Error", {
                        closeButton:true,
                        progressBar:true,
                        timeOut:5000
                    });
                });
            } else if(error.response.data.message){
                toastr.error(error.response.data.message || 'Something went wrong');
            }
        } else {
            toastr.error('Something went wrong');
        }
    });

    return false;
};


var formSubmitWithSignature = function (_form) {
    event.preventDefault();
    if (customerSignaturePad.isEmpty()) {
        alert('Please provide customer signature.');
        event.preventDefault(); 
    } else {
        document.getElementById('customer_signature_data').value = customerSignaturePad.toDataURL();
    }

    if (driverSignaturePad.isEmpty()) {
        alert('Please provide driver signature.');
        event.preventDefault(); 
    } else {
        document.getElementById('driver_signature_data').value = driverSignaturePad.toDataURL();
    }
    var frm = jQuery(_form);
    var btn = frm.find(".saveBtn");
    axios({
            method: 'post',
            url: frm.attr('action'),
            data: frm.serialize(),
            onUploadProgress: function (progressEvent) {
                startLoader(btn);
            }
        })
        .then(function (response) {
            console.log(response.data)
            if (response.data.redirect) {
                $(window).scrollTop(0);
                frm.find('.form-control').removeClass('error');
                frm.find('.help-block').html('');
                if (response.data.success) {
                    toastr.success(response.data.message);
                    $('input').attr('readonly', true);
                    $('select').attr('disabled', true);
                    endLoader(btn);
                    btn.hide();

                    if(response.data.redirect){
                        window.location.href = response.data.redirect
                    }

                    if (Dropzone.instances.length > 0) {
                        Dropzone.instances.forEach(dz => dz.destroy())
                        Dropzone.instances.forEach(dz => dz.destroy())
                        Dropzone.instances.forEach(dz => dz.destroy())
                    }
                    $('#editBtn').removeClass('invisible');
                    $('#updateProfilePic').hide();
                    $('#updateProfilePic').removeClass('upload_image_profile');
                    $('#updateProfilePic').removeClass('dz-clickable');
                } else {
                    toastr.error(response.data.message);
                    endLoader(btn);
                }
            }
        })
        .catch(function (error) {
            endLoader(btn);
            if (error.response) {
                if (error.response.status == "419") {
                   toastr.error('Page session expired');
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                }
                var errors = error.response.data.errors;
                frm.find('.form-control').removeClass('error');
                frm.find('.help-block').html('');
                var checkFirstEle = 0;
                jQuery.each(errors, function (i, _msg) {
                    var el = frm.find("[name=" + i + "]");
                    if (checkFirstEle == 0) {
                        el.focus();
                        checkFirstEle++;
                    }
                    el.addClass('error');
                    el.parents('.form__input').find('.help-block').html(_msg[0]);
                });
            }
        });
    return false;
};

var formSubmitPopup = function (_form) {
    event.preventDefault();
    var frm = jQuery(_form);
    var btn = frm.find(".saveBtn");
    axios({
            method: 'post',
            url: frm.attr('action'),
            data: frm.serialize(),
            onUploadProgress: function (progressEvent) {
                startLoader(btn);
            }
        })
        .then(function (response) {
            console.log(response.data)
            if (response.data.redirect) {
                frm.find('.form-control').removeClass('error');
                frm.find('.help-block').html('');
                if (response.data.success) {
                    toastr.success(response.data.message);
                    if(response.data.reload){
                        location.reload();
                    }
                    showFollowups();
                    endLoader(btn);
                    frm.find('input').not(':input[type=hidden]').val('')
                    frm.find('select').val('')
                    frm.find('#modal_lead_status').val(1)
                    frm.find('.btn-close').click();
                } else {
                    endLoader(btn);
                }
            }
        })
        .catch(function (error) {
            endLoader(btn);
            if (error.response) {
                if (error.response.status == "419") {
                   toastr.error('Page session expired');
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                }
                var errors = error.response.data.errors;
                frm.find('.form-control').removeClass('error');
                frm.find('.help-block').html('');
                var checkFirstEle = 0;
                jQuery.each(errors, function (i, _msg) {
                    var el = frm.find("[name=" + i + "]");
                    if (checkFirstEle == 0) {
                        el.focus();
                        checkFirstEle++;
                    }
                    el.addClass('error');
                    el.parents('.form__input').find('.help-block').html(_msg[0]);
                });
            }
        });
    return false;
};

jobsubmitForm = function (_form) {
    if (typeof(CKEDITOR) !== "undefined") {
        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
    }

    var frm = jQuery(_form);
    var btn = frm.find(".saveBtn");
    var msg = frm.find("#msg");

    if (frm.find('select[name="from[]"]').length) {
        frm.find('select[name="from[]"] option').prop('selected', true);
        frm.find('select[name="to[]"] option').prop('selected', true);
    }

    var formData = new FormData(frm[0]);

    axios({
            method: 'post',
            url: frm.attr('action'),
            data: formData,
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            onUploadProgress: function (progressEvent) {
                msg.hide();
                startLoader(btn);
            }
        })
        .then(function (response) {
            if (response.data) {
                $(window).scrollTop(0);
                frm.find('.form__input').removeClass('error');
                frm.find('.help-block').html('');
                if (response.data.success) {
                    frm[0].reset();
                    msg.removeClass('alert-danger');
                    msg.addClass('alert-success');
                    msg.show();
                    msg.find('.alert-text').html(response.data.message);
                    endLoader(btn);

                    // Display toastr success message
                    toastr.success(response.data.message);

                    setTimeout(function () {
                        window.location.href = response.data.redirect;
                    }, 1000);
                    setTimeout(function () {
                        msg.fadeOut(500);
                    }, 3000);
                } else {
                    msg.removeClass('alert-success');
                    msg.addClass('alert-danger');
                    msg.show();
                    msg.find('.alert-text').html(response.data.message);
                    endLoader(btn);
                    setTimeout(function () {
                        msg.fadeOut(500);
                    }, 3000);
                }
            }
        })
        .catch(function (error) {
            endLoader(btn);
            if (error.response) {
                if (error.response.status == "419") {
                    toastr.error('Page session expired');
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                }
                $(window).scrollTop(0);
                var errors = error.response.data.errors;
                frm.find('.form__input').removeClass('error');
                frm.find('.help-block').html('');
                var checkFirstEle = 0;
                jQuery.each(errors, function (i, _msg) {
                    var el;
                    if (i.indexOf('.') !== -1) {
                        el = frm.find("[name='" + i.split(".")[0] + "[]']");
                    } else {
                        el = frm.find("[name=" + i + "]");
                    }
                    if (checkFirstEle === 0) {
                        el.focus();
                        checkFirstEle++;
                    }
                    el.parents('.form__input').addClass('error');
                    el.parents('.form__input').find('.help-block').html(_msg[0]);
                });
            }
        });

    return false;
};

var dynamicFormSubmit = function (_form) {
    event.preventDefault();
    var frm = jQuery(_form);
    var btn = frm.find(".saveBtn");
    axios({
            method: 'post',
            url: frm.attr('action'),
            data: frm.serialize(),
            onUploadProgress: function (progressEvent) {
                startLoader(btn);
            }
        })
        .then(function (response) {
            console.log(response.data)
            if (response.data.redirect) {
                $(window).scrollTop(0);
                frm.find('.form-control').removeClass('error');
                frm.find('.help-block').html('');
                if (response.data.success) {
                    toastr.success(response.data.message);
                    $('input').attr('readonly', true);
                    $('select').attr('disabled', true);
                    endLoader(btn);
                    btn.hide();

                    if(response.data.redirect != 'javascript:void(0)'){
                        window.location.href = response.data.redirect
                    }else{
                        openEmailPopup(response.data.lead_id)
                    }

                    if (Dropzone.instances.length > 0) {
                        Dropzone.instances.forEach(dz => dz.destroy())
                        Dropzone.instances.forEach(dz => dz.destroy())
                        Dropzone.instances.forEach(dz => dz.destroy())
                    }
                    $('#editBtn').removeClass('invisible');
                    $('#updateProfilePic').hide();
                    $('#updateProfilePic').removeClass('upload_image_profile');
                    $('#updateProfilePic').removeClass('dz-clickable');
                } else {
                    endLoader(btn);
                }
            }
        })
        .catch(function (error) {
            endLoader(btn);
            if (error.response) {
                if (error.response.status == "419") {
                   toastr.error('Page session expired');
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                }
                var errors = error.response.data.errors;
                frm.find('.form-control').removeClass('error');
                frm.find('.help-block').html('');
                var checkFirstEle = 0;
                jQuery.each(errors, function (i, _msg) {
                    if(i.indexOf('.') != -1){
                        console.log(i)
                        if(i.split(".").length == 2){
                            var el = frm.find("[name='"+ i.split(".")[0] +"["+i.split(".")[1]+"]']")
                            if(!el.length){
                                el = frm.find("[name='"+ i.split(".")[0] +"[]']")
                            }
                            if(!el.length){
                                el = frm.find("[name='"+ i.split(".")[0] +"["+i.split(".")[1]+"][]']")
                            }
                        }else if(i.split(".").length == 3){
                            console.log("[name='"+ i.split(".")[0] +"["+i.split(".")[1]+"]["+i.split(".")[2]+"]']");
                            var el = frm.find("[name='"+ i.split(".")[0] +"["+i.split(".")[1]+"]["+i.split(".")[2]+"]']")
                            if(!el.length){
                                el = frm.find("[name='"+ i.split(".")[0] +"["+i.split(".")[1]+"][]']")
                            }
                        }
                    }else{
                        var el = frm.find("[name=" + i + "]");
                    }
                    //var el = frm.find("[name=" + i + "]");
                    if (checkFirstEle == 0) {
                        el.focus();
                        checkFirstEle++;
                    }
                    el.addClass('error');
                    el.parents('.form__input').find('.help-block').html(_msg[0]);
                });
            }
        });
    return false;
};

 function openEmailPopup(lead_id){
    axios({
        method: 'post',
        url: email_popup_url,
        data: {lead_id:lead_id, _token: $('meta[name="csrf-token"]').attr('content')}
    })
    .then(function (response) {
        
        if(response.data.rows){
            console.log('response.data.rows', response.data.rows)
        $('#tiny-body').html(`${response.data.rows}`);
        
        $('#email-lead-popup').modal('show');

        tinymce.init({
            selector: '#tiny-email-body',
            fixed_toolbar_container: '#tiny-email-body-toolbar',
            inline:true,
            menubar: false,
            plugins: 'placeholder',
            toolbar: 'undo redo | bold italic underline strikethrough',
            setup: function (editor) {
                editor.on('init', function () {
                    $('#email-text-input').val(editor.getContent())
                    editor.save();
                });
                editor.on('change', function () {
                    $('#email-text-input').val(editor.getContent())
                    editor.save();
                });
            }
          });

        }
    })
    .catch(function (error) {
    })
 }

 function checkOption(option){
    $('.email-block').hide();
    $('.sms-block').hide();
    if(option == 'email'){
        $('.email-block').show();
    }else if(option == 'sms'){
        console.log('option', option)
        $('.sms-block').show();
    }
}

var startLoader = function (btn) {
    btn.find('.fa-spinner').show();
    btn.prop("disabled", true);
}

var endLoader = function (btn) {
    btn.find('.fa-spinner').hide();
    btn.prop("disabled", false);
}
