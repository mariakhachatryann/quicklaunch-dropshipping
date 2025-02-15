$(document).ready(function () {

    $('[data-toggle="tooltip"]').tooltip()

    $("#search-post").autocomplete({
        source: function (request, response) {
            $.ajax({
                type: "POST",
                url: "/post/search",
                dataType: "json",
                data: {
                    keyword: request.term
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        select: function (event, ui) {
            $('#search-post').val(ui.item.label);
            searchPost();
            return false;
        }
    });

    $(document).on('mouseenter', '.monitoring_review_min_rate', function(){
        let id = $(this).attr('data-id')
        $('.monitoring_review_min_rate').each(function(index, item) {
            if (index <= id) {
                $(item).attr('old-color', $(item).css('color'))
                $(item).css('color', '#efc20f')
            }
        })
    })

    $(document).on('click', '.monitoring_review_min_rate', function(){
        const rate = Number($(this).attr('data-id'))
        const productId = $('.monitoring-checkbox')[0].getAttribute('data-id')
        $('.monitoring_review_min_rate').each(function(index, item) {
            if (index <= rate) {
                $(item).attr('old-color', '#efc20f')
                $(item).css('color', '#efc20f')
            }
        })
        $.ajax({
            type: "POST",
            url: "/product/monitoring-review-min-rate",
            data: {rate, productId},
            dataType: 'json',
            success: function (res) {
            }
        });
    })

    $(document).on('mouseleave', '.monitoring_review_min_rate', function(){
        let id = $(this).attr('data-id')
        $('.monitoring_review_min_rate').each(function(index, item) {
            if (index <= id) {
                if ($(item).attr('old-color')) {
                    $(item).css('color', $(item).attr('old-color'))
                } else {
                    $(item).css('color', '#b7b7b7')
                }
            }
        })
    })

    $('body').on('click', '.monitoring-checkbox', function () {
        let productId = $(this).attr('data-id');
        let checkbox = $(this);
        let checked = checkbox.prop("checked");
        let type = $(this).attr('data-type');

        if (type == 'review') {
            if (checked) {
                $('.monitorReviewRate').show()
            } else {
                $('.monitorReviewRate').hide()
            }
        }

        if (checked) {
            $('.productMonitorNowButton').removeClass('disabled')
        }

        $.ajax({
            type: "POST",
            url: "/product/monitoring",
            data: {productId, checked, type},
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        title: res.info,
                        html: '<strong>' + res.message + '</strong>',
                    });
                } else {
                    checkbox.prop('checked', false);
                    Swal.fire({
                        title: res.message,
                        type: "warning",
                        showCancelButton: !0,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Upgrade plan",
                        cancelButtonText: "Cancel",
                    }).then((result) => {
                        if (result.value) {
                            window.location.href = '/profile/subscribe'
                        }
                    })
                }
            }
        });

    });

    $(document).on('click', '.classDisabledShionExtension', function(e) {
        e.preventDefault()
        const text = $(this).attr('data-text')
        swal({
            title: 'Install the Chrome Extension',
            text: text,
            type: 'info',
            showCancelButton: !0,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Install",
            cancelButtonText: "Back",
        }).then(function(e) {
            if (e.value === true) {
                window.open($('#extensionLink').val());
            }
        })
    })

    $(document).on('input', '#cotegotyBulkImportLink', function () {
        if ($(this).val()) {
            $('#bulkImportCreate').removeAttr('disabled')
        } else {
            $('#bulkImportCreate').attr('disabled', true)
        }
    })

    $(document).on('click', '.monitoringRemainDisabled', function(e) {
        if (!$(this).hasClass('classDisabledShionExtension')) {
            e.preventDefault()
            Swal.fire({
                title: 'Product monitoring limit is expired, please upgrade your plan',
                type: "warning",
                showCancelButton: !0,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Upgrade plan",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.value) {
                    window.location.href = '/profile/subscribe'
                }
            })
        }
    })

    $('.reviewRemainsLimitExpired').click(function() {
        $(this).checked = false
        Swal.fire({
            title: 'Review limit is expired, please upgrade your plan',
            type: "warning",
            showCancelButton: !0,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Upgrade plan",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.value) {
                window.location.href = '/profile/subscribe'
            }
        })
    })

    $('#bulkImportCreate').click(function () {
        if (!$(this).hasClass('multipleImportDisabledButton')) {
            const url = $('#cotegotyBulkImportLink').val()
            $('#bulkImportLoading').addClass('d-flex')
            $('#bulkImportLoading').removeClass('d-none')
            $(this).attr('disabled', true)
            $('#cotegotyBulkImportLink').attr('disabled', true)
            const button = $(this)
            $.ajax({
                type: "POST",
                url: "/bulk-import/create",
                data: {url},
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        $('#bulkImportLoading').removeClass('d-flex')
                        $('#bulkImportLoading').addClass('d-none')
                        let newUrl = ''
                        if (url.includes('?')) {
                            newUrl = url.split('?')[0] + `?shionBulkImport=${res.id}` + `&${url.split('?')[1]}`
                        } else {
                            newUrl = url + `?shionBulkImport=${res.id}`
                        }

                        window.open(newUrl)

                    } else {
                        $('#bulkImportLoading').removeClass('d-flex')
                        $('#bulkImportLoading').addClass('d-none')
                        swal({
                            title: 'Error',
                            html: 'Something went wrong',
                            type: 'error',
                            showCancelButton: 0,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Ok",
                        }).then(function (e) {
                            button.removeAttr('disabled')
                            $('#cotegotyBulkImportLink').removeAttr('disabled')
                            $('#cotegotyBulkImportLink').val('')
                        })

                    }
                },
                error: () => {
                    swal({
                        title: 'Error',
                        html: 'Something went wrong',
                        type: 'error',
                        showCancelButton: 0,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Ok",
                    }).then(function (e) {
                        button.removeAttr('disabled')
                        $('#cotegotyBulkImportLink').removeAttr('disabled')
                        $('#cotegotyBulkImportLink').val('')
                    })
                }
            });
        }

    })

    $('.multipleImportDisabledButton').click(function (e) {
        $(this).checked = false
        e.preventDefault()
        Swal.fire({
            title: 'Plan upgrade required!',
            type: "warning",
            showCancelButton: !0,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Upgrade plan",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.value) {
                window.location.href = '/profile/subscribe'
            }
        })
    })

    $('body').on('click', '.subscribe-plan', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');
        let promo = form.find('.promo-input').val();
        let planId = form.find("input[name='planId']").val();
        let isCurrentFree = form.find("input[name='isCurrentFree']").val();
        let title = $(this).data('title');
        let text = $(this).data('text');
        if (promo) {
            $.ajax({
                type: "POST",
                url: "/plan/check-promo",
                data: {planId, promo},
                dataType: 'json',
                success: function (res) {
                    if (!res.success) {
                        Swal.fire(res.message);
                    } else {
                        form.submit();
                    }
                }
            });
        } else if (isCurrentFree) {
            form.submit();
        } else {
           new swal({
                title: title,
                text: text,
                type: 'info',
                showCancelButton: !0,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Confirm",
                cancelButtonText: "Back",
            }).then(function (e) {
                if (e.value === true) {
                    form.submit();
                }
            })
        }
    });

    $('body').on('click', '.search-button', function (e) {
        searchPost();
    });

    $('body').on('change', '#search-post', function (e) {
        searchPost();
    });

    function searchPost() {
        let keyword = $('#search-post').val();
        console.log(keyword)
        window.location.href = '/post?keyword=' + keyword
    }

    $('#notification-button').click(function () {
        const notificationButton = $(this);
        if (notificationButton.attr('aria-expanded') == 'true') {
            $('.newNotification').removeClass('newNotification');
        } else {
            let idsArray = [];
            $('.newNotification').each(function () {
                idsArray.push($(this).attr('data-id'));
            })
            let ids = JSON.stringify(idsArray);
            $.ajax({
                type: "POST",
                url: "/notifications/seen",
                data: {ids},
                dataType: 'json',
                success: function (res) {
                    $('#notification-button > div').addClass('pulse-css').removeClass('pulse-css');
                }
            });
        }
    })


    $('.changeTheme').on('click', function () {
        let direction = getUrlParams('dir');
        if (direction != 'rtl') {
            direction = 'ltr';
        }

        let theme = '';

        if ($('#moon').hasClass('hide')) {
            $('#moon').removeClass('hide');
            $('#sun').addClass('hide');
            theme = 'light';
        } else {
            $('#sun').removeClass('hide');
            $('#moon').addClass('hide');
            theme = 'dark';
        }

        // localStorage.setItem("theme", theme);

        let dezSettingsOptions = {
            typography: "poppins",
            version: theme,
            layout: "Vertical",
            headerBg: "color_1",
            navheaderBg: "color_1",
            sidebarBg: "color_1",
            sidebarStyle: "full",
            sidebarPosition: "fixed",
            headerPosition: "fixed",
            containerLayout: "full",
            direction: direction
        };

        new dezSettings(dezSettingsOptions);
        jQuery(window).on('resize', function () {
            new dezSettings(dezSettingsOptions);
        });

        $.ajax({
            method: "POST",
            url: "/profile/change-site-theme",
            data: {theme: theme}
        });
    })

    let lastNotificationId = 0;
    let notificationTypes = ['info', 'danger', 'success', 'warning'];
    let getNotification = true;
    let notificationInterval = setInterval(() => {
        if (!getNotification) {
            return false;
        }
        getNotification = false;
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/notifications/new-notifications',
            success: function (data) {
                getNotification = true;
                if (data.success && data.notifications.length) {
                    if (data.lastId > lastNotificationId) {
                        lastNotificationId = data.lastId;
                        $('#new-notification-light').removeClass('pulse-css').addClass('pulse-css');
                        notificationInfo();
                        for (let notification of data.notifications) {
                            if (notification.additional_data) {
                                let pathname = window.location.pathname;
                                let notificationData = JSON.parse(notification.additional_data);
                                if (notification.url == pathname && pathname == '/product/' + notificationData.id) {
                                    $('.viewOnShopifyBtn').attr('href', notificationData.handle);
                                    $('.viewOnShopifyBtn').removeClass('disabled');
                                    $('.editOnShopifyBtn').attr('href', notificationData.url);
                                    $('.editOnShopifyBtn').removeClass('disabled');
                                }
                            }
                            $('#notification_timeline').prepend(`
                        <li class="pt-3 newNotification" data-id="${notification.id}">
                            <a href="${notification.notificationUrl}">
                                <div class="timeline-panel">
                                    <div class="media mr-2 media-${notificationTypes[notification.notification_type]}">
                                        <i class="fa fa-bell"></i>
                                    </div>
                                    <div class="media-body">
                                            <h5 class="mb-1">${notification.subject}</h5>
                                            <small class="d-block">${notification.created_at}</small>
                                    </div> 
                                </div>
                            </a>
                    </li>`);
                        }
                    }
                    // let ids = JSON.stringify(idsArray);
                    // $.ajax({
                    //     type: "POST",
                    //     url: "/notifications/seen",
                    //     data: {ids : ids},
                    //     dataType: 'json',
                    //     success: function (res) {
                    //         if (!res.success) {
                    //             console.log(res.message);
                    //         }
                    //     }
                    // });
                }
            },

            error() {
                clearInterval(notificationInterval);
            }
        });
    }, 15000);

    function success(notification) {
        toastr.success(notification.text, notification.subject, {
            timeOut: 500000000,
            closeButton: !0,
            debug: !1,
            newestOnTop: !0,
            progressBar: !0,
            positionClass: "toast-top-right",
            preventDuplicates: !0,
            onclick: null,
            showDuration: "1000",
            hideDuration: "3000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: !1
        });
        toastr.clear();

    }

    function error(notification) {
        toastr.error(notification.text, notification.subject, {
            timeOut: 8000,
            closeButton: !0,
            debug: !1,
            newestOnTop: !0,
            progressBar: !0,
            positionClass: "toast-top-right",
            preventDuplicates: !0,
            onclick: null,
            showDuration: "300000",
            hideDuration: "1000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: !1
        });
        toastr.clear();

    }

    function notificationInfo() {
        toastr.info("You have new notifications!", "NEW!", {
            timeOut: 500000000,
            closeButton: !0,
            debug: !1,
            newestOnTop: !0,
            progressBar: !0,
            positionClass: "toast-top-right",
            preventDuplicates: !0,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: !1
        })
        // toastr.clear();
    }

    function warning(notification) {
        toastr.warning(notification.text, notification.subject, {
            timeOut: 500000000,
            closeButton: !0,
            debug: !1,
            newestOnTop: !0,
            progressBar: !0,
            positionClass: "toast-top-right",
            preventDuplicates: !0,
            onclick: null,
            showDuration: "1000",
            hideDuration: "3000",
            extendedTimeOut: "3000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: !1
        })
        toastr.clear();
    }

    $('body').on('click', ".subscribe-plan-cancel", function (e) {
        e.preventDefault();
        let form = e.target.form;
        let title = $(this).data('title');
        let text = $(this).data('text');
        new swal({
            title: title,
            text: text,
            type: 'info',
            showCancelButton: !0,
            confirmButtonColor: "#16CDC7",
            confirmButtonText: "Confirm",
            cancelButtonText: "Back",
        }).then(function (e) {
            if (e.value === true) {
                form.submit();
            }
        })

    })

    $("#multipleimport-importfile").on("change", function () {
        let label = $('label[for="multipleimport-importfile"]');
        let newLabel = $(this).val().split('\\').pop();
        label.text(newLabel);
    });

    function getVariantMarkupAttributes() {
        return {
            original: Number($('.editVariantModal').attr('data-original-price')).toFixed(2),
            finalPrice: $('input[name="price"].editVariantInput'),
            compareAtPrice: $('input[name="compare_at_price"].editVariantInput'),
            comparedAtByPercent: $('input[name="compare_at_price_by_percent"].variantPriceMarkupEdit'),
            compareAtByAmount: $('input[name="compare_at_price_by_amount"].variantPriceMarkupEdit'),
            priceByAmount: $('input[name="price_by_amount"].variantPriceMarkupEdit'),
            priceByPercent: $('input[name="price_by_percent"].variantPriceMarkupEdit'),
            comparePriceMarkup: $('select[name="compare_at_price_markup"].variantPriceMarkupEdit'),
            priceMarkup: $('select[name="price_markup"].variantPriceMarkupEdit'),
        }
    }

    function getProductMarkupValues() {
        return {
            price_by_amount: Number($('.editVariantModal').attr('price_by_amount')),
            price_by_percent: Number($('.editVariantModal').attr('price_by_percent')),
            compare_at_price_by_amount: Number($('.editVariantModal').attr('compare_at_price_by_amount')),
            compare_at_price_by_percent: Number($('.editVariantModal').attr('compare_at_price_by_percent')),
            price_markup: $('.editVariantModal').attr('price_markup'),
            compare_at_price_markup: $('.editVariantModal').attr('compare_at_price_markup'),
        }
    }

    function getProductMarkupDefaultValues() {
        return {
            price_by_amount: Number($('.editProductPrice').attr('price_by_amount')),
            price_by_percent: Number($('.editProductPrice').attr('price_by_percent')),
            compare_at_price_by_amount: Number($('.editProductPrice').attr('compare_at_price_by_amount')),
            compare_at_price_by_percent: Number($('.editProductPrice').attr('compare_at_price_by_percent')),
            price_markup: $('.editProductPrice').attr('price_markup'),
            compare_at_price_markup: $('.editProductPrice').attr('compare_at_price_markup'),
        }
    }

    $('.editVariantButton').click(function () {
        const variantFields = [
            'inventory_quantity',
            'sku',
            'option1',
            'option2',
            'option3',
            'compare_at_price',
            'price',
        ]

        let data = []

        const headLinks = $('#variantsDatatable thead a')
        headLinks.each((index, item) => {
            let attribute = item.getAttribute('data-sort')
            if (variantFields.indexOf(attribute) !== -1) {
                data.push({
                    index,
                    name: item.innerHTML,
                    attribute
                })
            }
        })
        let getFromProductMarkup = true
        const lineData = $(this).closest('tr').find('td')
        data.forEach((item) => {
            item.value = lineData[item.index + 1].innerHTML
        })
        let container = $('.editVariantModal .modal-body .hasBorder')
        $(container).empty()
        $('.errorMessage').remove()
        let priceMarkupData = []

        if (!!$(this).attr('compare_at_price_by_amount')) {
            priceMarkupData.push({
                attribute: 'price_markup',
                name: 'Price markup',
                value: $(this).attr('price_markup'),
                show: true
            })
            priceMarkupData.push({
                attribute: 'price_by_amount',
                name: 'Price by amount',
                value: Number($(this).attr('price_by_amount')),
                show: $(this).attr('price_markup') == '1'
            })
            priceMarkupData.push({
                attribute: 'price_by_percent',
                name: 'Price by percent',
                value: Number($(this).attr('price_by_percent')),
                show: $(this).attr('price_markup') == '0'
            })
            priceMarkupData.push({
                attribute: 'compare_at_price_markup',
                name: 'Compare at price markup',
                value: $(this).attr('compare_at_price_markup'),
                show: true
            })
            priceMarkupData.push({
                attribute: 'compare_at_price_by_amount',
                name: 'Compare at price by amount',
                value: Number($(this).attr('compare_at_price_by_amount')),
                show: $(this).attr('compare_at_price_markup') == '1'
            })
            priceMarkupData.push({
                attribute: 'compare_at_price_by_percent',
                name: 'Compare at price by percent',
                value: Number($(this).attr('compare_at_price_by_percent')),
                show: $(this).attr('compare_at_price_markup') == '0'
            })
            getFromProductMarkup = false
        } else {
            let productMarkupValues = getProductMarkupValues()
            priceMarkupData.push({
                attribute: 'price_markup',
                name: 'Price markup',
                value: productMarkupValues.price_markup,
                show: true
            })
            priceMarkupData.push({
                attribute: 'price_by_amount',
                name: 'Price by amount',
                value: Number(productMarkupValues.price_by_amount),
                show: productMarkupValues.price_markup == '1'
            })
            priceMarkupData.push({
                attribute: 'price_by_percent',
                name: 'Price by percent',
                value: Number(productMarkupValues.price_by_percent),
                show: productMarkupValues.price_markup == '0'
            })
            priceMarkupData.push({
                attribute: 'compare_at_price_markup',
                name: 'Compare at price markup',
                value: productMarkupValues.compare_at_price_markup,
                show: true
            })
            priceMarkupData.push({
                attribute: 'compare_at_price_by_amount',
                name: 'Compare at price by amount',
                value: Number(productMarkupValues.compare_at_price_by_amount),
                show: productMarkupValues.compare_at_price_markup == '1'
            })
            priceMarkupData.push({
                attribute: 'compare_at_price_by_percent',
                name: 'Compare at price by percent',
                value: Number(productMarkupValues.compare_at_price_by_percent),
                show: productMarkupValues.compare_at_price_markup == '0'
            })
        }
        $('.editVariantModal .modal-body .hasBorder').attr('data-id', $(this).attr('data-id'))
        $('.editVariantModal').attr('data-original-price', $(this).attr('original_price'))
        data.forEach((item) => {
            if (item.value.includes('(not set)')) {
                item.value = '';
            }
            $(container).append(`
            <div class="form-group col-md-6">
                <label class="mb-0"> ${item.name} :</label>
                <input type="${item.attribute == 'price' || item.attribute == 'compare_at_price' ? 'number' : 'text'}" name="${item.attribute}" value="${item.value}"  data-old-value="${item.value}" class="form-control mb-3 ${item.attribute == 'price' || item.attribute == 'compare_at_price' ? 'variantPriceMarkupEdit' : ''} editVariantInput">   
            </div>
            `)
        })
        priceMarkupData.forEach((item) => {
            if (item.attribute === 'price_markup' || item.attribute === 'compare_at_price_markup') {
                $(container).append(`
                    <div class="form-group col-md-6">
                        <label class="mb-0"> ${item.name} :</label>
                        <select name="${item.attribute}" value="${item.value}"  data-old-value="${item.value}"  class="form-select mb-3 variantPriceMarkupEdit">
                            <option value="0"
                                ${item.value == '0' ? 'selected' : ''}
                            > By Percent</option>
                            <option value="1"
                                ${item.value == '1' ? 'selected' : ''}
                            >By Amount</option>
                        </select>   
                    </div>
                `)
            } else {
                $(container).append(`
                    <div class="form-group col-md-6"  style="${!item.show ? 'display:none' : ''}">
                           <label class="mb-0" for="${item.attribute}"> ${item.name} :</label>
                           <input type="number" name="${item.attribute}" value="${item.value}" data-old-value="${item.value}" class="form-control form-control-lg variantPriceMarkupEdit">   
                    </div> `)
            }

        })
        $(container).append(`
            <div class="form-group">
                <div class="form-check form-check-inline col-md-12">
                    <label class="form-check-label" for="getFromProductMarkup"> Get From Product Markup</label>
                    <input type="checkbox" id="getFromProductMarkup" name="getFromProductMarkup" ${getFromProductMarkup ? 'checked' : ''} class="form-check-input mb-3 variantPriceMarkupEditCheckbox">   
                </div>
            </div>
            `)

        $('#editVariantModal').modal('show')
    })

    $(document).on('change', '.productCurrencySelector', function () {
        if ($(this).attr('data-disabled')) {
            $("#subscribeModal").modal('show');
            $(this).val($(this).attr('data-old-value'))
        } else {
            $('.saveProductCurrencyRateChanges').removeClass('disabled')
            let from = $('#supplierCurrency').val()
            let to = $('#shopifyCurrency').val()
            let csrf_param = $('meta[name="csrf-param"]').attr('content')
            let csrf_token = $('meta[name="csrf-token"]').attr('content')
            let data = {
                from,
                to,
            }
            data[csrf_param] = csrf_token
            $.ajax({
                type: 'POST',
                url: '/currency/convert',
                dataType: 'json',
                data,
                success: function (data) {
                    let fixed = 2
                    if (data < 0.1) {
                        data = 3
                    }
                    if (data < 0.01) {
                        data = 4
                    }
                    $('#currencyRate').val(data.toFixed(fixed))
                },
                error: function () {

                }
            })
        }
    })

    $(document).on('change', '#usersetting-import_reviews', function () {
        if ($(this).prop('checked')) {
            if ($('#usersetting-enable_add_reviews').prop('checked')) {
                $('#add-review').show()
            }
            $('.import_review_control').show()
        } else {
            $('#add-review').hide()
            $('.import_review_control').hide()
        }
    })

    $(document).on('change', '#usersetting-enable_add_reviews', function () {
        if ($(this).prop('checked')) {
            $('#add-review').show()
            $('#usersetting-enable_add_review_images').closest('.col-md-12').show()
        } else {
            $('#add-review').hide()
            $('#usersetting-enable_add_review_images').closest('.col-md-12').hide()
        }
    })

    $(document).on('change', '#usersetting-enable_add_review_images', function () {
        if ($(this).prop('checked')) {
            $('#reviewImage').show()
        } else {
            $('#reviewImage').hide()
        }
    })

    $(document).on('input', '#currencyRate', function () {
        notNegativeValue($(this), 1)
        if ($(this).attr('data-disabled')) {
            $("#subscribeModal").modal('show');
            $(this).val($(this).attr('data-old-value'))
        } else {
            if ($(this).val()) {
                $('.saveProductCurrencyRateChanges').removeClass('disabled')
            }
        }

    })
    $('#autoUpdateCurrencyRate').on('change', function () {
        if ($(this).attr('data-disabled')) {
            $("#subscribeModal").modal('show');
            $(this)[0].checked = false
        } else {
            $('.saveProductCurrencyRateChanges').removeClass('disabled')
        }
    })

    $('.saveProductCurrencyRateChanges').click(function () {
        if ($(this).attr('data-disabled')) {
            $("#subscribeModal").modal('show');
        } else {
            $('.currencyLoading').show()
            $('.currencyContainer').hide()
            let productId = $('.monitoring-checkbox')[0].getAttribute('data-id')
            let data = {
                Product: {
                    currency_id: $("#shopifyCurrency").val(),
                    default_currency_id: $("#supplierCurrency").val(),
                    currency_rate: $("#currencyRate").val(),
                    update_currency_rate: Number($("#autoUpdateCurrencyRate").is(':checked')),
                }
            }
            $.ajax({
                type: 'POST',
                url: '/product/update-product-currency?id=' + productId,
                dataType: 'json',
                data,
                success: function (data) {
                    window.location.reload()
                },
                error: function () {
                    window.location.reload()
                }
            })
        }

    })

    function notNegativeValue(e, changeTo = 0) {
        if (Number(e.val()) < 0) {
            e.val(changeTo || Number(e.val()) * -1 )
        }
    }

    $('.valueNotNegative').change(function() {
        notNegativeValue($(this))
    })

    $(document).on('input', "#usersetting-price_by_amount", function () {
        notNegativeValue($(this))
    })

    $(document).on('input', '#usersetting-compare_at_price_by_amount', function () {
        notNegativeValue($(this))
    })

    $(document).on('input', '#usersetting-price_by_percent', function () {
        notNegativeValue($(this))
    })

    $(document).on('input', '#usersetting-compare_at_price_by_percent', function () {
        notNegativeValue($(this))
    })

    $(document).on('change', '.variantPriceMarkupEditCheckbox', function () {
        if (!$('.editVariantModal').attr('data-disabled')) {
            if (this.checked) {
                let variantMarkupAttributes = getVariantMarkupAttributes();
                let productMarkupValues = getProductMarkupValues();
                let final = 0;
                let compareAtFinal = 0;
                variantMarkupAttributes.priceByPercent.val(productMarkupValues.price_by_percent)
                variantMarkupAttributes.priceByAmount.val(productMarkupValues.price_by_amount)
                variantMarkupAttributes.comparedAtByPercent.val(productMarkupValues.compare_at_price_by_percent)
                variantMarkupAttributes.compareAtByAmount.val(productMarkupValues.compare_at_price_by_amount)
                if (productMarkupValues.price_markup == '0') {
                    final = ((Number(variantMarkupAttributes.original) / 100) * Number(variantMarkupAttributes.priceByPercent.val())) + Number(variantMarkupAttributes.original)
                } else if (productMarkupValues.price_markup == '1') {
                    final = Number(variantMarkupAttributes.original) + Number(variantMarkupAttributes.priceByAmount.val())
                }
                variantMarkupAttributes.finalPrice.val(final.toFixed(2))
                if (productMarkupValues.compare_at_price_markup == '0') {
                    compareAtFinal = (final / 100) * Number(variantMarkupAttributes.comparedAtByPercent.val()) + final
                } else if (productMarkupValues.compare_at_price_markup == '1') {
                    compareAtFinal = final + Number(variantMarkupAttributes.compareAtByAmount.val())
                }
                variantMarkupAttributes.compareAtPrice.val(compareAtFinal.toFixed(2))
            }
        } else {
            $(this).val(true)
            $('#editVariantModal').modal('hide')
            $("#subscribeModal").modal('show');
        }

    })

    $(document).on('keydown', '.variantPriceMarkupEdit', function () {
        if ($('.editVariantModal').attr('data-disabled')) {
            $("#subscribeModal").modal('show');
            $(this).val($(this).attr('data-old-Value'))
            $('#editVariantModal').modal('hide')
        }
    })

    $(document).on('change', '.variantPriceMarkupEdit', function () {
        notNegativeValue($(this))
        if (!$('.editVariantModal').attr('data-disabled')) {
            const name = $(this).attr('name')
            const value = $(this).val()
            let variantMarkupAttributes = getVariantMarkupAttributes();
            $('.variantPriceMarkupEditCheckbox[name="getFromProductMarkup"]').removeAttr('checked')
            switch (name) {
                case 'price_markup':
                    if (value == '1') {
                        togglePriceLabelsAndButtons(variantMarkupAttributes, false, true)
                        variantMarkupAttributes.finalPrice.val((Number(variantMarkupAttributes.original) + Number(variantMarkupAttributes.priceByAmount.val())).toFixed(2))
                    } else if (value == '0') {
                        togglePriceLabelsAndButtons(variantMarkupAttributes, true, false)
                        variantMarkupAttributes.finalPrice.val((((Number(variantMarkupAttributes.original) / 100) * Number(variantMarkupAttributes.priceByPercent.val())) + Number(variantMarkupAttributes.original)).toFixed(2))
                    }

                    if (variantMarkupAttributes.comparePriceMarkup.val() == '1') {
                        variantMarkupAttributes.compareAtPrice.val((Number(variantMarkupAttributes.finalPrice.val()) + Number(variantMarkupAttributes.compareAtByAmount.val())).toFixed(2))
                    } else if (variantMarkupAttributes.comparePriceMarkup.val() == '0') {
                        variantMarkupAttributes.compareAtPrice.val((((Number(variantMarkupAttributes.finalPrice.val()) / 100) * Number(variantMarkupAttributes.comparedAtByPercent.val())) + Number(variantMarkupAttributes.finalPrice.val())).toFixed(2))
                    }

                    break;
                case 'price_by_amount':
                    variantMarkupAttributes.finalPrice.val((Number(variantMarkupAttributes.original) + Number($(this).val())).toFixed(2))
                    if (variantMarkupAttributes.comparePriceMarkup.val() == '1') {
                        variantMarkupAttributes.compareAtPrice.val((Number(variantMarkupAttributes.finalPrice.val()) + Number(variantMarkupAttributes.compareAtByAmount.val())).toFixed(2))
                    } else if (variantMarkupAttributes.comparePriceMarkup.val() == '0') {
                        variantMarkupAttributes.compareAtPrice.val((((Number(variantMarkupAttributes.finalPrice.val()) / 100) * Number(variantMarkupAttributes.comparedAtByPercent.val())) + Number(variantMarkupAttributes.finalPrice.val())).toFixed(2))
                    }
                    break;
                case 'price_by_percent':
                    variantMarkupAttributes.finalPrice.val((((Number(variantMarkupAttributes.original) / 100) * Number($(this).val())) + Number(variantMarkupAttributes.original)).toFixed(2))
                    if (variantMarkupAttributes.comparePriceMarkup.val() == '1') {
                        variantMarkupAttributes.compareAtPrice.val((Number(variantMarkupAttributes.finalPrice.val()) + Number(variantMarkupAttributes.compareAtByAmount.val())).toFixed(2))
                    } else if (variantMarkupAttributes.comparePriceMarkup.val() == '0') {
                        variantMarkupAttributes.compareAtPrice.val((((Number(variantMarkupAttributes.finalPrice.val()) / 100) * Number(variantMarkupAttributes.comparedAtByPercent.val())) + Number(variantMarkupAttributes.finalPrice.val())).toFixed(2))
                    }
                    break;
                case 'compare_at_price_markup':
                    if (value == '1') {
                        togglePriceLabelsAndButtons(variantMarkupAttributes, null, null, false, true)
                        variantMarkupAttributes.compareAtPrice.val((Number(variantMarkupAttributes.finalPrice.val()) + Number(variantMarkupAttributes.compareAtByAmount.val())).toFixed(2))
                    } else if (value == '0') {
                        togglePriceLabelsAndButtons(variantMarkupAttributes, null, null, true, false)
                        variantMarkupAttributes.compareAtPrice.val((((Number(variantMarkupAttributes.finalPrice.val()) / 100) * Number(variantMarkupAttributes.comparedAtByPercent.val())) + Number(variantMarkupAttributes.finalPrice.val())).toFixed(2))
                    }
                    break;
                case 'compare_at_price_by_amount':
                    variantMarkupAttributes.compareAtPrice.val((Number(variantMarkupAttributes.finalPrice.val()) + Number(variantMarkupAttributes.compareAtByAmount.val())).toFixed(2))
                    break;
                case 'compare_at_price_by_percent':
                    variantMarkupAttributes.compareAtPrice.val((((Number(variantMarkupAttributes.finalPrice.val()) / 100) * Number(variantMarkupAttributes.comparedAtByPercent.val())) + Number(variantMarkupAttributes.finalPrice.val())).toFixed(2))
                    break;
                case 'compare_at_price':
                    if (variantMarkupAttributes.comparePriceMarkup.val() == '1') {
                        variantMarkupAttributes.compareAtByAmount.val(Number(variantMarkupAttributes.compareAtPrice.val()) - Number(variantMarkupAttributes.finalPrice.val()))
                    } else if (variantMarkupAttributes.comparePriceMarkup.val() == '0') {
                        let percent = (Number(variantMarkupAttributes.compareAtPrice.val()) - Number(variantMarkupAttributes.finalPrice.val())) / (Number(variantMarkupAttributes.finalPrice.val()) / 100)
                        variantMarkupAttributes.comparedAtByPercent.val(percent.toFixed(2))
                    }
                    break;
                case 'price':
                    if (variantMarkupAttributes.priceMarkup.val() == '1') {
                        variantMarkupAttributes.priceByAmount.val(Number(variantMarkupAttributes.finalPrice.val()) - Number(variantMarkupAttributes.original))
                    } else if (variantMarkupAttributes.priceMarkup.val() == '0') {
                        let percent = (Number(variantMarkupAttributes.finalPrice.val()) - Number(variantMarkupAttributes.original)) / (Number(variantMarkupAttributes.original) / 100)
                        variantMarkupAttributes.priceByPercent.val(percent.toFixed(2))
                    }

                    if (variantMarkupAttributes.comparePriceMarkup.val() == '1') {
                        variantMarkupAttributes.compareAtPrice.val((Number(variantMarkupAttributes.finalPrice.val()) + Number(variantMarkupAttributes.compareAtByAmount.val()).toFixed(2)));
                    } else if (variantMarkupAttributes.comparePriceMarkup.val() == '0') {
                        variantMarkupAttributes.compareAtPrice.val((((Number(variantMarkupAttributes.finalPrice.val()) / 100) * Number(variantMarkupAttributes.comparedAtByPercent.val())) + Number(variantMarkupAttributes.finalPrice.val())).toFixed(2));
                    }
                    break;
                default:
            }
        } else {
            $("#subscribeModal").modal('show');
            $(this).val($(this).attr('data-old-Value'))
            $('#editVariantModal').modal('hide')

        }

    })

    function togglePriceLabelsAndButtons(variantMarkupAttributes, pbp = null, pba = null, cpbp = null, cpba = null) {
        if (pbp === false) {
            variantMarkupAttributes.priceByPercent.parent().hide()
        } else if (pbp === true) {
            variantMarkupAttributes.priceByPercent.parent().show()
        }

        if (pba === false) {
            variantMarkupAttributes.priceByAmount.parent().hide()
        } else if (pba === true) {
            variantMarkupAttributes.priceByAmount.parent().show()
        }

        if (cpbp === false) {
            variantMarkupAttributes.comparedAtByPercent.parent().hide()
        } else if (cpbp === true) {
            variantMarkupAttributes.comparedAtByPercent.parent().show()
        }

        if (cpba === false) {
            variantMarkupAttributes.compareAtByAmount.parent().hide()
        } else if (cpba === true) {
            variantMarkupAttributes.compareAtByAmount.parent().show()
        }
    }

    $('#editVariant').click(function () {
        const container = $('.editVariantModal .modal-body .hasBorder')
        const inputs = $(container).find($('.editVariantInput'));
        const priceMarkupData = $(container).find($('.variantPriceMarkupEdit'));
        const variantId = $(container).attr('data-id');
        let data = {
            ProductVariant: {
                getFromProductMarkup: Number($('.variantPriceMarkupEditCheckbox[name="getFromProductMarkup"]')[0].checked)
            },
            VariantPriceMarkup: {
                variant_id: variantId
            },
        }
        inputs.each((index, item) => {
            data.ProductVariant[item.getAttribute('name')] = item.value
        })
        priceMarkupData.each((index, item) => {
            data.VariantPriceMarkup[item.getAttribute('name')] = item.value
        })
        $('#editVariantModal .loading').show()
        $('.editVariantModal .variantControl').hide()
        $.ajax({
            type: "POST",
            url: `/product-variant/update?id=${variantId}`,
            dataType: "json",
            data: data,
            success: function (data) {
                window.location.reload();
            },
            error: function (e) {
                let error = JSON.parse(e.responseText)
                $('#editVariantModal .loading').hide()
                $('.editVariantModal .variantControl').show()
                for (const [key, value] of Object.entries(error)) {
                    $('.editVariantModal .modal-content').append(`
                        <p class="text-danger errorMessage">${value[0]}</p>
                    `)
                }
            }
        });
    })

    $(document).on('click', '.shionMonitorNowDisabled', function(e) {
        if (!$(this).hasClass('classDisabledShionExtension')) {
            e.preventDefault()
            $("#subscribeModal").modal('show');
        }
    })

    $(document).on('click', '.editProductPricingRules', function () {
        if ($('.editProductPrice').attr('data-disabled')) {
            $("#subscribeModal").modal('show');
        } else {
            const productMarkupValues = getProductMarkupValues()
            const productDefaultValues = getProductMarkupDefaultValues()

            for (key in productMarkupValues) {
                let input = $(`.editProductPrice input[name="${key}"]`)
                let select = $(`.editProductPrice select[name="${key}"]`)
                if (input) {
                    input.val(productMarkupValues[key])
                } else if (select) {
                    select.val(productMarkupValues[key])
                }
            }

            if (productMarkupValues.price_markup == 1) {
                $(`.editProductPrice input[name="price_by_percent"]`).parent().hide()
                $(`.editProductPrice input[name="price_by_amount"]`).parent().show()
            } else {
                $(`.editProductPrice input[name="price_by_amount"]`).parent().hide()
                $(`.editProductPrice input[name="price_by_percent"]`).parent().show()
            }

            if (productMarkupValues.compare_at_price_markup == 1) {
                $(`.editProductPrice input[name="compare_at_price_by_percent"]`).parent().hide()
                $(`.editProductPrice input[name="compare_at_price_by_amount"]`).parent().show()
            } else {
                $(`.editProductPrice input[name="compare_at_price_by_amount"]`).parent().hide()
                $(`.editProductPrice input[name="compare_at_price_by_percent"]`).parent().show()
            }

            $('#editProductPrice').modal('show')
        }
    })
    $(document).on('change', '.productPriceMarkupEditSelect', function () {
        let priceByPercent = $(`.editProductPrice input[name="price_by_percent"]`)
        let priceByAmount = $(`.editProductPrice input[name="price_by_amount"]`)
        let compareAtPriceByPercent = $(`.editProductPrice input[name="compare_at_price_by_percent"]`)
        let compareAtPriceByAmount = $(`.editProductPrice input[name="compare_at_price_by_amount"]`)

        if ($(this).val() == 1) {
            if ($(this).attr('name') === 'price_markup') {
                priceByPercent.parent().hide()
                priceByAmount.parent().show()
            } else if ($(this).attr('name') === 'compare_at_price_markup') {
                compareAtPriceByPercent.parent().hide()
                compareAtPriceByAmount.parent().show()
            }
        } else {
            if ($(this).attr('name') === 'price_markup') {
                priceByAmount.parent().hide()
                priceByPercent.parent().show()
            } else if ($(this).attr('name') === 'compare_at_price_markup') {
                compareAtPriceByAmount.parent().hide()
                compareAtPriceByPercent.parent().show()
            }
        }
    })

    $('#editProductPricing').click(function () {
        let productId = $('.monitoring-checkbox')[0].getAttribute('data-id')
        const data = {
            ProductPriceMarkup: {
                'price_markup': $(`.editProductPrice select[name="price_markup"]`).val(),
                'compare_at_price_markup': $(`.editProductPrice select[name="compare_at_price_markup"]`).val(),
                'price_by_percent': $(`.editProductPrice input[name="price_by_percent"]`).val(),
                'price_by_amount': $(`.editProductPrice input[name="price_by_amount"]`).val(),
                'compare_at_price_by_percent': $(`.editProductPrice input[name="compare_at_price_by_percent"]`).val(),
                'compare_at_price_by_amount': $(`.editProductPrice input[name="compare_at_price_by_amount"]`).val(),
            }
        }

        $('.editProductPrice .loading').css({'display': 'flex'})
        $(this).hide()

        $.ajax({
            type: "POST",
            url: '/product/update-product-price-markup?id=' + productId,
            dataType: "json",
            data: data,
            success: function (data) {
                window.location.reload()
            },
            error: (error) => {

            }
        })
    })

    $(document).on('change', '.selectProduct', function () {
        if (!$(this).prop('checked')) {
            $('.checkAllProducts').prop('checked', false)
        }
    })

    function getSelectedProductIds()
    {
        const productIds = []
        $('.selectProduct').each(function (index, item) {
            if ($(this).prop('checked')) {
                productIds.push($(this).attr('data-id'))
            }
        })

        return productIds;
    }
    let activeId = false

    let updateInterval

    $(document).on('click', '.productBulkMonitoring', function() {
        if ($(this).attr('data-disabled')) {
            $("#subscribeModal").modal('show');
        } else {
            const ids = getSelectedProductIds()

            $('.productBulkMonitoring').hide()
            $('.deleteMultipleProducts').hide()
            $('.tableHeadControl').append(getLoadingDiv(1))
            if (!ids.length) {
                return false
            }

            $.ajax({
                type: "POST",
                url: '/bulk-monitoring/create',
                dataType: "json",
                data: {ids},
                success: function (data) {
                    console.log(data)
                    if (data.success) {
                        $('.tableHeadControl').find('.loading').remove()
                        if (!data.active) {
                            bulkMonitoringEnd()
                            return
                        }
                        $('.selectProduct').hide()
                        $('.checkAllProducts').hide()
                        $('.tableHeadControl').find('.loading').remove()
                        $('#products_table thead tr .productListSelectAction').append('<span>Status</span>')
                        $('body').attr('data-monitoring-id', data.monitoring_id)
                        Object.keys(data.statuses)?.forEach((i, index) => {
                            let tr = $('tr[data-key="' + i + '"]').next('tr')
                            let statusText = data.statuses[i] == 1 ? 'Success' : data.statuses[i] == 2 ? 'Failed' : 'Pending'

                            if (!index) {
                                activeId = data.active
                                window.open(`${tr.attr('data-url')}?shionMonitorProductNow=${i}&shionMonitorProductNowBulk=${data.ids[i]}`)
                                tr.find('.productListSelectAction').append(getLoadingDiv(i))
                                tr.find('.productListSelectAction').attr('id', `ite-${i}`)
                            } else {
                                tr.find('.productListSelectAction').prepend(`<span class="bulkmonitorprepend" id="ite-${i}"> ${statusText} </span>`)
                                tr.find('.productListSelectAction').attr('id', `ite-${i}`)
                            }
                        })
                        updateInterval = setInterval(function() {updateMonitoringStatuses();},5000)
                    } else {
                        Swal.fire({
                            title: "Bulk monitoring",
                            text: "Something went wrong",
                        });
                    }
                },
                error: function () {
                    $('.loading').hide()
                    $('.productsTable').show()
                    $('.pagination').show()
                }
            })
        }
    })

    function getLoadingDiv(i)
    {
        return `<div class="loading" style="width:70px">
                    <div class="sk-three-bounce">
                        <div class="sk-child sk-bounce1"></div>
                        <div class="sk-child sk-bounce2"></div>
                         <div class="sk-child sk-bounce3"></div>
                    </div>
                </div>`
    }

    var successModalShowed = false

    function bulkMonitoringEnd()
    {
        $('.bulkmonitorprepend').hide()
        $('#bulkmonitoringhead').hide()
        $('.selectProduct').show()
        $('.productBulkMonitoring').show()
        $('.deleteMultipleProducts').show()
        $('.checkAllProducts').show()
        $('.productListSelectAction').find('span').remove()
        $('.productListSelectAction').find('.loading').remove()
        $('.selectProduct').prop('checked', false)
    }


    function updateMonitoringStatuses()
    {
        let id = $('body').attr('data-monitoring-id')
        var active = false
        $.ajax({
            type: "POST",
            url: '/bulk-monitoring/update-statuses',
            dataType: "json",
            data: {id},
            success: function (data) {
                if (data.success) {
                    if (!data.active) {
                        clearInterval(updateInterval)
                        bulkMonitoringEnd()
                       if (!successModalShowed) {
                           successModalShowed = true
                           swal({
                               title: 'Bulk monitoring',
                               text: 'Monitoring Completed!',
                               type: 'success'
                           });
                       }
                        return
                    }

                    Object.keys(data.statuses)?.forEach((i, index) => {
                        let statusText = data.statuses[i] == 1 ? 'Success' : data.statuses[i] == 2 ? 'Failed' : 'Pending'
                        let tr = $('tr[data-key="' + i + '"]').next('tr')

                        if (data.active && data.active == i && activeId != data.active) {
                            activeId = data.active
                            window.open(`${tr.attr('data-url')}?shionMonitorProductNow=${i}&shionMonitorProductNowBulk=${data.ids[i]}`)
                            active = true
                            $(`#ite-${i}`).find('span').remove()
                            $(`#ite-${i}`).find('.loading').remove()
                            $(`#ite-${i}`).append(getLoadingDiv(i))
                        } else if (data.active != i) {
                            $(`#ite-${i}`).find('span').remove()
                            $(`#ite-${i}`).find('.loading').remove()
                            $(`#ite-${i}`).append(`
                                <span>${statusText}</span>
                            `)
                        }
                    })
                } else {
                    Swal.fire({
                        title: "Bulk monitoring",
                        text: "Something went wrong",
                    });
                }
            },
        })
        return true
    }

    $(document).on('click', '.deleteMultipleProducts', function () {
        if ($(this).attr('data-disabled')) {
            $("#subscribeModal").modal('show');
        } else {
            const ids = getSelectedProductIds()
            console.log(typeof Swal !== "undefined");


            if (!ids.length) {
                return false
            }

            new swal({
                title: 'Are you sure you want to delete these products?',
                html: '',
                type: 'confirm',
                showCancelButton: true,
                confirmButtonColor: "#13B497",
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel",
            }).then(function (e) {
                if (e.value === true) {
                    $('.loading').show()
                    $('.productsTable').hide()
                    $('.pagination').hide()
                    $.ajax({
                        type: "POST",
                        url: '/product/add-to-delete-queue',
                        dataType: "json",
                        data: {ids},
                        success: function (data) {
                            window.location.reload()
                        },
                        error: function () {
                            $('.loading').hide()
                            $('.productsTable').show()
                            $('.pagination').show()
                        }
                    })
                }
            });
        }
    })

    $('.openHelpModal').click(function() {
        const dataId = $(this).attr('data-id')
        $('#helpModal').modal('show');
        $(`.help_modal_item_title[data-id="${dataId}"]`).show()
        $(`.help_modal_item[data-id="${dataId}"]`).show()
    })

    function pastDropImage(e) {
        var clipboardData = e.originalEvent.clipboardData || e.originalEvent.dataTransfer;
        if (clipboardData && clipboardData.items) {
            for (var i = 0; i < clipboardData.items.length; i++) {
                var item = clipboardData.items[i];

                if (item.type.indexOf('image') !== -1) {
                    // Handle pasted image
                    var file = item.getAsFile();
                    displayPastedImage(file);
                }
            }
        }
    }

    $(document).on('click', '#imagePreview .imageContainer .close', function() {
        $(this).closest('.imageContainer').remove()
    })

    $(document).on('click', '.modal .modal-content .close', function() {
        $(this).closest('.modal').hide()
    })

    $('#uploadform-imagefile').on('change', function (e) {
        var files = e.target.files[0];
        console.log(e.target.files)
        if (e.target.files.length) {
            displayPastedImage(e.target.files[0]);
        }
    });

    $('#editorLead').on('paste', function (e) {
        pastDropImage(e)
    })
    $('#editorLead').on('drop', function (e) {
        pastDropImage(e)
    })

    function displayPastedImage(file) {
        var reader = new FileReader();

        reader.onload = function (e) {
            var imageUrl = e.target.result;
            var imageContainer = $(`<div class='imageContainer'><span class='close'><iconify-icon icon="material-symbols:close"></iconify-icon></span> <img class='pasted-image leadImageSmall' src="${imageUrl}"></div>`)
            $('#editorLead').find('img').remove()
            $('#imagePreview').append(imageContainer);
        };

        reader.readAsDataURL(file);
    }

    $('.leadSend').click(async function (e) {
        e.preventDefault()
        var message = $('#editorLead').val();
        var subject = $('.leadSubject').val();
        var additional_data = $('#lead-additional_data').val();
        var images = [];

        $('#imagePreview img').each(function () {
            images.push($(this).attr('src'));
        });

        var formData = new FormData();
        let url = '/site/contact'

        if ($('.cardTitle').length) {
            url = `/site/chat?lead_id=${$('.cardTitle').attr('data-id')}`
            formData.append("LeadMessage[message]", message)
        } else {
            formData.append("Lead[message]", message)
            formData.append("Lead[subject_id]", subject)
            formData.append("Lead[additional_data]", additional_data)
        }

        var elements = document.getElementById('imagePreview').querySelectorAll('img')
        for (let i = 0; i < elements.length; i++) {
            var imageUrl = $(elements[i]).attr('src');
            var date = Date.now();
            await fetch(imageUrl)
                .then(response => response.blob())
                .then(blob => {
                    var file = new File([blob], 'image' + date + '.png', {type: 'image/png'});
                    formData.append('UploadForm[images][]', file);
                });
        }

        $(this).attr('disabled', true)
        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            success: function(data) {
              window.location.reload()
            },
            contentType: false,
            processData: false,
        });

    })

    $(document).on('change', '.checkAllProducts', function () {
        let checked = $(this).prop("checked")
        $('.selectProduct').each(function (index, item) {
            $(this).prop('checked', checked)
        })
    })

});

