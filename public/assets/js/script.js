"use strict";

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
function showErrorToast(message) {
    $.toast({
        text: message,
        showHideTransition: 'slide',
        icon: 'error',
        loaderBg: '#f2a654',
        position: 'top-right'
    });
}

function showSuccessToast(message) {
    $.toast({
        text: message,
        showHideTransition: 'slide',
        icon: 'success',
        loaderBg: '#f96868',
        position: 'top-right'
    });
}

function ajaxRequest(type, url, data, beforeSendCallback, successCallback, errorCallback, finalCallback, processData = false) {
    /*
    * @param
    * beforeSendCallback : This function will be executed before Ajax sends its request
    * successCallback : This function will be executed if no Error will occur
    * errorCallback : This function will be executed if some error will occur
    * finalCallback : This function will be executed after all the functions are executed
    */
    $.ajax({
        type: type,
        url: url,
        data: data,
        cache: false,
        processData: processData,
        contentType: false,
        dataType: 'json',
        beforeSend: function () {
            if (beforeSendCallback != null) {
                beforeSendCallback();
            }
        },
        success: function (data) {
            if (!data.error) {
                if (successCallback != null) {
                    successCallback(data);
                }
            } else {
                if (errorCallback != null) {
                    errorCallback(data);
                }
            }

            if (finalCallback != null) {
                finalCallback(data);
            }
        }, error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.responseJSON) {
                showErrorToast(jqXHR.responseJSON.message);
            }
            if (finalCallback != null) {
                finalCallback();
            }
        }
    })
}
function formAjaxRequest(type, url, data, formElement, submitButtonElement, successCallback, errorCallback) {
    // To Remove Red Border from the Validation tag.
    formElement.find('.has-danger').removeClass("has-danger");
    formElement.validate();
    if (formElement.valid()) {
        let submitButtonText = submitButtonElement.val();

        function beforeSendCallback() {
            submitButtonElement.val('Please Wait...').attr('disabled', true);
        }

        function mainSuccessCallback(response) {
            showSuccessToast(response.message);
            if (successCallback != null) {
                successCallback(response);
            }
        }

        function mainErrorCallback(response) {
            showErrorToast(response.message);
            if (errorCallback != null) {
                errorCallback(response);
            }
        }

        function finalCallback(response) {
            submitButtonElement.val(submitButtonText).attr('disabled', false);
        }

        ajaxRequest(type, url, data, beforeSendCallback, mainSuccessCallback, mainErrorCallback, finalCallback)
    }
}
$('#create-form,.create-form').on('submit', function (e) {
    e.preventDefault();
    let formElement = $(this);
    let submitButtonElement = $(this).find(':submit');
    let url = $(this).attr('action');
    let data = new FormData(this);

    function successCallback() {
        formElement[0].reset();
    }

    formAjaxRequest('POST', url, data, formElement, submitButtonElement, successCallback);
})
