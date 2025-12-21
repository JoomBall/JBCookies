(function () {
  'use strict';

  function uniqueByName(cookies) {
    var map = new Map();
    (cookies || []).forEach(function (c) {
      if (!c || !c.name) {
        return;
      }
      if (!map.has(c.name)) {
        map.set(c.name, c);
      }
    });
    return Array.from(map.values());
  }

  function isVolatileSessionCookie(name) {
    return /^[a-f0-9]{32}$/i.test(name || '');
  }

  function isIgnoredCookieName(name) {
    var lower = (name || '').toLowerCase();
    if (!lower) {
      return true;
    }
    // Keep `jbcookies` (main consent cookie) but ignore legacy/auxiliary ones
    if (lower !== 'jbcookies' && lower.indexOf('jbcookies') === 0) {
      return true;
    }
    if (lower.indexOf('atum') === 0) {
      return true;
    }
    if (lower.indexOf('oscolorscheme') === 0) {
      return true;
    }
    return false;
  }

  function applyHints(cookie) {
    if (!cookie || !cookie.name) {
      return cookie;
    }

    var nameLower = cookie.name.toLowerCase();

    if (nameLower === '_ga' || nameLower.indexOf('_ga_') === 0) {
      cookie.category = 'analytics';
      cookie.provider = cookie.provider || 'Google Analytics';
    }

    if (nameLower.indexOf('joomla_remember_me') === 0) {
      cookie.category = 'necessary';
      cookie.provider = cookie.provider || 'Joomla!';
      cookie.description = cookie.description || 'Mantiene la sesión recordada para el usuario autenticado.';
    }

    if (nameLower === 'joomla_user_state') {
      cookie.category = 'necessary';
      cookie.provider = cookie.provider || 'Joomla!';
      cookie.description = cookie.description || 'Conserva el estado de autenticación del usuario.';
    }

    return cookie;
  }

  function normaliseNameForConfig(name) {
    var raw = (name || '').trim();
    if (!raw) {
      return '';
    }

    var lower = raw.toLowerCase();

    // Make per-user / per-install cookie names stable for the module settings
    if (lower.indexOf('joomla_remember_me_') === 0) {
      return 'joomla_remember_me_*';
    }

    // GA4 property cookie is stable per site, but still nicer as a single entry
    if (lower.indexOf('_ga_') === 0) {
      return '_ga_*';
    }

    return raw;
  }

  function parseDocumentCookieString(cookieString, sourceHost) {
    if (!cookieString) {
      return [];
    }

    return cookieString.split(';').map(function (entry) {
      var name = entry.split('=')[0].trim();
      if (!name) {
        return null;
      }
      return {
        name: name,
        source: sourceHost || window.location.hostname,
        category: 'unassigned'
      };
    }).filter(function (c) {
      return c !== null;
    });
  }

  function captureCookiesFromSiteRoot(siteRootUrl, langPrefix) {
    return new Promise(function (resolve) {
      try {
        if (!siteRootUrl) {
          resolve([]);
          return;
        }

        var iframe = document.createElement('iframe');
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        iframe.style.position = 'absolute';
        iframe.style.left = '-9999px';
        iframe.style.top = '-9999px';
        iframe.setAttribute('aria-hidden', 'true');

        // Apply language prefix if present (for multilanguage sites)
        var src = siteRootUrl + (langPrefix || '');
        if (src.indexOf('?') === -1) {
          src += '?';
        } else {
          src += '&';
        }
        src += 'jbcookies_inventory_probe=1&_=' + String(Date.now());

        iframe.onload = function () {
          // Give the page a moment to run scripts that may set cookies
          setTimeout(function () {
            try {
              var doc = iframe.contentDocument || (iframe.contentWindow && iframe.contentWindow.document);
              var cookieStr = doc ? doc.cookie : '';
              var host = '';
              try {
                host = (iframe.contentWindow && iframe.contentWindow.location && iframe.contentWindow.location.hostname) ? iframe.contentWindow.location.hostname : '';
              } catch (e) {
                host = '';
              }
              resolve(parseDocumentCookieString(cookieStr, host || window.location.hostname));
            } catch (e) {
              resolve([]);
            } finally {
              try {
                iframe.remove();
              } catch (e) {}
            }
          }, 1500);
        };

        iframe.src = src;
        document.body.appendChild(iframe);
      } catch (e) {
        resolve([]);
      }
    });
  }

  function parseAjaxResponse(payload) {
    if (!payload) {
      return { success: false, message: 'Empty response', data: [] };
    }

    if (typeof payload === 'string') {
      try {
        payload = JSON.parse(payload);
      } catch (e) {
        return { success: false, message: 'Invalid JSON response', data: [] };
      }
    }

    if (typeof payload !== 'object') {
      return { success: false, message: 'Unexpected response type', data: [] };
    }

    if (Object.prototype.hasOwnProperty.call(payload, 'success')) {
      return {
        success: !!payload.success,
        message: payload.message || '',
        data: Array.isArray(payload.data) ? payload.data : (payload.data ? [payload.data] : []),
      };
    }

    return {
      success: true,
      message: '',
      data: Array.isArray(payload) ? payload : [payload],
    };
  }

  function findCookiePrefsSubform() {
    var selector = [
      'input[name^="jform[params][cookie_prefs]"]',
      'select[name^="jform[params][cookie_prefs]"]',
      'textarea[name^="jform[params][cookie_prefs]"]',
    ].join(',');

    var anyField = document.querySelector(selector);
    if (anyField && anyField.closest) {
      var subform = anyField.closest('joomla-field-subform');
      if (subform) {
        return subform;
      }
    }

    return document.querySelector('joomla-field-subform[name*="[cookie_prefs]"]');
  }

  function getRows(subform) {
    if (subform && typeof subform.getRows === 'function') {
      return subform.getRows();
    }

    return [];
  }

  function getOrCreateGroupRow(cookiePrefsSubform, category) {
    var rows = getRows(cookiePrefsSubform);

    for (var i = 0; i < rows.length; i++) {
      var row = rows[i];
      var categoryField = row.querySelector('select[name$="[category]"]');
      if (categoryField && categoryField.value === category) {
        return row;
      }
    }

    if (typeof cookiePrefsSubform.addRow !== 'function') {
      return null;
    }

    var newRow = cookiePrefsSubform.addRow();
    if (!newRow) {
      return null;
    }

    var newCategoryField = newRow.querySelector('select[name$="[category]"]');
    if (newCategoryField) {
      newCategoryField.value = category;
      newCategoryField.dispatchEvent(new Event('change', { bubbles: true }));
    }

    return newRow;
  }

  function findPreferencesSubform(groupRow) {
    var candidates = groupRow.querySelectorAll('joomla-field-subform');
    for (var i = 0; i < candidates.length; i++) {
      var name = candidates[i].getAttribute('name') || '';
      if (name.indexOf('[preferences]') !== -1) {
        return candidates[i];
      }
    }

    return null;
  }

  function ensureDefaultGroups(cookiePrefsSubform) {
    var categories = ['necessary', 'analytics', 'marketing', 'unassigned'];
    categories.forEach(function (category) {
      getOrCreateGroupRow(cookiePrefsSubform, category);
    });
  }

  function getExistingCookieNames(groupRow) {
    var names = new Set();

    var nameInputs = groupRow.querySelectorAll('input[name$="[name]"]');
    for (var i = 0; i < nameInputs.length; i++) {
      var value = (nameInputs[i].value || '').trim();
      if (value) {
        names.add(value);
        var normalised = normaliseNameForConfig(value);
        if (normalised) {
          names.add(normalised);
        }
      }
    }

    return names;
  }

  function getExistingCookieNamesGlobal(cookiePrefsSubform) {
    var names = new Set();
    if (!cookiePrefsSubform) {
      return names;
    }

    var inputs = cookiePrefsSubform.querySelectorAll('input[name$="[name]"]');
    for (var i = 0; i < inputs.length; i++) {
      var value = (inputs[i].value || '').trim();
      if (value) {
        names.add(value);
        var normalised = normaliseNameForConfig(value);
        if (normalised) {
          names.add(normalised);
        }
      }
    }

    return names;
  }

  function setRadioYes(row, fieldNameSuffix) {
    var yes = row.querySelector('input[type="radio"][name$="[' + fieldNameSuffix + ']"][value="1"]');
    if (yes) {
      yes.checked = true;
      yes.dispatchEvent(new Event('change', { bubbles: true }));
    }
  }

  function fillInventoryIntoForm(cookies) {
    var cookiePrefsSubform = findCookiePrefsSubform();
    if (!cookiePrefsSubform) {
      throw new Error('No se ha encontrado el subform cookie_prefs');
    }

    // Always create the 4 default groups first
    ensureDefaultGroups(cookiePrefsSubform);

    var existingGlobal = getExistingCookieNamesGlobal(cookiePrefsSubform);

    var grouped = {
      necessary: [],
      analytics: [],
      marketing: [],
      unassigned: [],
    };

    (cookies || []).forEach(function (c) {
      if (!c || !c.name) {
        return;
      }

      var category = (c.category || 'unassigned').toLowerCase();
      if (!grouped[category]) {
        category = 'unassigned';
      }

      grouped[category].push(c);
    });

    Object.keys(grouped).forEach(function (category) {
      var groupRow = getOrCreateGroupRow(cookiePrefsSubform, category);
      if (!groupRow) {
        return;
      }

      var existingNames = getExistingCookieNames(groupRow);
      var preferencesSubform = findPreferencesSubform(groupRow);
      if (!preferencesSubform || typeof preferencesSubform.addRow !== 'function') {
        return;
      }

      if (!grouped[category].length) {
        return;
      }

      grouped[category].forEach(function (cookie) {
        var name = normaliseNameForConfig(cookie.name);
        if (!name || existingNames.has(name) || existingGlobal.has(name)) {
          return;
        }

        var newRow = preferencesSubform.addRow();
        if (!newRow) {
          return;
        }

        var nameInput = newRow.querySelector('input[name$="[name]"]');
        if (nameInput) {
          nameInput.value = name;
          nameInput.dispatchEvent(new Event('input', { bubbles: true }));
          nameInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        var providerInput = newRow.querySelector('input[name$="[provider]"]');
        if (providerInput && !providerInput.value) {
          providerInput.value = (cookie.provider || '').trim();
          providerInput.dispatchEvent(new Event('input', { bubbles: true }));
          providerInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        var descInput = newRow.querySelector('input[name$="[description]"]');
        if (descInput && !descInput.value) {
          descInput.value = (cookie.description || '').trim();
          descInput.dispatchEvent(new Event('input', { bubbles: true }));
          descInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        setRadioYes(newRow, 'show');

        existingNames.add(name);
        existingGlobal.add(name);
      });
    });

    cookiePrefsSubform.dispatchEvent(new CustomEvent('joomla:updated', { bubbles: true }));
  }

  async function runDetectAndFill(url, button) {
    if (!url) {
      throw new Error('URL de detección no disponible');
    }

    if (button) {
      button.disabled = true;
    }

    try {
      var siteRoot = button ? (button.getAttribute('data-site-root') || '') : '';

      // 1) HTTP scan (Set-Cookie)

      var response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      var text = await response.text();
      var parsed = parseAjaxResponse(text);

      if (!parsed.success) {
        throw new Error(parsed.message || 'Error detectando cookies');
      }

      var httpCookies = parsed.data || [];

      // 2) Server-side detect (reads $_COOKIE, includes HttpOnly if present)
      var detectUrl = url;
      if (detectUrl.indexOf('mode=scan') !== -1) {
        detectUrl = detectUrl.replace('mode=scan', 'mode=detect');
      } else {
        detectUrl += (detectUrl.indexOf('?') === -1 ? '?' : '&') + 'mode=detect';
      }

      var detectCookies = [];
      try {
        var detectResponse = await fetch(detectUrl, {
          method: 'GET',
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
        });
        var detectText = await detectResponse.text();
        var detectParsed = parseAjaxResponse(detectText);
        if (detectParsed.success) {
          detectCookies = detectParsed.data || [];
        }
      } catch (e) {
        // ignore, we can still proceed with other sources
      }

      // 3) Browser scan (document.cookie) by loading site root
      var langPrefix = button ? (button.getAttribute('data-lang-prefix') || '') : '';
      var browserCookies = await captureCookiesFromSiteRoot(siteRoot, langPrefix);

      var merged = uniqueByName([].concat(httpCookies, detectCookies, browserCookies));
      merged = merged.map(function (c) {
        return applyHints(c);
      }).filter(function (c) {
        if (!c || !c.name) {
          return false;
        }
        if (isIgnoredCookieName(c.name)) {
          return false;
        }
        if (isVolatileSessionCookie(c.name)) {
          return false;
        }
        return true;
      });

      fillInventoryIntoForm(merged);
    } finally {
      if (button) {
        button.disabled = false;
      }
    }
  }

  window.JBCookiesInventory = window.JBCookiesInventory || {};
  window.JBCookiesInventory.detectAndFill = runDetectAndFill;

  document.addEventListener('click', function (event) {
    var btn = event.target.closest('[data-jbcookies-inventory-button]');
    if (!btn) {
      return;
    }

    event.preventDefault();

    var url = btn.getAttribute('data-url') || '';
    runDetectAndFill(url, btn).catch(function (e) {
      try {
        if (window.Joomla && Joomla.renderMessages) {
          Joomla.renderMessages({ error: [e && e.message ? e.message : String(e)] });
          return;
        }
      } catch (ignored) {}

      alert(e && e.message ? e.message : String(e));
    });
  });
})();
