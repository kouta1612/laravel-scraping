<?php

namespace App\Http\Controllers;

use Goutte;
use App\Blog;
use Carbon\Carbon;

class ScrapController extends Controller
{
    public function scraping()
    {
        $urls = $this->getUrls();
        foreach ($urls as $url) {
            $crawler = Goutte::request('GET', $url);
            $datas = $this->getParseData($crawler);

            $this->save($datas);
        }
        return redirect('/');
    }

    private function getUrls()
    {
        $urls = ['https://manablog.org/'];
        for ($i = 2; $i <= 110; $i++) {
            $urls[] = "https://manablog.org/page/{$i}/";
        }
        return $urls;
    }

    private function save($datas)
    {
        foreach ($datas as $data) {
            Blog::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'category' => $data['category'],
                'picture' => $data['picture'],
                'created_at' => $data['time'],
                'updated_at' => $data['time'],
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
            $time = new Carbon($this->getTime($node));

            $data[] = [
                'title' => $title,
                'content' => $content,
                'category' => $category,
                'picture' => $picture,
                'time' => $time
            ];
        });
        return $data;
    }

    private function getTime($node)
    {
        $matches = [];
        preg_match(
            '/(\d+\/\d+\/\d+)/',
            $node->filter('time[itemprop=dateModified],time[itemprop=datePublished]')->text(),
            $matches
        );
        return str_replace('/', '-', $matches[1]);
    }

    private function getPicture($node)
    {
        $matches = [];
        preg_match(
            '/background-image:url\((https:\/\/manablog.org\/wp-content\/uploads\/\d+\/\d+\/[\w_-]+\.[\w]+)/',
            $node->filter('.thumbnail-img')->attr('style'),
            $matches
        );
        if (!empty($matches)) {
            return $matches[1];
        }
        return "";
    }
}
