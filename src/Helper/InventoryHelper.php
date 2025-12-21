<?php
/**
 * @package			Joomla.Site
 * @subpackage		Modules - mod_jbcookies
 * 
 * @author			JoomBall! Project
 * @link			http://www.joomball.com
 * @copyright		Copyright © 2011-2026 JoomBall! Project. All Rights Reserved.
 * @license			GNU/GPL, http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace JB\Module\JBCookies\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class InventoryHelper
{
    public const DEFAULT_CATEGORY = 'unassigned';

    protected static function coerceToArray($value): array
    {
        if ($value instanceof Registry)
        {
            return $value->toArray();
        }

        if (is_array($value))
        {
            return $value;
        }

        if (is_object($value))
        {
            return get_object_vars($value);
        }

        return [];
    }

    protected static function coerceToIterable($value): array
    {
        $array = self::coerceToArray($value);

        // Some Joomla subforms come keyed as cookie_prefs0/preferences0/etc.
        // Iterating the values is what we want.
        return array_values($array);
    }

    public static function normaliseDetectedCookies(array $cookies): array
    {
        $date  = Factory::getDate()->toSql();
        $found = [];

        foreach ($cookies as $cookie)
        {
            if ($cookie instanceof Registry)
            {
                $cookie = $cookie->toArray();
            }

            if (!is_array($cookie))
            {
                continue;
            }

            $name = trim((string) ($cookie['name'] ?? ''));

            if ($name === '')
            {
                continue;
            }

            if (self::isIgnoredCookieName(strtolower($name)))
            {
                continue;
            }

            $prepared = self::prepareCookie([
                'name'        => $name,
                'category'    => $cookie['category'] ?? self::DEFAULT_CATEGORY,
                'description' => $cookie['description'] ?? '',
                'provider'    => $cookie['provider'] ?? '',
                'detected'    => $cookie['detected'] ?? $date,
            ], $date);

            if ($prepared)
            {
                $found[] = $prepared;
            }
        }

        $found = self::filterVolatileCookies($found);

        return self::ensureEssentialCookies($found);
    }

    public static function getInventoryFromParams(Registry $params): array
    {
        $cookiePrefs = $params->get('cookie_prefs', []);
        $inventory   = self::normaliseCookiePrefs($cookiePrefs);
        $inventory   = self::filterVolatileCookies($inventory);

        // Only what is configured in the module (plus essentials).
        return self::ensureEssentialCookies($inventory);
    }

    public static function detectCookiesFromRequest(): array
    {
        $date = Factory::getDate()->toSql();
        $found = [];

        if (!empty($_COOKIE))
        {
            foreach ($_COOKIE as $name => $value)
            {
                if (!is_string($name) || $name === '')
                {
                    continue;
                }

                $lower = strtolower($name);

                if (self::isIgnoredCookieName($lower))
                {
                    continue;
                }

                $prepared = self::prepareCookie([
                    'name'     => $name,
                    'category' => self::DEFAULT_CATEGORY,
                ], $date);

                if ($prepared)
                {
                    $found[] = $prepared;
                }
            }
        }

        $found = self::filterVolatileCookies($found);

        return self::ensureEssentialCookies($found);
    }

    protected static function ensureEssentialCookies(array $inventory): array
    {
        $indexed    = self::indexByName($inventory);
        $blueprints = self::getEssentialCookieBlueprints();

        foreach ($blueprints as $name => $data)
        {
            if (!isset($indexed[$name]))
            {
                $indexed[$name] = $data;
                continue;
            }

            $indexed[$name]['category'] = 'necessary';
            $indexed[$name]['description'] = ($indexed[$name]['description'] ?? '') ?: $data['description'];
            $indexed[$name]['provider'] = ($indexed[$name]['provider'] ?? '') ?: $data['provider'];
        }

        return array_values($indexed);
    }

    protected static function normaliseCookiePrefs($cookiePrefs): array
    {
        $cookiePrefs = self::coerceToIterable($cookiePrefs);

        $indexed = [];
        $date    = Factory::getDate()->toSql();

        foreach ($cookiePrefs as $groupRow)
        {
            $groupRow = self::coerceToArray($groupRow);

            $category = self::sanitiseSlug($groupRow['category'] ?? '') ?: self::DEFAULT_CATEGORY;
            $cookies  = $groupRow['preferences'] ?? [];

            $cookies = self::coerceToIterable($cookies);

            foreach ($cookies as $cookie)
            {
                $cookie = self::coerceToArray($cookie);

                // Joomla subform rows often wrap fields inside an `options` key.
                if (isset($cookie['options']))
                {
                    $cookie = self::coerceToArray($cookie['options']);
                }

                $show = (int) ($cookie['show'] ?? 1);

                if ($show !== 1)
                {
                    continue;
                }

                $prepared = self::prepareCookie([
                    'name'        => $cookie['name'] ?? '',
                    'category'    => $category,
                    'description' => $cookie['description'] ?? '',
                    'provider'    => $cookie['provider'] ?? '',
                ], $date);

                if (!$prepared)
                {
                    continue;
                }

                $indexed[$prepared['name']] = $prepared;
            }
        }

        return array_values($indexed);
    }

    public static function normaliseGroups($groups): array
    {
        $list = [];

        if ($groups instanceof Registry)
        {
            $groups = $groups->toArray();
        }

        if (!is_array($groups))
        {
            $groups = [];
        }

        foreach ($groups as $group)
        {
            if ($group instanceof Registry)
            {
                $group = $group->toArray();
            }

            if (!is_array($group))
            {
                continue;
            }

            $slug = self::sanitiseSlug($group['slug'] ?? $group['title'] ?? '');

            if ($slug === '')
            {
                continue;
            }

            $list[$slug] = [
                'slug'        => $slug,
                'title'       => trim($group['title'] ?? $slug),
                'description' => trim($group['description'] ?? ''),
                'required'    => (int) ($group['required'] ?? 0) === 1,
                'default'     => (int) ($group['default'] ?? 0) === 1,
            ];
        }

        if (!$list)
        {
            $list = self::getDefaultGroups();
        }

        if (!isset($list[self::DEFAULT_CATEGORY]))
        {
            $list[self::DEFAULT_CATEGORY] = [
                'slug'        => self::DEFAULT_CATEGORY,
                'title'       => Text::_('MOD_JBCOOKIES_GROUP_UNASSIGNED_TITLE'),
                'description' => Text::_('MOD_JBCOOKIES_GROUP_UNASSIGNED_DESC'),
                'required'    => false,
                'default'     => false,
            ];
        }

        return array_values($list);
    }

    public static function groupInventory(array $inventory, array $groups): array
    {
        $grouped = [];

        foreach ($groups as $group)
        {
            $grouped[$group['slug']] = [];
        }

        foreach ($inventory as $cookie)
        {
            $category = $cookie['category'] ?? self::DEFAULT_CATEGORY;

            if (!isset($grouped[$category]))
            {
                $grouped[$category] = [];
            }

            $grouped[$category][] = $cookie;
        }

        return $grouped;
    }

    public static function getDefaultGroups(): array
    {
        return [
            'necessary' => [
                'slug'        => 'necessary',
                'title'       => Text::_('MOD_JBCOOKIES_GROUP_NECESSARY_TITLE'),
                'description' => Text::_('MOD_JBCOOKIES_GROUP_NECESSARY_DESC'),
                'required'    => true,
                'default'     => true,
            ],
            'analytics' => [
                'slug'        => 'analytics',
                'title'       => Text::_('MOD_JBCOOKIES_GROUP_ANALYTICS_TITLE'),
                'description' => Text::_('MOD_JBCOOKIES_GROUP_ANALYTICS_DESC'),
                'required'    => false,
                'default'     => false,
            ],
            'marketing' => [
                'slug'        => 'marketing',
                'title'       => Text::_('MOD_JBCOOKIES_GROUP_MARKETING_TITLE'),
                'description' => Text::_('MOD_JBCOOKIES_GROUP_MARKETING_DESC'),
                'required'    => false,
                'default'     => false,
            ],
            self::DEFAULT_CATEGORY => [
                'slug'        => self::DEFAULT_CATEGORY,
                'title'       => Text::_('MOD_JBCOOKIES_GROUP_UNASSIGNED_TITLE'),
                'description' => Text::_('MOD_JBCOOKIES_GROUP_UNASSIGNED_DESC'),
                'required'    => false,
                'default'     => false,
            ],
        ];
    }

    protected static function prepareCookie(array $cookie, string $date): array
    {
        $name = trim($cookie['name'] ?? '');

        if ($name === '')
        {
            return [];
        }

        $category = self::sanitiseSlug($cookie['category'] ?? '') ?: self::DEFAULT_CATEGORY;

        $prepared = [
            'name'        => $name,
            'category'    => $category,
            'description' => trim($cookie['description'] ?? ''),
            'detected'    => $cookie['detected'] ?? $date,
            'provider'    => trim($cookie['provider'] ?? ''),
        ];

        return self::applyMetadataHints($prepared);
    }

    protected static function indexByName(array $inventory): array
    {
        $indexed = [];

        foreach ($inventory as $cookie)
        {
            if (!isset($cookie['name']))
            {
                continue;
            }

            $indexed[$cookie['name']] = $cookie;
        }

        return $indexed;
    }

    protected static function sanitiseSlug(string $raw): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9_\-]/i', '-', $raw));
        $slug = trim($slug, '-');

        return $slug;
    }

    protected static function getEssentialCookieBlueprints(): array
    {
        $date = Factory::getDate()->toSql();

        return [
            'jbcookies' => [
                'name'        => 'jbcookies',
                'category'    => 'necessary',
                'description' => 'MOD_JBCOOKIES_USE_JBCOOKIES',
                'detected'    => $date,
                'provider'    => 'JoomBall!',
            ],
            'joomla_user_state' => [
                'name'        => 'joomla_user_state',
                'category'    => 'necessary',
                'description' => 'MOD_JBCOOKIES_USE_JOOMLA_USER_STATE',
                'detected'    => $date,
                'provider'    => 'Joomla!',
            ],
            'joomla_remember_me_*' => [
                'name'        => 'joomla_remember_me_*',
                'category'    => 'necessary',
                'description' => 'MOD_JBCOOKIES_USE_JOOMLA_REMEMBER_ME',
                'detected'    => $date,
                'provider'    => 'Joomla!',
            ],
        ];
    }

    protected static function isIgnoredCookieName(string $lower): bool
    {
        if (str_starts_with($lower, 'jbcookies'))
        {
            return true;
        }

        if (str_starts_with($lower, 'atum'))
        {
            return true;
        }

        $ignoredPrefixes = [
            'oscolorscheme',
        ];

        foreach ($ignoredPrefixes as $prefix)
        {
            if (str_starts_with($lower, $prefix))
            {
                return true;
            }
        }

        return false;
    }

    protected static function applyMetadataHints(array $cookie): array
    {
        $cookie['provider'] = $cookie['provider'] ?? '';
        $cookie['category'] = $cookie['category'] ?? self::DEFAULT_CATEGORY;
        $cookie['description'] = $cookie['description'] ?? '';

        $nameLower = strtolower($cookie['name']);

        if ($nameLower === '_ga' || str_starts_with($nameLower, '_ga_'))
        {
            $cookie['category'] = 'analytics';
            $cookie['provider'] = $cookie['provider'] ?: 'Google Analytics';
            $cookie['description'] = $nameLower === '_ga' ? 'MOD_JBCOOKIES_USE_GA' : '';
        }

        if (str_starts_with($nameLower, 'joomla_remember_me'))
        {
            $cookie['category'] = 'necessary';
            $cookie['provider'] = $cookie['provider'] ?: 'Joomla!';
            $cookie['description'] = $cookie['description'] ?: 'MOD_JBCOOKIES_USE_JOOMLA_REMEMBER_ME';
        }

        if ($nameLower === 'joomla_user_state')
        {
            $cookie['category'] = 'necessary';
            $cookie['provider'] = $cookie['provider'] ?: 'Joomla!';
            $cookie['description'] = $cookie['description'] ?: 'MOD_JBCOOKIES_USE_JOOMLA_USER_STATE';
        }

        return $cookie;
    }

    protected static function filterVolatileCookies(array $inventory): array
    {
        return array_values(array_filter($inventory, function ($cookie) {
            $name = $cookie['name'] ?? '';

            if (!$name)
            {
                return false;
            }

            return !self::isVolatileSessionCookie($name);
        }));
    }

    protected static function isVolatileSessionCookie(string $name): bool
    {
        return (bool) preg_match('/^[a-f0-9]{32}$/i', $name);
    }
}
