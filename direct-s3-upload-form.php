<?PHP
/* Template Name: Direct S3 Form */
require_once(__DIR__ . '/vendor/autoload.php');
get_header();
get_currentuserinfo();
?>
<link rel='stylesheet' href='https://resonate.is/wp-content/plugins/gravityforms/css/formsmain.min.css' type='text/css' media='all' />
<div class="row">
	<div class="small-12 large-12 columns" role="main">
	<?php
	use EddTurtle\DirectUpload\Signature;
	use Ramsey\Uuid\Uuid;
	$current_user = wp_get_current_user();
	$uuid4 = Uuid::uuid4();
	$awsUuid = $uuid4->toString();
	$audioUploader = new Signature(
		    getenv('AWS_ACCESS_KEY_ID'),
		    getenv('AWS_SECRET_ACCESS_KEY'),
	            getenv('AWS_S3_BUCKET_NAME'),
		    getenv('AWS_S3_REGION'),
		    [
			'max_file_size' => 20,
			'expires' => '+10 minutes',
			'content_type' => 'audio/mpeg3',
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
	$visualUploader = new Signature(
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
	<div class="gform_wrapper">
	<!-- The two forms, probably best to manipulate by IDs  -->
	<form action="<?php echo $audioUploader->getFormUrl(); ?>" method="post" enctype="multipart/form-data" id="audio-form">
	    <?php echo $audioUploader->getFormInputsAsHtml(); ?>
	    <input type="file" name="audio-file" accept="audio/mpeg3" style="opacity: 0;
                    position: absolute;
                    top: 0px;
                    left: 0px;">
	</form>
	<form action="<?php echo $visualUploader->getFormUrl(); ?>" method="post" enctype="multipart/form-data" id="visual-form">
	    <?php echo $visualUploader->getFormInputsAsHtml(); ?>
	    <input type="file" name="visual-file" accept="image/jpeg" style="opacity: 0;
                    position: absolute;
                    top: 0px;
                    left: 0px;">
	</form>
	<!--"Fake form" without an actual form (to prevent submitting...) -->
	    <label for="track-name">Track Name</label>
            <input type="text" name="track-name">

	    <label for="album">Album</label>
            <input type="text" name="album">

	    <label for="artist">Artist Name</label>
            <input type="text" name="artist" value="">
	<!-- here we will probably have to have two upload areas, maybe left/right split? one for the audio, one for the visual (cover art) -->
	    <div class="gform_fileupload_multifile">
            <div class="gform_drop_area" style="position: relative;">
                <span class="gform_drop_instructions">Drop files here or </span>
                <input id="select-audio" type="button" value="Select files" class="button gform_button_select_files" style="z-index: 1;">
		<span class="gform_drop_instructions">(mp3, max. 20 MB)</span>
            </div>
            </div>
	<!-- checkboxes are "fake", but they need to react upon click, and need to be checked. CSS is stolen from WP gravity forms -->
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
<script type="text/javascript">
/**
 * BASICS:
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
 * Right now there's probably just one signed form ($audioUploader) in page.
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
 *                                       -> send both forms via an ajax POST request
 *                                          -> if successful (returns statusCode 201, created), drop the UUID into a localStorage field.
 *                                          -> show a visual indicator of success on the frontend UI (checkmark appers, flashing)
 *                                          -> BONUS: send another post to a php endpoint that will save the metadata 
 *                                             (track name, uuid, etc) into a custom WP post type for the logged in user. this will come handy
 *                                             later if someone wants to delete their track, or replace it with a different version
 * 
 *  BONUS: 
 *  - preview cover art after selecting image file
 *  - extract duration from audio file after selecting
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

document.addEventListener("DOMContentLoaded", function(event) { 
  console.log('...it\'s alive!!!')
});
</script>
<?php get_footer(); ?>
