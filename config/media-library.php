<?php

return [

    
    'disk_name' => env('MEDIA_DISK', 'public'),

    
    'max_file_size' => 1024 * 1024 * 10, // 10MB

    
    'queue_connection_name' => env('QUEUE_CONNECTION', 'sync'),

    
    'queue_name' => env('MEDIA_QUEUE', ''),

    
    'queue_conversions_by_default' => env('QUEUE_CONVERSIONS_BY_DEFAULT', true),

    
    'queue_conversions_after_database_commit' => env('QUEUE_CONVERSIONS_AFTER_DB_COMMIT', true),

    
    'media_model' => Spatie\MediaLibrary\MediaCollections\Models\Media::class,

    
    'media_observer' => Spatie\MediaLibrary\MediaCollections\Models\Observers\MediaObserver::class,

    
    'use_default_collection_serialization' => false,

    
    'temporary_upload_model' => Spatie\MediaLibraryPro\Models\TemporaryUpload::class,

    
    'enable_temporary_uploads_session_affinity' => true,

    
    'generate_thumbnails_for_temporary_uploads' => true,

    
    'file_namer' => Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer::class,

    
    'path_generator' => Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,

    
    'file_remover_class' => Spatie\MediaLibrary\Support\FileRemover\DefaultFileRemover::class,

    
    'custom_path_generators' => [
        // Model::class => PathGenerator::class
        // or
        // 'model_morph_alias' => PathGenerator::class
    ],

    
    'url_generator' => Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator::class,

    
    'moves_media_on_update' => false,

    
    'version_urls' => false,

    
    'image_optimizers' => [
        Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
            '-m85', // set maximum quality to 85%
            '--force', // ensure that progressive generation is always done also if a little bigger
            '--strip-all', // this strips out all text information such as comments and EXIF data
            '--all-progressive', // this will make sure the resulting image is a progressive one
        ],
        Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
            '--force', // required parameter for this package
        ],
        Spatie\ImageOptimizer\Optimizers\Optipng::class => [
            '-i0', // this will result in a non-interlaced, progressive scanned image
            '-o2', // this set the optimization level to two (multiple IDAT compression trials)
            '-quiet', // required parameter for this package
        ],
        Spatie\ImageOptimizer\Optimizers\Svgo::class => [
            '--disable=cleanupIDs', // disabling because it is known to cause troubles
        ],
        Spatie\ImageOptimizer\Optimizers\Gifsicle::class => [
            '-b', // required parameter for this package
            '-O3', // this produces the slowest but best results
        ],
        Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
            '-m 6', // for the slowest compression method in order to get the best compression.
            '-pass 10', // for maximizing the amount of analysis pass.
            '-mt', // multithreading for some speed improvements.
            '-q 90', // quality factor that brings the least noticeable changes.
        ],
        Spatie\ImageOptimizer\Optimizers\Avifenc::class => [
            '-a cq-level=23', // constant quality level, lower values mean better quality and greater file size (0-63).
            '-j all', // number of jobs (worker threads, "all" uses all available cores).
            '--min 0', // min quantizer for color (0-63).
            '--max 63', // max quantizer for color (0-63).
            '--minalpha 0', // min quantizer for alpha (0-63).
            '--maxalpha 63', // max quantizer for alpha (0-63).
            '-a end-usage=q', // rate control mode set to Constant Quality mode.
            '-a tune=ssim', // SSIM as tune the encoder for distortion metric.
        ],
    ],

    
    'image_generators' => [
        Spatie\MediaLibrary\Conversions\ImageGenerators\Image::class,
        Spatie\MediaLibrary\Conversions\ImageGenerators\Webp::class,
        Spatie\MediaLibrary\Conversions\ImageGenerators\Avif::class,
        Spatie\MediaLibrary\Conversions\ImageGenerators\Pdf::class,
        Spatie\MediaLibrary\Conversions\ImageGenerators\Svg::class,
        Spatie\MediaLibrary\Conversions\ImageGenerators\Video::class,
    ],

    
    'temporary_directory_path' => null,

    
    'image_driver' => env('IMAGE_DRIVER', 'gd'),

    
    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),

    
    'ffmpeg_timeout' => env('FFMPEG_TIMEOUT', 900),

    
    'ffmpeg_threads' => env('FFMPEG_THREADS', 0),

    
    'jobs' => [
        'perform_conversions' => Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob::class,
        'generate_responsive_images' => Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob::class,
    ],

    
    'media_downloader' => Spatie\MediaLibrary\Downloaders\DefaultDownloader::class,

    
    'media_downloader_ssl' => env('MEDIA_DOWNLOADER_SSL', true),

    
    'temporary_url_default_lifetime' => env('MEDIA_TEMPORARY_URL_DEFAULT_LIFETIME', 5),

    'remote' => [
        
        'extra_headers' => [
            'CacheControl' => 'max-age=604800',
        ],
    ],

    'responsive_images' => [
        
        'width_calculator' => Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\FileSizeOptimizedWidthCalculator::class,

        
        'use_tiny_placeholders' => true,

        
        'tiny_placeholder_generator' => Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\Blurred::class,
    ],

    
    'enable_vapor_uploads' => env('ENABLE_MEDIA_LIBRARY_VAPOR_UPLOADS', false),

    
    'default_loading_attribute_value' => null,

    
    'prefix' => env('MEDIA_PREFIX', ''),

    
    'force_lazy_loading' => env('FORCE_MEDIA_LIBRARY_LAZY_LOADING', true),
];
