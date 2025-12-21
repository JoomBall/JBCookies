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

use JB\Module\JBCookies\Site\Helper\InventoryHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Registry\Registry;
use RuntimeException;
use Throwable;

class JbcookiesHelper
{
    public function getDisplayData(Registry $params): array
    {
        $domain    = $this->resolveDomain($params);
        $document  = Factory::getDocument();
        $app       = Factory::getApplication();
        $wa        = $document->getWebAssetManager();

        $wa->registerStyle('cookies', 'media/mod_jbcookies/css/cookies.min.css', ['version' => 'auto'], ['rel' => 'preload', 'as' => 'style', 'onload' => "this.onload=null;this.rel='stylesheet'"])
            ->useStyle('cookies')
            ->useScript('jquery')
            ->useScript('jquery-noconflict')
            ->useScript('bootstrap.modal')
            ->registerScript('mod_jbcookies.consent', 'media/mod_jbcookies/js/consent.min.js', ['version' => 'auto'], ['defer' => true])
            ->useScript('mod_jbcookies.consent');


        $show_policy_cookies = (int) $params->get('show_policy_cookies', 1);
        $show_article_modal  = (int) $params->get('show_article_modal', 1);
        $moduleclass_sfx     = htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_COMPAT, 'UTF-8');
        $showInfo            = $show_policy_cookies;

        $lang        = Factory::getLanguage();
        $currentLang = $lang->getTag();
        $langs       = $params->get('lang');

        $title               = !empty($langs->$currentLang->title) ? $langs->$currentLang->title : Text::_('MOD_JBCOOKIES_LANG_TITLE');
        $text                = !empty($langs->$currentLang->text) ? $langs->$currentLang->text : Text::_('MOD_JBCOOKIES_LANG_TEXT');
        $header              = !empty($langs->$currentLang->header) ? $langs->$currentLang->header : Text::_('MOD_JBCOOKIES_LANG_HEADER');
        $body                = !empty($langs->$currentLang->body) ? $langs->$currentLang->body : Text::_('MOD_JBCOOKIES_LANG_BODY');
        $accept              = !empty($langs->$currentLang->accept) ? $langs->$currentLang->accept : Text::_('MOD_JBCOOKIES_GLOBAL_ACCEPT');
        $aLink               = !empty($langs->$currentLang->alink) ? $langs->$currentLang->alink : '';
        $reject              = !empty($langs->$currentLang->reject) ? $langs->$currentLang->reject : Text::_('MOD_JBCOOKIES_LANG_TITLE_DEFAULT');
        
