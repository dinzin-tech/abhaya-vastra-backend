var getParameters = function () {
    var parameters = '';
    if ($('#q').val() != null && $('#q').val() != '')
        parameters += '&q=' + $('#q').val();
    if (getUrlParameter('status') != null && getUrlParameter('status') != '')
        parameters += '&status=' + getUrlParameter('status');
    if (getUrlParameter('shiprocket') != null && getUrlParameter('shiprocket') != '')
        parameters += '&shiprocket=' + getUrlParameter('shiprocket');
    if ($('#phone').val() != null && $('#phone').val() != '')
        parameters += '&phone=' + $('#phone').val();

    if ($('#order_number').val() != null && $('#order_number').val() != '')
        parameters += '&order_number=' + $('#order_number').val();

    if ($('#razorpay_payment_id').val() != null && $('#razorpay_payment_id').val() != '')
        parameters += '&razorpay_payment_id=' + $('#razorpay_payment_id').val();

    if ($('#amount').val() != null && $('#amount').val() != '')
        parameters += '&amount=' + $('#amount').val();

    
    if ($('#name').val() != null && $('#name').val() != '')
        parameters += '&name=' + $('#name').val();
    if ($('#mobile').val() != null && $('#mobile').val() != '')
        parameters += '&mobile=' + $('#mobile').val();
    if ($('#region_name').val() != null && $('#region_name').val() != '')
        parameters += '&region_name=' + $('#region_name').val();
    if ($('#email').val() != null && $('#email').val() != '')
        parameters += '&email=' + $('#email').val();
    if ($('#city_name').val() != null && $('#city_name').val() != '')
        parameters += '&city_name=' + $('#city_name').val();
    if ($('#state_name').val() != null && $('#state_name').val() != '')
        parameters += '&state_name=' + $('#state_name').val();
    if ($('#country_name').val() != null && $('#country_name').val() != '')
        parameters += '&country_name=' + $('#country_name').val();
    if (getUrlParameter('page') != null)
        parameters += '&page=' + getUrlParameter('page');
    if ($('#offset').val() != null && $('#offset').val() != '')
        parameters += '&offset=' + $('#offset').val();
    if ($('#truck_id_field').val() != null && $('#truck_id_field').val() != '')
        parameters += '&truck_id=' + $('#truck_id_field').val();
    if ($('#duration_field').val() != null && $('#duration_field').val() != '')
        parameters += '&duration=' + $('#duration_field').val();
    if ($('#search_doe').val() != null && $('#search_doe').val() != '')
        parameters += '&doe=' + $('#search_doe').val();
    if ($('#search_followup_date').val() != null && $('#search_followup_date').val() != '')
        parameters += '&followup_date=' + $('#search_followup_date').val();
    if ($('#search_service_type').val() != null && $('#search_service_type').val() != '')
        parameters += '&service_type=' + $('#search_service_type').val();
    if ($('#search_lead_type').val() != null && $('#search_lead_type').val() != '')
        parameters += '&lead_type=' + $('#search_lead_type').val();
    if ($('#search_company').val() != null && $('#search_company').val() != '')
        parameters += '&company=' + $('#search_company').val();
    if ($('#search_state').val() != null && $('#search_state').val() != '')
        parameters += '&state=' + $('#search_state').val();
    if ($('#search_city').val() != null && $('#search_city').val() != '')
        parameters += '&city=' + $('#search_city').val();
    if ($('#search_source').val() != null && $('#search_source').val() != '')
        parameters += '&source=' + $('#search_source').val();
    if ($('#search_added_by').val() != null && $('#search_added_by').val() != '')
        parameters += '&added_by=' + $('#search_added_by').val();
    if ($('#search_type').val() != null && $('#search_type').val() != '')
        parameters += '&type=' + $('#search_type').val();
    if ($('#search_vendor').val() != null && $('#search_vendor').val() != '')
        parameters += '&vendor=' + $('#search_vendor').val();
    if ($('#global_search').val() != null && $('#global_search').val() != '')
        parameters += '&global_search=' + $('#global_search').val();
      if (getUrlParameter('status') != null && getUrlParameter('status') != '')
        parameters += '&status=' + getUrlParameter('status');
    if ($('#booking_id').val() != null && $('#booking_id').val() != '')
        parameters += '&booking_id=' + $('#booking_id').val();
    
    return parameters;
};

