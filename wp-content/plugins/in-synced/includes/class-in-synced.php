<?php

if (!defined('ABSPATH')) {
    exit;
}

final class In_Synced
{
    private const SETTINGS_OPTION = 'in_synced_settings';
    private const PROFILES_OPTION = 'in_synced_profiles';
    private const LAST_RUNS_OPTION = 'in_synced_last_runs';
    private const REST_NAMESPACE = 'in-synced/v1';
    private const MAX_CLOCK_SKEW = 300;
    private const FILE_CHUNK_SIZE = 786432;
    private const PROGRESS_TRANSIENT_PREFIX = 'in_synced_progress_';
    private const PROGRESS_TTL = 3600;

    private static $instance = null;

    public static function instance(): In_Synced
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_menu', [$this, 'register_admin_page']);
        add_action('admin_post_in_synced_save_settings', [$this, 'handle_save_settings']);
        add_action('admin_post_in_synced_save_profile', [$this, 'handle_save_profile']);
        add_action('admin_post_in_synced_test_connection', [$this, 'handle_test_connection']);
        add_action('admin_post_in_synced_run_profile', [$this, 'handle_run_profile']);
        add_action('wp_ajax_in_synced_preview', [$this, 'ajax_preview']);
        add_action('wp_ajax_in_synced_compare', [$this, 'ajax_compare']);
        add_action('wp_ajax_in_synced_run', [$this, 'ajax_run']);
        add_action('wp_ajax_in_synced_progress', [$this, 'ajax_progress']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public static function activate(): void
    {
        if (!get_option(self::SETTINGS_OPTION)) {
            add_option(self::SETTINGS_OPTION, self::default_settings(), '', false);
        }

        if (!get_option(self::PROFILES_OPTION)) {
            add_option(self::PROFILES_OPTION, self::default_profiles(), '', false);
        }
    }

    public static function default_settings(): array
    {
        return [
            'remote_url' => '',
            'local_url' => home_url(),
            'remote_public_url' => '',
            'shared_secret' => '',
            'allow_db_receive' => false,
            'allow_media_receive' => false,
            'allow_theme_receive' => false,
            'allow_file_receive' => false,
            'theme_slug' => function_exists('get_stylesheet') ? get_stylesheet() : '',
            'allowed_file_roots' => function_exists('get_stylesheet') ? 'themes/' . get_stylesheet() : '',
        ];
    }

    public static function default_profiles(): array
    {
        return [
            'default' => [
                'name' => 'Default staging push',
                'mode' => 'upsert',
                'sync_database' => true,
                'sync_media' => true,
                'sync_theme' => true,
                'incremental_files' => true,
                'include_users' => false,
                'include_tables' => '',
                'exclude_tables' => "users\nusermeta",
                'database_scope' => 'tables',
                'post_type_filter' => '',
                'upload_includes' => '',
                'upload_excludes' => "in-synced-backups\n*.tmp\n*.log",
                'batch_size' => 100,
                'theme_slug' => function_exists('get_stylesheet') ? get_stylesheet() : '',
                'file_roots' => function_exists('get_stylesheet') ? 'themes/' . get_stylesheet() : '',
                'theme_excludes' => ".git\nnode_modules\nvendor\n.DS_Store\n*.map\n*.log",
                'file_excludes' => ".git\nnode_modules\nvendor\n.DS_Store\n*.map\n*.log\n.cache",
            ],
        ];
    }

    public function get_settings(): array
    {
        return array_merge(self::default_settings(), (array) get_option(self::SETTINGS_OPTION, []));
    }

    public function get_profiles(): array
    {
        $defaults = self::default_profiles();
        $profiles = $defaults;
        $saved = (array) get_option(self::PROFILES_OPTION, []);

        foreach ($saved as $key => $profile) {
            if (!is_array($profile)) {
                continue;
            }

            $base = $defaults[$key] ?? $defaults['default'];
            $profiles[$key] = array_merge($base, $profile);
        }

        return $profiles;
    }

    public function get_profile(string $profile_key): array
    {
        $profiles = $this->get_profiles();

        return $profiles[$profile_key] ?? $profiles['default'];
    }

    public function register_rest_routes(): void
    {
        $routes = [
            'ping' => 'rest_ping',
            'begin-db' => 'rest_begin_db',
            'table-batch' => 'rest_table_batch',
            'end-db' => 'rest_end_db',
            'begin-content' => 'rest_begin_content',
            'compare-content' => 'rest_compare_content',
            'file-chunk' => 'rest_file_chunk',
        ];

        foreach ($routes as $route => $method) {
            register_rest_route(self::REST_NAMESPACE, '/' . $route, [
                'methods' => 'POST',
                'callback' => [$this, $method],
                'permission_callback' => [$this, 'authorize_rest_request'],
            ]);
        }
    }

    public function authorize_rest_request(WP_REST_Request $request)
    {
        $settings = $this->get_settings();
        $secret = $this->get_shared_secret($settings);

        if (!$secret) {
            return new WP_Error('in_synced_no_secret', 'In Synced is not paired on this site.', ['status' => 403]);
        }

        $timestamp = (int) $request->get_header('x_in_synced_timestamp');
        $nonce = (string) $request->get_header('x_in_synced_nonce');
        $signature = (string) $request->get_header('x_in_synced_signature');

        if (!$timestamp || !$nonce || !$signature) {
            return new WP_Error('in_synced_unsigned', 'Missing In Synced signature headers.', ['status' => 401]);
        }

        if (abs(time() - $timestamp) > self::MAX_CLOCK_SKEW) {
            return new WP_Error('in_synced_stale', 'In Synced request timestamp is outside the allowed window.', ['status' => 401]);
        }

        $nonce_key = 'in_synced_nonce_' . md5($nonce);
        if (get_transient($nonce_key)) {
            return new WP_Error('in_synced_replay', 'In Synced request nonce has already been used.', ['status' => 401]);
        }

        $body = $request->get_body();
        $base = implode("\n", [
            strtoupper($request->get_method()),
            $request->get_route(),
            (string) $timestamp,
            hash('sha256', $body),
        ]);
        $expected = 'sha256=' . hash_hmac('sha256', $base, $secret);

        if (!hash_equals($expected, $signature)) {
            return new WP_Error('in_synced_bad_signature', 'Invalid In Synced request signature.', ['status' => 401]);
        }

        set_transient($nonce_key, 1, self::MAX_CLOCK_SKEW);

        return true;
    }

    public function register_admin_page(): void
    {
        add_management_page(
            'In Synced',
            'In Synced',
            'manage_options',
            'in-synced',
            [$this, 'render_admin_page']
        );
    }

    public function render_admin_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access In Synced.', 'in-synced'));
        }

