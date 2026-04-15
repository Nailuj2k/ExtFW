# Login Module

Multi-authentication system: password, passwordless (ECDSA), Nostr (NIP-07), Google OAuth, LDAP.

## File Map

| File | Purpose |
|---|---|
| `init.php` | Module init: password strength, `Invitation::ensureTable()`, role helper functions |
| `index.php` | Main router: dispatches to ajax, pdf, run based on OUTPUT |
| `run.php` | HTML output: login/register/profile/changepassword/lostpassword views with tab system |
| `ajax.php` | AJAX operations: all auth flows, invitations, PIN, device linking, magic links |
| `footer.php` | JS validation for registration, invitation tab logic, Nostr register handler, `nostrRegisterFlow()`, `signNostrEventLocal()` |
| `form_register.php` | Registration form: invitation gate, passwordless, traditional, Nostr buttons |
| `form_login.php` | Login form wrapper |
| `form_login_login.php` | Login form fields (email/username, password, Nostr button) |
| `form_lostpassword.php` | Password recovery form |
| `form_changepassword.php` | Change password (logged in) |
| `form_import_nostr.php` | Nostr identity import dialog |
| `passwordless_js.php` | Client-side ECDSA key generation (Web Crypto API) |
| `device_linking.js` | Device linking flow for passwordless |
| `google.php` | Google OAuth client setup |
| `head.php` | Additional head content |
| `style.css` | Module styles |
| `i18n.php` | Translation strings |
| `TABLE_CLI_USER.php` | Scaffold definition for users table |
| `TABLE_CLI_USER_KEYS.php` | Scaffold definition for passwordless keys |
| `TABLE_CLI_USER_TIMESTAMP.php` | Scaffold wrapper for timestamping records |

## Authentication Methods

| Method | Config Key | Classes |
|---|---|---|
| Password | Default | Login |
| Passwordless (ECDSA) | `CFG::$vars['login']['passwordless']['enabled']` | PasswordlessAuth, Login |
| Nostr (NIP-07) | `CFG::$vars['login']['nostr']['enabled']` | NostrAuth |
| Google OAuth | `CFG::$vars['oauth']['google']['enabled']` | Login, google.php |
| LDAP | `CFG::$vars['auth'] == 'ldap'` | AuthLDAP, Login |

## AJAX Operations (ajax.php)

**Password:** `randompassword`
**Passwordless Auth:** `passwordless_challenge`, `passwordless_verify`, `update_user_keys`, `get_current_user_id`, `get_user_has_keys`, `revoke_device`
**Passwordless Registration:** `register_passwordless`
**Device Linking:** `generate_device_link_token`, `verify_device_link_token`
**Magic Link:** `request_magic_link`
**PIN:** `save_pin`, `verify_pin`, `check_user_pin`
**Nostr:** `nostr_challenge`, `nostr_challenge_for_pubkey`, `nostr_verify`, `nostr_link`, `nostr_link_challenge`, `nostr_unlink`
**Invitations:** `validate_invitation`, `generate_invitation` (auth), `get_my_invitations` (auth)
**LDAP:** `sync-ldap`

## Invitation System

Controlled by `CFG::$vars['login']['invitation']['required']`. When enabled:
- Registration page shows an **invitation gate** first (validates code via AJAX before showing forms)
- Single shared input `#shared-invitation-code` at top, hidden field `#invitation_code_hidden` inside form
- Server validates in `Login::register()` and `Login::register_passwordless()` before creating user
- `Invitation::markUsed($code, $newUserId)` called after successful INSERT
- Registration with valid invitation skips email verification (`user_verify = '1'`)
- Nostr registration also checks invitation in `nostr_verify` handler
- Class: `Invitation` (extends DbConnection), table: `CLI_INVITATIONS`
- Cost: 1000 karma per invitation via `Karma::spendPoints()`

## Registration Flows

### Traditional (Password)
1. Form in `form_register.php` → POST to `login` with `op=register`
2. `Login::register()` validates email, password, invitation (if required)
3. Creates user (`user_verify = '0'` normally, `'1'` with invitation)
4. Without invitation: sends verification email. With invitation: auto-verified

### Passwordless (ECDSA)
1. Email input → `btn-register-passwordless` click → `PasswordlessRegistration.register(email)`
2. Client generates ECDSA P-256 keypair (Web Crypto API)
3. POST to `register_passwordless` AJAX with email + keys
4. `Login::register_passwordless()` creates user + registers device keys
5. With invitation: auto-verified, returns `requires_verification: false`

