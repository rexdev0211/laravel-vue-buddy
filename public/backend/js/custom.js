toastr.options = {
    "showMethod": "slideDown",
    "hideMethod": "slideUp"
}

$(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        error : function(jqXHR, textStatus, errorThrown) {
            // console.log(jqXHR, textStatus, errorThrown);
            processAjaxFail(jqXHR);
        }
    });

    $('ul').bind('expanded.tree', saveMenuState)
           .bind('collapsed.tree', saveMenuState);
});

function showServerErrors(responseText) {
    if(_.isString(responseText)) {
        showErrorNotification(responseText, 'Error');
    } else {
        _.each(responseText, function (value, index) {
            _.each(value, function (value2) {
                // title = index.charAt(0).toUpperCase() + index.slice(1).replace('_', ' ') + ' error';
                title = 'Error';
                // toastr.error(value2, title)
                showErrorNotification(value2, title);
            });
        });
    }
}

function processAjaxFail(data) {
    if (data.status == 422) {
        responseText = JSON.parse(data.responseText);

        showServerErrors(responseText);
    } else if (data.status == 500) {
        // toastr.error("Error 500 has occurred, we're already working on fixing it", "Error 500");
        showErrorNotification("Error 500 has occurred, we're already working on fixing it", "Error 500");
    } else if (data.status == 401) {
        showErrorNotification("Your session has expired. Please re-login to continue.", "Error 401");
    } else if (data.status == 404) {
        showErrorNotification("Page or resource was not found.", "Error 404");
    } else if (data.status == 503) {
        showErrorNotification("Service is unavailable or website is in maintenance mode.", "Error 503");
    } else if (data.status == 403) {
        showErrorNotification("Forbidden area. You must be authorized to access it.", "Error 403");
    } else {
        // toastr.error('An unknown problem has occurred', "Error");
        showErrorNotification('An unknown problem has occurred', "Error");
    }
}

function showNotification(message, title)
{
    toastr.success(message, title)
}

function showErrorNotification(message, title)
{
    toastr.error(message, title)
}

function confirmDelete(name, url, subTitle) {
    confirmDeletePromise(name, subTitle)
        .then(function() {
            location.href = url;
        })
        .catch(swal.noop);
}

function confirmDeletePost(name, obj, subTitle) {
    confirmDeletePromise(name, subTitle)
        .then(function() {
            $(obj).closest('form').submit();
        })
        .catch(swal.noop);
}

function confirmDeletePromise(name, subTitle) {
    return swal({
        title: 'Delete "' + name + '"?',
        text: subTitle,
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        // cancelButtonColor: '#d33',
        confirmButtonText: 'Delete'
    });

    //don't forget to add .catch(swal.noop)
}

function confirmBlockIp(ip) {
    console.log('[confirmBlockIp]', { ip: ip })
    swal({
        title: 'Block IP?',
        text: 'Are you sure you want to block IP ' + ip + '?',
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Block'
    })
    .then(async function(result) {
        console.log('[confirmBlockIp]', { result: result })
        let response = await makeAjaxRequest('/admin/blockedDomains/ip/add', { ip: ip }, 'POST', true)
        console.log('[confirmBlockIp]', { response: response })
    })
    .catch(swal.noop)
}

function makeAjaxRequest(submitUrl, submitData = {}, requestType = 'GET', processData = false) {
    return new Promise(function (resolve, reject) {
        $.ajax({
            type: requestType,
            url: submitUrl,
            data: submitData,
            // contentType: false, //for POST method the parameters end up in the Content, instead of $request
            processData: processData,
        })
            .done(function (data) {
                resolve(data);
            })
            .fail(function (data) {
                // console.log(data);
                // processAjaxFail(data);

                reject(data);
            })
    });
}

function saveMenuState() {
    var adminMenu = {
        'community':  $('[data-id="community"]').hasClass('menu-open') ? 1 : 0,
        'moderation': $('[data-id="moderation"]').hasClass('menu-open') ? 1 : 0,
        'admin':      $('[data-id="admin"]').hasClass('menu-open') ? 1 : 0,
    };
    makeAjaxRequest('/admin/menu', adminMenu, 'POST', true);
}

$(document).on('hidden.bs.modal', function (e) {
    $(e.target).removeData('bs.modal');
});

$('#modal-box-div').on('hidden.bs.modal', function (e) {
    $(e.target).find('.modal-content').html('');
});
