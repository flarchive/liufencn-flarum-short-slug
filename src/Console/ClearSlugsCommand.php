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
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * `php flarum short-slug:clear`
 *
 * Drops the slugs of discussions that already exist, so that their URLs become
 * `/d/{id}` right away instead of only for newly created discussions.
 */
class ClearSlugsCommand extends AbstractCommand
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('short-slug:clear')
            ->setDescription('Remove auto-generated slugs from existing discussions so their URLs become /d/{id}.')
            ->addOption('id', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Only process the given discussion IDs (can be repeated)')
            ->addOption('keep-custom', null, InputOption::VALUE_NONE, 'Keep slugs that do not look auto-generated (i.e. hand-written ones)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Report what would change without writing anything');
    }

    protected function fire(): int
    {
        $ids = array_values(array_filter(array_map('intval', (array) $this->input->getOption('id'))));
        $keepCustom = (bool) $this->input->getOption('keep-custom');
        $dryRun = (bool) $this->input->getOption('dry-run');
        $locale = (string) $this->settings->get('default_locale', 'en');

        $query = Discussion::query()->where('slug', '!=', '');

        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        $cleared = 0;
        $kept = 0;
        $preview = [];

        $query->chunkById(200, function ($discussions) use (&$cleared, &$kept, &$preview, $keepCustom, $dryRun, $locale) {
            foreach ($discussions as $discussion) {
                if ($keepCustom && $discussion->slug !== Str::slug((string) $discussion->title, '-', $locale)) {
                    $kept++;

                    continue;
                }

                if ($dryRun) {
                    if (count($preview) < 10) {
                        $preview[] = sprintf('  #%s  %s/%s', $discussion->id, $discussion->slug, $discussion->title);
                    }
                } else {
                    Discussion::query()->whereKey($discussion->id)->update(['slug' => '']);
                }

                $cleared++;
            }
        });

        if ($preview !== []) {
            $this->comment('Sample of slugs that would be cleared:');

            foreach ($preview as $line) {
                $this->output->writeln($line);
            }
        }

        $this->info(sprintf(
            '%s %d discussion slug(s).%s',
            $dryRun ? 'Would clear' : 'Cleared',
            $cleared,
            $kept > 0 ? sprintf(' Kept %d hand-written slug(s).', $kept) : ''
        ));

        if ($ids === [] && $cleared === 0 && $kept === 0) {
            $this->comment('Nothing to do: no discussion currently carries a slug.');
        }

        return self::SUCCESS;
    }
}
