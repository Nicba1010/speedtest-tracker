<?php

use App\Actions\CheckForScheduledSpeedtests;
use App\Models\Result;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    Bus::fake();
});

test('runs single speedtest when mode random', function () {
    config()->set('speedtest.schedule', '* * * * *');
    config()->set('speedtest.mode', 'random');
    config()->set('speedtest.servers', '1,2');

    CheckForScheduledSpeedtests::run();

    expect(Result::count())->toBe(1);
});

test('runs sequential speedtests when mode sequential', function () {
    config()->set('speedtest.schedule', '* * * * *');
    config()->set('speedtest.mode', 'sequential');
    config()->set('speedtest.servers', '1,2');

    CheckForScheduledSpeedtests::run();

    expect(Result::count())->toBe(2);
    expect(Result::orderBy('id')->pluck('data->server->id')->toArray())->toBe([
        1,
        2,
    ]);
});
