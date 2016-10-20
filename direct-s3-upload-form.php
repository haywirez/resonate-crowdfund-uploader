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
        'default_filename' => 'track/audio/' . $awsUuid . '.mp3',
        'additional_inputs' => [
            'x-amz-meta-artist' => '',
            'x-amz-meta-track-name' => '',
            'x-amz-meta-album' => '',
            'x-amz-meta-track-duration' => '',
            'x-amz-meta-visual-key' => 'track/visual/' . $awsUuid . '.png',
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
        'acl' => 'public-read',
        'max_file_size' => 2,
        'expires' => '+10 minutes',
        'content_type' => 'image/jpeg',
        'default_filename' => 'track/visual/' . $awsUuid . '.png',
        'additional_inputs' => [
            'x-amz-meta-owner-id' => $current_user->ID
        ],
    ]
);
?>
<link rel='stylesheet' href='https://resonate.is/wp-content/plugins/gravityforms/css/formsmain.min.css' type='text/css'
      media='all'/>
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
<div class="row header-container">
    <p id="header-info" class="small-12 large-12 columns">Start by adding your mp3 here. We'll attempt to extract all
        the metadata associated with this file for you!</p>
</div>
<form id="fake-form">
    <div class="row" id="first-step">
        <div class="small-12 large-12 columns gform_wrapper" role="audio-upload-area">
            <div class="gform_fileupload_multifile">
                <div class="gform_drop_area" style="position: relative;">
                    <span class="gform_drop_instructions">Drop files here or </span>
                    <input id="select-audio" type="button" value="Select files" class="button gform_button_select_files"
                           style="z-index: 1;">
                    <span class="gform_drop_instructions">(mp3, max. 20 MB)</span>
                </div>
            </div>
        </div>
    </div>
    <div class="row hidden" id="second-step">
        <div class="small-12 large-4 columns gform_wrapper" role="visual-upload-area">
            <div class="gform_fileupload_multifile" id="image-preview">
                <div class="gform_drop_area" id="image-drop-area" style="position: relative;"></div>
            </div>
            <!-- <img src="" class="" id="image-preview" -->
            <input id="select-visual" type="button" value="Select image" class="button gform_button_select_files"
                   style="z-index: 1;">
        </div>
        <div class="small-12 large-8 columns gform_wrapper" role="main">
            <label for="track-name">Track Name</label>
            <div class="error-message track-name-error"></div>
            <input type="text" name="track-name" id="track-name">
            <label for="album">Album</label>
            <div class="error-message album-name-error"></div>
            <input type="text" name="album" id="album-name">
            <label for="artist">Artist Name</label>
            <div class="error-message artist-name-error"></div>
            <input type="text" name="artist" value="" id="artist-name">
        </div>
        <div class="small-12 large-12 columns gform_wrapper" role="submission">
        <!-- checkboxes are "fake", but they need to react upon click, and need to be checked. CSS is stolen from WP gravity forms -->
        <div class="error-message" id="terms-error-message"></div>
        <div class="ginput_container ginput_container_checkbox">
            <ul class="gfield_checkbox" style="list-style: none; margin-left: 0;">
                <li class="gfield_checkbox">
                    <label for="x-amz-meta-no-covers">These songs are 100% written by me or my band. NO
                        COVERS.</label>
                </li>
                <li class="gfield_checkbox">
                    <label for="x-amz-meta-streaming-agreement">Resonate may stream these songs for free during the
                        crowd campaign</label>
                </li>
                <li class="gfield_checkbox">
                    <label for="x-amz-meta-song-title-information">All song titles, artist names and artwork are
                        included in these files.</label>
                </li>
            </ul>
        </div>
        <div class="upload-button button">Upload</div>
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

    #second-step {
        transition: opacity 750ms;
    }

    .hidden {
        opacity: 0;
    }

    #image-preview {
        background-size: cover;
        width: 240px;
        height: 240px;
    }

    #image-drop-area {
        height: 240px;
    }

    #select-visual {
        margin-top: 20px;
        width: 240px;
    }

    .upload-button {
        margin-top: 20px;
    }

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
<script type="text/javascript" src="https://cdn.rawgit.com/nodeca/pica/master/dist/pica.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"
        integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
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
        var dragDropTarget = $('.gform_fileupload_multifile')[ 0 ]
        dragDropTarget.addEventListener('drop', drop)
        dragDropTarget.addEventListener('dragover', dropZoneDragover)
        dragDropTarget.addEventListener('dragleave', dropZoneDragleave)

        function dropZoneDragover (ev) {
            dragDropTarget.classList.add('dragover')
            ev.preventDefault()
            ev.stopPropagation()
        }

        // Returns a function, that, as long as it continues to be invoked, will not
        // be triggered. The function will be called after it stops being called for
        // N milliseconds. If `immediate` is passed, trigger the function on the
        // leading edge, instead of the trailing.
        function debounce (func, wait, immediate) {
            var timeout
            return function () {
                var context = this, args = arguments
                var later = function () {
                    timeout = null
                    if (!immediate) func.apply(context, args)
                };
                var callNow = immediate && !timeout
                clearTimeout(timeout)
                timeout = setTimeout(later, wait)
                if (callNow) func.apply(context, args)
            }
        }

        // create reference to global, keep null initially
        var imageBlobToUpload

        function updateFinalImageBlob (img) {
            imageBlobToUpload = dataUriToBlob(img.src)
        }

        function getFinalImageBlob () {
            if (imageBlobToUpload) {
                return imageBlobToUpload
            } else {
                console.log('getFinalImageBlob: something is wrong, no global image blob set!')
                return false
            }
        }

        function getFinalImageBlobUrl () {
            if (imageBlobToUpload) {
                return URL.createObjectURL(imageBlobToUpload)
            } else {
                console.log('getFinalImageBlobURL: something is wrong, no global image blob set!')
            }
        }

        function convertCanvasToImage (canvas) {
            var image = new Image()
            image.src = canvas.toDataURL('image/png')
            return image
        }

        function convertImageWithPica (img) {
            // force to 600x600
            console.log('---- %cconverting image to 600x600 (pica)', 'color: #FF00FF')
            var canvas = document.createElement('canvas')
            canvas.height = 600
            canvas.width = 600
            window.pica.resizeCanvas(img, canvas, { alpha: true }, function (err) {
                if (err) { console.log('pica conversion error: ', err) }
                var finalImg = convertCanvasToImage(canvas)
                updateFinalImageBlob(finalImg)
                changePreviewImage(finalImg)
            })
        }

        function dataUriToBlob (dataUri) {
            var binary = atob(dataUri.split(',')[ 1 ])
            var array = []
            for (var i = 0; i < binary.length; i++) {
                array.push(binary.charCodeAt(i))
            }
            return new Blob([ new Uint8Array(array) ], { type: 'image/png' })
        }

        function drop (ev) {
            ev.preventDefault()
            ev.stopPropagation()
            console.log('dropped file: ', ev.dataTransfer.files, ev.dataTransfer.files[ 0 ].type)
            if (ev.dataTransfer.files.length === 1 &&
                (ev.dataTransfer.files[ 0 ].type === 'audio/mpeg' || ev.dataTransfer.files[ 0 ].type === 'audio/mp3') &&
                ev.dataTransfer.files[ 0 ].size < 20971520 // 20 MB
            ) {
                $('#audio-file-input')[ 0 ].files = ev.dataTransfer.files
                $('.gform_drop_instructions')[ 0 ].innerText = ' '
                $('.gform_drop_instructions')[ 1 ].innerText = ev.dataTransfer.files[ 0 ].name
            } else {
                $('.gform_drop_instructions')[ 1 ].innerText = 'you can only drop 1 mp3 file smaller than 20 MB...'
                console.log('error: you can only drop 1 file, and it must be an mp3 less than 20 MB in size')
            }
            audioFileSelected()
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
                    byteNumbers[ i ] = slice.charCodeAt(i)
                }
                var byteArray = new Uint8Array(byteNumbers)
                byteArrays.push(byteArray)
            }
            return new Blob(byteArrays, { type: contentType })
        }

        var uploadProgressHandler = debounce(function (e) {
            if (e.lengthComputable) {
                var max = e.total
                var current = e.loaded
                var percentage = Math.floor((current * 100) / max)
                console.log('upload: %c' + percentage + '% complete', 'color: yellow')
                if (percentage >= 100) {
                    // upload process completed
                }
            }
        }, 100, true)

        // drag and drop image section
        var dragDropTargetVisual = $('.gform_fileupload_multifile')[ 1 ]

        function dropZoneDragoverVisual (ev) {
            dragDropTargetVisual.classList.add('dragover')
            ev.preventDefault()
            ev.stopPropagation()
        }

        function dropVisual (ev) {
            ev.preventDefault()
            ev.stopPropagation()
            console.log('dropped file: ', ev.dataTransfer.files, ev.dataTransfer.files[ 0 ].type)
            // TODO: suggest uploading 300x300 for best results
            if (ev.dataTransfer.files.length === 1 &&
                ev.dataTransfer.files[ 0 ].size < 2097152 // 2 MB
            ) {
                $('#visual-file-input')[ 0 ].files = ev.dataTransfer.files
                var reader = new FileReader()
                reader.onload = function (e) {
                    visualFileSelected(e.target.result)
                }
                reader.readAsDataURL(ev.dataTransfer.files[ 0 ])
            } else {
                console.log('error: you can only drop 1 file, and it must be an mp3 less than 20 mb in size')
            }
        }

        function dropZoneDragleaveVisual (ev) {
            dragDropTargetVisual.classlist.remove('dragover')
        }

        dragDropTargetVisual.addEventListener('drop', dropVisual)
        dragDropTargetVisual.addEventListener('dragover', dropZoneDragoverVisual)
        dragDropTargetVisual.addEventListener('dragleave', dropZoneDragleaveVisual)

        $('[type=file]')[ 1 ].addEventListener('change', function () {
            var file = $('[type=file]')[ 1 ].files[ 0 ]
            var reader = new FileReader()
            reader.onload = function (e) {
                visualFileSelected(e.target.result)
            }
            reader.readAsDataURL(file)
        })

        function visualFileSelected (file) {
            $('#select-visual')[ 0 ].value = "Change Image"
            var img = new Image()
            img.onload = function () {convertImageWithPica(img)}
            img.src = file
        }

        function copyFormFields () {
            var trackName = $('#track-name')[ 0 ]
            var artistName = $('#artist-name')[ 0 ]
            var albumName = $('#album-name')[ 0 ]

            var realArtistField = $('[name=x-amz-meta-artist]')[ 0 ]
            var realTrackNameField = $('[name=x-amz-meta-track-name]')[ 0 ]
            var realAlbumField = $('[name=x-amz-meta-album]')[ 0 ]
            // TODO: not really used now, filled from jsmediatags instead...
            var realTrackDurationField = $('[name=x-amz-meta-track-duration]')[ 0 ]

            realArtistField.value = artistName.value
            realTrackNameField.value = trackName.value
            realAlbumField.value = albumName.value

            return true
        }

        function submitForm () {
            var validForm = validateForm()
            var fieldsCopied = copyFormFields()

            // TODO: rework this, messy...

            if (validForm && fieldsCopied) {
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
                    url: $('#audio-form')[ 0 ].getAttribute('action'),
                    data: new FormData($('#audio-form')[ 0 ]),
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
                        throw new Error("Audio submission didn't succeed, aborting :( ")
                    }
                    console.log('%cattempting visual form submission...', 'font-weight: bold')
                    var _formData = new FormData($('#visual-form')[ 0 ])
                    _formData.delete('file')
                    _formData.append('file', getFinalImageBlob())
                    return jQuery.ajax({
                        type: 'POST',
                        url: $('#visual-form')[ 0 ].getAttribute('action'),
                        data: _formData,
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
                        throw new Error("Artwork submission didn't succeed, aborting :( ")
                    }
                    $('.upload-button')[ 0 ].innerHTML = 'Success!'
                    return true
                }).catch(function (err) {
                    console.error(err)
                })
            } else {
                return false
            }
        }

        function validateForm () {
            var inputsValid = nameInputsValid()
            var tsAndCsValid = termsAndCondValid()
            var audioAndVisualExist = checkForAudioAndVisualFiles()
            return (inputsValid && tsAndCsValid && audioAndVisualExist)
        }

        function checkForAudioAndVisualFiles () {
            var audio = $('#audio-file-input')[ 0 ].files.length > 0
            var visual = !!imageBlobToUpload
            if (!audio) {
                console.log('audio is missing???')
            }
            if (!visual) {
                console.log('image is missing')
                // TODO:
                // $('.track-name-error')[ 0 ].innerText = 'Please choose an image'
            }
            return (audio && visual)
        }

        function nameInputsValid () {
            var trackName = $('#track-name')[ 0 ]
            var artistName = $('#artist-name')[ 0 ]
            var albumName = $('#album-name')[ 0 ]
            var returnValue = true

            if (!trackName.value) {
                $('.track-name-error')[ 0 ].innerText = 'Please enter a title for the track'
                if (trackName.className.indexOf('error') === -1) {
                    trackName.classList.add('error')
                }
                returnValue = false
            } else {
                trackName.classList.remove('error')
                $('.track-name-error')[ 0 ].innerText = ''
            }
            if (!artistName.value) {
                $('.artist-name-error')[ 0 ].innerText = 'Please enter the artist name'
                if (artistName.className.indexOf('error') === -1) {
                    artistName.classList.add('error')
                }
                returnValue = false
            } else {
                artistName.classList.remove('error')
                $('.artist-name-error')[ 0 ].innerText = ''
            }
            if (!albumName.value) {
                $('.album-name-error')[ 0 ].innerText = 'Please enter the name of the album'
                if (albumName.className.indexOf('error') === -1) {
                    albumName.classList.add('error')
                }
                returnValue = false
            } else {
                albumName.classList.remove('error')
                $('.album-name-error')[ 0 ].innerText = ''
            }
            return returnValue
        }

        function termsAndCondValid () {
            var chkbxs = $('.gfield_checkbox')
            var returnValue = true
            ;[].slice.call(chkbxs).map(function (bx) {
                var box = bx.getElementsByTagName('label')[ 0 ]
                if (box.className.indexOf('checkbox-checked') === -1) {
                    $('#terms-error-message')[ 0 ].innerText = 'Please check the following boxes. Thank you'
                    returnValue = false
                } else {
                    $('#terms-error-message')[ 0 ].innerText = ''
                }
            })
            return returnValue
        }

        // super basic hack for checking checkboxes, just for demo
        ;[].slice.call($('.gfield_checkbox label')).map(function (el) {
            el.addEventListener('click', function () {
                el.classList.toggle('checkbox-checked')
            })
        })

        // NOTE: I've removed the copying of values to the invisible forms, as everything will be copied in
        //       the last step before submission anyway - @attila

        function changePreviewImage (img) {
            console.log('changePreviewImage: ')
            console.dir(img)
            $('#image-preview')[ 0 ].style.backgroundImage = "url('" + img.src + "')"
            $('#select-visual')[ 0 ].value = "Change Image"
        }

        // trigger file inputs
        $('#select-audio')[ 0 ].onclick = function () {
            $('#audio-file-input')[ 0 ].click()
        }
        $('#select-visual')[ 0 ].onclick = function () {
            $('#visual-file-input')[ 0 ].click()
        }

        $('[type=file]')[ 0 ].addEventListener('change', function () {
            // some kind of a visual feedback in the area
            audioFileSelected()
        })

        function audioFileSelected () {
            var jsmediatags = window.jsmediatags
            var file = $('[type=file]')[ 0 ].files[ 0 ]
            var okFlag = false

            // check type, length etc...
            if ($('[type=file]')[ 0 ].files.length === 1 &&
                (file.type === 'audio/mpeg' || file.type === 'audio/mp3') &&
                file.size < 20971520 // 20 MB
            ) {
                okFlag = true
                $('.gform_drop_instructions')[ 0 ].innerText = ' '
                $('.gform_drop_instructions')[ 1 ].innerText = file.name
                $('#select-audio')[ 0 ].value = 'Change Audio'
            } else {
                $('#select-audio')[ 0 ].value = 'Select Again'
                $('.gform_drop_instructions')[ 1 ].innerText = 'you can only select 1 mp3 file smaller than 20 MB...'
                console.log('error: selected file is not mp3 or less than 20 MB in size')
            }

            if (okFlag) {
                $('.header-container')[ 0 ].remove()
                _a = document.createElement('audio')
                _a.src = URL.createObjectURL(file)
                _a.addEventListener('loadedmetadata', function () {
                    console.log('audio file info, duration:', _a.duration, 'seconds, audio element:')
                    console.dir(_a)
                    $('[name=x-amz-meta-track-duration]')[ 0 ].value = _a.duration
                    jsmediatags.read(file, {
                        onSuccess: function (tag) {
                            console.log('successfully read audio file metadata:')
                            console.log(tag.tags)
                            if (tag.tags.artist) {
                                $('[name=artist]')[ 0 ].value = tag.tags.artist
                                $('[name="x-amz-meta-artist"]')[ 0 ].value = tag.tags.artist
                            }
                            if (tag.tags.title) {
                                $('[name=track-name]')[ 0 ].value = tag.tags.title
                                $('[name=x-amz-meta-track-name]')[ 0 ].value = tag.tags.title
                            }
                            if (tag.tags.album) {
                                $('[name=album]')[ 0 ].value = tag.tags.album
                                $('[name=x-amz-meta-album]')[ 0 ].value = tag.tags.album
                            }
                            if (tag.tags.picture) {
                                var base64String = ''
                                for (var i = 0; i < tag.tags.picture.data.length; i++) {
                                    base64String += String.fromCharCode(tag.tags.picture.data[ i ])
                                }
                                var base64 = 'data:' + tag.tags.picture.format + ';base64,' + window.btoa(base64String)
                                var img = new Image()
                                img.onload = function () {convertImageWithPica(img)}
                                img.src = base64
                            } else if (tag.tags.APIC) {
                                // TODO: make DRY
                                console.log('NO PICTURE FOUND FROM jsmediatags - use APIC', tag.tags.APIC[ 0 ])
                                var base64String = ''
                                for (var i = 0; i < tag.tags.APIC[ 0 ].data.data.length; i++) {
                                    base64String += String.fromCharCode(tag.tags.APIC[ 0 ].data.data[ i ])
                                }
                                var base64 = 'data:' + tag.tags.APIC[ 0 ].data.format + ';base64,' + window.btoa(base64String)
                                var img = new Image()
                                img.onload = function () {convertImageWithPica(img)}
                                img.src = base64
                            }

                            // show other input fields
                            $('#second-step')[ 0 ].classList.remove('hidden')

                        },
                        onError: function (error) {
                            // also show other input fields?
                            $('#second-step')[ 0 ].classList.remove('hidden')
                            console.log(error)
                        }
                    })
                })
            }
        }

        $('.upload-button')[ 0 ].addEventListener('click', function (e) {
            // shake for now if not valid
            if (!submitForm()) {
                e.target.classList.add('shake')
                setTimeout(function () {
                    e.target.classList.remove('shake')
                }, 3000)
            }
        })
    })

</script>
<?php get_footer(); ?>
