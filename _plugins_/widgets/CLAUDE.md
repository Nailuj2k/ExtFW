# Widgets plugin — shortcodes Lightning

Plugin de shortcodes ExtFW. Cada shortcode se registra via `APP::$shortcodes->add_shortcode(...)` en [main.php](main.php).

Este documento cubre los dos shortcodes Lightning: `[zap]` y `[btcpay]`.

> **Estado**: codigo implementado segun la especificacion descrita. Pendiente de pruebas end-to-end del flujo completo (cobro real + confeti). Confirmado: `<canvas id="canvas">` ya esta inyectado globalmente por [_includes_/footer.php](../../_includes_/footer.php), asi que `bum()` puede dispararse sin añadir nada extra a la pagina.

---

## I18N (2026-06-08)

Textos de `[zap]` y `[btcpay]` migrados a `t('KEY','es','en')`. Reglas aprendidas (aplican a cualquier shortcode con heredoc):

- **Dentro de un heredoc `<<<HTML ... HTML;`** se interpolan **variables** (`$x`, `{$x}`, `{$obj->m()}`) pero **NO** se ejecutan ni `<?= ... ?>` ni llamadas a función. Había un bug real: dos `<?= t(...) ?>` dentro del heredoc `$widget` salían como **texto literal** en el modal. Fix: precalcular `$tAmountSats = t(...)` antes del heredoc y usar `{$tAmountSats}`.
- **Strings que caen en contexto JavaScript** (dentro de los strings JS del `$assets`): se inyectan vía `$zapI18nJs = json_encode([... t(...) ...], JSON_UNESCAPED_UNICODE)` y en el heredoc `var I18N = {$zapI18nJs};`. `json_encode` produce literales JS seguros (comillas/acentos), así que un apóstrofo en una traducción no rompe el JS. Luego se usa `I18N.invalidAmount`, `I18N.generating`, etc.
- El botón "Copiar" pasó a guardar el texto "Copiado" en `data-copied` (en vez del antiguo `&quot;Copiado&quot;` inline) para evitar líos de comillas.
- `[btcpay]`: el `title` del dialog se traduce e inyecta con `addslashes()` (va dentro de un string JS con comillas simples en el `onclick`).
- Emojis (⚡, ✅) **fuera** del string traducible, concatenados en JS/PHP.
- Texto por defecto del botón: `'⚡ ' . t('DONATE','Donar','Donate')`.

Keys añadidas: `ZAP_*` (invalid_amount, generating, invalid_lnaddr, no_callback, no_invoice, min, max, open_wallet, copy, copied, waiting_payment, payment_received, bad_shortcode_lnaddr), `AMOUNT_SATS`, `GENERATE_INVOICE`, `BTCPAY_DIALOG_TITLE`, `DONATE`.

---

## `[zap]` — Boton de propina con LN address

Genera un boton que abre un popup, pide importe en sats, descarga una bolt11 del LN address indicado (LUD-16 estandar) y muestra QR + acciones. Si el endpoint LNURLp es el de noxtr (`raw.php` de este proyecto), tambien hace polling para detectar el cobro y dispara confeti via `bum()`.

### Sintaxis

```
[zap lnaddress="usuario@dominio.tld"]
[zap lnaddress="usuario@dominio.tld" text="invitame a cerveza"]
[zap lnaddress="usuario@dominio.tld" label="🍺 cerveza"]
```

### Atributos

| Atributo | Obligatorio | Default | Descripcion |
|---|---|---|---|
| `lnaddress` | si | — | LN address LUD-16 valida (regex `^[^@]+@[^@]+\.[^@]+$`) |
| `text` | no | `⚡ Donar` | Texto del boton. Alias: `label` |

### Funcionamiento

1. **Click** → abre modal con input "Importe en sats" + presets (210 / 1k / 5k / 21k) + boton "Generar factura".
2. **Generar factura** (JS):
   - Discovery: `GET https://<dominio>/.well-known/lnurlp/<usuario>`
   - Lee `callback` y limites (`minSendable` / `maxSendable`).
   - Callback: `GET <callback>?amount=<sats * 1000>`
   - Recibe `{pr: "lnbc...", routes: [], _paymentStatusUrl?: "..."}`.
