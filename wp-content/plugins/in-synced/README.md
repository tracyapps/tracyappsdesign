# In Synced

Reusable WordPress sync tooling for pushing selected local changes to a paired staging or production WordPress install when the host does not provide a good sync path.

## What It Does

- Pairs two WordPress installs with a shared secret and HMAC-signed REST requests.
- Pushes selected database tables in batches.
- Supports table include and exclude lists for partial content syncs.
- Compares local and remote post type counts before a repair push.
- Supports targeted post type repair syncs for interrupted content pushes.
- Rewrites local URLs to remote URLs, including inside serialized WordPress values.
- Transfers uploads/media files in chunks, with include and exclude patterns.
- Transfers explicit `wp-content` file roots such as a theme or custom plugin.
- Saves profiles for repeatable pushes and incremental file syncs.
- Previews table counts, row counts, file counts, sizes, and sample paths before pushing.
- Shows admin progress while a push is running.
- Preserves the receiver's `in_synced_*` options during database replacement so pairing survives.
- Creates a SQL backup under `wp-content/uploads/in-synced-backups/` before a replace-mode database push.

## Setup

1. Install and activate this plugin on both sites.
2. Open `Tools > In Synced` on both sites.
3. Use the same long shared secret on both sites.
4. On the sending site, set the remote URL and find/replace URLs.
5. On the receiving site, enable only the receiver permissions you want.
6. On the receiving site, list allowed `wp-content` roots, for example:

```text
themes/my-theme
plugins/my-custom-plugin
```

## Partial Sync Controls

Quick scopes:

- `Full`: database, uploads, and configured files.
- `Content + Media`: database and uploads.
- `Database`: database only.
- `Media`: uploads only.
- `Files`: configured `wp-content` roots only.
- `Media + Files`: uploads and configured file roots.

Profile filters:

- `Included table suffixes`: when filled, only these database tables sync.
- `Excluded table suffixes`: removes tables from the selected set.
- `Database scope type`: choose normal selected-table sync or post type repair.
- `Post types for compare/repair`: one post type per line for targeted repair.
- `Upload include patterns`: optional upload-only folder/glob allow list.
- `Upload excludes`: upload folder/glob deny list.
- `wp-content file roots`: explicit relative roots to send.
- `File excludes`: folder/glob deny list for configured roots.

## WP-CLI

```bash
wp in-synced preview --profile=default --path=app/public
wp in-synced compare --profile=default --post-types=vc_business,vc_event --path=app/public
wp in-synced push --profile=default --path=app/public
wp in-synced push --profile=default --scope=database --mode=upsert --path=app/public
wp in-synced push --profile=default --scope=database --mode=replace --post-types=vc_business,vc_event --path=app/public
wp in-synced push --profile=default --scope=theme --all-files --path=app/public
wp in-synced profiles --path=app/public
```

The `theme` scope name is retained for CLI compatibility, but it now means the profile's configured `wp-content` file roots.

## Repairing Interrupted Content Syncs

If a sync dies halfway through and custom post types look wrong on the remote site:

1. Set `Database scope type` to `Post type repair`.
2. Add the affected post types under `Post types for compare/repair`.
3. Click `Compare Content` to see local vs remote counts.
4. Use `replace` mode with only `Database` checked to overwrite just those post types.

Post type repair deletes matching remote posts for the selected post types, then sends local rows from `posts`, `postmeta`, `comments`, `commentmeta`, and `term_relationships`. Term tables are upserted so taxonomy relationships have matching term records.

## Security Notes

- There is no file browser endpoint.
- Requests must be signed with the shared secret, timestamp, nonce, route, and body hash.
- Nonces are single-use for the allowed timestamp window.
- Upload writes are limited to the WordPress uploads directory.
- Custom file writes are limited to receiver-approved `wp-content` roots.
- Users/usermeta are excluded by default.
- The plugin's own `in_synced_*` options are never imported from local to remote.

For stronger staging security, define the shared secret in `wp-config.php` instead of storing it in the database:

```php
define('IN_SYNCED_SHARED_SECRET', 'paste-a-long-random-secret-here');
```
