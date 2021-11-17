<?php

use Dillingham\Formation\Tests\Fixtures\PostFormation;
use Illuminate\Support\Facades\Route;

Route::get('login')->name('login');

Route::formation('posts', PostFormation::class);