3. **Renderiza** QR (`lightning:LNBC...` en mayusculas, LUD-17), bolt11 en texto, botones "Abrir wallet" (link `lightning:`) y "Copiar".
4. **Polling de pago** (solo si la respuesta incluye `_paymentStatusUrl`):
   - Cada 2.5 segundos hace `GET _paymentStatusUrl` esperando `{paid: true}`.
   - Limite: 120 intentos (~5 min) o hasta cerrar el modal.
   - Al detectar pago: reemplaza QR por mensaje de exito y llama `window.bum()` para confeti.

### Dependencias en pagina

- **`_lib_/qrcode/qrcode.min.js`** — se inyecta dinamicamente con `document.write` si `QRCode` no existe. Lib local de ExtFW (~20KB).
- **`bum()` global** — definida en `_js_/bum/bum.js`. Requiere `<canvas id="canvas">` en la pagina. Ya esta presente globalmente via [_includes_/footer.php](../../_includes_/footer.php), no hay que añadir nada por shortcode. Si por algun motivo no estuviera, el polling sigue funcionando pero no hay confeti (no es error — el shortcode hace `if (typeof window.bum === 'function')`).
- Sin jQuery / sin wquery. Modal y CSS son self-contained.

### Compatibilidad con LN addresses externos

Funciona con cualquier LN address (LUD-16 estandar):

| LN address | QR generado | Polling / confeti |
|---|---|---|
| `erwin@noxtr.net` (LNURLp propio) | si | **si** — raw.php devuelve `_paymentStatusUrl` |
| `usuario@walletofsatoshi.com` | si | no — no hay endpoint de status |
| `usuario@coinos.io`, `usuario@getalby.com`, etc. | si | no |

El polling solo funciona contra **el propio LNURLp del proyecto** porque depende de la accion `invoice_status` añadida en [_modules_/noxtr/raw.php](../../_modules_/noxtr/raw.php).

### Multi-instancia

- Cada uso del shortcode genera un `instanceId` unico (hash sha256 + microtime + random_int).
- Los assets globales (CSS / JS / lib QR) se inyectan **una sola vez por pagina** con `static $assetsLoaded`.
- Cada modal tiene IDs `zm-<id>`, `za-<id>`, `zr-<id>`, `zg-<id>`, `zq-<id>` para no colisionar.

---

## `[btcpay]` — Dialog wquery con iframe al checkout de BTCPayServer

Genera un boton que abre `$("body").dialog({type:'iframe', content: <url_wrapper>})` (de [_js_/wquery/wquery.dialog.js](../../_js_/wquery/wquery.dialog.js)). El wrapper server-side (`/noxtr/raw?action=btcpay_pay&...`) crea la invoice con el `api_key` que ya tenemos en `CFG_CFG` y responde con `302` al checkout de BTCPay — el iframe del dialog sigue la redireccion y muestra el checkout dentro.

