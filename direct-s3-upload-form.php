<?PHP
/* Template Name: Direct S3 Form */
require_once(__DIR__ . '/vendor/autoload.php');
get_header();
get_currentuserinfo();
?>
<div class="row">
	<div class="small-12 large-12 columns" role="main">
	<?php
	$uploader = new \EddTurtle\DirectUpload\Signature(
		    getenv('AWS_ACCESS_KEY_ID'),
		    getenv('AWS_SECRET_ACCESS_KEY'),
	            getenv('AWS_S3_BUCKET_NAME'),
		    getenv('AWS_S3_REGION')
		);
	?>
	<form action="<?php echo $uploader->getFormUrl(); ?>" method="POST" enctype="multipart/form-data">
	    <?php echo $uploader->getFormInputsAsHtml(); ?>
	    <label for="X-amz-meta-track-name">Track Name</label>
            <input type="text" name="X-amz-meta-track-name">

	    <label for="X-amz-meta-album">Album</label>
            <input type="text" name="X-amz-meta-album">

	    <label for="X-amz-meta-artist">Artist Name</label>
            <input type="text" name="X-amz-meta-artist" value="">

	    <input type="hidden" name="X-amz-meta-visual-key" value="<?php ?>">

	    <label for="file">Choose file: (mp3, max. 20 MB)</label>
	    <input type="file" name="file" accept="audio/mpeg3">
         </FORM>


	</div>
</div>

<?php get_footer(); ?>