function suggestReview(suggestReviewTitle, suggestReviewText) {
    const reviewLink = 'https://apps.shopify.com/shein-importer/reviews';

    swal({
        title: suggestReviewTitle,
        html: suggestReviewText,
        type: 'confirm',
        showCancelButton: true,
        confirmButtonColor: "#13B497",
        confirmButtonText: "Leave Review",
        cancelButtonText: "Cancel",
    }).then(function (e) {
        if (e.value === true) {
            window.open(reviewLink);
        }
    });
}

function showAlert(title, text) {
    const reviewLink = 'https://apps.shopify.com/shein-importer/reviews';

    swal({
        title: title,
        html: text,
        type: 'confirm',
        showCancelButton: true,
        confirmButtonColor: "#13B497",
        confirmButtonText: "Leave Review",
        cancelButtonText: "Cancel",
    }).then(function (e) {
        if (e.value === true) {
            window.open(reviewLink);
        }
    });
}

$('#editProductDescription').click(function() {
    $('#editProductDescriptionModal').show()
})

$("#saveDescriptionChanges").click(function() {
    let data = CKEDITOR.instances['editor1'].getData();
    let productId = $(this).attr('data-id')
    new swal({
        title: 'Are you sure you want to change product description?',
        html: '',
        type: 'confirm',
        showCancelButton: true,
        confirmButtonColor: "#13B497",
        confirmButtonText: "Save",
        cancelButtonText: "Cancel",
    }).then(function (e) {
        if (e.value === true) {
            $('.loading').show()
            $("#saveDescriptionChanges").hide()
            $.ajax({
                type: "POST",
                url: '/product/edit-description',
                dataType: "json",
                data: {
                    productId,
                    data
                },
                success: function (data) {
                    window.location.reload()
                },
                error: function () {
                    $('.loading').hide()
                    $("#saveDescriptionChanges").show()
                }
            })
        }
    });
})