        $settings = $this->get_settings();
        $profiles = $this->get_profiles();
        $profile_key = isset($_GET['profile']) ? sanitize_key(wp_unslash($_GET['profile'])) : 'default';
        $profile = $profiles[$profile_key] ?? $profiles['default'];
        $last_log = get_transient('in_synced_last_log_' . get_current_user_id());
        $last_runs = (array) get_option(self::LAST_RUNS_OPTION, []);
        $secret_fingerprint = $this->secret_fingerprint($settings);
        $ajax_nonce = wp_create_nonce('in_synced_ajax');
        delete_transient('in_synced_last_log_' . get_current_user_id());
        ?>
        <div class="wrap">
            <style>
                .in-synced-shell { max-width: 1180px; }
                .in-synced-hero { align-items: flex-start; background: linear-gradient(135deg, #101517, #17352f 58%, #284b68); border-radius: 10px; box-shadow: 0 18px 42px rgba(16,21,23,.12); color: #fff; display: flex; gap: 24px; justify-content: space-between; margin: 18px 0 20px; overflow: hidden; padding: 24px; position: relative; }
                .in-synced-hero:after { border: 1px solid rgba(255,255,255,.18); border-radius: 999px; content: ""; height: 170px; position: absolute; right: -56px; top: -70px; width: 170px; }
                .in-synced-hero h1 { color: #fff; font-size: 34px; line-height: 1; margin: 0 0 8px; }
                .in-synced-hero p { color: rgba(255,255,255,.78); max-width: 760px; font-size: 14px; margin: 0; }
                .in-synced-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin: 16px 0; }
                .in-synced-card, .in-synced-panel { background: #fff; border: 1px solid #d9e1df; border-radius: 10px; box-shadow: 0 6px 22px rgba(16,21,23,.05); }
                .in-synced-card { border-top: 4px solid #2f8068; padding: 14px 16px; }
                .in-synced-card strong { display: block; color: #1d2327; font-size: 12px; letter-spacing: .04em; margin-bottom: 6px; text-transform: uppercase; }
                .in-synced-card span { color: #50575e; overflow-wrap: anywhere; }
                .in-synced-panel { padding: 20px; margin: 16px 0; }
                .in-synced-panel h2 { margin: 0 0 12px; font-size: 19px; }
                .in-synced-actions { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0 16px; }
                .in-synced-run-controls { background: #f7faf9; border: 1px solid #dfe8e5; border-radius: 8px; display: grid; grid-template-columns: 220px repeat(4, max-content); gap: 14px; align-items: center; margin: 16px 0; padding: 14px; }
                .in-synced-preview { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 14px; }
                .in-synced-preview-card { border: 1px solid #d9e1df; border-radius: 10px; padding: 15px; background: #f7faf9; min-height: 120px; }
                .in-synced-preview-card h3 { color: #17352f; margin: 0 0 8px; font-size: 14px; letter-spacing: .04em; text-transform: uppercase; }
                .in-synced-preview-card .count { font-size: 28px; font-weight: 700; color: #101517; }
                .in-synced-preview-card ul { margin: 10px 0 0 18px; max-height: 180px; overflow: auto; }
                .in-synced-progress-bar { height: 14px; border-radius: 999px; overflow: hidden; background: #dcdcde; margin-top: 12px; }
                .in-synced-progress-bar span { display: block; height: 100%; width: 0%; background: linear-gradient(90deg, #2f8068, #2271b1); transition: width .25s ease; }
                .in-synced-progress-meta { display: flex; justify-content: space-between; gap: 20px; margin-top: 8px; color: #50575e; }
                .in-synced-log { white-space: pre-wrap; max-height: 320px; overflow: auto; background: #101517; color: #d6f0e0; border-radius: 8px; padding: 14px; margin-top: 14px; font-size: 12px; }
                .in-synced-badge { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.24); border-radius: 999px; color: #fff; display: inline-block; font-size: 12px; padding: 5px 10px; position: relative; z-index: 1; }
                .in-synced-danger { color: #b32d2e; }
                .in-synced-details { margin-top: 18px; }
                .in-synced-details summary { cursor: pointer; font-weight: 600; padding: 12px 0; }
                @media (max-width: 960px) {
                    .in-synced-grid, .in-synced-preview { grid-template-columns: 1fr; }
                    .in-synced-hero, .in-synced-run-controls { display: block; }
                    .in-synced-run-controls label { display: block; margin: 8px 0; }
                }
            </style>

            <div class="in-synced-shell">
                <div class="in-synced-hero">
                    <div>
                        <h1>In Synced</h1>
                        <p>Push selected database tables, uploads, and explicit wp-content file roots from this site to a paired WordPress site using signed requests.</p>
                    </div>
                    <span class="in-synced-badge"><?php echo $secret_fingerprint ? 'Key ' . esc_html($secret_fingerprint) : 'Not paired'; ?></span>
                </div>

            <?php if ($last_log) : ?>
                <div class="notice notice-info">
                    <pre style="white-space: pre-wrap; max-height: 420px; overflow: auto;"><?php echo esc_html($last_log); ?></pre>
                </div>
            <?php endif; ?>

                <div class="in-synced-grid">
                    <div class="in-synced-card"><strong>Remote</strong><span><?php echo esc_html($settings['remote_url'] ?: 'Not configured'); ?></span></div>
                    <div class="in-synced-card"><strong>Find/replace</strong><span><?php echo esc_html(($settings['local_url'] ?: home_url()) . ' -> ' . ($settings['remote_public_url'] ?: $settings['remote_url'] ?: 'remote')); ?></span></div>
                    <div class="in-synced-card"><strong>Allowed file roots</strong><span><?php echo esc_html($settings['allowed_file_roots'] ?: $settings['theme_slug'] ?: 'Not configured'); ?></span></div>
                    <div class="in-synced-card"><strong>Last success</strong><span><?php echo !empty($last_runs[$profile_key]) ? esc_html(gmdate('M j, Y H:i', (int) $last_runs[$profile_key]) . ' UTC') : 'No completed run yet'; ?></span></div>
                </div>

                <div class="in-synced-panel">
                    <h2>Push Console</h2>
                    <div class="in-synced-actions" aria-label="Quick sync scopes">
                        <button type="button" class="button in-synced-scope" data-scope="full">Full</button>
                        <button type="button" class="button in-synced-scope" data-scope="content_media">Content + Media</button>
                        <button type="button" class="button in-synced-scope" data-scope="database">Database</button>
                        <button type="button" class="button in-synced-scope" data-scope="media">Media</button>
                        <button type="button" class="button in-synced-scope" data-scope="theme">Files</button>
                        <button type="button" class="button in-synced-scope" data-scope="files">Media + Files</button>
                    </div>

                    <form id="in-synced-run-form">
                        <input type="hidden" name="profile_key" value="<?php echo esc_attr($profile_key); ?>" />
                        <input type="hidden" id="in-synced-scope" name="scope" value="full" />
                        <div class="in-synced-run-controls">
                            <label>
                                Database mode<br />
                                <select name="mode" id="in-synced-run-mode">
                                    <option value="upsert" <?php selected($profile['mode'], 'upsert'); ?>>Upsert</option>
                                    <option value="replace" <?php selected($profile['mode'], 'replace'); ?>>Replace</option>
                                </select>
                            </label>
                            <label><input type="checkbox" name="sync_database" value="1" <?php checked($profile['sync_database']); ?> /> Database</label>
                            <label><input type="checkbox" name="sync_media" value="1" <?php checked($profile['sync_media']); ?> /> Media</label>
                            <label><input type="checkbox" name="sync_theme" value="1" <?php checked($profile['sync_theme']); ?> /> Files</label>
                            <label><input type="checkbox" name="incremental_files" value="1" <?php checked($profile['incremental_files']); ?> /> Incremental files</label>
                        </div>
                        <p>
                            <button type="button" class="button button-secondary" id="in-synced-preview-button">Preview Update</button>
                            <button type="button" class="button button-secondary" id="in-synced-compare-button">Compare Content</button>
                            <button type="button" class="button button-primary" id="in-synced-run-button">Push Now</button>
                            <span id="in-synced-inline-status" class="description"></span>
                        </p>
                    </form>

                    <div id="in-synced-preview" class="in-synced-preview" aria-live="polite"></div>
                    <div id="in-synced-compare" class="in-synced-preview" aria-live="polite"></div>

                    <div id="in-synced-progress" style="display:none;">
                        <div class="in-synced-progress-bar"><span id="in-synced-progress-fill"></span></div>
                        <div class="in-synced-progress-meta">
                            <span id="in-synced-progress-message">Waiting.</span>
                            <strong id="in-synced-progress-percent">0%</strong>
                        </div>
                        <pre id="in-synced-progress-log" class="in-synced-log"></pre>
                    </div>

                    <p class="description">For very large pushes, WP-CLI is still the most durable path: <code>wp in-synced push --profile=<?php echo esc_html($profile_key); ?></code></p>
                </div>

                <details class="in-synced-panel in-synced-details">
                    <summary>Connection Settings</summary>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('in_synced_save_settings'); ?>
                        <input type="hidden" name="action" value="in_synced_save_settings" />
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="in-synced-remote-url">Remote admin/API URL</label></th>
                                <td><input class="regular-text" id="in-synced-remote-url" name="remote_url" type="url" value="<?php echo esc_attr($settings['remote_url']); ?>" placeholder="https://staging.example.com" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-local-url">This site's public URL</label></th>
                                <td><input class="regular-text" id="in-synced-local-url" name="local_url" type="url" value="<?php echo esc_attr($settings['local_url']); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-remote-public-url">Remote public URL</label></th>
                                <td><input class="regular-text" id="in-synced-remote-public-url" name="remote_public_url" type="url" value="<?php echo esc_attr($settings['remote_public_url']); ?>" placeholder="https://staging.example.com" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-secret">Shared secret</label></th>
                                <td>
                                    <input class="regular-text" id="in-synced-secret" name="shared_secret" type="password" value="" autocomplete="new-password" placeholder="<?php echo $secret_fingerprint ? 'Stored secret' : 'Paste shared secret'; ?>" />
                                    <button type="button" class="button" id="in-synced-generate-secret">Generate suggested secret</button>
                                    <p class="description">Current fingerprint: <code><?php echo $secret_fingerprint ? esc_html($secret_fingerprint) : 'none'; ?></code>. Leave blank to keep the current secret. Generate a suggestion when pairing both sites so the same known value can be pasted into each install.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Receiver permissions</th>
                                <td>
                                    <label><input type="checkbox" name="allow_db_receive" value="1" <?php checked($settings['allow_db_receive']); ?> /> Allow signed database writes to this site</label><br />
                                    <label><input type="checkbox" name="allow_media_receive" value="1" <?php checked($settings['allow_media_receive']); ?> /> Allow signed upload writes to this site</label><br />
                                    <label><input type="checkbox" name="allow_theme_receive" value="1" <?php checked($settings['allow_theme_receive'] || $settings['allow_file_receive']); ?> /> Allow signed wp-content file writes to this site</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-file-roots">Allowed wp-content roots</label></th>
                                <td>
                                    <textarea id="in-synced-file-roots" name="allowed_file_roots" rows="4" class="large-text code"><?php echo esc_textarea($settings['allowed_file_roots'] ?: ($settings['theme_slug'] ? 'themes/' . $settings['theme_slug'] : '')); ?></textarea>
                                    <p class="description">One relative wp-content path per line. Examples: <code>themes/my-theme</code>, <code>plugins/my-custom-plugin</code>. Receivers only accept writes inside these roots.</p>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button('Save connection'); ?>
                    </form>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('in_synced_test_connection'); ?>
                        <input type="hidden" name="action" value="in_synced_test_connection" />
                        <?php submit_button('Test remote', 'secondary'); ?>
                    </form>
                </details>

                <details class="in-synced-panel in-synced-details">
                    <summary>Profile Settings</summary>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('in_synced_save_profile'); ?>
                        <input type="hidden" name="action" value="in_synced_save_profile" />
                        <input type="hidden" name="profile_key" value="<?php echo esc_attr($profile_key); ?>" />
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="in-synced-profile-name">Name</label></th>
                                <td><input class="regular-text" id="in-synced-profile-name" name="profile[name]" type="text" value="<?php echo esc_attr($profile['name']); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Sync scope</th>
                                <td>
                                    <label><input type="checkbox" name="profile[sync_database]" value="1" <?php checked($profile['sync_database']); ?> /> Database</label><br />
                                    <label><input type="checkbox" name="profile[sync_media]" value="1" <?php checked($profile['sync_media']); ?> /> Uploads/media</label><br />
                                    <label><input type="checkbox" name="profile[sync_theme]" value="1" <?php checked($profile['sync_theme']); ?> /> wp-content files</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-mode">Database mode</label></th>
                                <td>
                                    <select id="in-synced-mode" name="profile[mode]">
                                        <option value="upsert" <?php selected($profile['mode'], 'upsert'); ?>>Upsert rows without wiping tables</option>
                                        <option value="replace" <?php selected($profile['mode'], 'replace'); ?>>Replace selected tables first</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-database-scope">Database scope type</label></th>
                                <td>
                                    <select id="in-synced-database-scope" name="profile[database_scope]">
                                        <option value="tables" <?php selected($profile['database_scope'] ?? 'tables', 'tables'); ?>>Selected tables</option>
                                        <option value="post_types" <?php selected($profile['database_scope'] ?? 'tables', 'post_types'); ?>>Post type repair</option>
                                    </select>
                                    <p class="description">Post type repair sends posts, postmeta, comments, and taxonomy relationships for the post types below. In replace mode, matching remote posts are deleted before import.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-post-types">Post types for compare/repair</label></th>
                                <td>
                                    <textarea id="in-synced-post-types" name="profile[post_type_filter]" rows="4" class="large-text code"><?php echo esc_textarea($profile['post_type_filter'] ?? ''); ?></textarea>
                                    <p class="description">Optional for compare, required for post type repair. One post type per line, such as <code>page</code>, <code>post</code>, or a custom post type.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">User tables</th>
                                <td><label><input type="checkbox" name="profile[include_users]" value="1" <?php checked($profile['include_users']); ?> /> Include users and usermeta</label></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-include-tables">Included table suffixes</label></th>
                                <td>
                                    <textarea id="in-synced-include-tables" name="profile[include_tables]" rows="4" class="large-text code"><?php echo esc_textarea($profile['include_tables'] ?? ''); ?></textarea>
                                    <p class="description">Optional. When filled, only these table suffixes are synced. Example: <code>posts</code>, <code>postmeta</code>, <code>options</code>.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-exclude-tables">Excluded table suffixes</label></th>
                                <td>
                                    <textarea id="in-synced-exclude-tables" name="profile[exclude_tables]" rows="5" class="large-text code"><?php echo esc_textarea($profile['exclude_tables']); ?></textarea>
                                    <p class="description">One suffix per line, without the WordPress table prefix. Example: users</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-batch-size">Database batch size</label></th>
                                <td><input id="in-synced-batch-size" name="profile[batch_size]" type="number" min="10" max="1000" value="<?php echo esc_attr((int) $profile['batch_size']); ?>" /></td>
                            </tr>
                            <tr>
                                <th scope="row">File incrementals</th>
                                <td><label><input type="checkbox" name="profile[incremental_files]" value="1" <?php checked($profile['incremental_files']); ?> /> Only send upload/file-root items modified since the previous successful run</label></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-upload-includes">Upload include patterns</label></th>
                                <td>
                                    <textarea id="in-synced-upload-includes" name="profile[upload_includes]" rows="4" class="large-text code"><?php echo esc_textarea($profile['upload_includes'] ?? ''); ?></textarea>
                                    <p class="description">Optional. One glob or folder per line. Leave empty to include all uploads.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-upload-excludes">Upload excludes</label></th>
                                <td><textarea id="in-synced-upload-excludes" name="profile[upload_excludes]" rows="4" class="large-text code"><?php echo esc_textarea($profile['upload_excludes'] ?? ''); ?></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-file-roots-profile">wp-content file roots</label></th>
                                <td>
                                    <textarea id="in-synced-file-roots-profile" name="profile[file_roots]" rows="5" class="large-text code"><?php echo esc_textarea($profile['file_roots'] ?? ($profile['theme_slug'] ? 'themes/' . $profile['theme_slug'] : '')); ?></textarea>
                                    <p class="description">One relative wp-content path per line. Examples: <code>themes/<?php echo esc_html(get_stylesheet()); ?></code>, <code>plugins/my-custom-plugin</code>.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="in-synced-file-excludes">File excludes</label></th>
                                <td><textarea id="in-synced-file-excludes" name="profile[file_excludes]" rows="5" class="large-text code"><?php echo esc_textarea($profile['file_excludes'] ?? $profile['theme_excludes']); ?></textarea></td>
                            </tr>
                        </table>
                        <?php submit_button('Save profile'); ?>
                    </form>
                </details>
            </div>

            <script>
                (function () {
                    var config = <?php echo wp_json_encode([
                        'ajaxUrl' => admin_url('admin-ajax.php'),
                        'nonce' => $ajax_nonce,
                        'profileKey' => $profile_key,
                    ]); ?>;
                    var form = document.getElementById('in-synced-run-form');
                    var scopeInput = document.getElementById('in-synced-scope');
                    var status = document.getElementById('in-synced-inline-status');
                    var preview = document.getElementById('in-synced-preview');
                    var compare = document.getElementById('in-synced-compare');
                    var progress = document.getElementById('in-synced-progress');
                    var progressFill = document.getElementById('in-synced-progress-fill');
                    var progressPercent = document.getElementById('in-synced-progress-percent');
                    var progressMessage = document.getElementById('in-synced-progress-message');
                    var progressLog = document.getElementById('in-synced-progress-log');
                    var pollTimer = null;

                    function formPayload(action, runId) {
                        var data = new FormData(form);
                        data.append('action', action);
                        data.append('nonce', config.nonce);
                        data.append('profile_key', config.profileKey);
                        ['sync_database', 'sync_media', 'sync_theme', 'incremental_files'].forEach(function (name) {
                            var field = form.querySelector('[name="' + name + '"]');
                            data.set(name, field && field.checked ? '1' : '0');
                        });
                        if (runId) {
                            data.append('run_id', runId);
                        }
                        return data;
                    }

                    function post(action, runId) {
                        return fetch(config.ajaxUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: formPayload(action, runId)
                        }).then(function (response) {
                            return response.json();
                        });
                    }

                    function escapeHtml(value) {
                        return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
                            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
                        });
                    }

                    function renderPreview(data) {
                        var warnings = data.warnings && data.warnings.length
                            ? '<p class="in-synced-danger">' + data.warnings.map(escapeHtml).join('<br>') + '</p>'
                            : '';
                        var tables = (data.database.tables || []).slice(0, 12).map(function (table) {
                            return '<li>' + escapeHtml(table.label) + ' - ' + escapeHtml(table.rows) + ' rows</li>';
                        }).join('');
                        var uploads = (data.uploads.sample || []).slice(0, 10).map(function (file) {
                            return '<li>' + escapeHtml(file.path) + ' - ' + escapeHtml(file.size_label) + '</li>';
                        }).join('');
                        var files = (data.theme.sample || []).slice(0, 10).map(function (file) {
                            return '<li>' + escapeHtml(file.path) + ' - ' + escapeHtml(file.size_label) + '</li>';
                        }).join('');

                        preview.innerHTML =
                            '<div class="in-synced-preview-card"><h3>Database</h3><div class="count">' + escapeHtml(data.database.table_count) + '</div><p>' + escapeHtml(data.database.row_count) + ' rows in ' + escapeHtml(data.mode) + ' mode</p><ul>' + tables + '</ul></div>' +
                            '<div class="in-synced-preview-card"><h3>Media</h3><div class="count">' + escapeHtml(data.uploads.file_count) + '</div><p>' + escapeHtml(data.uploads.bytes_label || '0 B') + (data.uploads.since ? ' since ' + escapeHtml(data.uploads.since) : '') + '</p><ul>' + uploads + '</ul></div>' +
                            '<div class="in-synced-preview-card"><h3>Files</h3><div class="count">' + escapeHtml(data.theme.file_count) + '</div><p>' + escapeHtml(data.theme.theme_slug) + ' - ' + escapeHtml(data.theme.bytes_label || '0 B') + '</p><ul>' + files + '</ul>' + warnings + '</div>';
                    }

                    function renderCompare(data) {
                        var rows = (data.post_types || []).map(function (item) {
                            var delta = Number(item.local || 0) - Number(item.remote || 0);
                            return '<li><strong>' + escapeHtml(item.post_type) + '</strong>: local ' + escapeHtml(item.local) + ', remote ' + escapeHtml(item.remote) + ', delta ' + escapeHtml(delta) + '</li>';
                        }).join('');
                        var tableRows = (data.tables || []).slice(0, 10).map(function (item) {
                            return '<li><strong>' + escapeHtml(item.label) + '</strong>: local ' + escapeHtml(item.local) + ', remote ' + escapeHtml(item.remote) + '</li>';
                        }).join('');

                        compare.innerHTML =
                            '<div class="in-synced-preview-card"><h3>Post Types</h3><div class="count">' + escapeHtml(data.post_types.length) + '</div><ul>' + rows + '</ul></div>' +
                            '<div class="in-synced-preview-card"><h3>Tables</h3><div class="count">' + escapeHtml(data.tables.length) + '</div><ul>' + tableRows + '</ul></div>' +
                            '<div class="in-synced-preview-card"><h3>Remote</h3><p>' + escapeHtml(data.remote_site || 'Connected') + '</p><p>Compare complete.</p></div>';
                    }

                    function setProgress(data) {
                        progress.style.display = 'block';
                        var percent = Math.max(0, Math.min(100, parseInt(data.percent || 0, 10)));
                        progressFill.style.width = percent + '%';
                        progressPercent.textContent = percent + '%';
                        progressMessage.textContent = (data.phase ? '[' + data.phase + '] ' : '') + (data.message || '');
                        progressLog.textContent = (data.log || []).join("\n");
                    }

                    function poll(runId) {
                        clearInterval(pollTimer);
                        pollTimer = setInterval(function () {
                            post('in_synced_progress', runId).then(function (response) {
                                if (response && response.success) {
                                    setProgress(response.data);
                                    if (response.data.status === 'complete' || response.data.status === 'failed') {
                                        clearInterval(pollTimer);
                                    }
                                }
                            });
                        }, 1200);
                    }

                    function previewRun() {
                        status.textContent = 'Building preview...';
                        post('in_synced_preview').then(function (response) {
                            if (!response.success) {
                                status.textContent = response.data && response.data.message ? response.data.message : 'Preview failed.';
                                return;
                            }
                            renderPreview(response.data);
                            status.textContent = 'Preview ready.';
                        }).catch(function () {
                            status.textContent = 'Preview failed.';
                        });
                    }

                    function compareRun() {
                        status.textContent = 'Comparing local and remote content...';
                        post('in_synced_compare').then(function (response) {
                            if (!response.success) {
                                status.textContent = response.data && response.data.message ? response.data.message : 'Compare failed.';
                                return;
                            }
                            renderCompare(response.data);
                            status.textContent = 'Compare ready.';
                        }).catch(function () {
                            status.textContent = 'Compare failed.';
                        });
                    }

                    function runSync() {
                        var runId = 'run-' + Date.now() + '-' + Math.random().toString(16).slice(2);
                        status.textContent = 'Push running...';
                        setProgress({phase: 'starting', message: 'Starting sync.', percent: 1, log: []});
                        poll(runId);
                        post('in_synced_run', runId).then(function (response) {
                            if (response && response.success) {
                                setProgress(response.data.progress || {phase: 'complete', message: 'Sync complete.', percent: 100, log: []});
                                status.textContent = 'Push complete.';
                            } else {
                                setProgress(response.data && response.data.progress ? response.data.progress : {phase: 'failed', message: 'Sync failed.', percent: 100, log: []});
                                status.textContent = response.data && response.data.message ? response.data.message : 'Push failed.';
                            }
                            clearInterval(pollTimer);
                        }).catch(function () {
                            status.textContent = 'Push request failed. Check the latest progress log.';
                        });
                    }

                    document.querySelectorAll('.in-synced-scope').forEach(function (button) {
                        button.addEventListener('click', function () {
                            scopeInput.value = button.getAttribute('data-scope');
                            document.querySelectorAll('.in-synced-scope').forEach(function (item) { item.classList.remove('button-primary'); });
                            button.classList.add('button-primary');
                            var scope = scopeInput.value;
                            form.querySelector('[name="sync_database"]').checked = ['full', 'database', 'content', 'content_media'].indexOf(scope) !== -1;
                            form.querySelector('[name="sync_media"]').checked = ['full', 'media', 'files', 'content_media'].indexOf(scope) !== -1;
                            form.querySelector('[name="sync_theme"]').checked = ['full', 'theme', 'files'].indexOf(scope) !== -1;
                            previewRun();
                        });
                    });

                    document.getElementById('in-synced-preview-button').addEventListener('click', previewRun);
                    document.getElementById('in-synced-compare-button').addEventListener('click', compareRun);
                    document.getElementById('in-synced-run-button').addEventListener('click', runSync);
                    var generateSecret = document.getElementById('in-synced-generate-secret');
                    if (generateSecret) {
                        generateSecret.addEventListener('click', function () {
                            var field = document.getElementById('in-synced-secret');
                            var bytes = new Uint8Array(48);
                            if (window.crypto && window.crypto.getRandomValues) {
                                window.crypto.getRandomValues(bytes);
                            } else {
                                for (var index = 0; index < bytes.length; index++) {
                                    bytes[index] = Math.floor(Math.random() * 256);
                                }
                            }
                            field.type = 'text';
                            field.value = Array.prototype.map.call(bytes, function (byte) {
                                return ('0' + byte.toString(16)).slice(-2);
                            }).join('');
                            field.focus();
                            field.select();
                        });
                    }
                    previewRun();
                })();
            </script>
        </div>
        <?php
    }

    public function handle_save_settings(): void
    {
        $this->require_admin_action('in_synced_save_settings');

        $settings = $this->get_settings();
        $secret = isset($_POST['shared_secret']) ? trim((string) wp_unslash($_POST['shared_secret'])) : '';
        if ($secret === '') {
            $secret = (string) ($settings['shared_secret'] ?? '');
        }

        $settings = [
            'remote_url' => $this->sanitize_url_field('remote_url'),
            'local_url' => $this->sanitize_url_field('local_url') ?: home_url(),
            'remote_public_url' => $this->sanitize_url_field('remote_public_url'),
            'shared_secret' => $secret,
            'allow_db_receive' => !empty($_POST['allow_db_receive']),
            'allow_media_receive' => !empty($_POST['allow_media_receive']),
            'allow_theme_receive' => !empty($_POST['allow_theme_receive']),
            'allow_file_receive' => !empty($_POST['allow_theme_receive']),
            'theme_slug' => $this->sanitize_theme_slug($_POST['theme_slug'] ?? $settings['theme_slug']),
            'allowed_file_roots' => $this->sanitize_file_roots_text($_POST['allowed_file_roots'] ?? $settings['allowed_file_roots'] ?? ''),
        ];

        update_option(self::SETTINGS_OPTION, $settings, false);
        $this->redirect_admin();
    }

    public function handle_save_profile(): void
    {
        $this->require_admin_action('in_synced_save_profile');

        $profile_key = isset($_POST['profile_key']) ? sanitize_key(wp_unslash($_POST['profile_key'])) : 'default';
        $raw = isset($_POST['profile']) && is_array($_POST['profile']) ? wp_unslash($_POST['profile']) : [];
        $profiles = $this->get_profiles();

        $profiles[$profile_key] = [
            'name' => sanitize_text_field($raw['name'] ?? 'Default staging push'),
            'mode' => (($raw['mode'] ?? 'upsert') === 'replace') ? 'replace' : 'upsert',
            'sync_database' => !empty($raw['sync_database']),
            'sync_media' => !empty($raw['sync_media']),
            'sync_theme' => !empty($raw['sync_theme']),
            'incremental_files' => !empty($raw['incremental_files']),
            'include_users' => !empty($raw['include_users']),
            'include_tables' => sanitize_textarea_field($raw['include_tables'] ?? ''),
            'exclude_tables' => sanitize_textarea_field($raw['exclude_tables'] ?? ''),
            'database_scope' => (($raw['database_scope'] ?? 'tables') === 'post_types') ? 'post_types' : 'tables',
            'post_type_filter' => sanitize_textarea_field($raw['post_type_filter'] ?? ''),
            'upload_includes' => sanitize_textarea_field($raw['upload_includes'] ?? ''),
            'upload_excludes' => sanitize_textarea_field($raw['upload_excludes'] ?? ''),
            'batch_size' => max(10, min(1000, (int) ($raw['batch_size'] ?? 100))),
            'theme_slug' => $this->sanitize_theme_slug($raw['theme_slug'] ?? get_stylesheet()),
            'theme_excludes' => sanitize_textarea_field($raw['theme_excludes'] ?? ''),
            'file_roots' => $this->sanitize_file_roots_text($raw['file_roots'] ?? ''),
            'file_excludes' => sanitize_textarea_field($raw['file_excludes'] ?? ''),
        ];

        update_option(self::PROFILES_OPTION, $profiles, false);
        $this->redirect_admin(['profile' => $profile_key]);
    }

    public function handle_test_connection(): void
    {
        $this->require_admin_action('in_synced_test_connection');

        $log = [];
        $response = $this->remote_request('ping', ['site' => home_url()], $log);
        if (is_wp_error($response)) {
            $log[] = 'Connection failed: ' . $response->get_error_message();
        } else {
            $log[] = 'Connected to remote site: ' . ($response['site_url'] ?? 'unknown');
            $log[] = 'Receiver permissions: database=' . $this->bool_label($response['allow_db_receive'] ?? false) . ', media=' . $this->bool_label($response['allow_media_receive'] ?? false) . ', files=' . $this->bool_label(($response['allow_file_receive'] ?? false) || ($response['allow_theme_receive'] ?? false));
        }

        set_transient('in_synced_last_log_' . get_current_user_id(), implode("\n", $log), HOUR_IN_SECONDS);
        $this->redirect_admin();
    }

    public function handle_run_profile(): void
    {
        $this->require_admin_action('in_synced_run_profile');

        @set_time_limit(0);
        $profile_key = isset($_POST['profile_key']) ? sanitize_key(wp_unslash($_POST['profile_key'])) : 'default';
        $log = [];
        $result = $this->run_sync($profile_key, $log);
        if (is_wp_error($result)) {
            $log[] = 'Sync failed: ' . $result->get_error_message();
        }

        set_transient('in_synced_last_log_' . get_current_user_id(), implode("\n", $log), HOUR_IN_SECONDS);
        $this->redirect_admin(['profile' => $profile_key]);
    }

    public function ajax_preview(): void
    {
        $this->require_ajax_action();

        $profile_key = isset($_POST['profile_key']) ? sanitize_key(wp_unslash($_POST['profile_key'])) : 'default';
        $preview = $this->build_sync_preview($profile_key, $this->runtime_overrides_from_request());

        wp_send_json_success($preview);
    }

    public function ajax_compare(): void
    {
        $this->require_ajax_action();

        $profile_key = isset($_POST['profile_key']) ? sanitize_key(wp_unslash($_POST['profile_key'])) : 'default';
        $compare = $this->compare_content($profile_key, $this->runtime_overrides_from_request());

        if (is_wp_error($compare)) {
            wp_send_json_error(['message' => $compare->get_error_message()]);
        }

        wp_send_json_success($compare);
    }

    public function ajax_run(): void
    {
        $this->require_ajax_action();
        @set_time_limit(0);

        $profile_key = isset($_POST['profile_key']) ? sanitize_key(wp_unslash($_POST['profile_key'])) : 'default';
        $run_id = isset($_POST['run_id']) ? sanitize_key(wp_unslash($_POST['run_id'])) : wp_generate_uuid4();
        $log = [];

        $this->update_progress($run_id, [
            'status' => 'running',
            'phase' => 'starting',
            'message' => 'Starting sync.',
            'percent' => 1,
            'log' => [],
        ]);

        $result = $this->run_sync($profile_key, $log, $this->runtime_overrides_from_request(), $run_id);

        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
                'progress' => $this->get_progress($run_id),
            ]);
        }

        wp_send_json_success([
            'message' => 'Sync completed.',
            'progress' => $this->get_progress($run_id),
        ]);
    }

    public function ajax_progress(): void
    {
        $this->require_ajax_action();

        $run_id = isset($_POST['run_id']) ? sanitize_key(wp_unslash($_POST['run_id'])) : '';
        wp_send_json_success($this->get_progress($run_id));
    }

    public function build_sync_preview(string $profile_key = 'default', array $overrides = []): array
    {
        global $wpdb;

        $profile = $this->apply_runtime_overrides($this->get_profile($profile_key), $overrides);
        $settings = $this->get_settings();
        $last_runs = (array) get_option(self::LAST_RUNS_OPTION, []);
        $since = $this->file_since_timestamp($profile_key, $profile);
        $preview = [
            'profile_name' => (string) ($profile['name'] ?? $profile_key),
            'scope' => [
                'database' => !empty($profile['sync_database']),
                'uploads' => !empty($profile['sync_media']),
                'theme' => !empty($profile['sync_theme']),
            ],
            'connection' => [
                'remote_url' => $this->normalize_remote_base_url((string) ($settings['remote_url'] ?? '')),
                'local_url' => (string) ($settings['local_url'] ?? home_url()),
                'remote_public_url' => (string) ($settings['remote_public_url'] ?? ''),
                'secret_fingerprint' => $this->secret_fingerprint($settings),
                'paired' => $this->get_shared_secret($settings) !== '',
            ],
            'mode' => (($profile['mode'] ?? 'upsert') === 'replace') ? 'replace' : 'upsert',
            'incremental_files' => !empty($profile['incremental_files']),
            'last_success' => !empty($last_runs[$profile_key]) ? gmdate('c', (int) $last_runs[$profile_key]) : '',
            'database' => [
                'tables' => [],
                'table_count' => 0,
                'row_count' => 0,
                'scope' => (string) ($profile['database_scope'] ?? 'tables'),
                'post_types' => [],
            ],
            'uploads' => [
                'file_count' => 0,
                'bytes' => 0,
                'sample' => [],
                'since' => $since ? gmdate('c', $since) : '',
            ],
            'theme' => [
                'theme_slug' => $this->preview_file_roots_label($profile),
                'file_count' => 0,
                'bytes' => 0,
                'sample' => [],
                'since' => $since ? gmdate('c', $since) : '',
            ],
            'warnings' => [],
        ];

        if (empty($settings['remote_url'])) {
            $preview['warnings'][] = 'Remote URL is not configured.';
        }

        if (!$this->get_shared_secret($settings)) {
            $preview['warnings'][] = 'Shared secret is missing or shorter than 32 characters.';
        }

        if (!empty($profile['sync_database']) && (($profile['database_scope'] ?? 'tables') === 'post_types')) {
            $post_types = $this->selected_post_types($profile, false);
            $preview['database']['post_types'] = $post_types;
            if (!$post_types) {
                $preview['warnings'][] = 'Post type repair needs at least one post type in the profile.';
            } else {
                foreach ($this->content_table_queries($post_types) as $table => $query) {
                    $rows = (int) $wpdb->get_var('SELECT COUNT(*) FROM `' . $this->escape_identifier($table) . '` WHERE ' . $query['where']);
                    $preview['database']['tables'][] = [
                        'name' => $table,
                        'label' => $this->table_suffix($table),
                        'rows' => $rows,
                    ];
                    $preview['database']['row_count'] += $rows;
                }
                $preview['database']['table_count'] = count($preview['database']['tables']);
            }
        } elseif (!empty($profile['sync_database'])) {
            $tables = $this->select_tables($profile);
            foreach ($tables as $table) {
                $rows = (int) $wpdb->get_var('SELECT COUNT(*) FROM `' . $this->escape_identifier($table) . '`');
                $preview['database']['tables'][] = [
                    'name' => $table,
                    'label' => $this->table_suffix($table),
                    'rows' => $rows,
                ];
                $preview['database']['row_count'] += $rows;
            }
            $preview['database']['table_count'] = count($preview['database']['tables']);
        }

        if (!empty($profile['sync_media'])) {
            $uploads = wp_get_upload_dir();
            if (!empty($uploads['basedir']) && is_dir($uploads['basedir'])) {
                $preview['uploads'] = array_merge(
                    $preview['uploads'],
                    $this->summarize_files(
                        $uploads['basedir'],
                        $this->line_list($profile['upload_excludes'] ?? ''),
                        $since,
                        $this->line_list($profile['upload_includes'] ?? '')
                    )
                );
            } else {
                $preview['warnings'][] = 'Uploads directory was not found.';
            }
        }

        if (!empty($profile['sync_theme'])) {
            $roots = $this->select_file_roots($profile);
            $excludes = $this->line_list($profile['file_excludes'] ?? $profile['theme_excludes'] ?? '');

            if (!$roots) {
                $preview['warnings'][] = 'No wp-content file roots are configured.';
            }

            foreach ($roots as $root) {
                $base = trailingslashit(WP_CONTENT_DIR) . $root;
                if (!is_dir($base)) {
                    $preview['warnings'][] = 'File root was not found: wp-content/' . $root;
                    continue;
                }

                $summary = $this->summarize_files($base, $excludes, $since);
                $preview['theme']['file_count'] += $summary['file_count'];
                $preview['theme']['bytes'] += $summary['bytes'];
                foreach ($summary['sample'] as $sample) {
                    if (count($preview['theme']['sample']) >= 24) {
                        break;
                    }
                    $sample['path'] = $root . '/' . $sample['path'];
                    $preview['theme']['sample'][] = $sample;
                }
            }
            $preview['theme']['bytes_label'] = size_format((int) $preview['theme']['bytes'], 1);
        }

        return $preview;
    }

    public function compare_content(string $profile_key = 'default', array $overrides = [])
    {
        $profile = $this->apply_runtime_overrides($this->get_profile($profile_key), $overrides);
        $post_types = $this->selected_post_types($profile, true);
        $local = $this->build_content_inventory($profile, $post_types);
        $log = [];
        $remote = $this->remote_request('compare-content', [
            'post_types' => $post_types,
            'tables' => array_map(function ($table) {
                return $this->table_suffix($table);
            }, $this->select_tables($profile)),
        ], $log, 60);

        if (is_wp_error($remote)) {
            return $remote;
        }

        return $this->merge_content_inventory($local, is_array($remote) ? $remote : []);
    }

    public function run_sync(string $profile_key = 'default', array &$log = [], array $overrides = [], string $run_id = '')
    {
        @set_time_limit(0);

        $profile = $this->apply_runtime_overrides($this->get_profile($profile_key), $overrides);
        $started_at = time();
        $log[] = 'Starting profile "' . ($profile['name'] ?? $profile_key) . '" at ' . gmdate('c', $started_at);
        $this->update_progress($run_id, [
            'status' => 'running',
            'phase' => 'connecting',
            'message' => 'Testing the remote connection.',
            'percent' => 2,
            'log' => $log,
        ]);

        $ping = $this->remote_request('ping', ['site' => home_url()], $log);
        if (is_wp_error($ping)) {
            $this->update_progress($run_id, [
                'status' => 'failed',
                'phase' => 'connecting',
                'message' => $ping->get_error_message(),
                'percent' => 100,
                'log' => $log,
            ]);
            return $ping;
        }

        if (!empty($profile['sync_database'])) {
            $result = (($profile['database_scope'] ?? 'tables') === 'post_types')
                ? $this->push_post_type_content($profile, $log, $run_id)
                : $this->push_database($profile, $log, $run_id);
            if (is_wp_error($result)) {
                $this->update_progress($run_id, [
                    'status' => 'failed',
                    'message' => $result->get_error_message(),
                    'percent' => 100,
                    'log' => $log,
                ]);
                return $result;
            }
        }

        if (!empty($profile['sync_media'])) {
            $result = $this->push_uploads($profile_key, $profile, $log, $run_id);
            if (is_wp_error($result)) {
                $this->update_progress($run_id, [
                    'status' => 'failed',
                    'message' => $result->get_error_message(),
                    'percent' => 100,
                    'log' => $log,
                ]);
                return $result;
            }
        }

        if (!empty($profile['sync_theme'])) {
            $result = $this->push_theme($profile_key, $profile, $log, $run_id);
            if (is_wp_error($result)) {
                $this->update_progress($run_id, [
                    'status' => 'failed',
                    'message' => $result->get_error_message(),
                    'percent' => 100,
                    'log' => $log,
                ]);
                return $result;
            }
        }

        $last_runs = (array) get_option(self::LAST_RUNS_OPTION, []);
        $last_runs[$profile_key] = $started_at;
        update_option(self::LAST_RUNS_OPTION, $last_runs, false);

        $log[] = 'Sync completed at ' . gmdate('c');
        $this->update_progress($run_id, [
            'status' => 'complete',
            'phase' => 'complete',
            'message' => 'Sync completed.',
            'percent' => 100,
            'log' => $log,
        ]);

        return true;
    }

    private function push_database(array $profile, array &$log, string $run_id = '')
    {
        global $wpdb;

        $settings = $this->get_settings();
        $tables = $this->select_tables($profile);
        $manifest = [];

        foreach ($tables as $table) {
            $target = $this->map_table_name($table, $wpdb->prefix, '__REMOTE_PREFIX__');
            $manifest[] = [
                'source' => $table,
                'target_template' => $target,
                'schema' => $this->get_create_table_sql($table),
            ];
        }

        $mode = (($profile['mode'] ?? 'upsert') === 'replace') ? 'replace' : 'upsert';
        $log[] = 'Database: preparing ' . count($manifest) . ' table(s) in ' . $mode . ' mode.';
        $this->update_progress($run_id, [
            'status' => 'running',
            'phase' => 'database',
            'message' => 'Preparing ' . count($manifest) . ' database table(s).',
            'percent' => 8,
            'log' => $log,
        ]);

        $begin = $this->remote_request('begin-db', [
            'mode' => $mode,
            'source_prefix' => $wpdb->prefix,
            'tables' => $manifest,
        ], $log, 120);

        if (is_wp_error($begin)) {
            return $begin;
        }

        $replacements = $this->replacement_pairs($settings);
        $batch_size = max(10, min(1000, (int) ($profile['batch_size'] ?? 100)));
        $total_rows = 0;
        $table_counts = [];

        foreach ($tables as $table) {
            $table_counts[$table] = (int) $wpdb->get_var('SELECT COUNT(*) FROM `' . $this->escape_identifier($table) . '`');
            $total_rows += $table_counts[$table];
        }

        $sent_rows = 0;

        foreach ($tables as $table) {
            $count = (int) $table_counts[$table];
            $log[] = 'Database: sending ' . $table . ' (' . $count . ' rows).';
            $this->update_progress($run_id, [
                'status' => 'running',
                'phase' => 'database',
                'message' => 'Sending ' . $this->table_suffix($table) . ' (' . $count . ' rows).',
                'percent' => $this->scale_percent($sent_rows, max(1, $total_rows), 10, 55),
                'log' => $log,
            ]);

            for ($offset = 0; $offset < $count; $offset += $batch_size) {
                $rows = $wpdb->get_results(
                    'SELECT * FROM `' . $this->escape_identifier($table) . '` LIMIT ' . (int) $offset . ', ' . (int) $batch_size,
                    ARRAY_A
                );
                $rows = $this->transform_rows_for_remote($table, $rows, $replacements);

                $response = $this->remote_request('table-batch', [
                    'mode' => $mode,
                    'source_prefix' => $wpdb->prefix,
                    'table' => $table,
                    'rows' => $rows,
                ], $log, 120);

                if (is_wp_error($response)) {
                    return $response;
                }

                $sent_rows += count($rows);
                $this->update_progress($run_id, [
                    'status' => 'running',
                    'phase' => 'database',
                    'message' => 'Sent ' . number_format_i18n($sent_rows) . ' of ' . number_format_i18n($total_rows) . ' database row(s).',
                    'percent' => $this->scale_percent($sent_rows, max(1, $total_rows), 10, 55),
                    'log' => $log,
                ]);
            }
        }

        $end = $this->remote_request('end-db', [], $log, 120);
        if (is_wp_error($end)) {
            return $end;
        }

        $log[] = 'Database: complete.';
        $this->update_progress($run_id, [
            'status' => 'running',
            'phase' => 'database',
            'message' => 'Database sync complete.',
            'percent' => 58,
            'log' => $log,
        ]);

        return true;
    }

    private function push_post_type_content(array $profile, array &$log, string $run_id = '')
    {
        global $wpdb;

        $settings = $this->get_settings();
        $post_types = $this->selected_post_types($profile, false);
        if (!$post_types) {
            return new WP_Error('in_synced_no_post_types', 'Post type repair requires at least one post type in the profile.');
        }

        $tables = $this->content_table_queries($post_types);
        $manifest = [];
        foreach ($tables as $table => $query) {
            $manifest[] = [
                'source' => $table,
                'target_template' => $this->map_table_name($table, $wpdb->prefix, '__REMOTE_PREFIX__'),
                'schema' => $this->get_create_table_sql($table),
            ];
        }

        $mode = (($profile['mode'] ?? 'upsert') === 'replace') ? 'replace' : 'upsert';
        $log[] = 'Content repair: preparing post types ' . implode(', ', $post_types) . ' in ' . $mode . ' mode.';
        $this->update_progress($run_id, [
            'status' => 'running',
            'phase' => 'database',
            'message' => 'Preparing post type repair for ' . implode(', ', $post_types) . '.',
            'percent' => 8,
            'log' => $log,
        ]);

        $begin = $this->remote_request('begin-content', [
            'mode' => $mode,
            'source_prefix' => $wpdb->prefix,
            'post_types' => $post_types,
            'tables' => $manifest,
        ], $log, 120);

        if (is_wp_error($begin)) {
            return $begin;
        }

        $replacements = $this->replacement_pairs($settings);
        $batch_size = max(10, min(1000, (int) ($profile['batch_size'] ?? 100)));
        $table_counts = [];
        $total_rows = 0;

        foreach ($tables as $table => $query) {
            $count = (int) $wpdb->get_var('SELECT COUNT(*) FROM `' . $this->escape_identifier($table) . '` WHERE ' . $query['where']);
            $table_counts[$table] = $count;
            $total_rows += $count;
        }

        $sent_rows = 0;
        foreach ($tables as $table => $query) {
            $count = (int) $table_counts[$table];
            $log[] = 'Content repair: sending ' . $this->table_suffix($table) . ' (' . $count . ' rows).';

            for ($offset = 0; $offset < $count; $offset += $batch_size) {
                $rows = $wpdb->get_results(
                    'SELECT * FROM `' . $this->escape_identifier($table) . '` WHERE ' . $query['where'] . ' LIMIT ' . (int) $offset . ', ' . (int) $batch_size,
                    ARRAY_A
                );
                $rows = $this->transform_rows_for_remote($table, $rows, $replacements);

                $response = $this->remote_request('table-batch', [
                    'mode' => $mode,
                    'source_prefix' => $wpdb->prefix,
                    'table' => $table,
                    'rows' => $rows,
                ], $log, 120);

                if (is_wp_error($response)) {
                    return $response;
                }

                $sent_rows += count($rows);
                $this->update_progress($run_id, [
                    'status' => 'running',
                    'phase' => 'database',
                    'message' => 'Sent ' . number_format_i18n($sent_rows) . ' of ' . number_format_i18n($total_rows) . ' repair row(s).',
                    'percent' => $this->scale_percent($sent_rows, max(1, $total_rows), 10, 55),
                    'log' => $log,
                ]);
            }
        }

        $end = $this->remote_request('end-db', [], $log, 120);
        if (is_wp_error($end)) {
            return $end;
        }

        $log[] = 'Content repair: complete.';
        $this->update_progress($run_id, [
            'status' => 'running',
            'phase' => 'database',
            'message' => 'Post type repair complete.',
            'percent' => 58,
            'log' => $log,
        ]);

        return true;
    }

    private function push_uploads(string $profile_key, array $profile, array &$log, string $run_id = '')
    {
        $uploads = wp_get_upload_dir();
        if (empty($uploads['basedir']) || !is_dir($uploads['basedir'])) {
            $log[] = 'Uploads: no upload directory found.';
            return true;
        }

        $since = $this->file_since_timestamp($profile_key, $profile);
        $files = $this->list_files(
            $uploads['basedir'],
            $this->line_list($profile['upload_excludes'] ?? ''),
            $since,
            $this->line_list($profile['upload_includes'] ?? '')
        );
        $log[] = 'Uploads: sending ' . count($files) . ' file(s)' . ($since ? ' changed since ' . gmdate('c', $since) : '') . '.';
        $this->update_progress($run_id, [
            'status' => 'running',
            'phase' => 'uploads',
            'message' => 'Preparing ' . count($files) . ' upload file(s).',
            'percent' => 60,
            'log' => $log,
        ]);

        $sent = 0;
        $total = count($files);
        foreach ($files as $file) {
            $relative = $this->relative_path($uploads['basedir'], $file);
            $result = $this->send_file('uploads', '', $relative, $file, $log);
            if (is_wp_error($result)) {
                return $result;
            }
            $sent++;
            $this->update_progress($run_id, [
                'status' => 'running',
                'phase' => 'uploads',
                'message' => 'Sent upload file ' . $sent . ' of ' . $total . ': ' . $relative,
                'percent' => $this->scale_percent($sent, max(1, $total), 60, 78),
                'log' => $log,
            ]);
        }

        return true;
    }

    private function push_theme(string $profile_key, array $profile, array &$log, string $run_id = '')
    {
        $roots = $this->select_file_roots($profile);
        if (!$roots) {
            return new WP_Error('in_synced_file_roots_missing', 'No wp-content file roots are configured.');
        }

        $excludes = $this->line_list($profile['file_excludes'] ?? $profile['theme_excludes'] ?? '');
        $since = $this->file_since_timestamp($profile_key, $profile);
        $batches = [];
        $total = 0;

        foreach ($roots as $root) {
            $base = trailingslashit(WP_CONTENT_DIR) . $root;
            if (!is_dir($base)) {
                return new WP_Error('in_synced_file_root_missing', 'File root not found: wp-content/' . $root);
            }
            $files = $this->list_files($base, $excludes, $since);
            $batches[] = [
                'root' => $root,
                'base' => $base,
                'files' => $files,
            ];
            $total += count($files);
        }

        $log[] = 'Files: sending ' . count($roots) . ' root(s), ' . $total . ' file(s)' . ($since ? ' changed since ' . gmdate('c', $since) : '') . '.';
        $this->update_progress($run_id, [
            'status' => 'running',
            'phase' => 'theme',
            'message' => 'Preparing ' . $total . ' wp-content file(s).',
            'percent' => 80,
            'log' => $log,
        ]);

        $sent = 0;
        foreach ($batches as $batch) {
            foreach ($batch['files'] as $file) {
                $relative = $this->relative_path($batch['base'], $file);
                $result = $this->send_file('content', $batch['root'], $relative, $file, $log);
                if (is_wp_error($result)) {
                    return $result;
                }
                $sent++;
                $this->update_progress($run_id, [
                    'status' => 'running',
                    'phase' => 'theme',
                    'message' => 'Sent wp-content file ' . $sent . ' of ' . $total . ': ' . $batch['root'] . '/' . $relative,
                    'percent' => $this->scale_percent($sent, max(1, $total), 80, 96),
                    'log' => $log,
                ]);
            }
        }

        return true;
    }

    private function send_file(string $target, string $content_root, string $relative, string $file, array &$log)
    {
        $size = filesize($file);
        $hash = hash_file('sha256', $file);
        $mtime = filemtime($file) ?: time();
        $handle = fopen($file, 'rb');

        if (!$handle) {
            return new WP_Error('in_synced_file_read', 'Could not read file: ' . $file);
        }

        $offset = 0;
        do {
            $data = fread($handle, self::FILE_CHUNK_SIZE);
            if ($data === false) {
                fclose($handle);
                return new WP_Error('in_synced_file_read_chunk', 'Could not read file chunk: ' . $file);
            }

            $final = feof($handle);
            $response = $this->remote_request('file-chunk', [
                'target' => $target,
                'theme_slug' => $content_root,
                'content_root' => $content_root,
                'relative_path' => $relative,
                'offset' => $offset,
                'total_size' => $size,
                'sha256' => $hash,
                'mtime' => $mtime,
                'final' => $final,
                'data' => base64_encode($data),
            ], $log, 120);

            if (is_wp_error($response)) {
                fclose($handle);
                return $response;
            }

            $offset += strlen($data);
        } while (!$final);

        fclose($handle);
        $log[] = 'File: ' . ($content_root ? $content_root . '/' : $target . '/') . $relative;

        return true;
    }

    public function rest_ping(WP_REST_Request $request): WP_REST_Response
    {
        $settings = $this->get_settings();

        return rest_ensure_response([
            'ok' => true,
            'site_url' => home_url(),
            'allow_db_receive' => (bool) $settings['allow_db_receive'],
            'allow_media_receive' => (bool) $settings['allow_media_receive'],
            'allow_theme_receive' => (bool) ($settings['allow_theme_receive'] || $settings['allow_file_receive']),
            'allow_file_receive' => (bool) ($settings['allow_file_receive'] || $settings['allow_theme_receive']),
            'theme_slug' => $settings['theme_slug'],
            'allowed_file_roots' => $settings['allowed_file_roots'] ?? '',
        ]);
    }

    public function rest_begin_db(WP_REST_Request $request)
    {
        global $wpdb;

        $settings = $this->get_settings();
        if (empty($settings['allow_db_receive'])) {
            return new WP_Error('in_synced_db_receive_disabled', 'Database receiving is disabled on this site.', ['status' => 403]);
        }

        $payload = $request->get_json_params();
        $mode = (($payload['mode'] ?? 'upsert') === 'replace') ? 'replace' : 'upsert';
        $source_prefix = (string) ($payload['source_prefix'] ?? '');
        $tables = is_array($payload['tables'] ?? null) ? $payload['tables'] : [];
        $prepared = [];

        foreach ($tables as $table) {
            $source = (string) ($table['source'] ?? '');
            $target = $this->map_table_name($source, $source_prefix, $wpdb->prefix);

            if (!$this->is_allowed_table($target)) {
                return new WP_Error('in_synced_bad_table', 'Table is outside the WordPress prefix: ' . $target, ['status' => 400]);
            }

            if (!$this->table_exists($target)) {
                $schema = $this->rewrite_create_table_sql((string) ($table['schema'] ?? ''), $source, $target);
                if (!$schema || $wpdb->query($schema) === false) {
                    return new WP_Error('in_synced_create_table_failed', 'Could not create table: ' . $target, ['status' => 500]);
                }
            }

            $prepared[] = $target;
        }

        $backup = '';
        if ($mode === 'replace' && $prepared) {
            $backup = $this->backup_tables($prepared);

            foreach ($prepared as $target) {
                $preserved = $this->preserve_table_rows($target);
                $wpdb->query('TRUNCATE TABLE `' . $this->escape_identifier($target) . '`');
                $this->restore_preserved_rows($target, $preserved);
            }
        }

        return rest_ensure_response([
            'ok' => true,
            'mode' => $mode,
            'tables' => $prepared,
            'backup' => $backup,
        ]);
    }

    public function rest_begin_content(WP_REST_Request $request)
    {
        global $wpdb;

        $settings = $this->get_settings();
        if (empty($settings['allow_db_receive'])) {
            return new WP_Error('in_synced_db_receive_disabled', 'Database receiving is disabled on this site.', ['status' => 403]);
        }

        $payload = $request->get_json_params();
        $mode = (($payload['mode'] ?? 'upsert') === 'replace') ? 'replace' : 'upsert';
        $source_prefix = (string) ($payload['source_prefix'] ?? '');
        $post_types = $this->sanitize_post_types($payload['post_types'] ?? []);
        $tables = is_array($payload['tables'] ?? null) ? $payload['tables'] : [];
        $prepared = [];

        if (!$post_types) {
            return new WP_Error('in_synced_missing_post_types', 'No post types were provided for content repair.', ['status' => 400]);
        }

        foreach ($tables as $table) {
            $source = (string) ($table['source'] ?? '');
            $target = $this->map_table_name($source, $source_prefix, $wpdb->prefix);

            if (!$this->is_allowed_table($target)) {
                return new WP_Error('in_synced_bad_table', 'Table is outside the WordPress prefix: ' . $target, ['status' => 400]);
            }

            if (!$this->table_exists($target)) {
                $schema = $this->rewrite_create_table_sql((string) ($table['schema'] ?? ''), $source, $target);
                if (!$schema || $wpdb->query($schema) === false) {
                    return new WP_Error('in_synced_create_table_failed', 'Could not create table: ' . $target, ['status' => 500]);
                }
            }

            $prepared[] = $target;
        }

        $backup = '';
        if ($mode === 'replace') {
            $backup = $this->backup_tables(array_values(array_intersect($prepared, [
                $wpdb->posts,
                $wpdb->postmeta,
                $wpdb->comments,
                $wpdb->commentmeta,
                $wpdb->term_relationships,
            ])));
            $this->delete_remote_post_type_rows($post_types);
        }

        return rest_ensure_response([
            'ok' => true,
            'mode' => $mode,
            'post_types' => $post_types,
            'tables' => $prepared,
            'backup' => $backup,
        ]);
    }

    public function rest_compare_content(WP_REST_Request $request): WP_REST_Response
    {
        $payload = $request->get_json_params();
        $post_types = $this->sanitize_post_types($payload['post_types'] ?? []);
        $tables = $this->sanitize_table_suffixes($payload['tables'] ?? []);

        return rest_ensure_response($this->build_content_inventory([], $post_types, $tables));
    }

    public function rest_table_batch(WP_REST_Request $request)
    {
        global $wpdb;

        $settings = $this->get_settings();
        if (empty($settings['allow_db_receive'])) {
            return new WP_Error('in_synced_db_receive_disabled', 'Database receiving is disabled on this site.', ['status' => 403]);
        }

        $payload = $request->get_json_params();
        $source_prefix = (string) ($payload['source_prefix'] ?? '');
        $source_table = (string) ($payload['table'] ?? '');
        $target = $this->map_table_name($source_table, $source_prefix, $wpdb->prefix);
        $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];

        if (!$this->is_allowed_table($target)) {
            return new WP_Error('in_synced_bad_table', 'Table is outside the WordPress prefix: ' . $target, ['status' => 400]);
        }

        if (!$this->table_exists($target)) {
            return new WP_Error('in_synced_missing_table', 'Table has not been prepared: ' . $target, ['status' => 400]);
        }

        $inserted = 0;
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            if ($this->is_sync_option_row($target, $row)) {
                continue;
            }

            if ($target === $wpdb->options) {
                unset($row['option_id']);
            }

            $result = $wpdb->replace($target, $row);
            if ($result === false) {
                return new WP_Error('in_synced_insert_failed', 'Failed inserting into ' . $target . ': ' . $wpdb->last_error, ['status' => 500]);
            }
            $inserted++;
        }

        return rest_ensure_response([
            'ok' => true,
            'table' => $target,
            'inserted' => $inserted,
        ]);
    }

    public function rest_end_db(WP_REST_Request $request): WP_REST_Response
    {
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        return rest_ensure_response(['ok' => true]);
    }

    public function rest_file_chunk(WP_REST_Request $request)
    {
        $settings = $this->get_settings();
        $payload = $request->get_json_params();
        $target = sanitize_key($payload['target'] ?? '');

        if ($target === 'uploads' && empty($settings['allow_media_receive'])) {
            return new WP_Error('in_synced_media_receive_disabled', 'Media receiving is disabled on this site.', ['status' => 403]);
        }

        if (in_array($target, ['theme', 'content'], true) && empty($settings['allow_theme_receive']) && empty($settings['allow_file_receive'])) {
            return new WP_Error('in_synced_theme_receive_disabled', 'File receiving is disabled on this site.', ['status' => 403]);
        }

        $relative = $this->sanitize_relative_path($payload['relative_path'] ?? '');
        if (!$relative) {
            return new WP_Error('in_synced_bad_path', 'Invalid relative file path.', ['status' => 400]);
        }

        $root = '';
        if ($target === 'uploads') {
            $uploads = wp_get_upload_dir();
            $root = $uploads['basedir'];
        } elseif ($target === 'theme') {
            $theme_slug = $this->sanitize_theme_slug($payload['theme_slug'] ?? '');
            if (!$theme_slug || $theme_slug !== $settings['theme_slug']) {
                return new WP_Error('in_synced_theme_not_allowed', 'Theme directory is not allowed on this receiver.', ['status' => 403]);
            }
            $root = trailingslashit(get_theme_root()) . $theme_slug;
        } elseif ($target === 'content') {
            $content_root = $this->sanitize_relative_path($payload['content_root'] ?? $payload['theme_slug'] ?? '');
            if (!$content_root || !$this->file_root_is_allowed($content_root, $settings)) {
                return new WP_Error('in_synced_file_root_not_allowed', 'File root is not allowed on this receiver: ' . $content_root, ['status' => 403]);
            }
            $root = trailingslashit(WP_CONTENT_DIR) . $content_root;
        } else {
            return new WP_Error('in_synced_bad_target', 'Invalid file target.', ['status' => 400]);
        }

        if (!is_dir($root) && !wp_mkdir_p($root)) {
            return new WP_Error('in_synced_root_create_failed', 'Could not create target root.', ['status' => 500]);
        }

        $destination = trailingslashit($root) . $relative;
        $directory = dirname($destination);
        if (!is_dir($directory) && !wp_mkdir_p($directory)) {
            return new WP_Error('in_synced_dir_create_failed', 'Could not create target directory.', ['status' => 500]);
        }

        $offset = max(0, (int) ($payload['offset'] ?? 0));
        $data = base64_decode((string) ($payload['data'] ?? ''), true);
        if ($data === false) {
            return new WP_Error('in_synced_bad_chunk', 'File chunk is not valid base64.', ['status' => 400]);
        }

        $handle = fopen($destination, $offset === 0 ? 'wb' : 'c+b');
        if (!$handle) {
            return new WP_Error('in_synced_file_write_open', 'Could not open target file for writing.', ['status' => 500]);
        }

        if ($offset > 0) {
            fseek($handle, $offset);
        }

        fwrite($handle, $data);
        fclose($handle);

        if (!empty($payload['final'])) {
            $expected_size = (int) ($payload['total_size'] ?? 0);
            $expected_hash = (string) ($payload['sha256'] ?? '');
            clearstatcache(true, $destination);

            if ($expected_size !== filesize($destination)) {
                return new WP_Error('in_synced_size_mismatch', 'File size did not match after upload: ' . $relative, ['status' => 500]);
            }

            if ($expected_hash && !hash_equals($expected_hash, hash_file('sha256', $destination))) {
                return new WP_Error('in_synced_hash_mismatch', 'File hash did not match after upload: ' . $relative, ['status' => 500]);
            }

            if (!empty($payload['mtime'])) {
                touch($destination, (int) $payload['mtime']);
            }
        }

        return rest_ensure_response(['ok' => true]);
    }

    private function remote_request(string $route, array $payload, array &$log, int $timeout = 45)
    {
        $settings = $this->get_settings();
        $remote_url = $this->normalize_remote_base_url((string) $settings['remote_url']);
        $secret = $this->get_shared_secret($settings);

        if (!$remote_url) {
            return new WP_Error('in_synced_missing_remote', 'Remote URL is not configured.');
        }

        if (!$secret) {
            return new WP_Error('in_synced_missing_secret', 'Shared secret is not configured.');
        }

        $rest_route = '/' . self::REST_NAMESPACE . '/' . trim($route, '/');
        $body = wp_json_encode($payload);
        $timestamp = time();
        $base = implode("\n", [
            'POST',
            $rest_route,
            (string) $timestamp,
            hash('sha256', $body),
        ]);

        $url = trailingslashit($remote_url) . 'wp-json/' . self::REST_NAMESPACE . '/' . trim($route, '/');
        $response = wp_remote_post($url, [
            'timeout' => $timeout,
            'redirection' => 3,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-In-Synced-Timestamp' => (string) $timestamp,
                'X-In-Synced-Nonce' => wp_generate_uuid4(),
                'X-In-Synced-Signature' => 'sha256=' . hash_hmac('sha256', $base, $secret),
            ],
            'body' => $body,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $raw = (string) wp_remote_retrieve_body($response);
        $decoded = json_decode($raw, true);

        if ($code < 200 || $code >= 300) {
            $message = is_array($decoded) && isset($decoded['message']) ? $decoded['message'] : trim($raw);
            return new WP_Error('in_synced_remote_error', 'Remote ' . $route . ' failed (' . $code . '): ' . $message);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function require_admin_action(string $nonce_action): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage In Synced.', 'in-synced'));
        }

        check_admin_referer($nonce_action);
    }

    private function require_ajax_action(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'You do not have permission to manage In Synced.'], 403);
        }

        check_ajax_referer('in_synced_ajax', 'nonce');
    }

    private function runtime_overrides_from_request(): array
    {
        $scope = isset($_POST['scope']) ? sanitize_key(wp_unslash($_POST['scope'])) : '';

        return [
            'scope' => $scope,
            'mode' => isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : '',
            'sync_database' => isset($_POST['sync_database']) ? (bool) absint($_POST['sync_database']) : null,
            'sync_media' => isset($_POST['sync_media']) ? (bool) absint($_POST['sync_media']) : null,
            'sync_theme' => isset($_POST['sync_theme']) ? (bool) absint($_POST['sync_theme']) : null,
            'incremental_files' => isset($_POST['incremental_files']) ? (bool) absint($_POST['incremental_files']) : null,
        ];
    }

    private function apply_runtime_overrides(array $profile, array $overrides): array
    {
        $scope = isset($overrides['scope']) ? sanitize_key((string) $overrides['scope']) : '';

        if ($scope) {
            $profile['sync_database'] = in_array($scope, ['full', 'database', 'content', 'content_media'], true);
            $profile['sync_media'] = in_array($scope, ['full', 'media', 'files', 'content_media'], true);
            $profile['sync_theme'] = in_array($scope, ['full', 'theme', 'files'], true);
        }

        foreach (['sync_database', 'sync_media', 'sync_theme', 'incremental_files'] as $key) {
            if (array_key_exists($key, $overrides) && $overrides[$key] !== null) {
                $profile[$key] = (bool) $overrides[$key];
            }
        }

        if (!empty($overrides['database_scope'])) {
            $profile['database_scope'] = $overrides['database_scope'] === 'post_types' ? 'post_types' : 'tables';
        }

        if (!empty($overrides['post_type_filter'])) {
            $profile['post_type_filter'] = implode("\n", $this->sanitize_post_types($overrides['post_type_filter']));
        }

        if (!empty($overrides['mode'])) {
            $profile['mode'] = $overrides['mode'] === 'replace' ? 'replace' : 'upsert';
        }

        return $profile;
    }

    private function get_progress(string $run_id): array
    {
        if (!$run_id) {
            return [
                'status' => 'idle',
                'phase' => '',
                'message' => '',
                'percent' => 0,
                'log' => [],
            ];
        }

        $progress = get_transient(self::PROGRESS_TRANSIENT_PREFIX . $run_id);

        return is_array($progress) ? $progress : [
            'status' => 'idle',
            'phase' => '',
            'message' => 'No progress has been recorded for this run.',
            'percent' => 0,
            'log' => [],
        ];
    }

    private function update_progress(string $run_id, array $updates): void
    {
        if (!$run_id) {
            return;
        }

        $progress = $this->get_progress($run_id);
        $progress = array_merge($progress, $updates);
        $progress['updated_at'] = gmdate('c');
        $progress['percent'] = max(0, min(100, (int) ($progress['percent'] ?? 0)));

        if (isset($progress['log']) && is_array($progress['log']) && count($progress['log']) > 200) {
            $progress['log'] = array_slice($progress['log'], -200);
        }

        set_transient(self::PROGRESS_TRANSIENT_PREFIX . $run_id, $progress, self::PROGRESS_TTL);
    }

    private function scale_percent(int $current, int $total, int $start, int $end): int
    {
        if ($total <= 0) {
            return $end;
        }

        $ratio = max(0, min(1, $current / $total));

        return (int) round($start + (($end - $start) * $ratio));
    }

    private function redirect_admin(array $args = []): void
    {
        wp_safe_redirect(add_query_arg($args, admin_url('tools.php?page=in-synced')));
        exit;
    }

    private function sanitize_url_field(string $field): string
    {
        $url = isset($_POST[$field]) ? esc_url_raw(trim((string) wp_unslash($_POST[$field]))) : '';

        return $field === 'remote_url' ? $this->normalize_remote_base_url($url) : $url;
    }

    private function normalize_remote_base_url(string $url): string
    {
        $url = untrailingslashit(trim($url));
        $url = preg_replace('#/(wp-admin|wp-json)(/.*)?$#', '', $url);

        return $url ? esc_url_raw($url) : '';
    }

    private function sanitize_theme_slug($value): string
    {
        $value = trim((string) wp_unslash($value));
        return preg_match('/^[A-Za-z0-9._-]+$/', $value) ? $value : '';
    }

    private function get_shared_secret(array $settings): string
    {
        if (defined('IN_SYNCED_SHARED_SECRET') && IN_SYNCED_SHARED_SECRET) {
            return (string) IN_SYNCED_SHARED_SECRET;
        }

        $secret = (string) ($settings['shared_secret'] ?? '');
        return strlen($secret) >= 32 ? $secret : '';
    }

    private function bool_label($value): string
    {
        return $value ? 'yes' : 'no';
    }

    private function secret_fingerprint(array $settings): string
    {
        $secret = $this->get_shared_secret($settings);

        return $secret ? substr(hash('sha256', $secret), 0, 12) : '';
    }

    private function table_suffix(string $table): string
    {
        global $wpdb;

        if (strpos($table, $wpdb->prefix) === 0) {
            return substr($table, strlen($wpdb->prefix));
        }

        return $table;
    }

    private function summarize_files(string $base, array $excludes = [], int $since = 0, array $includes = []): array
    {
        $files = $this->list_files($base, $excludes, $since, $includes);
        $bytes = 0;
        $sample = [];

        foreach ($files as $file) {
            $size = (int) filesize($file);
            $bytes += $size;

            if (count($sample) < 24) {
                $sample[] = [
                    'path' => $this->relative_path($base, $file),
                    'size' => $size,
                    'size_label' => size_format($size, 1),
                    'modified' => gmdate('c', filemtime($file) ?: time()),
                ];
            }
        }

        return [
            'file_count' => count($files),
            'bytes' => $bytes,
            'bytes_label' => size_format($bytes, 1),
            'sample' => $sample,
        ];
    }

    private function select_tables(array $profile): array
    {
        global $wpdb;

        $like = $wpdb->esc_like($wpdb->prefix) . '%';
        $tables = (array) $wpdb->get_col($wpdb->prepare('SHOW TABLES LIKE %s', $like));
        $included_suffixes = $this->line_list($profile['include_tables'] ?? '');
        $excluded_suffixes = $this->line_list($profile['exclude_tables'] ?? '');

        if (empty($profile['include_users'])) {
            $excluded_suffixes[] = 'users';
            $excluded_suffixes[] = 'usermeta';
        }

        return array_values(array_filter($tables, function ($table) use ($wpdb, $included_suffixes, $excluded_suffixes) {
            $suffix = $this->table_suffix($table);

            if ($included_suffixes && !in_array($suffix, $included_suffixes, true)) {
                return false;
            }

            foreach ($excluded_suffixes as $suffix) {
                if ($table === $wpdb->prefix . $suffix) {
                    return false;
                }
            }

            return true;
        }));
    }

    private function selected_post_types(array $profile, bool $fallback_all = false): array
    {
        global $wpdb;

        $post_types = $this->sanitize_post_types($this->line_list((string) ($profile['post_type_filter'] ?? '')));

        if (!$post_types && $fallback_all) {
            $post_types = (array) $wpdb->get_col("SELECT DISTINCT post_type FROM `" . $this->escape_identifier($wpdb->posts) . "` WHERE post_type NOT IN ('revision', 'nav_menu_item')");
            $post_types = $this->sanitize_post_types($post_types);
        }

        return $post_types;
    }

    private function sanitize_post_types($value): array
    {
        $items = is_array($value) ? $value : $this->line_list((string) $value);
        $clean = [];

        foreach ($items as $item) {
            $item = sanitize_key((string) $item);
            if ($item !== '') {
                $clean[] = $item;
            }
        }

        return array_values(array_unique($clean));
    }

    private function sanitize_table_suffixes($value): array
    {
        $items = is_array($value) ? $value : $this->line_list((string) $value);
        $clean = [];

        foreach ($items as $item) {
            $item = trim((string) $item);
            if ($item !== '' && preg_match('/^[A-Za-z0-9_]+$/', $item)) {
                $clean[] = $item;
            }
        }

        return array_values(array_unique($clean));
    }

    private function build_content_inventory(array $profile = [], array $post_types = [], array $table_suffixes = []): array
    {
        global $wpdb;

        if (!$post_types) {
            $post_types = $this->selected_post_types($profile, true);
        }

        if (!$table_suffixes && $profile) {
            $table_suffixes = array_map(function ($table) {
                return $this->table_suffix($table);
            }, $this->select_tables($profile));
        }

        $inventory = [
            'site_url' => home_url(),
            'post_types' => [],
            'tables' => [],
        ];

        foreach ($post_types as $post_type) {
            $inventory['post_types'][$post_type] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM `" . $this->escape_identifier($wpdb->posts) . "` WHERE post_type = %s",
                $post_type
            ));
        }

        foreach ($table_suffixes as $suffix) {
            $table = $wpdb->prefix . $suffix;
            if (!$this->is_allowed_table($table) || !$this->table_exists($table)) {
                continue;
            }

            $inventory['tables'][$suffix] = (int) $wpdb->get_var('SELECT COUNT(*) FROM `' . $this->escape_identifier($table) . '`');
        }

        return $inventory;
    }

    private function merge_content_inventory(array $local, array $remote): array
    {
        $post_type_keys = array_unique(array_merge(array_keys($local['post_types'] ?? []), array_keys($remote['post_types'] ?? [])));
        sort($post_type_keys);
        $table_keys = array_unique(array_merge(array_keys($local['tables'] ?? []), array_keys($remote['tables'] ?? [])));
        sort($table_keys);

        return [
            'remote_site' => (string) ($remote['site_url'] ?? ''),
            'post_types' => array_map(static function ($key) use ($local, $remote) {
                return [
                    'post_type' => $key,
                    'local' => (int) ($local['post_types'][$key] ?? 0),
                    'remote' => (int) ($remote['post_types'][$key] ?? 0),
                ];
            }, $post_type_keys),
            'tables' => array_map(static function ($key) use ($local, $remote) {
                return [
                    'label' => $key,
                    'local' => (int) ($local['tables'][$key] ?? 0),
                    'remote' => (int) ($remote['tables'][$key] ?? 0),
                ];
            }, $table_keys),
        ];
    }

    private function content_table_queries(array $post_types): array
    {
        global $wpdb;

        $post_ids = $this->selected_post_ids($post_types);
        $post_id_where = $post_ids ? 'ID IN (' . implode(',', array_map('intval', $post_ids)) . ')' : '0=1';
        $object_id_where = $post_ids ? 'object_id IN (' . implode(',', array_map('intval', $post_ids)) . ')' : '0=1';
        $postmeta_where = $post_ids ? 'post_id IN (' . implode(',', array_map('intval', $post_ids)) . ')' : '0=1';
        $comment_where = $post_ids ? 'comment_post_ID IN (' . implode(',', array_map('intval', $post_ids)) . ')' : '0=1';
        $comment_ids = $this->selected_comment_ids($post_ids);
        $commentmeta_where = $comment_ids ? 'comment_id IN (' . implode(',', array_map('intval', $comment_ids)) . ')' : '0=1';

        $queries = [
            $wpdb->posts => ['where' => $post_id_where],
            $wpdb->postmeta => ['where' => $postmeta_where],
            $wpdb->term_relationships => ['where' => $object_id_where],
            $wpdb->comments => ['where' => $comment_where],
            $wpdb->commentmeta => ['where' => $commentmeta_where],
            $wpdb->terms => ['where' => '1=1'],
            $wpdb->term_taxonomy => ['where' => '1=1'],
            $wpdb->termmeta => ['where' => '1=1'],
        ];

        return array_filter($queries, function ($query, $table) {
            return $table && $this->table_exists($table);
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function selected_post_ids(array $post_types): array
    {
        global $wpdb;

        if (!$post_types) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
        return array_map('intval', (array) $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM `" . $this->escape_identifier($wpdb->posts) . "` WHERE post_type IN ($placeholders)",
            $post_types
        )));
    }

    private function selected_comment_ids(array $post_ids): array
    {
        global $wpdb;

        if (!$post_ids) {
            return [];
        }

        return array_map('intval', (array) $wpdb->get_col(
            "SELECT comment_ID FROM `" . $this->escape_identifier($wpdb->comments) . "` WHERE comment_post_ID IN (" . implode(',', array_map('intval', $post_ids)) . ')'
        ));
    }

    private function delete_remote_post_type_rows(array $post_types): void
    {
        global $wpdb;

        $post_ids = $this->selected_post_ids($post_types);
        if (!$post_ids) {
            return;
        }

        $post_id_list = implode(',', array_map('intval', $post_ids));
        $comment_ids = $this->selected_comment_ids($post_ids);
        if ($comment_ids) {
            $wpdb->query("DELETE FROM `" . $this->escape_identifier($wpdb->commentmeta) . "` WHERE comment_id IN (" . implode(',', array_map('intval', $comment_ids)) . ')');
        }

        $wpdb->query("DELETE FROM `" . $this->escape_identifier($wpdb->comments) . "` WHERE comment_post_ID IN ($post_id_list)");
        $wpdb->query("DELETE FROM `" . $this->escape_identifier($wpdb->postmeta) . "` WHERE post_id IN ($post_id_list)");
        $wpdb->query("DELETE FROM `" . $this->escape_identifier($wpdb->term_relationships) . "` WHERE object_id IN ($post_id_list)");
        $wpdb->query("DELETE FROM `" . $this->escape_identifier($wpdb->posts) . "` WHERE ID IN ($post_id_list)");
    }

    private function select_file_roots(array $profile): array
    {
        $roots = $this->line_list((string) ($profile['file_roots'] ?? ''));

        if (!$roots && !empty($profile['theme_slug'])) {
            $roots[] = 'themes/' . $this->sanitize_theme_slug($profile['theme_slug']);
        }

        return $this->sanitize_file_roots($roots);
    }

    private function sanitize_file_roots_text($value): string
    {
        return implode("\n", $this->sanitize_file_roots($this->line_list((string) wp_unslash($value))));
    }

    private function sanitize_file_roots(array $roots): array
    {
        $clean = [];

        foreach ($roots as $root) {
            $root = $this->sanitize_relative_path($root);
            if (!$root) {
                continue;
            }

            $parts = explode('/', $root);
            $top = $parts[0] ?? '';
            if (!in_array($top, ['themes', 'plugins', 'mu-plugins', 'languages'], true)) {
                continue;
            }

            $clean[] = $root;
        }

        return array_values(array_unique($clean));
    }

    private function preview_file_roots_label(array $profile): string
    {
        $roots = $this->select_file_roots($profile);

        return $roots ? implode(', ', array_map(static function ($root) {
            return 'wp-content/' . $root;
        }, $roots)) : 'No file roots configured';
    }

    private function file_root_is_allowed(string $root, array $settings): bool
    {
        $root = $this->sanitize_relative_path($root);
        $allowed = $this->sanitize_file_roots($this->line_list((string) ($settings['allowed_file_roots'] ?? '')));

        if (!$allowed && !empty($settings['theme_slug'])) {
            $allowed[] = 'themes/' . $this->sanitize_theme_slug($settings['theme_slug']);
        }

        return $root && in_array($root, $allowed, true);
    }

    private function line_list(string $text): array
    {
        $items = preg_split('/\R+/', $text) ?: [];
        $items = array_map('trim', $items);
        $items = array_filter($items, static function ($item) {
            return $item !== '';
        });

        return array_values(array_unique($items));
    }

    private function replacement_pairs(array $settings): array
    {
        $local = untrailingslashit((string) ($settings['local_url'] ?: home_url()));
        $remote = untrailingslashit((string) ($settings['remote_public_url'] ?: $settings['remote_url']));

        if (!$local || !$remote || $local === $remote) {
            return [];
        }

        $pairs = [
            $local => $remote,
            trailingslashit($local) => trailingslashit($remote),
            str_replace('/', '\/', $local) => str_replace('/', '\/', $remote),
            str_replace('/', '\/', trailingslashit($local)) => str_replace('/', '\/', trailingslashit($remote)),
        ];

        return array_unique($pairs);
    }

    private function transform_rows_for_remote(string $table, array $rows, array $replacements): array
    {
        global $wpdb;

        $out = [];
        foreach ($rows as $row) {
            if ($this->is_sync_option_row($this->map_table_name($table, $wpdb->prefix, $wpdb->prefix), $row)) {
                continue;
            }

            $new_row = [];
            foreach ($row as $key => $value) {
                $new_row[$key] = is_string($value) ? $this->replace_serialized_value($value, $replacements) : $value;
            }
            $out[] = $new_row;
        }

        return $out;
    }

    private function replace_serialized_value(string $value, array $replacements): string
    {
        if (!$replacements) {
            return $value;
        }

        if (is_serialized($value)) {
            $decoded = maybe_unserialize($value);
            return maybe_serialize($this->replace_recursive($decoded, $replacements));
        }

        return str_replace(array_keys($replacements), array_values($replacements), $value);
    }

    private function replace_recursive($value, array $replacements)
    {
        if (is_string($value)) {
            return str_replace(array_keys($replacements), array_values($replacements), $value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->replace_recursive($item, $replacements);
            }
        } elseif (is_object($value)) {
            foreach (get_object_vars($value) as $key => $item) {
                $value->$key = $this->replace_recursive($item, $replacements);
            }
        }

        return $value;
    }

    private function get_create_table_sql(string $table): string
    {
        global $wpdb;

        $row = $wpdb->get_row('SHOW CREATE TABLE `' . $this->escape_identifier($table) . '`', ARRAY_N);

        return is_array($row) && isset($row[1]) ? (string) $row[1] : '';
    }

    private function rewrite_create_table_sql(string $sql, string $source, string $target): string
    {
        if (!$sql || !$source || !$target) {
            return '';
        }

        $source = $this->escape_identifier($source);
        $target = $this->escape_identifier($target);

        return preg_replace('/^CREATE TABLE `?' . preg_quote($source, '/') . '`?/i', 'CREATE TABLE `' . $target . '`', $sql, 1) ?: '';
    }

    private function map_table_name(string $table, string $source_prefix, string $target_prefix): string
    {
        if ($source_prefix && strpos($table, $source_prefix) === 0) {
            return $target_prefix . substr($table, strlen($source_prefix));
        }

        return $table;
    }

    private function is_allowed_table(string $table): bool
    {
        global $wpdb;

        return (bool) preg_match('/^[A-Za-z0-9_]+$/', $table) && strpos($table, $wpdb->prefix) === 0;
    }

    private function table_exists(string $table): bool
    {
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
    }

    private function backup_tables(array $tables): string
    {
        global $wpdb;

        $uploads = wp_get_upload_dir();
        if (empty($uploads['basedir'])) {
            return '';
        }

        $directory = trailingslashit($uploads['basedir']) . 'in-synced-backups';
        if (!is_dir($directory) && !wp_mkdir_p($directory)) {
            return '';
        }

        $file = trailingslashit($directory) . 'db-' . gmdate('Ymd-His') . '.sql';
        $handle = fopen($file, 'wb');
        if (!$handle) {
            return '';
        }

        fwrite($handle, "-- In Synced backup created " . gmdate('c') . "\n\n");

        foreach ($tables as $table) {
            if (!$this->table_exists($table)) {
                continue;
            }

            $schema = $this->get_create_table_sql($table);
            fwrite($handle, "DROP TABLE IF EXISTS `" . $this->escape_identifier($table) . "`;\n");
            fwrite($handle, $schema . ";\n\n");

            $offset = 0;
            $limit = 500;
            do {
                $rows = $wpdb->get_results(
                    'SELECT * FROM `' . $this->escape_identifier($table) . '` LIMIT ' . (int) $offset . ', ' . (int) $limit,
                    ARRAY_A
                );

                foreach ($rows as $row) {
                    $columns = array_map(function ($column) {
                        return '`' . $this->escape_identifier((string) $column) . '`';
                    }, array_keys($row));
                    $values = array_map([$this, 'sql_literal'], array_values($row));
                    fwrite($handle, 'INSERT INTO `' . $this->escape_identifier($table) . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n");
                }

                $offset += $limit;
            } while (count($rows) === $limit);

            fwrite($handle, "\n");
        }

        fclose($handle);

        return $file;
    }

    private function sql_literal($value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $value = str_replace(
            ["\\", "\0", "\n", "\r", "'", "\x1a"],
            ["\\\\", "\\0", "\\n", "\\r", "\\'", "\\Z"],
            (string) $value
        );

        return "'" . $value . "'";
    }

    private function preserve_table_rows(string $table): array
    {
        global $wpdb;

        if ($table !== $wpdb->options) {
            return [];
        }

        return (array) $wpdb->get_results(
            "SELECT * FROM `" . $this->escape_identifier($table) . "` WHERE option_name LIKE 'in_synced\\_%'",
            ARRAY_A
        );
    }

    private function restore_preserved_rows(string $table, array $rows): void
    {
        global $wpdb;

        foreach ($rows as $row) {
            unset($row['option_id']);
            $wpdb->insert($table, $row);
        }
    }

    private function is_sync_option_row(string $table, array $row): bool
    {
        global $wpdb;

        return $table === $wpdb->options
            && isset($row['option_name'])
            && strpos((string) $row['option_name'], 'in_synced_') === 0;
    }

    private function list_files(string $base, array $excludes = [], int $since = 0, array $includes = []): array
    {
        $files = [];
        $base = rtrim($base, DIRECTORY_SEPARATOR);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $path = $item->getPathname();
            $relative = $this->relative_path($base, $path);

            if ($this->path_is_excluded($relative, $excludes)) {
                if ($item->isDir()) {
                    $iterator->next();
                }
                continue;
            }

            if (!$item->isFile()) {
                continue;
            }

            if (!$this->path_is_included($relative, $includes)) {
                continue;
            }

            if ($since && $item->getMTime() <= $since) {
                continue;
            }

            $files[] = $path;
        }

        sort($files);

        return $files;
    }

    private function path_is_excluded(string $relative, array $patterns): bool
    {
        $relative = str_replace('\\', '/', $relative);

        foreach ($patterns as $pattern) {
            $pattern = trim(str_replace('\\', '/', $pattern));
            if ($pattern === '') {
                continue;
            }

            if (fnmatch($pattern, basename($relative)) || fnmatch($pattern, $relative) || strpos($relative . '/', trim($pattern, '/') . '/') === 0) {
                return true;
            }
        }

        return false;
    }

    private function path_is_included(string $relative, array $patterns): bool
    {
        $patterns = array_values(array_filter(array_map('trim', $patterns), static function ($pattern) {
            return $pattern !== '';
        }));

        if (!$patterns) {
            return true;
        }

        $relative = str_replace('\\', '/', $relative);

        foreach ($patterns as $pattern) {
            $pattern = trim(str_replace('\\', '/', $pattern));
            if (fnmatch($pattern, basename($relative)) || fnmatch($pattern, $relative) || strpos($relative . '/', trim($pattern, '/') . '/') === 0) {
                return true;
            }
        }

        return false;
    }

    private function relative_path(string $base, string $path): string
    {
        $base = rtrim(str_replace('\\', '/', $base), '/');
        $path = str_replace('\\', '/', $path);

        return ltrim(substr($path, strlen($base)), '/');
    }

    private function sanitize_relative_path($path): string
    {
        $path = trim(str_replace('\\', '/', (string) $path), '/');

        if ($path === '' || strpos($path, '..') !== false || strpos($path, "\0") !== false || preg_match('#^[A-Za-z]:/#', $path)) {
            return '';
        }

        return $path;
    }

    private function file_since_timestamp(string $profile_key, array $profile): int
    {
        if (empty($profile['incremental_files'])) {
            return 0;
        }

        $last_runs = (array) get_option(self::LAST_RUNS_OPTION, []);

        return (int) ($last_runs[$profile_key] ?? 0);
    }

    private function escape_identifier(string $identifier): string
    {
        return str_replace('`', '``', $identifier);
    }
}

register_activation_hook(IN_SYNCED_FILE, ['In_Synced', 'activate']);
