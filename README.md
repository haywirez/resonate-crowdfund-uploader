[![Stories in Ready](https://badge.waffle.io/resonatecoop/resonate-crowdfund-uploader.png?label=ready&title=Ready)](https://waffle.io/resonatecoop/resonate-crowdfund-uploader)
# resonate-crowdfund-uploader

Upload form for artists to upload tracks to AWS

Uses composer for php package management. Install if needed via https://getcomposer.org/, then run `php composer.phar update`. The `composer.json` contains all that should be needed.
Also don't forget to set the environmental variables:
`AWS_ACCESS_KEY_ID`
`AWS_SECRET_ACCESS_KEY`
`AWS_S3_REGION`
`AWS_S3_BUCKET_NAME`

These need to be set for the process running the WP site. You might have to add them to php-fpm, settings should be located in /etc/php5/fpm/pool.d/www.conf
Simply add the correct lines in the following format:
`env[TEST] = something`