var getParametersObject = function () {
    var parameters = new Object();
    parameters['sync'] = 1;
    if ($('#q').val() != null && $('#q').val() != '')
        parameters['q'] = $('#q').val();
    if (getUrlParameter('status') != null && getUrlParameter('status') != '')
        parameters['status'] = getUrlParameter('status');
    if (getUrlParameter('shiprocket') != null && getUrlParameter('shiprocket') != '')
        parameters['shiprocket'] = getUrlParameter('shiprocket');
    if ($('#phone').val() != null && $('#phone').val() != '')
        parameters['phone'] = $('#phone').val();
    if ($('#order_number').val() != null && $('#order_number').val() != '')
        parameters['order_number'] = $('#order_number').val();

    if ($('#razorpay_payment_id').val() != null && $('#razorpay_payment_id').val() != '')
        parameters['razorpay_payment_id'] = $('#razorpay_payment_id').val();

    if ($('#amount').val() != null && $('#amount').val() != '')
        parameters['amount'] = $('#amount').val();
    
    if ($('#name').val() != null && $('#name').val() != '')
        parameters['name'] = $('#name').val();
    if ($('#mobile').val() != null && $('#mobile').val() != '')
        parameters['mobile'] = $('#mobile').val();
    if ($('#region_name').val() != null && $('#region_name').val() != '')
        parameters += '&region_name=' + $('#region_name').val();
    if ($('#email').val() != null && $('#email').val() != '')
        parameters += '&email=' + $('#email').val();
    if ($('#city_name').val() != null && $('#city_name').val() != '')
        parameters += '&city_name=' + $('#city_name').val();
    if ($('#state_name').val() != null && $('#state_name').val() != '')
        parameters += '&state_name=' + $('#state_name').val();
    if ($('#country_name').val() != null && $('#country_name').val() != '')
        parameters += '&country_name=' + $('#country_name').val();
    if (getUrlParameter('page') != null)
        parameters['page'] = getUrlParameter('page');
    if ($('#offset').val() != null && $('#offset').val() != '')
        parameters['offset'] = $('#offset').val();
    if ($('#user_name').val() != null && $('#user_name').val() != '')
        parameters['name'] = $('#user_name').val();
    if ($('#short_name').val() != null && $('#short_name').val() != '')
        parameters['short_name'] = $('#short_name').val();
    if ($('#phonecode').val() != null && $('#phonecode').val() != '')
        parameters['phonecode'] = $('#phonecode').val();
    if ($('#truck_id_field').val() != null && $('#truck_id_field').val() != '')
        parameters['truck_id'] = $('#truck_id_field').val();
    if ($('#duration_field').val() != null && $('#duration_field').val() != '')
        parameters['duration'] = $('#duration_field').val();
    if ($('#search_doe').val() != null && $('#search_doe').val() != '')
        parameters['doe'] = $('#search_doe').val();
    if ($('#search_followup_date').val() != null && $('#search_followup_date').val() != '')
        parameters['followup_date'] = $('#search_followup_date').val();
    if ($('#search_service_type').val() != null && $('#search_service_type').val() != '')
        parameters['service_type'] = $('#search_service_type').val();
    if ($('#search_lead_type').val() != null && $('#search_lead_type').val() != '')
        parameters['lead_type'] = $('#search_lead_type').val();
    if ($('#search_company').val() != null && $('#search_company').val() != '')
        parameters['company'] = $('#search_company').val();
    if ($('#search_state').val() != null && $('#search_state').val() != '')
        parameters['state'] = $('#search_state').val();
    if ($('#search_city').val() != null && $('#search_city').val() != '')
        parameters['city'] = $('#search_city').val();
    if ($('#search_source').val() != null && $('#search_source').val() != '')
        parameters['source'] = $('#search_source').val();
    if ($('#search_added_by').val() != null && $('#search_added_by').val() != '')
        parameters['added_by'] = $('#search_added_by').val();
    if ($('#search_type').val() != null && $('#search_type').val() != '')
        parameters['type'] = $('#search_type').val();
    if ($('#search_vendor').val() != null && $('#search_vendor').val() != '')
        parameters['vendor'] = $('#search_vendor').val();
    if ($('#global_search').val() != null && $('#global_search').val() != '')
        parameters['global_search'] = $('#global_search').val();
    if ($('#booking_id').val() != null && $('#booking_id').val() != '')
        parameters['booking_id'] = $('#booking_id').val();
    return parameters;
};

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};
    
