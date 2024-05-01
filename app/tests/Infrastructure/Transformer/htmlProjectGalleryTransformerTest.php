<?php

namespace App\Tests\Infrastructure\Transformer;

use App\Infrastructure\Transformer\htmlProjectGalleryTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class htmlProjectGalleryTransformerTest extends TestCase
{
    public function testTransform()
    {
        // Sample HTML input
        $html = <<<HTML
<div id="grid">
    <div class="">
        <div data-render-type="regular">
            <div>
                <a href="https://planner5d.com/gallery/floorplans/LaPGTG/floorplans-bathroom-cafe-office-kitchen-entryway-3d"
                   class="">
                    <img src="https://storage.planner5d.com/thumbs.600/81f4b1f95a21d099ea87be83115a9602.webp?v=1706701957"
                         alt="floor plans bathroom cafe office kitchen entryway 3d" height="214" width="292">
                </a>
                <div>
                    <h3>
                        <a href="https://planner5d.com/gallery/floorplans/LaPGTG/floorplans-bathroom-cafe-office-kitchen-entryway-3d">
                            Trailer House </a>
                    </h3>
                    <a class=""
                       data-onclick="P5D.getContext().galleryShare().toggleLike(this, '/gallery/floorplans/', 139907, 'project', 'regular')">
                    </a>
                </div>
            </div>
            <div>
                <a href="https://planner5d.com/profile/id112822715" class="">
                    <img src="https://storage.planner5d.com/ud/80d599a03714d650f7f1339a2308ead7.jpg?v=946684801" alt=""
                         height="28" width="28">
                    Lastro Star
                </a>
                <span>55</span>
                <span id="project_likes_139907">5</span>
                <span>0</span>
            </div>
        </div>
    </div>
</div>
HTML;

        $crawler = new Crawler($html);
        $grid = $crawler->filter('#grid > div:first-child')->first();

        $transformer = new htmlProjectGalleryTransformer();
        $projects = $transformer->transform($grid);

        // Expected output
        $expectedProjects = [
            [
                'title' => 'Trailer House',
                'user' => 'id112822715',
                'url' => 'https://planner5d.com/gallery/floorplans/LaPGTG/floorplans-bathroom-cafe-office-kitchen-entryway-3d',
                'hits' => 55,
                'statistics' => [
                    'likes' => '5',
                    'comments' => '0',
                ],
            ],
        ];

        $this->assertEquals($expectedProjects, $projects);
    }
}
