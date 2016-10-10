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
	use \EddTurtle\DirectUpload\Signature;
	use Ramsey\Uuid\Uuid;
	$uuid4 = Uuid::uuid4();
	$awsUuid = $uuid4->toString();
	$uploader = new Signature(
		    getenv('AWS_ACCESS_KEY_ID'),
		    getenv('AWS_SECRET_ACCESS_KEY'),
	            getenv('AWS_S3_BUCKET_NAME'),
		    getenv('AWS_S3_REGION'),
		    [
			'max_file_size' => 20,
			'expires' => '+10 minutes',
			'content_type' => 'audio/mpeg3',
			'default_filename' => $awsUuid . '.mp3',
			'additional_inputs' => [
				'x-amz-meta-artist' => '',
				'x-amz-meta-track-name' => '',
				'x-amz-meta-album' => '',
				'x-amz-meta-track-duration' => '',
				'x-amz-meta-visual-key' => $awsUuid
			],
		    ]
		);
	?>
	<div class="gform_wrapper">
	<form action="<?php echo $uploader->getFormUrl(); ?>" method="POST" enctype="multipart/form-data">
	    <?php echo $uploader->getFormInputsAsHtml(); ?>
	    <label for="X-amz-meta-track-name">Track Name</label>
            <input type="text" name="X-amz-meta-track-name">

	    <label for="X-amz-meta-album">Album</label>
            <input type="text" name="X-amz-meta-album">

	    <label for="X-amz-meta-artist">Artist Name</label>
            <input type="text" name="X-amz-meta-artist" value="">

 	    <input type="hidden" name="X-amz-meta-track-duration">
	    
	    <div class="gform_fileupload_multifile">
            <div class="gform_drop_area" style="position: relative;">
                <span class="gform_drop_instructions">Drop files here or </span>
                <input id="select-files" type="button" value="Select files" class="button gform_button_select_files" style="z-index: 1;">
		<span class="gform_drop_instructions">(mp3, max. 20 MB)</span>
                <input type="file" name="file" accept="audio/mpeg3" style="opacity: 0;
		    position: absolute;
		    top: 0px;
		    left: 0px;">
            </div>
            </div>
            <div class="ginput_container ginput_container_checkbox">
            	<ul class="gfield_checkbox" style="list-style: none; margin-left: 0;">
		<li class="gfield_checkbox">
			    <label for="x-amz-meta-no-covers">These songs are 100% written by me or my band. NO COVERS.</label>
			    <input name="x-amz-meta-no-covers" type="checkbox"  value="1" checked="checked"/>
			</li>
			<li class="gfield_checkbox">
			    <label for="x-amz-meta-streaming-agreement" >Resonate may stream these songs for free during the crowd campaign</label>
			    <input name="x-amz-meta-streaming-agreement" type="checkbox"  value="1" checked="checked" />
			</li>
			<li class="gfield_checkbox">
			   <label for="x-amz-meta-song-title-information" >All song titles, artist names and artwork are included in these files.</label>
			   <input name="x-amz-meta-song-title-information" type="checkbox"  value="1" checked="checked" />
			</li>
		</ul>
            </div>
            <div class="upload-button button disabled">Upload</div>
         </form>
	</div>
	</div>
</div>
<?php get_footer(); ?>
