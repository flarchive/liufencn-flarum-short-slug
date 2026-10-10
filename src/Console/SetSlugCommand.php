<?php

/*
 * This file is part of liufencn/flarum-short-slug.
 *
 * Copyright (c) LiuFenCN
 *
 * Licensed under the GNU Affero General Public License v3.0 or later.
 * See the LICENSE file distributed with this package.
 */

namespace LiuFenCN\ShortSlug\Console;

use Flarum\Console\AbstractCommand;
use Flarum\Discussion\Discussion;
use LiuFenCN\ShortSlug\StripAutoSlug;
use Symfony\Component\Console\Input\InputArgument;

/**
 * `php flarum short-slug:set 123 my-custom-title`
 */
class SetSlugCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setName('short-slug:set')
            ->setDescription('Set (or clear) the URL slug of a single discussion.')
            ->addArgument('id', InputArgument::REQUIRED, 'The discussion ID')
            ->addArgument('slug', InputArgument::OPTIONAL, 'The slug to use. Omit it (or pass an empty string) to fall back to /d/{id}.');
    }

    protected function fire(): int
    {
        $id = (int) $this->input->getArgument('id');
        $raw = $this->input->getArgument('slug');

        /** @var Discussion|null $discussion */
        $discussion = Discussion::query()->find($id);

        if ($discussion === null) {
            $this->error("Discussion #$id does not exist.");

            return self::FAILURE;
        }

        if ($raw === null || trim((string) $raw) === '') {
            $discussion->slug = '';
            $discussion->save();

            $this->info("Discussion #$id now uses the short URL /d/$id.");

            return self::SUCCESS;
        }

        $slug = StripAutoSlug::normalise($raw);

        if ($slug === '') {
            $this->error('The given slug contains no usable characters (letters, digits, dot, underscore and dash are allowed).');

            return self::FAILURE;
        }

        $discussion->slug = $slug;
        $discussion->save();

        $this->info("Discussion #$id now uses the slug \"$slug\" (URL: /d/$id-$slug).");

        return self::SUCCESS;
    }
}
