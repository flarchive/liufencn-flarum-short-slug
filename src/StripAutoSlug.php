<?php

/*
 * This file is part of liufencn/flarum-short-slug.
 *
 * Copyright (c) LiuFenCN
 *
 * Licensed under the GNU Affero General Public License v3.0 or later.
 * See the LICENSE file distributed with this package.
 */

namespace LiuFenCN\ShortSlug;

use Flarum\Discussion\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Str;

/**
 * Rewrites discussion URLs to the bare `/d/{id}` form.
 *
 * Flarum's core generates a slug out of the title (`Discussion::setTitleAttribute()`
 * calls `Str::slug($title, '-', default_locale)`), which turns Chinese titles into
 * long pinyin URLs such as `/d/248-qoder-mo-ren-she-zhi`. This listener runs right
 * before the discussion is saved and drops that auto-generated value.
 *
 * Three cases are handled:
 *
 *  1. A slug explicitly submitted in the request body is honoured (that is the
 *     only way to set a custom slug, because the attribute is read-only in the
 *     JSON:API schema). Only actors allowed to rename the discussion may do so.
 *  2. A slug that was clearly hand-written earlier (`slug !== Str::slug(title)`)
 *     is left untouched, as long as the title is not being changed.
 *  3. Everything else collapses to an empty slug, i.e. `/d/{id}`.
 */
class StripAutoSlug
{
    public const ENABLED = 'liufencn-short-slug.enabled';
    public const ALLOW_CUSTOM = 'liufencn-short-slug.allow_custom';

    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function handle(Saving $event): void
    {
        if (! $this->enabled()) {
            return;
        }

        $discussion = $event->discussion;
        $attributes = is_array($event->data) ? ($event->data['attributes'] ?? []) : [];

        // 1. An explicitly submitted slug wins.
        if (is_array($attributes) && array_key_exists('slug', $attributes)) {
            $requested = self::normalise($attributes['slug']);

            if ($requested === '') {
                $discussion->slug = '';

                return;
            }

            if ($this->allowsCustom() && $event->actor->can('rename', $discussion)) {
                $discussion->slug = $requested;

                return;
            }
        }

        // 2. Keep a hand-written slug while the title is untouched.
        $original = (string) $discussion->getOriginal('slug');

        if (
            $this->allowsCustom()
            && $original !== ''
            && ! $discussion->isDirty('title')
            && $original !== $this->autoSlug((string) $discussion->title)
        ) {
            return;
        }

        // 3. Everything else becomes the bare numeric URL.
        $discussion->slug = '';
    }

    /**
     * Turn an arbitrary value into something safe to place inside a URL path.
     * Letters, digits, dots, underscores and dashes survive; anything else
     * (spaces, slashes, punctuation) collapses into a single dash.
     */
    public static function normalise(mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/[^\p{L}\p{N}._~-]+/u', '-', $value) ?? '';
        $value = trim($value, '-');

        return mb_substr($value, 0, 255);
    }

    /**
     * Reproduce the slug Flarum core would have generated for this title.
     */
    protected function autoSlug(string $title): string
    {
        return Str::slug($title, '-', (string) $this->settings->get('default_locale', 'en'));
    }

    protected function enabled(): bool
    {
        return (bool) $this->settings->get(self::ENABLED, true);
    }

    protected function allowsCustom(): bool
    {
        return (bool) $this->settings->get(self::ALLOW_CUSTOM, true);
    }
}