        // if ($params->get('show_decline', 1))
        // {
        //     $aliasButton_decline = '<button class="btn btn-primary" type="button"><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgMTIwLjIzIDEyMi44OCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTIwLjIzIDEyMi44OCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PHN0eWxlIHR5cGU9InRleHQvY3NzIj4uc3Qwe2ZpbGwtcnVsZTpldmVub2RkO2NsaXAtcnVsZTpldmVub2RkO308L3N0eWxlPjxnPjxwYXRoIGZpbGw9IiNmZmYiIGNsYXNzPSJzdDAiIGQ9Ik05OC4xOCwwYzMuMywwLDUuOTgsMi42OCw1Ljk4LDUuOThjMCwzLjMtMi42OCw1Ljk4LTUuOTgsNS45OGMtMy4zLDAtNS45OC0yLjY4LTUuOTgtNS45OCBDOTIuMjEsMi42OCw5NC44OCwwLDk4LjE4LDBMOTguMTgsMHogTTk5Ljc4LDUyLjA4YzUuMTYsNy43LDExLjY5LDEwLjA2LDIwLjE3LDQuODVjMC4yOCwyLjksMC4zNSw1Ljg2LDAuMiw4Ljg2IGMtMS42NywzMy4xNi0yOS45LDU4LjY5LTYzLjA2LDU3LjAyQzIzLjk0LDEyMS4xMy0xLjU5LDkyLjksMC4wOCw1OS43NUMxLjc0LDI2LjU5LDMwLjk1LDAuNzgsNjQuMSwyLjQ1IGMtMi45NCw5LjItMC40NSwxNy4zNyw3LjAzLDIwLjE1QzY0LjM1LDQ0LjM4LDc5LjQ5LDU4LjYzLDk5Ljc4LDUyLjA4TDk5Ljc4LDUyLjA4eiBNMzAuMDMsNDcuNzljNC45NywwLDguOTksNC4wMyw4Ljk5LDguOTkgcy00LjAzLDguOTktOC45OSw4Ljk5Yy00Ljk3LDAtOC45OS00LjAzLTguOTktOC45OVMyNS4wNyw0Ny43OSwzMC4wMyw0Ny43OUwzMC4wMyw0Ny43OXogTTU4LjM1LDU5LjI1YzIuODYsMCw1LjE4LDIuMzIsNS4xOCw1LjE4IGMwLDIuODYtMi4zMiw1LjE4LTUuMTgsNS4xOGMtMi44NiwwLTUuMTgtMi4zMi01LjE4LTUuMThDNTMuMTYsNjEuNTcsNTUuNDgsNTkuMjUsNTguMzUsNTkuMjVMNTguMzUsNTkuMjV6IE0zNS44Nyw4MC41OSBjMy40OSwwLDYuMzIsMi44Myw2LjMyLDYuMzJjMCwzLjQ5LTIuODMsNi4zMi02LjMyLDYuMzJjLTMuNDksMC02LjMyLTIuODMtNi4zMi02LjMyQzI5LjU1LDgzLjQxLDMyLjM4LDgwLjU5LDM1Ljg3LDgwLjU5IEwzNS44Nyw4MC41OXogTTQ5LjQ5LDMyLjIzYzIuNzQsMCw0Ljk1LDIuMjIsNC45NSw0Ljk1YzAsMi43NC0yLjIyLDQuOTUtNC45NSw0Ljk1Yy0yLjc0LDAtNC45NS0yLjIyLTQuOTUtNC45NSBDNDQuNTQsMzQuNDUsNDYuNzYsMzIuMjMsNDkuNDksMzIuMjNMNDkuNDksMzIuMjN6IE03Ni4zOSw4Mi44YzQuNTksMCw4LjMsMy43Miw4LjMsOC4zYzAsNC41OS0zLjcyLDguMy04LjMsOC4zIGMtNC41OSwwLTguMy0zLjcyLTguMy04LjNDNjguMDksODYuNTIsNzEuODEsODIuOCw3Ni4zOSw4Mi44TDc2LjM5LDgyLjh6IE05My44NywyMy4xYzMuMDgsMCw1LjU4LDIuNSw1LjU4LDUuNThjMCwzLjA4LTIuNSw1LjU4LTUuNTgsNS41OHMtNS41OC0yLjUtNS41OC01LjU4Qzg4LjI5LDI1LjYsOTAuNzksMjMuMSw5My44NywyMy4xTDkzLjg3LDIzLjF6Ii8+PC9nPjwvc3ZnPg=="/></button>';
        // }

        $item = null;
        if ($show_policy_cookies && $aLink)
        {
            if ($show_article_modal)
            {
                $model = $app->bootComponent('com_content')->getMVCFactory()->createModel('Article', 'Site', ['ignore_request' => true]);
                $model->setState('filter.published', 1);
                $paramsApp = $app->getParams();
                $model->setState('params', $paramsApp);
                $model->setState('article.id', (int) $aLink);
                $item = $model->getItem();

                if (!empty($item->params) && is_object($item->params))
                {
                    $showInfo = ($item->params->get('show_intro', '1') == '1');
                }
                else
                {
                    $paramsContent = ComponentHelper::getParams('com_content');
                    $showInfo      = ($paramsContent->get('show_intro', '1') == '1');
                }

                if ($showInfo)
                {
                    $item->text = $item->introtext . ' ' . $item->fulltext;
                }
                elseif ($item->fulltext)
                {
                    $item->text = $item->fulltext;
                }
                else
                {
                    $item->text = $item->introtext;
                }

                $aLink = 0;
                $show_policy_cookies = 1;
                $header = $item->title;
                $body   = $item->text;
            }
            else
            {
                $db    = Factory::getDbo();
                $query = $db->getQuery(true)
                    ->select('a.id, a.alias, a.catid, a.language')
                    ->from('#__content AS a')
                    ->where('a.id = ' . (int) $aLink);

                $db->setQuery((string) $query);
                $item = $db->loadObject();

                if ($item)
                {
                    $item->slug          = $item->id . ':' . $item->alias;
                    $item->readmore_link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
                }
            }
        }

        $cookieGroups          = InventoryHelper::getDefaultGroups();
        $durationDays          = (int) $params->get('duration_cookie_days', 365);

        $cookieInventory       = InventoryHelper::getInventoryFromParams($params);
        $cookieInventoryGroups = InventoryHelper::groupInventory($cookieInventory, $cookieGroups);

