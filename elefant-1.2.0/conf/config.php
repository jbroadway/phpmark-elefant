; <?php /* Global configurations go here

[General]

; The name of your website.
site_name = "Your Site Name"

; Default outbound email address.
email_from = "you@example.com"

; Default character set for output.

charset = UTF-8

; Default timezone for date functions.

timezone = GMT

; Default handler for requests to / and /(.*) that don't match
; any other handler.

default_handler = "admin/page"

; Default layout template (aka theme) to use for page rendering.

default_layout = "default"

; Handler for errors, to be called by other handlers via
; $controller->error() with 'code', 'title' and an optional
; 'message' parameters.

error_handler = "admin/error"

; Whether to gzip output for browsers that support it.
; This usually gives a noticeable performance boost.

compress_output = On

; For development, turn debugging on and Elefant will output
; helpful information on errors.

debug = Off

[I18n]

; This is the method for determining which language to show the
; current visitor. Options are: url (e.g., /fr/), subdomain
; (e.g., fr.example.com), http (uses Accept-Language header),
; or cookie.

negotiation_method = http

[Database]

; Database settings go here. Driver must be a valid PDO driver.

master[driver] = sqlite
master[file] = "conf/site.db"

[Mongo]

; Settings to connect to MongoDB. Must have PHP Mongo extension
; installed via `pecl install mongo`.

;host = localhost:27017
;user = username
;pass = password
;set_name = my_replica_set

[Hooks]

; This is a list of hooks in the system and associated handlers
; to trigger when they occur. It's a good idea to name the hooks
; you define after the handler they occur in, to make it easier
; to look up the parameters they will receive.

admin/edit[] = navigation/hook/edit
admin/delete[] = navigation/hook/delete
;admin/add[] = search/add
;admin/edit[] = search/add
;admin/delete[] = search/delete
;blog/add[] = search/add
;blog/edit[] = search/add
;blog/delete[] = search/delete

[Memcache]

; Configure your memcache server list. If set, there will be a
; global $memcache object available to your apps. If the list is
; blank, or memcache is not available, it will create a fake
; global $memcache object that caches to memory within the PHP
; request, so you can hard-code cache functions even if caching
; is disabled on some systems.

;server[] = localhost:11211

; */ ?>