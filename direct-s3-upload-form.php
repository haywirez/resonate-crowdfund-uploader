<?php
/* Template Name: Direct S3 Form */
require_once(__DIR__ . '/vendor/autoload.php');

use EddTurtle\DirectUpload\Signature;
use Ramsey\Uuid\Uuid;

get_header();
get_currentuserinfo();

$current_user = wp_get_current_user();
$uuid4 = Uuid::uuid4();
$awsUuid = $uuid4->toString();
$audioForm = new Signature(
        getenv('AWS_ACCESS_KEY_ID'),
        getenv('AWS_SECRET_ACCESS_KEY'),
        getenv('AWS_S3_BUCKET_NAME'),
        getenv('AWS_S3_REGION'),
        [
        'max_file_size' => 20,
        'expires' => '+10 minutes',
        'content_type' => 'audio/mpeg',
        'default_filename' => 'test/track/audio/' . $awsUuid . '.mp3',
        'additional_inputs' => [
            'x-amz-meta-artist' => '',
            'x-amz-meta-track-name' => '',
            'x-amz-meta-album' => '',
            'x-amz-meta-track-duration' => '',
            'x-amz-meta-visual-key' => $awsUuid,
            'x-amz-meta-owner-id' => $current_user->ID
        ],
        ]
    );
$visualForm = new Signature(
        getenv('AWS_ACCESS_KEY_ID'),
        getenv('AWS_SECRET_ACCESS_KEY'),
        getenv('AWS_S3_BUCKET_NAME'),
        getenv('AWS_S3_REGION'),
        [
        'max_file_size' => 2,
        'expires' => '+10 minutes',
        'content_type' => 'image/jpeg',
        'default_filename' => 'test/track/visual/' . $awsUuid . '.jpg',
        'additional_inputs' => [
            'x-amz-meta-owner-id' => $current_user->ID
        ],
        ]
    );
?>
<link rel='stylesheet' href='https://resonate.is/wp-content/plugins/gravityforms/css/formsmain.min.css' type='text/css' media='all' />
<!-- The two real forms that will have to be submitted in sequence, probably best to manipulate by IDs  -->
<form action="<?php echo $audioForm->getFormUrl(); ?>" method="post" enctype="multipart/form-data" id="audio-form">
    <?php echo $audioForm->getFormInputsAsHtml(); ?>
    <input type="file" name="file" id="audio-file-input" accept="audio/mpeg" style="opacity: 0;
            position: absolute;
            top: 0px;
            left: 0px;">
</form>
<form action="<?php echo $visualForm->getFormUrl(); ?>" method="post" enctype="multipart/form-data" id="visual-form">
    <?php echo $visualForm->getFormInputsAsHtml(); ?>
    <input type="file" name="file" id="visual-file-input" accept="image/jpeg" style="opacity: 0;
            position: absolute;
            top: 0px;
            left: 0px;">
</form>
<form id="fake-form">
<div class="row">
	<div class="small-12 large-12 columns" role="main">
        <div class="gform_wrapper">
            <label for="track-name">Track Name</label>
            <div class="error-message track-name-error"></div>
                <input type="text" name="track-name" id="track-name">
            <label for="album">Album</label>
            <div class="error-message album-name-error"></div>
                <input type="text" name="album" id="album-name">
            <label for="artist">Artist Name</label>
            <div class="error-message artist-name-error"></div>
                <input type="text" name="artist" value="" id="artist-name">
        <!-- here we will probably have to have two upload areas, maybe left/right split? one for the audio, one for the visual (cover art) -->
        <div class="row">
            <div class="small-12 large-2 columns" role="visual-upload-area">
                <img src="" class="hidden" id="image-preview">
                <input id="select-visual" type="button" value="Select image" class="button gform_button_select_files" style="z-index: 1;">
            </div>
            <div class="small-12 large-10 columns" role="audio-upload-area">
                <div class="gform_fileupload_multifile">
                    <div class="gform_drop_area" style="position: relative;">
                        <span class="gform_drop_instructions">Drop files here or </span>
                        <input id="select-audio" type="button" value="Select files" class="button gform_button_select_files" style="z-index: 1;">
                        <span class="gform_drop_instructions">(mp3, max. 20 MB)</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- checkboxes are "fake", but they need to react upon click, and need to be checked. CSS is stolen from WP gravity forms -->
            <div class="error-message" id="terms-error-message"></div>
            <div class="ginput_container ginput_container_checkbox">
                <ul class="gfield_checkbox" style="list-style: none; margin-left: 0;">
                    <li class="gfield_checkbox">
                        <label for="x-amz-meta-no-covers">These songs are 100% written by me or my band. NO COVERS.</label>
                    </li>
                    <li class="gfield_checkbox">
                        <label for="x-amz-meta-streaming-agreement" >Resonate may stream these songs for free during the crowd campaign</label>
                    </li>
                    <li class="gfield_checkbox">
                    <label for="x-amz-meta-song-title-information" >All song titles, artist names and artwork are included in these files.</label>
                    </li>
                </ul>
            </div>
            <div class="upload-button button disabled">Upload</div>
        </div>
	</div>