var recordList = function() {
    var parameters = getParametersObject();
    var urlparameters = getParameters();

    $.ajax({
        url: tblObj.data('url'),
        type: 'GET',
        data: parameters,
        dataType: 'json',
        success: function(response) {
            tblObj.find('tbody').html(response.rows); // Update table rows
            tblObj.find('tfoot tr td').html(response.pagination); // Update pagination
            reinitPagination(); // Reinitialize pagination events
            window.history.replaceState(
                {
                    isBackPage: false,
                    html: 'jscv',
                    pageTitle: 'bsckj'
                },
                "",
                listUrl + '?' + urlparameters
            );
        },
        error: function(response) {
            if (response.status == 419) {
                toastr.error('Page session expired');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                toastr.error('Error in listing rows');
            }
        }
    });
};

var reinitPagination = function() {
    $('.pagination a').on('click', function(e) {
        e.preventDefault();
        var globalSearch = $('#global_search').val(); // Include global search on pagination
        var parameters = $.param({ global_search: globalSearch });
        $("#record-list").data('url', $(this).data('url') + '&' + parameters);
        recordList();
    });
};

var recordChatList = function(){
    var parameters = getParametersObject();
    var urlparameters = getParameters();
            $.ajax({
                url: tblObj.data('url'),
                type : 'GET',
                data : parameters,
                dataType: 'json'
            })
            .success(function(response) {
                tblObj.html(response.rows);
                window.history.replaceState({
                    isBackPage: false,
                    "html": 'jscv',
                    "pageTitle": 'bsckj'
                }, "", listUrl + '?query=q' + urlparameters);
            })
            .error(function(response, code) {
                if(response.status == "419"){
                    toastr.error('Page session expired');
                    setTimeout(function(){
                        location.reload();             
                        },2000);
                }
                toastr.error('Error in listing rows');
            });
        };

var recordListDiv = function(){
    var parameters = getParametersObject();
    var urlparameters = getParameters();
            $.ajax({
                url: tblObj.data('url'),
                type : 'GET',
                data : parameters,
                dataType: 'json'
            })
                    .success(function(response) {
                        tblObj.html(response.rows);
                        tblObjPagination.html(response.pagination);
                        reinitDivPagination();
                        window.history.replaceState({
                            isBackPage: false,
                            "html": 'jscv',
                            "pageTitle": 'bsckj'
                        }, "", listUrl + '?query=q' + urlparameters);
                    })
                    .error(function(response, code) {
                        if(response.status == "419"){
                            toastr.error('Page session expired');
                            setTimeout(function(){
                                location.reload();             
                                },2000);
                        }
                        toastr.error('Error in listing rows');
                    });
        };

var reinitDivPagination = function(){
    $('.pagination a').on('click', function(e){
        var parameters = getParameters();
        e.preventDefault();
        $("#record-list").data('url', ($(this).data('url') + parameters));
        recordListDiv();
    });
}

// var deleteRecord = function(id, ele){
       
//     if(confirm('Are you sure want to delete this record?'))
//     {    
//         let tenantId =  $("#tenantId").val() ?? null; 
//         $.ajax({
//             url: deleteUrl,
//             type : 'DELETE',
//             data : {id:id, _token:window.Laravel.csrfToken , tenantId:tenantId},
//             dataType: 'json'
//         })
//                 .success(function(response) {
//                     toastr.success(response.message);
//                     recordList();
//                 })
//                 .error(function(response, code) {
//                     if(response.status == "419"){
//                         toastr.error('Page session expired');
//                         setTimeout(function(){
//                             location.reload();             
//                             },2000);
//                     }
//                     toastr.error(response.responseJSON.message);
//                 });
//     }
// }

