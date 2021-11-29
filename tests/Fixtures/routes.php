<?php

use Dillingham\Formation\Tests\Fixtures\AuthorFormation;
use Dillingham\Formation\Tests\Fixtures\PostFormation;
use Illuminate\Support\Facades\Route;

Route::get('login')->name('login');

Route::formation('posts', PostFormation::class);
Route::formation('authors', AuthorFormation::class);
Route::formation('authors.posts', PostFormation::class);

