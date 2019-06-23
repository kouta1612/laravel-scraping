<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $crawler = Goutte::request('GET', 'https://manablog.org/');
    $data = [];
    $crawler->filter('.thumbnail-img')->each(function ($node) use (&$data) {
        $matches = [];
        preg_match(
            '/background-image:url\((https:\/\/manablog.org\/wp-content\/uploads\/\d+\/\d+\/[\w_-]+\.[\w]+)/',
            $node->attr('style'),
            $matches
        );
        // dd($matches[1]);
        \Log::info($matches[1]);
        $data[] = $matches[1];
    });
    return $data;
});
