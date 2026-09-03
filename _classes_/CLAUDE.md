# _classes_ Directory

All classes use classmap autoloader (`_includes_/autoloader.php`). Most extend `DbConnection`.

## Database Abstraction

### DbConnection Pattern
```php
// Selected at boot based on CFG::$vars['db']['type']
class DbConnection { use MysqlConnection; }  // or SQLiteConnection
```

### Available Methods (inherited by all classes extending DbConnection)
- `sqlQuery($sql)` — SELECT queries, returns array of rows
- `sqlExec($sql)` — INSERT/UPDATE/DELETE, returns affected rows
- `sqlQueryPrepared($sql, $params)` — parameterized queries (PREFERRED)
- `beginTransaction()`, `commit()`, `rollBack()` — transactions
- `lastInsertId()` — last auto-increment ID

### Dual-DB Pattern
```php
if (self::isSQLite()) {
    // SQLite syntax (INSERT OR IGNORE, ON CONFLICT, INTEGER PRIMARY KEY AUTOINCREMENT)
} else {
    // MySQL syntax (INSERT IGNORE, ON DUPLICATE KEY UPDATE, INT AUTO_INCREMENT)
}
```

## Core Classes

| Class | File | Purpose |
|---|---|---|
| **APP** | `app.class.php` | App singleton: args, shortcodes, plugins |
| **CFG** | `config.class.php` | Config singleton: `CFG::$vars`, `CFG::get()`, `CFG::set()` |
| **Login** | `login.class.php` | Auth: login, register, register_passwordless, lostpassword, changepassword |
| **ACL** | `acl.class.php` | RBAC: roles, permissions, user/item roles. Tables: ACL_ROLES, ACL_PERMISSIONS, ACL_USER_ROLES, etc. |
| **LOG** | `log.class.php` | Event logging to LOG_EVENTS table |
| **Mailer** | `mail.class.php` | Email sending |

## Auth Classes

| Class | File | Purpose |
|---|---|---|
| **PasswordlessAuth** | `passwordless.class.php` | ECDSA P-256 auth: challenges, signature verification. Tables: CLI_USER_KEYS, CLI_AUTH_CHALLENGES |
| **NostrAuth** | `nostrauth.class.php` | Nostr NIP-07 auth: BIP-340 Schnorr verification, user creation by pubkey. Uses CLI_USER.nostr_pubkey. **Critical**: `verifySchnorr()` pads GMP hex values to 64 chars before `hex2bin()` — `gmp_strval($r, 16)` can produce odd-length hex causing `hex2bin()` to fail silently |
| **AuthLDAP** | `auth.ldap.class.php` | LDAP bind authentication |
| **AuthDEMO** | `demo.class.php` | Demo mode auth |

## Content/Social Classes

| Class | File | Purpose |
|---|---|---|
| **Karma** | `karma.class.php` | Reputation points. Constants: POST_CREATED=10, COMMENT_POSTED=5, DAILY_LOGIN=1. Methods: `addPoints()`, `spendPoints()`, `canSpend()`, `getUserScore()` |
| **Invitation** | `invitation.class.php` | Invite codes. KARMA_COST=1000. Table: CLI_INVITATIONS. Methods: `create()`, `validate()`, `markUsed()`, `isRequired()`, `ensureTable()` |
| **Comments** | `comments.class.php` | Nested comments with voting. Tables: POST_COMMENTS, POST_VOTES, POST_RATINGS |
| **Rating** | `rating.class.php` | Star rating system |
| **Shortcodes** | `shortcodes.class.php` | `[shortcode]` processing |
| **Hook** | `hooks.class.php` | Event hooks system |
| **Plugins** | `plugins.class.php` | Plugin loading |

## Utility Classes

| Class | File | Purpose |
|---|---|---|
| **Str** | `str.class.php` | String utilities: `password()`, `valid_email()`, `is_valid_username()`, `removeStopWords()` |
| **XSS / XssHtml** | `xss.class.php` | HTML sanitization (whitelist tags/attrs) |
| **Crypt** | `crypt.class.php` | Encrypt/decrypt data |
| **CryptoLib** | `crypto.class.php` | Cryptographic utilities |
| **i18n** | `i18n.class.php` | Translation: `write_lang_file($lang)` generates `_i18n_/{lang}.php` |
| **Vars** | `vars.class.php` | Session/request variable helpers: `setDefaultSessionVar()` |
| **SYS** | `sys.class.php` | System utilities |
| **RateLimiter** | `ratelimiter.class.php` | Token bucket rate limiting |
| **Captcha** | `captcha.class.php` | CAPTCHA generation/validation |
| **Breadcrumb** | `breadcrumb.class.php` | Breadcrumb navigation |
| **Menu** | `menu.class.php` | Menu rendering |
| **Messages** | `messages.class.php` | Flash messages (info/warning/error) |
| **Banner** | `banner.class.php` | Banner management |
| **Browser** | `browser.class.php` | Browser detection |
| **MarkdownParser** | `markdown.class.php` | Markdown to HTML |
| **MyCache** | `cache.class.php` | Query caching |
| **MemVar** | `memvar.class.php` | Memory variables |
| **Persistent** | `persistent.class.php` | Persistent storage |
| **Template** | `template.class.php` | Template engine |
| **HTML** | `html.class.php` | HTML utilities |
| **HtmlToText** | `html2text.class.php` | HTML to plain text |
| **Sitemap** | `sitemap.class.php` | XML sitemap generation |
| **PDF** | `pdf.class.php` | PDF generation |
| **Encoding / FixUtf8** | `encoding.class.php` | Character encoding fixes |
| **Errors** | `errors.class.php` | Error handling |
| **SecurityValidator** | `security.validator.class.php` | Input validation |
| **Redis** | `redis.class.php` | Redis client |
| **Inflect** | `inflect.class.php` | Word inflection |
| **NgramComparator** | `ngram.class.php` | Text similarity |

## Scaffold (CRUD)

| Class | File | Purpose |
|---|---|---|
| **Table / JS** | `scaffold/table.class.php` | Base CRUD table with markup templates |
| **TableMysql** | `scaffold/table.mysql.class.php` | MySQL-specific table operations |
| **TableSqlite** | `scaffold/table.sqlite.class.php` | SQLite-specific table operations |
| **Field** | `scaffold/field.class.php` | Column definition (type, length, editable, searchable, etc.) |
| **FORM** | `scaffold/form.class.php` | Form generation + all form elements (formInput, formSelect, formCheckbox, etc.) |
| **defaultTableEvents** | `scaffold/table.events.class.php` | CRUD event hooks |

## Storage

| Class | File | Purpose |
|---|---|---|
| **StorageInterface** | `storage/storage.interface.php` | Storage contract |
| **MySQLStorage** | `storage/mysql.storage.class.php` | MySQL-backed storage |
| **SQLiteStorage** | `storage/sqlite.storage.class.php` | SQLite-backed storage |
| **RedisStorage** | `storage/redis.storage.class.php` | Redis-backed storage |
| **SessionStorage** | `storage/session.storage.class.php` | Session-backed storage |

## Database Connection Files

| File | Purpose |
|---|---|
| `db/db.mysql.class.php` | MySql_PDO singleton |
| `db/db.sqlite.class.php` | SQLite_PDO singleton |
| `db/db.oracle.class.php` | Oracle PDO |
| `db/connection.mysql.trait.php` | MysqlConnection trait |
| `db/connection.sqlite.trait.php` | SQLiteConnection trait |
| `db/connection.demo.trait.php` | Demo connection trait |