        // echo '<pre>'; print_r($params->get('cookie_prefs')); echo '</pre>'; // For debug purposes

        // $formToken             = Session::getFormToken();
        // $ajaxEndpoint          = Route::_('index.php?option=com_ajax&module=jbcookies&method=inventory&format=json&' . $formToken . '=1', false);

        $cookiePreferencesConfig = [
            'domain'    => trim($domain),
            'duration'  => $durationDays,
            // 'groups'    => $cookieGroups,
            'inventory' => $cookieInventoryGroups,
            // 'ajax'      => [
            //     'url'   => $ajaxEndpoint,
            //     'token' => $formToken,
            // ],
            // 'allowBrowserCapture' => false,
        ];

        $this->injectConsentScript($cookiePreferencesConfig);

        $cookiePreferencesConfig['groups'] = $cookieGroups;

        return [
            'domain'                  => $domain,
            'show_article_modal'      => $show_article_modal,
            'moduleclass_sfx'         => $moduleclass_sfx,
            'title'                   => $title,
            'text'                    => $text,
            'header'                  => $header,
            'body'                    => $body,
            'accept'                  => $accept,
            'aLink'                   => $aLink,
            'reject'                  => $reject,
            'item'                    => $item,
            'show_info'               => $showInfo,
            'cookiePreferencesConfig' => $cookiePreferencesConfig,
        ];
    }

    public static function inventoryAjax()
    {
        $helper = new self();
        return $helper->inventory();
    }

    public static function detectAjax()
    {
        try
        {
            // Detect no requiere autorización para permitir llamadas admin→site
            return InventoryHelper::detectCookiesFromRequest();
        }
        catch (Throwable $exception)
        {
            Log::add('mod_jbcookies detect error: ' . $exception->getMessage(), Log::ERROR, 'mod_jbcookies');
            throw $exception;
        }
    }

    public static function scanAjax()
    {
        try
        {
            $helper = new self();

            $app   = Factory::getApplication();
            $input = $app->getInput();
            $urls  = $input->get('urls', [], 'array');
            $urls  = $helper->sanitiseUrls($urls);
            $urls  = $urls ?: [$helper->getSiteRootUrl()];

            $found = [];

            foreach ($urls as $url)
            {
                $found = array_merge($found, $helper->scanUrl($url));
            }

            return InventoryHelper::normaliseDetectedCookies($found);
        }
        catch (Throwable $exception)
        {
            Log::add('mod_jbcookies scan error: ' . $exception->getMessage(), Log::ERROR, 'mod_jbcookies');
            throw $exception;
        }
    }

    public function inventory()
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();
        $mode  = $input->getCmd('mode', 'list');
        $format = $input->getCmd('format', 'json');

        // Autorizar según el modo (no por token, ya que puede venir de admin con token distinto)
        // 'scan' y 'detect' NO requieren autorización para evitar 403 por diferencia de sesión admin/site
        if (in_array($mode, ['register'], true))
        {
            $this->authoriseScan();
        }

        try
        {
            switch ($mode)
            {
                case 'detect':
                    $result = InventoryHelper::detectCookiesFromRequest();
                    break;

                case 'scan':
                    $urls  = $input->get('urls', [], 'array');
                    $urls  = $this->sanitiseUrls($urls);
                    $urls  = $urls ?: [$this->getSiteRootUrl()];
                    $found = [];

                    foreach ($urls as $url)
                    {
                        $found = array_merge($found, $this->scanUrl($url));
                    }

                    $result = InventoryHelper::normaliseDetectedCookies($found);
                    break;

                case 'register':
                    $this->authoriseScan();
                    $result = [];
                    break;

                default:
                    $result = [];
                    break;
            }
        }
        catch (Throwable $exception)
        {
            Log::add('mod_jbcookies inventory error: ' . $exception->getMessage(), Log::ERROR, 'mod_jbcookies');

            if ($format !== 'json')
            {
                return $this->renderInventoryError($exception);
            }

            throw $exception;
        }

        return $format === 'json' ? $result : $this->renderInventoryConsole($result, $mode);
    }

    protected function authoriseScan(): void
    {
        $user = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.manage') && !$user->authorise('core.admin'))
        {
            throw new RuntimeException(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
        }
    }

    protected function scanUrl(string $url): array
    {
        $cookies = [];

        $response = null;

        $host = (string) (parse_url($url, PHP_URL_HOST) ?: '');
        $isDevHost = $host === 'localhost' || str_ends_with($host, '.test') || str_ends_with($host, '.local');

        try
        {
            $options = [];
            
            if ($isDevHost && strpos($url, 'https://') === 0)
            {
                $options = [
                    'transport.curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ],
                ];
            }

            $client   = HttpFactory::getHttp($options);
            $response = $client->get($url);
        }
        catch (Throwable $exception)
        {
            if ($isDevHost)
            {
                try
                {
                    $client   = HttpFactory::getHttp([
                        'transport.curl' => [
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                        ],
                    ]);
                    $response = $client->get($url);
                }
                catch (Throwable $retryException)
                {
                    throw new RuntimeException(
                        'Error al escanear cookies (dev): ' . $retryException->getMessage(),
                        0,
                        $retryException
                    );
                }
            }
            else
            {
                throw $exception;
            }
        }

        $headers = method_exists($response, 'getHeaders') ? $response->getHeaders() : ($response->headers ?? []);

        foreach ($this->extractHeaderValues($headers, 'set-cookie') as $value)
        {
            $parts      = explode(';', (string) $value);
            $namePair   = explode('=', trim($parts[0]), 2);
            $cookieName = trim($namePair[0] ?? '');

            if ($cookieName === '')
            {
                continue;
            }

            $cookies[] = [
                'name'     => $cookieName,
                'source'   => parse_url($url, PHP_URL_HOST) ?: 'http',
                'category' => InventoryHelper::DEFAULT_CATEGORY,
            ];
        }

        return $cookies;
    }

    protected function extractHeaderValues($headers, string $needle): array
    {
        $values = [];

        if ($headers instanceof Registry)
        {
            $headers = $headers->toArray();
        }

        if (!is_array($headers))
        {
            return $values;
        }

        foreach ($headers as $key => $value)
        {
            if (is_numeric($key) && is_array($value) && isset($value['name'], $value['value']))
            {
                $key   = $value['name'];
                $value = $value['value'];
            }

            if (is_string($key) && strtolower($key) === $needle)
            {
                if (is_array($value))
                {
                    foreach ($value as $item)
                    {
                        $values[] = $item;
                    }
                }
                else
                {
                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    protected function parseCookieAttributes(array $segments): array
    {
        $attributes = [];

        foreach ($segments as $segment)
        {
            $segment = trim($segment);

            if ($segment === '')
            {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $segment, 2), 2, '');
            $key = strtolower(trim($key));

            if ($key === 'expires')
            {
                $attributes['expires'] = trim($value);
            }
            elseif ($key === 'path')
            {
                $attributes['path'] = trim($value);
            }
        }

        return $attributes;
    }

    protected function sanitiseUrls(array $urls): array
    {
        $clean = [];

        foreach ($urls as $url)
        {
            $url = trim((string) $url);

            if ($url === '')
            {
                continue;
            }

            if (!preg_match('#^https?://#i', $url))
            {
                $url = Uri::root() . ltrim($url, '/');
            }

            $clean[] = $url;
        }

        return array_unique($clean);
    }

    protected function renderInventoryConsole(array $result, string $mode): string
    {
        $token        = Session::getFormToken();
        $ajaxEndpoint = Route::_('index.php?option=com_ajax&module=jbcookies&method=inventory&format=json', false);
        $count        = count($result);
        $modeLabel    = strtoupper($mode);
        $resultJson   = htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_NOQUOTES, 'UTF-8');

        $tokenJs     = json_encode($token, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $ajaxUrlJs   = json_encode($ajaxEndpoint, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $ignoredJs   = json_encode($this->getIgnoredCookiePrefixes(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        $title   = Text::_('MOD_JBCOOKIES_FIELD_INVENTORY_BUTTON_LABEL');
        $summary = Text::sprintf('MOD_JBCOOKIES_INVENTORY_SCAN_SUMMARY', $modeLabel, $count);

        if ($summary === 'MOD_JBCOOKIES_INVENTORY_SCAN_SUMMARY')
        {
            $summary = sprintf('Acción: %s · Cookies registradas: %d', strtolower($modeLabel), $count);
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{$title}</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 2rem; background: #f9fafb; color: #111; }
        h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .jb-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        pre { background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-size: 0.85rem; }
        .jb-status { font-weight: 600; margin-bottom: 0.5rem; }
        .jb-muted { color: #6b7280; }
    </style>
</head>
<body>
    <div class="jb-card">
        <h1>{$title}</h1>
        <p>{$summary}</p>
        <pre>{$resultJson}</pre>
    </div>

    <div class="jb-card">
        <h2>Browser Capture</h2>
        <p class="jb-muted">Esta ventana intentará leer las cookies disponibles en este navegador y registrarlas automáticamente.</p>
        <div id="jb-browser-status" class="jb-status">Analizando cookies del navegador…</div>
        <pre id="jb-browser-result">Esperando resultados…</pre>
    </div>

    <script>
    (function () {
        const token = {$tokenJs};
        const ajaxUrl = {$ajaxUrlJs};
        const ignored = {$ignoredJs};
        const statusEl = document.getElementById('jb-browser-status');
        const resultEl = document.getElementById('jb-browser-result');

        const cookies = readBrowserCookies();

        if (!cookies.length) {
            statusEl.textContent = 'No se detectaron cookies accesibles desde este contexto del navegador.';
            resultEl.textContent = '[]';
            return;
        }

        statusEl.textContent = 'Se encontraron ' + cookies.length + ' cookies en el navegador. Registrando…';

        const payload = new FormData();
        payload.append(token, '1');
        payload.append('mode', 'register');
        payload.append('cookies', JSON.stringify(cookies));

        fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: payload
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                return response.json();
            })
            .then(function (data) {
                if (data && data.error) {
                    statusEl.textContent = 'El servidor devolvió un error al registrar las cookies.';
                    resultEl.textContent = JSON.stringify(data, null, 2);
                    return;
                }

                const length = Array.isArray(data) ? data.length : 0;
                statusEl.textContent = 'Importación completada. Inventario total: ' + length + ' cookies.';
                resultEl.textContent = JSON.stringify(data, null, 2);
            })
            .catch(function (error) {
                statusEl.textContent = 'No se pudo registrar las cookies del navegador: ' + error.message;
                resultEl.textContent = error.stack || error.message;
            });

        function readBrowserCookies() {
            if (!document.cookie) {
                return [];
            }

            return document.cookie.split(';').map(function (entry) {
                var pair = entry.split('=');
                var name = (pair[0] || '').trim();

                if (!name) {
                    return null;
                }

                var lowered = name.toLowerCase();

                for (var i = 0; i < ignored.length; i++) {
                    if (lowered.indexOf(ignored[i]) === 0) {
                        return null;
                    }
                }

                return {
                    name: name,
                    source: window.location.hostname,
                    category: 'unassigned'
                };
            }).filter(function (cookie) {
                return cookie !== null;
            });
        }
    })();
    </script>
</body>
</html>
HTML;
    }

    protected function resolveDomain(Registry $params): string
    {
        $domain = str_replace(['https://www.', 'http://www.', 'https://', 'http://'], '', Uri::base());

        if ((strpos($domain, '/') !== false) || (strstr($domain, 'localhost', true) !== false))
        {
            return '';
        }

        if ($params->get('subdomain_alias', 0) && count(explode('.', $domain)) > 1)
        {
            $parts = explode('.', $domain);

            return $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        }

        return '';
    }

    protected function getSiteRootUrl(): string
    {
        $root = Uri::root();

        if (Factory::getApplication()->isClient('administrator'))
        {
            $root = preg_replace('#/administrator/?$#i', '/', $root) ?: $root;
        }

        return rtrim($root, '/') . '/';
    }

    protected function injectConsentScript(array $config): void
    {
        $document  = Factory::getDocument();
        $configJson = json_encode($config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        // Keep config inline (tiny) and load the runtime from a cached asset.
        $document->addScriptDeclaration('window.JBCOOKIES_CONFIG = ' . $configJson . ';');
    }

    protected function renderInventoryError(Throwable $exception): string
    {
        $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
        $trace   = htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>mod_jbcookies · Error</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 2rem; background: #fff7ed; color: #7c2d12; }
        pre { background: #fff; border: 1px solid #fed7aa; border-radius: 0.5rem; padding: 1rem; overflow-x: auto; }
        .card { border: 1px solid #fed7aa; background: #fffbeb; border-radius: 0.5rem; padding: 1.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Error al registrar cookies del navegador</h1>
        <p>{$message}</p>
        <pre>{$trace}</pre>
    </div>
</body>
</html>
HTML;
    }

    protected function getIgnoredCookiePrefixes(): array
    {
        return [
            'jbcookies',
            'oscolorscheme',
            'atum',
        ];
    }
}