var deleteRecord = function(id, ele){
    if(confirm('Are you sure want to delete this record?')) {    
        let tenantId = $("#tenantId").val() ?? null; 
        $.ajax({
            url: deleteUrl,
            type : 'DELETE',
            data : {id:id, _token:window.Laravel.csrfToken , tenantId:tenantId},
            dataType: 'json',
            success: function(response) {
                toastr.success(response.message);
                recordList();
            },
            error: function(response) {
                if(response.status == 419){
                    toastr.error('Page session expired');
                    setTimeout(function(){ location.reload(); },2000);
                } else {
                    toastr.error(response.responseJSON?.message || 'Error occurred');
                }
            }
        });
    }
}

var deleteTeamRecord = function(id, ele){
    if(confirm('Are you sure want to delete this record?'))
    {
        let tenantId =  $("#tenantId").val() ?? null; 
        $.ajax({
            url: deleteTeamUrl,
            type : 'DELETE',
            data : {id:id, _token:window.Laravel.csrfToken ,  tenantId:tenantId},
            dataType: 'json'
        })
                .success(function(response) {
                    toastr.success(response.message);
                    fetchUser();
                })
                .error(function(response, code) {
                    if(response.status == "419"){
                        toastr.error('Page session expired');
                        setTimeout(function(){
                            location.reload();             
                            },2000);
                    }
                    toastr.error(response.responseJSON.message);
                });
    }
}

var deleteRecordDiv = function(id, ele){
    if(confirm('Are you sure want to delete this record?'))
    {
        $.ajax({
            url: deleteUrl,
            type : 'DELETE',
            data : {id:id, _token:window.Laravel.csrfToken},
            dataType: 'json'
        })
                .success(function(response) {
                    toastr.success(response.message);
                    recordListDiv();
                })
                .error(function(response, code) {
                    if(response.status == "419"){
                        toastr.error('Page session expired');
                        setTimeout(function(){
                            location.reload();             
                            },2000);
                    }
                    toastr.error(response.responseJSON.message);
                });
    }
}

var changeStatus = function(id, ele){
    if(confirm('Are you sure want to change this status?'))
    {
        $.ajax({
            url: changeUrl,
            type : 'POST',
            data : {id:id, _token:window.Laravel.csrfToken},
            dataType: 'json'
        })
            .success(function(response) {
                toastr.success(response.message);
                recordList();
            })
            .error(function(response, code) {
                if(response.status == "419"){
                    toastr.error('Page session expired');
                    setTimeout(function(){
                        location.reload();             
                        },2000);
                }
                // console.log(response.responseJSON.message);
                toastr.error(response.responseJSON.message);
            });
    }
}

var changeStatusDiv = function(id, ele){
    if(confirm('Are you sure want to change this status?'))
    {
        $.ajax({
            url: changeUrl,
            type : 'POST',
            data : {id:id, _token:window.Laravel.csrfToken},
            dataType: 'json'
        })
            .success(function(response) {
                toastr.success(response.message);
                recordListDiv();
            })
            .error(function(response, code) {
                if(response.status == "419"){
                    showCustomToast('bottom-center', 'Page session expired');
                    setTimeout(function(){
                        location.reload();             
                        },2000);
                }
                // console.log(response.responseJSON.message);
                toastr.error(response.responseJSON.message);
            });
    }
}

var paginationURL = function (page) {
    if (getUrlParameter('page') != null){
        var newUrl = location.href.replace("page="+encodeURIComponent(getUrlParameter('page').trim()), "page="+page);
        window.history.replaceState({
            isBackPage: false,
            "html": 'jscv',
            "pageTitle": 'bsckj'
        }, "", newUrl);
    }else if(window.location.search){
        window.history.replaceState({
            isBackPage: false,
            "html": 'jscv',
            "pageTitle": 'bsckj'
        }, "", window.location.href+'&page='+page);
    }else{
        window.history.replaceState({
            isBackPage: false,
            "html": 'jscv',
            "pageTitle": 'bsckj'
        }, "", window.location.href+'?page='+page);
    }
   }

var reformatSerialNo = function(ele){
    var tr = tblObj.find('tbody').find('tr');
    tr.each(function( index ) {
        $(this).find('th').html(index+1)
      });
}