$(document).on('click', '.leadImageSmall', function() {
    const src = $(this).attr('src')
    $('.leadImageZoom').show()
    $('.leadImageZoomPreview').attr('src', src)
})

$('.leadImageZoom').click(function() {
    $(this).hide()
})

$('.leadImageZoomPreview').click(function(e) {
    e.stopPropagation()
})

$(document).on('change', ".bulk-check", function () {
    $('.form-check-input').not(this).prop('checked', this.checked);}
)


$(document).ready(function () {
    const bulkSelectionButton = $('.bulk-selection');
    const actionBar = $('<div>').attr('id', 'action-bar').addClass('action-bar');
    const cancelBulkButton = $('<button>').text('Cancel Bulk Selection').addClass('btn bg-primary-subtle text-primary');
    const importButton = $('<button>').attr('data-bs-target', '#bulk-import-modal').attr('data-bs-toggle', 'modal').text('Import Products').addClass('btn bg-primary-subtle text-primary import-button');
    const selectedCount = $('<span>').attr('id', 'selected-count').text('Selected: 0').addClass('selected-count');

    let bulkSelectionEnabled = false;

    actionBar.append(cancelBulkButton, importButton, selectedCount);
    $('body').append(actionBar);

    bulkSelectionButton.on('click', function () {
        bulkSelectionEnabled = true;
        bulkSelectionButton.hide();
        actionBar.css('display', 'flex');
    });

    cancelBulkButton.on('click', function () {
        bulkSelectionEnabled = false;
        bulkSelectionButton.show();
        $('.btn-primary').show();
        actionBar.hide();
        $('.product-card').removeClass('selected');
        selectedCount.text('Selected: 0');
    });

    $(document).on('click', '.product-card', function () {
        if (bulkSelectionEnabled) {
            $(this).toggleClass('selected');
            const selectedCards = $('.product-card.selected').length;
            selectedCount.text(`Selected: ${selectedCards}`);

            if (selectedCards === 0) {
                bulkSelectionEnabled = false;
                bulkSelectionButton.show();
                $('.btn-primary').show();
                actionBar.hide();
                selectedCount.text('Selected: 0');
                importButton.prop('disabled', true);
                importButton.prop('readonly', true);
            } else {
                importButton.prop('disabled', false);
                importButton.prop('readonly', false);
            }
        }
    })

    $(document).on('click', '.import-button', function () {
        $('#importModal').modal('show');
        let selectedIds = $(".card.selected").map(function () {
            return $(this).data("productId");
        }).get();

        $("#bulkImportProductIds").val(JSON.stringify(selectedIds));
    });
})