Es el mismo patron que el boton "Dar propina" de [_classes_/comments.class.php:1551](../../_classes_/comments.class.php#L1551). Uniforme con el resto del framework, responsive out-of-the-box.

### Sintaxis

```
[btcpay text="Donar 1€" amount="1" currency="EUR"]
[btcpay text="5€ pizza" amount="5" currency="EUR" method="BTC-LightningNetwork"]
[btcpay text="Lo que quieras"]
[btcpay text="Volver al sitio" amount="3" redirect="https://noxtr.net/gracias"]
```

### Atributos

| Atributo | Obligatorio | Default | Descripcion |
|---|---|---|---|
| `text` | no | `⚡ Donar` | Texto del boton. Alias: `label` |
| `amount` | no | — (importe libre, lo elige el donante en BTCPay) | Importe en `currency`. Coma o punto decimal |
| `currency` | no | `EUR` | Codigo ISO 4217. Solo letras |
| `method` | no | — (elige el donante) | `BTC-LightningNetwork`, `BTC-CHAIN`, etc. |
| `redirect` | no | — | URL a la que volver tras pago (debe ser URL valida) |

> No hay `url` ni `storeid` como atributos: ambos se leen siempre server-side de `CFG_CFG` para evitar exponer el `storeId` en el HTML del cliente.

### Funcionamiento

1. Click en el boton → onclick dispara `$("body").dialog({type:'iframe', content:'/noxtr/raw?action=btcpay_pay&amount=1&currency=EUR&...'})`.
2. wquery.dialog crea el overlay + dialog + iframe automaticamente. Cierre con `$.dialog.closeButton`.
3. El wrapper (en [_modules_/noxtr/raw.php](../../_modules_/noxtr/raw.php), `case 'btcpay_pay'`):
   - Lee `btcpay.url`, `btcpay.store_id`, `btcpay.api_key` de `CFG_CFG`.
   - Construye payload `{currency, amount, checkout: {defaultPaymentMethod, redirectURL}}`.
   - POST a `<btcpay_url>/api/v1/stores/<storeId>/invoices` autenticado con `api_key`.
   - Recibe `{id: "<invoiceId>", ...}`.
   - `header('Location: <btcpay_url>/i/<invoiceId>')` → el iframe sigue el 302 y carga el checkout dentro del dialog.

Dimensiones por defecto: `500px × 874px` (mismas que el dialog de "Dar propina" de comments).

### Requisito en pagina: wquery cargado

El onclick usa `$().dialog()` de [_js_/wquery/wquery.dialog.js](../../_js_/wquery/wquery.dialog.js). En este proyecto wquery se carga globalmente desde el theme. Si en algun caso particular no lo estuviese, hay que añadir manualmente:

```php
HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.min.js');
HTML::js(SCRIPT_DIR_JS . '/wquery/wquery.dialog.js');
HTML::css(SCRIPT_DIR_JS . '/wquery/wquery.dialog.css');
```

### Requisito en BTCPay: iframe permitido

BTCPayServer sirve `/i/*` con headers que **pueden bloquear iframes cross-origin** por defecto:

- `X-Frame-Options: SAMEORIGIN` o `DENY`
- `Content-Security-Policy: frame-ancestors 'self'`

Si el modal sale en blanco al pulsar el boton, es esto. Soluciones:

1. **Configurar BTCPay** para permitir el dominio que mete el iframe. En BTCPay Server Settings → Server Settings → Policies, buscar la opcion de CSP/frame-ancestors y añadir `https://noxtr.net https://tienda.extralab.net` (o el dominio donde aparece el shortcode).
2. **Alternativa de proxy**: hacer que el wrapper `btcpay_pay` no haga 302 al BTCPay externo, sino que reescriba/sirva el HTML del checkout desde tu propio dominio (complejo, mantenimiento alto, no recomendado).
3. **Fallback automatico**: el modal incluye un link "Abrir en pestaña" que abre el checkout en pestaña nueva por si el iframe falla.

### Por que no usar un `<form>` POST directo al endpoint publico de BTCPay

La version anterior usaba el "Pay Button" estandar de BTCPay: `<form action="<btcpay>/api/v1/invoices" target="_blank">`. Se descarto porque:

- **Requeria activar "Allow anyone to create invoice"** en Store Settings de BTCPay (POST publico sin auth).
- **Necesitaba `class="no-ajax"`** en el form para esquivar el interceptor de [_js_/wquery/wquery.form.js:200](../../_js_/wquery/wquery.form.js#L200), que intercepta cualquier `<form>` no marcado con `no-ajax`, lo manda por XHR y muestra "Error en la respuesta del servidor" cuando BTCPay no responde JSON.
- **Exponia el `storeId`** en HTML cliente (no es secreto pero limpia mas no exponerlo).
- **Mas HTML** (form + inputs hidden) que un simple `<a>`.

Con el wrapper actual: todo eso desaparece. La autenticacion va por `api_key` server-side, el `storeId` no aparece en cliente, no hay form que interceptar, y la URL es un GET normal compatible con "abrir en pestaña nueva" via click derecho.

### Diferencias con `[zap]`

| Caracteristica | `[zap]` | `[btcpay]` |
|---|---|---|
| Donante sale del sitio | no | si (nueva pestaña a BTCPay) |
| Importe en | sats | fiat (EUR, USD, …) con conversion |
| Metodos | solo Lightning | Lightning + on-chain |
| Pagina de confirmacion / recibo | no | si (la de BTCPay) |
| Webhook de cobro | no implementado | si (BTCPay) |
| Confeti via `bum()` | si | no |
| Funciona con LN address ajena | si | no (solo tu BTCPay) |

---

## Backend dependencias — `_modules_/noxtr/raw.php`

Ambos shortcodes se apoyan en endpoints del modulo noxtr:

### 1. LNURLp (LUD-16)

`GET /.well-known/lnurlp/<usuario>` y `GET /.well-known/lnurlp/<usuario>?amount=<msat>` ya existian.

Modificacion: la respuesta del callback ahora incluye `_paymentStatusUrl` con la URL para hacer polling. Campo no estandar — wallets LUD-16 lo ignoran.

### 2. Invoice status (polling)

Endpoint añadido para `[zap]`:

```
GET /noxtr/raw?action=invoice_status&id=<btcpay_invoice_id>
```

Respuesta: `{paid: true|false, status: "settled"|"new"|"processing"|"expired"|"invalid"|"unknown"}`.

Implementacion: consulta a `GET <btcpay_url>/api/v1/stores/<storeId>/invoices/<id>` con el `api_key` de `CFG_CFG`. Devuelve `paid: true` si el estado BTCPay es `Settled`, `Processing` o `Complete`.

CORS: `Access-Control-Allow-Origin: *` (mismo que el LNURLp). Permite que el JS de `[zap]` haga polling desde otro dominio (ej. tienda.extralab.net usando erwin@noxtr.net).

### 3. BTCPay direct pay (wrapper para `[btcpay]`)

Endpoint añadido para el shortcode `[btcpay]`:

```
GET /noxtr/raw?action=btcpay_pay&amount=<N>&currency=<CCY>&method=<m>&redirect=<url>
```

- Lee `btcpay.url`, `btcpay.store_id`, `btcpay.api_key` de `CFG_CFG`.
- POST a `<btcpay_url>/api/v1/stores/<storeId>/invoices` con `{amount, currency, checkout: {defaultPaymentMethod, redirectURL}}`.
- Responde con `302 Location: <btcpay_url>/i/<invoiceId>` → el navegador termina en el checkout.

Validacion server-side: `amount` solo digitos/coma/punto, `currency` solo letras, `method` alfanumerico+guion, `redirect` debe pasar `FILTER_VALIDATE_URL`.

Si falta cualquier credencial BTCPay → HTTP 500 JSON `{error: "payment service not configured"}`. Si BTCPay rechaza la creacion → HTTP 502 JSON con `btcpay_response`.

---

## Estilos

| Clase | Donde | Para que |
|---|---|---|
| `.zap-btn` | `[zap]` | Boton naranja Bitcoin |
| `.zap-modal-bg` / `.zap-modal` | `[zap]` | Overlay + caja del popup |
| `.zap-preset` | `[zap]` | Botones de importes rapidos |
| `.zap-qr` | `[zap]` | Contenedor del QR |
| `.zap-bolt11` | `[zap]` | Texto monospace con la bolt11 |
| `.zap-actions` | `[zap]` | Fila "Abrir wallet" + "Copiar" |
| `.zap-error` | `[zap]` | Mensajes de error |
| `.btcpay-btn` | `[btcpay]` | Boton azul oscuro |
| `.btcpay-form` | `[btcpay]` | Form inline |

Los estilos se inyectan **una sola vez por pagina** via `static $assetsLoaded` / `static $btcpayStyleLoaded`.

---

## Pendiente / mejoras futuras

- **WebLN / Alby** en `[zap]`: detectar `window.webln`, ofrecer boton "Pagar con extensión" que llama `webln.sendPayment(bolt11)` y devuelve preimage al instante. Sirve como atajo para usuarios con Alby; el polling sigue cubriendo el resto. Apuntado tambien en `_modules_/noxtr/CLAUDE.md`.
- **wquery.dialog** en `[zap]`: reemplazar el modal custom por `$('selector').dialog({...})` de `_js_/wquery/wquery.dialog.js` para alinear con el resto del framework. Decision actual: dejarlo independiente para que funcione sin wquery cargado.
- **Cache de la lib QRCode**: `document.write` para cargar la lib es feo. Mejor: en `head.php` del theme principal cargar `_lib_/qrcode/qrcode.min.js` con `defer` para que este disponible cuando carga el shortcode.

---

## Otros shortcodes en este plugin

Lista de los shortcodes definidos en [main.php](main.php) que NO son Lightning:

- `[latest_posts limit="5"]` — lista de paginas de `CLI_PAGES`.
- `[ajax url="news/html"]` — carga via fetch un fragmento HTML en un div.
- `[jsfiddle code="abc" tabs="result"]` — embed iframe de jsfiddle.
- `[hash url="https://..."]` — SHA-256 del contenido remoto.
- `[spotify]` — markup del modulo spotify.
- `[now_playing]` — widget "now playing".
- `[whatsapp phone="34..." message="Hola"]` — icono fijo abajo-derecha con link wa.me.
