<?php

namespace App\Http\Controllers;

use Goutte;
use Illuminate\Http\Request;
use App\Blog;

class ScrapController extends Controller
{
    public function scraping()
    {
        $crawler = Goutte::request('GET', 'https://manablog.org/');
        $datas = $this->getParseData($crawler);
        $this->save($datas);
        return 1;
    }

    private function save($datas)
    {
        foreach ($datas as $data) {
            Blog::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'category' => $data['category'],
                'picture' => $data['picture']
            ]);
        }
    }

    private function getParseData($crawler)
    {
        $data = [];
        $crawler->filter('div[itemprop=articleBody] div.col-xs-12.wrap')->each(function ($node) use (&$data) {
            $title = $node->filter('h2.title a')->first()->attr('title');
            $content = $node->filter('p.description')->text();
            $category = implode(',', explode(' ', $node->filter('p.cat')->text()));
            $picture = $this->getPicture($node);

            $data[] = ['title' => $title, 'content' => $content, 'category' => $category, 'picture' => $picture];
        });
        return $data;
    }

    private function getPicture($node)
    {
        $matches = [];
        preg_match(
            '/background-image:url\((https:\/\/manablog.org\/wp-content\/uploads\/\d+\/\d+\/[\w_-]+\.[\w]+)/',
            $node->filter('.thumbnail-img')->attr('style'),
            $matches
        );
        return $matches[1];
    }
}
