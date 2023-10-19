$(document).ready(function () {

    let update_link = 'index.php?sta=ws/api_links'; // TODO - set this link!

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // -------------------------------------------------------------------------------------------------------------- //

    let imageSource = '';

    $('.photo-return-id').change(function () {
        var data = new FormData();
        var ins = document.getElementById($(this).attr('id')).files.length;

        data.append('photo-input', document.getElementById($(this).attr('id')).files[0]);  // Append an image
        data.append('path', $(this).attr('path'));                                           // Append source of image - place where to save !
        data.append('category', $(this).attr('category'));

        let photoID    = $(this).attr('photo-name');
        let src        = $(this).attr('path');
        let input_id   = $(this).attr('photo-name') + '-input';
        // document.getElementById("loading_wrapper").style.display = 'block'; /** show loading part **/

        imageSource = $(this).attr('path');

        var xml = new XMLHttpRequest();
        xml.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let response = JSON.parse(this.responseText);

                if(response['code'] === '0000'){
                    // Set source of an image returned from ajax request
                    let image = document.getElementById(photoID);
                    image.setAttribute('src', src + response['photo']);

                    // Set input as image ID for additional processing
                    $('#'+photoID+'-input').val(response['photo']);
                }else{
                    $.notify(response['message'], "warn");
                }
            }
        };
        xml.open('POST', $(this).attr('url'));

        // ** Postavi tokene ** //
        var metas = document.getElementsByTagName('meta');
        for (var i=0; i<metas.length; i++) {
            if (metas[i].getAttribute("name") == "csrf-token") {
                xml.setRequestHeader("X-CSRF-Token", metas[i].getAttribute("content"));
            }
        }
        xml.send(data); // napravi http
    });

    // -------------------------------------------------------------------------------------------------------------- //

    $(".user-image").click(function () {
        $(".mp-profile-image-element").fadeIn();
    });

    // ** Save profile image ** //

    $(".mp-pie-submit").click(function () {
        let image = $("#profile-image-p-input").val();
        $.ajax({
            url: update_link,
            method: 'POST',
            data: {
                image: image,
                uploadImage : true
            },
            success: function success(response) {

                if(response['code'] === '0000'){
                    $.notify(response['message'], "success");

                    $(".mp-profile-image").attr('src', imageSource + image);
                    $(".mp-profile-image-element").fadeOut();
                }else{
                    $.notify(response['message'], "warn");
                }
                console.log(response);
            }
        });
    });
});
