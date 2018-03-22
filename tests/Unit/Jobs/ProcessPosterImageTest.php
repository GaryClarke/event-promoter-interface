<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessPosterImage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_resizes_the_poster_image_to_600px_wide()
    {
        Storage::fake('public');

        Storage::disk('public')->put(
            'posters/example-poster.jpg',
            file_get_contents(base_path('tests/__fixtures__/large-image.jpg'))
        );

        $concert = \ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.jpg',
        ]);

        ProcessPosterImage::dispatch($concert);

        $resizedImage = Storage::disk('public')->get('posters/example-poster.jpg');

        list($width, $height) = getimagesizefromstring($resizedImage);

        $this->assertEquals(600, $width);

        $this->assertEquals(348, $height);

        $resizedImageContents = Storage::disk('public')->get('posters/example-poster.jpg');

        $controlImageContents = file_get_contents(base_path('tests/__fixtures__/small-unoptimized-poster.jpg'));

        $this->assertEquals($controlImageContents, $resizedImageContents);
    }


    /** @test */
    function it_optimizes_the_image()
    {
        Storage::fake('public');

        Storage::disk('public')->put(
            'posters/example-poster.jpg',
            file_get_contents(base_path('tests/__fixtures__/small-unoptimized-poster.jpg'))
        );

        $concert = \ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.jpg',
        ]);

        ProcessPosterImage::dispatch($concert);

        $originalSize = filesize(base_path('tests/__fixtures__/small-unoptimized-poster.jpg'));

        $optimizedImageSize = Storage::disk('public')->size('posters/example-poster.jpg');

        $this->assertLessThan($originalSize, $optimizedImageSize);

        $optimizedImageContents = Storage::disk('public')->get('posters/example-poster.jpg');

        $controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-image.jpg'));

        $this->assertEquals($controlImageContents, $optimizedImageContents);
    }
}
