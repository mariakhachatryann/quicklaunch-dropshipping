$('body').on('click', '.generate-message', function () {
    let leadId = $(this).attr('data-lead-id');
    let message = $('.lead-message-form .lead_message').text();
    let answer = $('#leadmessage-prepare_message').val();
    let messageId = $(this).attr('data-message-id');
    let type = 'lead-reply';
    $.ajax({
        type: "POST",
        url: "prepare-message",
        data: {leadId, message, answer, messageId, type},
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                CKEDITOR.instances['leadmessage-message'].setData(res.content)
            }
        }
    });
});

$('#leadmessage-messageImage').on('paste', function (e) {
    pastDropImage(e)
})

$('#leadmessage-messageImage').on('drop', function (e) {
    pastDropImage(e)
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

$('#uploadform-imagefile').on('change', function (e) {
    var files = e.target.files[0];
    if (e.target.files.length) {
        displayPastedImage(e.target.files[0]);
    }
});

function displayPastedImage(file) {
    var reader = new FileReader();

    reader.onload = function (e) {
        var imageUrl = e.target.result;
        var imageContainer = $(`<div class='imageContainer'><span class='close'><svg style="width:20px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M464 32H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48zm-83.6 290.5c4.8 4.8 4.8 12.6 0 17.4l-40.5 40.5c-4.8 4.8-12.6 4.8-17.4 0L256 313.3l-66.5 67.1c-4.8 4.8-12.6 4.8-17.4 0l-40.5-40.5c-4.8-4.8-4.8-12.6 0-17.4l67.1-66.5-67.1-66.5c-4.8-4.8-4.8-12.6 0-17.4l40.5-40.5c4.8-4.8 12.6-4.8 17.4 0l66.5 67.1 66.5-67.1c4.8-4.8 12.6-4.8 17.4 0l40.5 40.5c4.8 4.8 4.8 12.6 0 17.4L313.3 256l67.1 66.5z"/></svg>
        </span> <img class='pasted-image leadImageSmall' src="${imageUrl}"></div>`)
        $('#leadmessage-messageImage').find('img').remove()
        $('#imagePreview').append(imageContainer);
    };

    reader.readAsDataURL(file);
}

$('.leadSend').click(async function (e) {
    e.preventDefault()
    var message = $('#leadmessage-message').val();
    var subject = $('#leadmessage-prepare_message').val();
    var images = [];

    $('#imagePreview img').each(function () {
        images.push($(this).attr('src'));
    });

    var formData = new FormData();
    formData.append("LeadMessage[message]", message)
    formData.append("LeadMessage[prepare_message]", subject)
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
        url: '/admin/lead-message/create?lead_id=' + location.href.split('lead_id=')[1] ,
        data: formData,
        success: function(data) {
            window.location.href = '/admin/lead-message/view?id=' + data
        },
        contentType: false,
        processData: false,
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