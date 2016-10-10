<?PHP
/* Template Name: Direct S3 Form */
require_once(__DIR__ . '/vendor/autoload.php');
get_header();
get_currentuserinfo();
?>
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
	echo json_encode($uploader->getOptions());
	?>
	<form action="<?php echo $uploader->getFormUrl(); ?>" method="POST" enctype="multipart/form-data">
	    <?php echo $uploader->getFormInputsAsHtml(); ?>
	    <label for="X-amz-meta-track-name">Track Name</label>
            <input type="text" name="X-amz-meta-track-name">

	    <label for="X-amz-meta-album">Album</label>
            <input type="text" name="X-amz-meta-album">

	    <label for="X-amz-meta-artist">Artist Name</label>
            <input type="text" name="X-amz-meta-artist" value="">

 	    <input type="hidden" name="X-amz-meta-track-duration">
	    <label for="file">Choose file: (mp3, max. 20 MB)</label>
	    <input type="file" name="file" accept="audio/mpeg3">
         </form>
	</div>
</div>
<?php get_footer(); ?>