</div>
</form>
<style>
.dragover {
background: rgba(84, 235, 128, 0.3);
}
.checkbox-checked:before {
    background: #54E866;
}

// animation
@-webkit-keyframes shake {
    0%, 100% {
        -webkit-transform: translate3d(0, 0, 0);
        transform: translate3d(0, 0, 0);
    }

    10%, 30%, 50%, 70%, 90% {
        -webkit-transform: translate3d(-5px, 0, 0);
        transform: translate3d(-5px, 0, 0);
    }

    20%, 40%, 60%, 80% {
        -webkit-transform: translate3d(5px, 0, 0);
        transform: translate3d(5px, 0, 0);
    }
}

@keyframes shake {
    0%, 100% {
        -webkit-transform: translate3d(0, 0, 0);
        transform: translate3d(0, 0, 0);
    }

    10%, 30%, 50%, 70%, 90% {
        -webkit-transform: translate3d(-5px, 0, 0);
        transform: translate3d(-5px, 0, 0);
    }

    20%, 40%, 60%, 80% {
        -webkit-transform: translate3d(5px, 0, 0);
        transform: translate3d(5px, 0, 0);
    }
}

.shake {
    -webkit-animation-name: shake;
    animation-name: shake;
    -webkit-animation-duration: 1s;
    animation-duration: 1s; 
}
.error-message {
  color: red;
  font-size: 12px;
}
input.error {
  border: 2px solid red;
  margin-bottom: 1rem;
}
</style>
<script type="text/javascript" src="https://cdn.rawgit.com/aadsm/jsmediatags/master/dist/jsmediatags.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
<script type="text/javascript">
/**
 * ====== 
 * BASICS:
 * ======
 *
 * What the php code is doing is creating a signed form that the AWS S3 endpoint will accept upon POSTing.
 * It contains a JSON policy that is signed server side by the API key - the policy contains information 
 * about filesize limits, destination, allowed form fields and values. This way we don't have to send anything through our server,
 * it goes directly into storage. More info about this on the following links:
 * 
 * http://docs.aws.amazon.com/AmazonS3/latest/API/sigv4-authentication-HTTPPOST.html
 * https://github.com/eddturtle/direct-upload
 * https://www.designedbyaturtle.co.uk/2015/direct-upload-to-s3-using-aws-signature-v4-php/
 * 
 * =====
 * TODO:
 * =====
 * 
 * We will actually need to create 2 signed forms + a fake form used for the inputs.
 * - for the audio file
 * - for the cover art image
 * 
 * These two forms have to be submitted via AJAX, because we want to stay on the page to do some additional processing.
 * The php code contains a generated UUID, this server as the common identifier for the files for now: 
 * (9204391e-3b58-4d3e-8a1d-b81a976a1fb9.mp3 -> 9204391e-3b58-4d3e-8a1d-b81a976a1fb9.jpg)
 * 
 * Pseudocode for the JavaScript:
 *  Make drag & drop work (<input type="file"> has to be populated with the path upon dropping), see resources
 *  select file onClick -> trigger <input name="audio-file" onClick>
 *  Upload button onClick event  -> validate form
 *                                  -> if not valid, show error messages
 *                                  -> if valid, fill x-amz-meta-track-name etc. hidden fields (VERY IMPORTANT)
 *                                       -> send both forms via an ajax POST request, in sequence (mp3, visual)
 *                                          -> if successful (returns statusCode 201, created), drop the UUID into a localStorage field.
                                                submitted: {
                                                    audio: $uuid
                                                    visual: $uuid
                                                }
 *                                          -> show a visual indicator of success on the frontend UI (checkmark appers, flashing)
 *                                          -> BONUS: send another post to a php endpoint that will save the metadata 
 *                                             (track name, uuid, etc) into a custom WP post type for the logged in user. this will come handy
 *                                             later if someone wants to delete their track, or replace it with a different version
 * 
 *  BONUS: 
 *  - preview cover art after selecting image file
 *  - extract metadata, images duration from audio file after selecting
 * 
 *  Test the form AJAX in all major browsers
 *  Work on CSS (currently uses some stolen classes from WP GravityForms)   
 * 
 * =========
 * RESOURCES
 * =========
 * Drag & drop
 * https://css-tricks.com/drag-and-drop-file-uploading/
 * Extract duration from audio file:
 * https://jsfiddle.net/derickbailey/s4P2v/
 * 
 * Hit me up on the resonate slack @attila for questions, I'll try to help
 */
