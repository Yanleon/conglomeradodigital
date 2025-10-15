<?php

use Illuminate\Support\Facades\Event;

Event::listen('bagisto.package.start', function () {
    app()->register(\Webkul\Bold\Providers\BoldServiceProvider::class);
});