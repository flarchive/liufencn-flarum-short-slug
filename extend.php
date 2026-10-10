<?php

/*
 * This file is part of liufencn/flarum-short-slug.
 *
 * Copyright (c) LiuFenCN
 *
 * Licensed under the GNU Affero General Public License v3.0 or later.
 * See the LICENSE file distributed with this package.
 */

use Flarum\Discussion\Event\Saving;
use Flarum\Extend;
use LiuFenCN\ShortSlug\Console\ClearSlugsCommand;
use LiuFenCN\ShortSlug\Console\SetSlugCommand;
use LiuFenCN\ShortSlug\StripAutoSlug;

return [
    (new Extend\Settings())
        ->default(StripAutoSlug::ENABLED, true)
        ->default(StripAutoSlug::ALLOW_CUSTOM, true),

    (new Extend\Locales(__DIR__.'/locale')),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Event())
        ->listen(Saving::class, StripAutoSlug::class),

    (new Extend\Console())
        ->command(ClearSlugsCommand::class)
        ->command(SetSlugCommand::class),
];