### Nostr
1. Click `#btn-nostr-register` → calls `nostrRegisterFlow(btn)` (global async function)
2. `window._nostr_invitation_code` set from shared input
3. Shows wquery dialog with 3 registration methods:
   - **Crear nueva identidad** — `generateNostrKeypair()` → `signNostrEventLocal()` → `nostr_verify`
   - **Ya tengo Nostr (importar nsec)** — shows nsec input form → `deriveKeysFromNsec()` → `signNostrEventLocal()` → `nostr_verify`
   - **Usar extensión** (only if NIP-07 detected) — `window.nostr.signEvent()` → `nostr_verify`
4. All paths: challenge → sign → `nostr_verify` AJAX (includes `invitation_code` if set)
5. `NostrAuth::createOrUpdateUser()` creates user (always auto-verified)
6. If new user + invitation required: validates and marks used
7. Keys saved to IndexedDB (`ExtFWNostrKeys`) for Noxtr auto-login
8. Dialog uses `onLoad: function(dialog)` callback — `dialog.overlay` is the DOM element, `dialog.close()` to dismiss

## Profile Tabs (run.php)

Views determined by `$_ARGS[1]`: login, register, lostpassword, changepassword, profile.

Profile tabs (when logged in):
- User data, avatar, Nostr link/unlink
- Passwordless devices management
- Device linking (QR code)
- **Invitations tab** (conditional on `Invitation::isRequired()`) — karma info, generate button, table of codes

Tab system uses `ftab-` prefix: `$('a[href="#ftab-tab_invitations"]')`

## JS Patterns in footer.php

- Uses `$(document).ready()` — wquery, NOT jQuery
- `$.getJSON` / `$.post` do NOT exist — use `fetch()` for all AJAX
- Validation variables: `user_email_ok`, `password_ok`, `invitation_code_ok`, etc.
- `valid_data()` function enables/disables submit button
- Invitation validation: `$('#shared-invitation-code').on('change blur', ...)` → `fetch('login/ajax/op=validate_invitation')`
- Invitation tab JS: `fetch()` for `get_my_invitations` and `generate_invitation`
- Nostr register: `#btn-nostr-register` calls `nostrRegisterFlow(btn)` which shows a wquery dialog with 3 options (new identity, import nsec, use extension). Sets `window._nostr_invitation_code` from shared input. Does NOT trigger `#btn-nostr` (which only exists on the login page, not register)
- `signNostrEventLocal(event, privkeyHex)` — signs Nostr events using noble-secp256k1 v1.7.1 (ESM). Serializes per NIP-01, SHA-256 via Web Crypto, Schnorr sign. Returns `{ ...event, id, sig }`
- `nostrRegisterFlow(btn)` — shows registration options dialog. Uses `onLoad: function(dialog)` where `dialog` is `{ overlay, close(), maximize(), minimize() }`. Access DOM via `$(dialog.overlay).find()`. Close via `dialog.close()` (NOT `removeChild`)
- wquery `$("body").dialog()` supports: `onLoad(dialogInstance)`, `onBeforeLoad(params)`, `onClose()`. The `onLoad` receives `dialogInstance` object, NOT the overlay DOM element directly

## Config Keys

| Key | Purpose |
|---|---|
| `CFG::$vars['login']['passwordless']['enabled']` | Enable passwordless auth |
| `CFG::$vars['login']['nostr']['enabled']` | Enable Nostr auth |
| `CFG::$vars['login']['invitation']['required']` | Require invitation to register |
| `CFG::$vars['login']['username']['required']` | Username field in register |
| `CFG::$vars['login']['card_id']['required']` | Card/ID field in register |
| `CFG::$vars['login']['register_code']['required']` | Registration code field |
| `CFG::$vars['login']['password']['strength']` | Password strength (0-3) |
| `CFG::$vars['login']['default_method']` | 'passwordless' or default |
| `CFG::$vars['auth']` | 'ldap', 'demo', or default |
| `CFG::$vars['oauth']['google']['enabled']` | Google OAuth |
| `CFG::$vars['captcha']['enabled']` | reCAPTCHA |

## Database Tables

- **CLI_USER** — main users table (user_id, username, user_password, user_email, user_fullname, user_level, user_verify, nostr_pubkey, balance_sats, PIN, etc.)
- **CLI_USER_KEYS** — passwordless device keys (id_user, device_id, sign_public_key, enc_public_key, ACTIVE)
- **CLI_AUTH_CHALLENGES** — passwordless challenges (user_id, challenge, expires_at, used)
- **CLI_INVITATIONS** — invitation codes (code, user_id_from, user_id_to, used, created_at, used_at)

## Rules

- DB access via `Login::sqlQueryPrepared()` or `Table::sqlQueryPrepared()`
- JS using `$()` goes in `footer.php`, vanilla JS can go in `form_register.php`
- Use `fetch()` for AJAX, never `$.getJSON` or `$.post`
- `Invitation::isRequired()` is the single toggle for the entire invitation feature
