(function () {
	document.addEventListener('DOMContentLoaded', function () {
		var cfg = window.JBCOOKIES_CONFIG || {};
		var allowBrowserCapture = cfg.allowBrowserCapture !== false;
		var domain = cfg.domain || '';
		var duration = parseInt(cfg.duration || 90, 10);
		var inventory = cfg.inventory || {};
		var ajax = cfg.ajax || {};

		var banner = new bootstrap.Modal('.jb-cookie', {});
		var decline = jQuery('.jb-cookie-decline');
		var consent = readConsent();

		applyPreferences(consent.preferences || {});
		handleInitialState(consent.status);
		wireButtons();
		wireModalFocus();

		if (allowBrowserCapture) {
			sendInventoryUpdate();
		}

		function handleInitialState(status) {
			if (!status) {
				setTimeout(function () {
					banner.show();
				}, 500);
				return;
			}

			showDeclinePanel();
		}

		function wireButtons() {
			jQuery(document).on('click', '.jb-accept, .jb-accept-all', function () {
				setAllToggles(true);
				persistConsent('allow');
				hidePreferencesModal();
				banner.hide();
				showDeclinePanel();
			});

			jQuery(document).on('click', '.jb-deny, .jb-reject-all', function () {
				setAllToggles(false);
				persistConsent('deny');
				clearOptionalCookies();
				hidePreferencesModal();
				banner.hide();
				showDeclinePanel();
			});

			jQuery(document).on('click', '.jb-save-selection', function () {
				persistConsent('custom');
				hidePreferencesModal();
				banner.hide();
				showDeclinePanel();
			});

			jQuery('.jb-cookie-decline').on('click', function () {
				eraseCookie('jbcookies');
				decline.fadeOut('slow', function () {
					banner.show();
				});
			});
		}

		function setAllToggles(state) {
			document.querySelectorAll('.jb-cookie-toggle').forEach(function (input) {
				if (!input.disabled) {
					input.checked = state;
				}
			});
		}

		function collectPreferences() {
			var prefs = {};
			document.querySelectorAll('.jb-cookie-toggle').forEach(function (input) {
				var key = input.getAttribute('data-group');
				prefs[key] = input.checked ? 1 : 0;
			});
			return prefs;
		}

		function readConsent() {
			var raw = getCookieValue('jbcookies');
			if (!raw) {
				return { status: '', preferences: {} };
			}

			try {
				var parsed = JSON.parse(raw);
				if (parsed && typeof parsed === 'object' && typeof parsed.status === 'string') {
					return {
						status: parsed.status,
						preferences: parsed.preferences && typeof parsed.preferences === 'object' ? parsed.preferences : {}
					};
				}
			} catch (e) {
				// ignore legacy non-JSON
			}

			if (raw === 'allow' || raw === 'deny' || raw === 'custom') {
				return { status: raw, preferences: {} };
			}

			return { status: '', preferences: {} };
		}

		function applyPreferences(prefs) {
			document.querySelectorAll('.jb-cookie-toggle').forEach(function (input) {
				var key = input.getAttribute('data-group');
				if (Object.prototype.hasOwnProperty.call(prefs, key) && !input.disabled) {
					input.checked = !!prefs[key];
				}
			});
		}

		function persistConsentCombined(status, prefs) {
			var payload = JSON.stringify({ status: status, preferences: prefs || {} });
			setCookieValue('jbcookies', payload);
			dispatchConsentEvent(status, prefs || {});
		}

		function persistConsent(status) {
			persistConsentCombined(status, collectPreferences());
		}

		function dispatchConsentEvent(status, prefs) {
			if (typeof window.CustomEvent === 'function') {
				var ev = new CustomEvent('jbcookies:update', { detail: { status: status, preferences: prefs } });
				document.dispatchEvent(ev);
			}
		}

		function showDeclinePanel() {
			decline.fadeIn('slow');
		}

		function setCookieValue(name, value, daysOverride) {
			var days = typeof daysOverride === 'number' ? daysOverride : duration;
			var expires = new Date();
			expires.setDate(expires.getDate() + days);
			var cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires.toUTCString() + '; path=/';
			if (domain) {
				cookie += '; domain=' + domain;
			}
			document.cookie = cookie;
		}

		function eraseCookie(name) {
			setCookieValue(name, '', -365);
		}

		function getCookieValue(name) {
			if (!name) {
				return '';
			}

			var escaped = name.replace(/[-\[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
			var pattern = new RegExp('(?:^|;\\s*)' + escaped + '\\s*=\\s*([^;]+)');
			var match = document.cookie.match(pattern);
			return match ? decodeURIComponent(match[1]) : '';
		}

		function clearOptionalCookies() {
			Object.keys(inventory).forEach(function (group) {
				if (String(group || '').toLowerCase() === 'necessary') {
					return;
				}

				var cookies = inventory[group] || [];

				cookies.forEach(function (item) {
					if (!item || !item.name) {
						return;
					}

					var nameLower = String(item.name).toLowerCase();
					if (nameLower.indexOf('jbcookies') === 0) {
						return;
					}

					// Support simple wildcard patterns, e.g. "_ga_*"
					if (item.name.slice(-1) === '*') {
						var prefix = String(item.name.slice(0, -1)).toLowerCase();
						if (!prefix || !document.cookie) {
							return;
						}

						document.cookie.split(';').forEach(function (entry) {
							var cookieName = entry.split('=')[0].trim();
							if (!cookieName) {
								return;
							}

							var cookieLower = cookieName.toLowerCase();
							if (cookieLower.indexOf('jbcookies') === 0) {
								return;
							}

							if (cookieLower.indexOf(prefix) === 0) {
								eraseCookie(cookieName);
							}
						});

						return;
					}

					eraseCookie(item.name);
				});
			});
		}

		function hidePreferencesModal() {
			var modalEl = document.getElementById('jbcookies-preferences');
			if (!modalEl) {
				return;
			}

			if (window.bootstrap && window.bootstrap.Modal) {
				window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
				return;
			}

			if (typeof jQuery !== 'undefined' && typeof jQuery(modalEl).modal === 'function') {
				jQuery(modalEl).modal('hide');
			}
		}

		function wireModalFocus() {
			var prefModal = document.getElementById('jbcookies-preferences');
			var mainModal = document.querySelector('.jb-cookie');

			if (prefModal) {
				prefModal.addEventListener('shown.bs.modal', function () {
					prefModal.removeAttribute('aria-hidden');
					prefModal.focus({ preventScroll: true });
				});

				prefModal.addEventListener('hidden.bs.modal', function () {
					if (document.activeElement && prefModal.contains(document.activeElement)) {
						document.activeElement.blur();
					}
				});
			}

			if (mainModal) {
				mainModal.addEventListener('shown.bs.modal', function () {
					mainModal.removeAttribute('aria-hidden');
					mainModal.focus({ preventScroll: true });
				});

				mainModal.addEventListener('hidden.bs.modal', function () {
					if (document.activeElement && mainModal.contains(document.activeElement)) {
						document.activeElement.blur();
					}
				});
			}
		}

		function readBrowserCookies() {
			if (!document.cookie) {
				return [];
			}

			return document.cookie.split(';').map(function (entry) {
				var name = entry.split('=')[0].trim();

				if (!name || name.toLowerCase().indexOf('jbcookies') === 0) {
					return null;
				}

				return {
					name: name,
					source: window.location.hostname,
					category: 'unassigned'
				};
			}).filter(function (item) {
				return item !== null;
			});
		}

		function sendInventoryUpdate() {
			if (!allowBrowserCapture || isAdministratorContext()) {
				return;
			}

			if (!ajax.url || !ajax.token) {
				return;
			}

			var cookies = readBrowserCookies();
			if (!cookies.length) {
				return;
			}

			var payload = new FormData();
			payload.append(ajax.token, '1');
			payload.append('mode', 'register');
			payload.append('cookies', JSON.stringify(cookies));

			try {
				fetch(ajax.url, {
					method: 'POST',
					body: payload,
					credentials: 'same-origin'
				});
			} catch (e) {
				// ignore fetch errors
			}
		}

		function isAdministratorContext() {
			var path = (window.location && window.location.pathname) ? window.location.pathname.toLowerCase() : '';
			return path.indexOf('/administrator') === 0;
		}
	});
})();