document.addEventListener('DOMContentLoaded', function (event) {
    console.log("...it's alive!!!")

    // not a real jquery, just a wrapper :) jQuery is available though, maybe we should rewrite everything to use it
    var $ = function (x) {
        return document.querySelectorAll(x)
    }

    // drag & drop section
    var dragDropTarget = $('.gform_fileupload_multifile')[0]

    function dropZoneDragover (ev) {
        dragDropTarget.classList.add('dragover')
        ev.preventDefault()
        ev.stopPropagation()
    }

    function drop (ev) {
        ev.preventDefault()
        ev.stopPropagation()
        console.log(ev.dataTransfer.files)
        // add dropped file to input field
        $('#audio-file-input')[0].files = ev.dataTransfer.files
        $('.gform_drop_instructions')[1].innerText = ev.dataTransfer.files[0].name
        dragDropTarget.classList.remove('dragover')
    }

    function dropZoneDragleave (ev) {
        dragDropTarget.classList.remove('dragover')
    }

    function b64toBlob (b64Data, contentType, sliceSize) {
        contentType = contentType || ''
        sliceSize = sliceSize || 512
        var byteCharacters = atob(b64Data)
        var byteArrays = []
        for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
            var slice = byteCharacters.slice(offset, offset + sliceSize)
            var byteNumbers = new Array(slice.length)
            for (var i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i)
            }
            var byteArray = new Uint8Array(byteNumbers)
            byteArrays.push(byteArray)
        }
        var blob = new Blob(byteArrays, { type: contentType })
        return blob
    }

    function uploadProgressHandler (e) {
        if (e.lengthComputable) {
            var max = e.total
            var current = e.loaded
            var percentage = Math.floor((current * 100) / max)
            console.log('upload: %c' + percentage + '% complete', 'color: yellow')
            if (percentage >= 100) {
                // process completed
            }
        }
    }

    function validateForm () {
        nameInputsValid()
        termsAndCondValid()
        /**
         * Validation placeholder:
         * -- input fields cannot be empty / must be strings
         * -- both file inputs (audio/visual) must be filled with a valid .mp3, .jpeg file
         * -- all terms & conditions checkboxes must be in a checked state
         * -- hidden fields in both signed forms must be populated and equal the values of the inputs
         * -- if not valid, add a red border to the missing places, and perhaps an explanation box
         */
        if (!window.overrideForm) {
            return false
        } else {
            console.log('%call form fields are filled out & valid.', 'color: #00FF00')
            console.log('%cattempting audio form submission...', 'font-weight: bold')
            jQuery.ajax({
                type: 'POST',
                xhr: function () {
                    var myXhr = jQuery.ajaxSettings.xhr()
                    if (myXhr.upload) {
                        myXhr.upload.addEventListener('progress', uploadProgressHandler, false)
                    }
                    return myXhr
                },
                url: $('#audio-form')[0].getAttribute('action'),
                data: new FormData($('#audio-form')[0]),
                crossDomain: true,
                processData: false,
                dataType: 'xml',
                cache: false,
                contentType: false,
                success: function (data) {
                    console.log('audio form submission %cok', 'background: #222; color: #bada55')
                    console.log(data)
                },
                error: function (err) {
                    console.log(err)
                }
            }).then(function (res, type, prevObject) {
		if (prevObject.status !== 201 || window.throwTest) {
                  throw new Error('Audio submission didn\'t succeed, aborting :(')
                }
                console.log('%cattempting visual form submission...', 'font-weight: bold')
                return jQuery.ajax({
                    type: 'POST',
                    url: $('#visual-form')[0].getAttribute('action'),
                    data: new FormData($('#visual-form')[0]),
                    crossDomain: true,
                    processData: false,
                    dataType: 'xml',
                    cache: false,
                    contentType: false,
                    success: function (data) {
                        console.log('visual form submission %cok', 'background: #222; color: #bada55')
                        console.log(data)
                    },
                    error: function (err) {
                        console.log(err)
                    }
                })
            }).then(function (res, statusType, prevObject) {
                if (prevObject.status !== 201 || window.throwTest2) {
                  throw new Error('Artwork submission didn\'t succeed, aborting :(')
                }
                $('.upload-button')[0].innerHTML = 'Success!'
            }).catch(function (err) {
                console.error(err)
            })
        }
    }

    function nameInputsValid () {
        var trackName = $('#track-name')[0]
        var artistName = $('#artist-name')[0]
        var albumName = $('#album-name')[0]
        var returnValue = true
        console.log('trackName', trackName.value)
        console.log('artistName', artistName.value)
        console.log('albumName', albumName.value)

        if (!trackName.value) {
            $('.track-name-error')[0].innerText = 'Please enter a title for the track'
            if (trackName.className.indexOf('error') === -1) {
                trackName.classList.add('error')
            }
            returnValue = false
        } else {
            trackName.className = ''
            $('.track-name-error')[0].innerText = ''
        }
        if (!artistName.value) {
            $('.artist-name-error')[0].innerText = 'Please enter the artist name'
            if (artistName.className.indexOf('error') === -1) {
                artistName.classList.add('error')
            }
            returnValue = false
        } else {
            artistName.className = ''
            $('.artist-name-error')[0].innerText = ''
        }
        if (!albumName.value) {
            $('.album-name-error')[0].innerText = 'Please enter the name of the album'
            if (albumName.className.indexOf('error') === -1) {
                albumName.classList.add('error')
            }
            returnValue = false
        } else {
            albumName.className = ''
            $('.album-name-error')[0].innerText = ''
        }
        return returnValue
    }

    function termsAndCondValid () {
        var chkbxs = $('.gfield_checkbox')
        var returnValue = true
        chkbxs.forEach(function (bx) {
            var box = bx.getElementsByTagName('label')[0]
            if (box.className.indexOf('checkbox-checked') === -1) {
                $('#terms-error-message')[0].innerText = 'Please check the following boxes. Thank you'
                returnValue = false
            } else {
                $('#terms-error-message')[0].innerText = ''
            }
            console.log(bx.getElementsByTagName('label')[0])
        })
        return returnValue
    }

    dragDropTarget.addEventListener('drop', drop)
    dragDropTarget.addEventListener('dragover', dropZoneDragover)
    dragDropTarget.addEventListener('dragleave', dropZoneDragleave)

    // super basic hack for checking checkboxes, just for demo
    ;[].slice.call($('.gfield_checkbox label')).map(function (el) {
        el.addEventListener('click', function () {
            el.classList.toggle('checkbox-checked')
        })
    })

    // hacky demonstration, this is how you trigger the selection of an audio file + get the duration
    $('#select-audio')[0].onclick = function () {
        $('#audio-file-input')[0].click()
    }
    $('#select-visual')[0].onclick = function () {
        $('#visual-file-input')[0].click()
    }
    $('[type=file]')[0].addEventListener('change', function () {
        $('#select-audio')[0].value = 'Thanks!'
        var jsmediatags = window.jsmediatags
        var file = $('[type=file]')[0].files[0]
        console.log('we listened to a change in the selected file, now we will create an <audio> element, wait and try to squeeze some info out of it...')
        _a = document.createElement('audio')
        _a.src = URL.createObjectURL(file)
        _a.addEventListener('loadedmetadata', function () {
            console.log('...and now we can get the duration:', _a.duration / 60, 'minutes, audio element:')
            // not implemented yet, make sure to add duration to the hidden field x-amz-meta-track-duration
            console.dir(_a)
            console.log('... and we can also get the ID3 metadata!')
            jsmediatags.read(file, {
                onSuccess: function (tag) {
                    console.log(tag.tags)
                    if (tag.tags.artist) {
                        $('[name=artist]')[0].value = tag.tags.artist
                    }
                    if (tag.tags.title) {
                        $('[name=track-name]')[0].value = tag.tags.title
                    }
                    if (tag.tags.album) {
                        $('[name=album]')[0].value = tag.tags.album
                    }
                    if (tag.tags.picture) {
                        var base64String = ''
                        for (var i = 0; i < tag.tags.picture.data.length; i++) {
                            base64String += String.fromCharCode(tag.tags.picture.data[i])
                        }
                        var base64 = 'data:' + tag.tags.picture.format + ';base64,' + window.btoa(base64String)
                        $('#image-preview')[0].src = base64

                        // attempting to convert
                        // TODO: https://davidwalsh.name/convert-canvas-image, then save as blob
                        // TODO: if image is set from mp3, upon visual form ajax submission remove last file field and instead formData.append('file', blob, 'filename')
                        var extractedImageBlob = b64toBlob(window.btoa(base64String), tag.tags.picture.format)
                        console.log(extractedImageBlob)
                    }
                },
                onError: function (error) {
                    console.log(error)
                }
            })
        })
    })
    $('.upload-button')[0].addEventListener('click', function (e) {
        // shake for now if not valid
        if (!validateForm()) {
            e.target.classList.add('shake')
            setTimeout(function () {
                e.target.classList.remove('shake')
            }, 3000)
        }
    })
})

</script>
<?php get_footer(); ?>
