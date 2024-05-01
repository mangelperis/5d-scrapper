<?php
declare(strict_types=1);


namespace App\Infrastructure\Transformer;

use Symfony\Component\DomCrawler\Crawler;

class htmlProjectGalleryTransformer
{
    public function transform(Crawler $grid): array
    {
        $projects = [];

        foreach ($grid->children() as $child) {
            $node = new Crawler($child);

            $projectNode = $node->filter('div:first-child');
            $statisticsNode = $node->filter('div:nth-of-type(2)');

            // Empty elements discard
            if (!$projectNode->count() > 0 && !$statisticsNode->count() > 0) {
                continue;
            }

            // Selectors by CSS classes
            $title = $projectNode->filter('div > h3 > a');
            $projectUrl = $projectNode->filter('a');

            $userUrl = $statisticsNode->filter('a')->attr('href');
            $array = explode('/', rtrim($userUrl, '/'));
            $user = end($array);

            $hits = $statisticsNode->filter('span')->eq(0);
            $likes = $statisticsNode->filter('span')->eq(1);
            $comments = $statisticsNode->filter('span')->eq(2);


            //TODO use DTO->toArray() instead...
            $project = [
                'title' => $title->text(),
                'user' => $user ?? '',
                'url' => $projectUrl->attr('href'),
                'hits' => (int)$hits->text(),
                'statistics' => [
                    'likes' => $likes->text(),
                    'comments' => $comments->text(),
                ],
            ];

            $projects[] = $project;
        }

        return $projects;
    }
}