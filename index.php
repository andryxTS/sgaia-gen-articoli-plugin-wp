<?php
/*
Plugin Name: SGAIA - Generatore Articoli (Final UI)
Description: Generatore articoli con UI perfezionata, gestione Token esplicita e feedback visivo avanzato.
Version: 1.7
Author: SGAIA
Ultima modifica: Inserita risposta da WP a n8n di conferma finale (mancava proprio)
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Menu
add_action('admin_menu', 'sgaia_iframe_menu');
function sgaia_iframe_menu() {
    add_menu_page('Generatore Articoli', 'Generatore SGAIA', 'edit_posts', 'sgaia-generator', 'sgaia_render_ui', 'dashicons-superhero', 3);
}

// 2. Settings
add_action('admin_init', 'sgaia_register_settings');
function sgaia_register_settings() {
    register_setting('sgaia_settings_group', 'sgaia_n8n_api_token');
}

// 3. UI Frontend
function sgaia_render_ui() {
    $sheet_url = 'https://docs.google.com/spreadsheets/d/1V2LkVpTc4KjmRwrVT2xPxOXMAes3krc7XoiP-2lhFg4/edit?usp=sharing';
    $api_token = get_option('sgaia_n8n_api_token');
    $n8n_url = 'https://ailo.sgaia.it/webhook/ingresso-generatore-olimpico/'; 
    ?>
    
    <div class="wrap sgaia-wrapper">
        <h1 class="wp-heading-inline"></h1> <!-- Spacer per WP -->

        <style>
            .sgaia-wrapper { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 900px; margin: 20px auto; }
            
            /* Card Principale */
            .sgaia-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.05);
                padding: 60px 40px;
                max-width: 500px;
                margin: 40px auto;
                text-align: center;
                border: 1px solid #e2e4e7;
                position: relative;
            }
            
            /* Header */
            .sgaia-title { font-size: 26px; font-weight: 700; color: #1d2327; margin: 0 0 5px 0; }
            .sgaia-subtitle { font-size: 16px; color: #646970; margin: 0 0 35px 0; font-weight: 400; }

            /* Pulsante Hero */
            .sgaia-btn-hero {
                background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
                color: #fff;
                border: none;
                padding: 18px 30px;
                font-size: 18px;
                font-weight: 600;
                border-radius: 8px;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(34, 113, 177, 0.25);
                transition: all 0.2s ease;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
            }
            .sgaia-btn-hero:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(34, 113, 177, 0.35); }
            .sgaia-btn-hero:disabled { background: #f0f0f1; color: #a7aaad; cursor: wait; transform: none; box-shadow: none; }
            
            /* Input Token Style */
            .sgaia-token-box { background: #f6f7f7; padding: 20px; border-radius: 8px; border: 1px dashed #c3c4c7; text-align: left; margin-bottom: 20px; }
            .sgaia-label { display: block; font-weight: 600; margin-bottom: 8px; color: #1d2327; }
            .sgaia-input { width: 100%; padding: 10px; font-size: 14px; border: 1px solid #8c8f94; border-radius: 4px; box-sizing: border-box; }
            .sgaia-input-desc { font-size: 12px; color: #646970; margin-top: 5px; }

            /* Mega Spunta Animata */
            .sgaia-success-placeholder { display: none; margin: 20px 0; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
            .sgaia-check-circle {
                width: 80px; height: 80px; background: #dff0d8; border-radius: 50%; color: #46b450;
                display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;
            }
            .sgaia-check-icon { font-size: 45px; width: 45px; height: 45px; }

            /* Stato e Azioni */
            .sgaia-status { margin-top: 20px; font-size: 14px; color: #646970; min-height: 20px; font-style: italic; }
            
            .sgaia-results { display: none; margin-top: 30px; animation: fadeInUp 0.5s ease; border-top: 1px solid #eee; padding-top: 20px; }
            .sgaia-action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; }
            .sgaia-btn-action { text-decoration: none; padding: 12px; border-radius: 5px; text-align: center; font-weight: 500; display: block; }
            .sgaia-btn-primary { background: #2271b1; color: #fff; }
            .sgaia-btn-primary:hover { background: #135e96; color: #fff; }
            .sgaia-btn-secondary { background: #f0f0f1; color: #2271b1; border: 1px solid #2271b1; }
            .sgaia-btn-secondary:hover { background: #fff; }
            .sgaia-btn-reset { grid-column: 1 / -1; background: transparent; color: #646970; border: none; margin-top: 10px; cursor: pointer; text-decoration: underline; }

            /* Dettagli Token (Toggle) */
            .sgaia-token-toggle { margin-top: 40px; font-size: 12px; color: #a7aaad; border-top: 1px solid #f0f0f1; padding-top: 15px; }
            .sgaia-token-toggle summary { cursor: pointer; list-style: none; }
            .sgaia-token-toggle summary:hover { color: #2271b1; }
            
            @keyframes popIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
            @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
            @keyframes spin { 100% { transform: rotate(360deg); } }
            .spin { animation: spin 1s infinite linear; }
        </style>

        <a href="<?php echo esc_url($sheet_url); ?>" target="_blank" style="float: right; text-decoration: none; color: #2271b1; display: flex; align-items: center; gap: 5px; margin-bottom: 20px;">
            <span class="dashicons dashicons-media-spreadsheet"></span> Piano Editoriale
        </a>

        <div class="sgaia-card">
            <h2 class="sgaia-title">Generatore Articoli SGAIA</h2>
            <p class="sgaia-subtitle">Crea articoli tramite flusso n8n</p>

            <!-- SCENARIO 1: Token Mancante (Mostra Form Grande) -->
            <?php if (empty($api_token)): ?>
                
                <form method="post" action="options.php" class="sgaia-token-box" style="background: #fff8e5; border-color: #f0c33c; border-style: solid;">
                    <?php settings_fields('sgaia_settings_group'); ?>
                    <h3 style="margin: 0 0 10px 0; color: #b76e00;">⚙️ Configurazione Iniziale</h3>
                    <p style="margin-bottom: 15px;">Inserisci il token di sicurezza per collegare n8n.</p>
                    
                    <label class="sgaia-label" for="token_input_main">Token n8n (Bearer)</label>
                    <input type="text" id="token_input_main" name="sgaia_n8n_api_token" class="sgaia-input" placeholder="incolla qui la stringa lunga..." required>
                    
                    <button type="submit" class="button button-primary" style="margin-top: 15px; width: 100%;">Salva e Abilita</button>
                </form>

            <!-- SCENARIO 2: Token Presente (Mostra App) -->
            <?php else: ?>

                <!-- Pulsante Principale -->
                <button type="button" id="launch-btn" class="sgaia-btn-hero">
                    <span class="dashicons dashicons-rocket" style="font-size:24px;width:24px;height:24px;"></span> 
                    <span>AVVIA GENERATORE</span>
                </button>

                <!-- Feedback Testuale -->
                <div id="status-text" class="sgaia-status">Pronto all'uso.</div>

                <!-- Mega Spunta (nascosta inizialmente) -->
                <div id="success-visual" class="sgaia-success-placeholder">
                    <div class="sgaia-check-circle">
                        <span class="dashicons dashicons-yes sgaia-check-icon"></span>
                    </div>
                    <div style="font-size: 18px; font-weight: bold; color: #2c3338;">Salvato con successo!</div>
                </div>

                <!-- Azioni (nascoste inizialmente) -->
                <div id="result-actions" class="sgaia-results">
                    <div class="sgaia-action-grid">
                        <a href="#" id="btn-edit" target="_blank" class="sgaia-btn-action sgaia-btn-primary">✏️ Modifica</a>
                        <a href="#" id="btn-view" target="_blank" class="sgaia-btn-action sgaia-btn-secondary">👁️ Vedi</a>
                        <button type="button" id="btn-reset" class="sgaia-btn-reset">↺ Crea un nuovo articolo</button>
                    </div>
                </div>

                <!-- Modifica Token (Accordion in basso) -->
                <details class="sgaia-token-toggle">
                    <summary>🔧 Modifica Token n8n</summary>
                    <div style="margin-top: 15px; text-align: left; background: #f6f7f7; padding: 15px; border-radius: 5px;">
                        <form method="post" action="options.php">
                            <?php settings_fields('sgaia_settings_group'); ?>
                            <label class="sgaia-label" for="sgaia_n8n_api_token">Token attuale:</label>
                            <input type="text" id="sgaia_n8n_api_token" name="sgaia_n8n_api_token" 
                                   value="<?php echo esc_attr($api_token); ?>" 
                                   class="sgaia-input" style="background: #fff;">
                            <p class="sgaia-input-desc">Token di autenticazione per il Webhook n8n (Header Authorization).</p>
                            <button type="submit" class="button button-secondary button-small" style="margin-top: 10px;">Aggiorna Token</button>
                        </form>
                    </div>
                </details>

            <?php endif; ?>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===========================================
            // 🔧 MODALITÀ TEST
            // ===========================================
            const SIMULATION_MODE = false; 
            // ===========================================

            const launchBtn = document.getElementById('launch-btn');
            const statusText = document.getElementById('status-text');
            const successVisual = document.getElementById('success-visual');
            const resultActions = document.getElementById('result-actions');
            const btnEdit = document.getElementById('btn-edit');
            const btnView = document.getElementById('btn-view');
            const btnReset = document.getElementById('btn-reset');

            if (!launchBtn) return;

            // Helper UI
            function updateUI(state, msg = '') {
                switch(state) {
                    case 'loading':
                        launchBtn.disabled = true;
                        launchBtn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Connessione...';
                        statusText.innerText = 'Verifica token in corso...';
                        break;
                    case 'working':
                        launchBtn.innerHTML = '⚠️ Finestra Aperta';
                        statusText.innerHTML = '<strong>Non chiudere questa scheda.</strong><br>Compila i dati nella nuova finestra';
                        statusText.style.color = '#d63638';
                        break;
                    case 'saving':
                        launchBtn.innerHTML = '<span class="dashicons dashicons-cloud-upload"></span> Salvataggio...';
                        statusText.innerText = msg || 'Ricezione dati...';
                        statusText.style.color = '#646970';
                        break;
                    case 'success':
                        // Nascondo elementi attivi
                        launchBtn.style.display = 'none';
                        statusText.style.display = 'none';
                        
                        // Mostra Mega Spunta
                        successVisual.style.display = 'block';
                        
                        // Mostra azioni
                        resultActions.style.display = 'block';
                        break;
                }
            }

            // Click Handler
            launchBtn.addEventListener('click', function() {
                updateUI('loading');

                if (SIMULATION_MODE) {
                    // --- SIMULAZIONE ---
                    setTimeout(() => {
                        updateUI('working');
                        
                        // Simuliamo utente che lavora
                        setTimeout(() => {
                            handleSaveArticle({ 
                                title: "Titolo Articolo Simulato", 
                                content: "Contenuto generato...", 
                                slug: "test-articolo" 
                            });
                        }, 4500); 
                    }, 3000);
                } else {
                    // --- REALE ---
                    fetch('<?php echo esc_url($n8n_url); ?>', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer <?php echo esc_js($api_token); ?>',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ action: 'start_from_wp' })
                    })
                    .then(async response => {
                        if (response.ok) {
                            const html = await response.text();
                            
                            // Creiamo un Blob con l'HTML ricevuto
                            const blob = new Blob([html], { type: 'text/html' });
                            const blobUrl = URL.createObjectURL(blob);
                            
                            // Apriamo il Blob URL invece di una pagina vuota
                            const n8nWin = window.open(blobUrl, 'sgaia_generator');
                            
                            if(n8nWin) {
                                updateUI('working');
                            } else {
                                throw new Error('Nuova finestra bloccata.');
                            }
                        } else {
                            throw new Error('Errore API: ' + response.status);
                        }
                    })
                    .catch(err => {
                        launchBtn.disabled = false;
                        launchBtn.innerHTML = 'Riprova';
                        statusText.innerHTML = '<span style="color:red">Errore: ' + err.message + '</span>';
                    });
                }
            });

            // Handle Save
            function handleSaveArticle(payload, sourceWindow) {
                updateUI('saving', 'Dati ricevuti. Salvataggio in corso...');

                if (SIMULATION_MODE) {
                    setTimeout(() => {
                        finishSuccess({ edit_link: '#', view_link: '#' });
                        // Simulazione risposta alla popup
                        if(sourceWindow) {
                            sourceWindow.postMessage({ action: 'sgaia_save_success', edit_link: '#' }, '*');
                        }
                    }, 1500);
                } else {
                    jQuery.post(ajaxurl, {
                        action: 'sgaia_save_ajax',
                        security: '<?php echo wp_create_nonce("sgaia_save_nonce"); ?>',
                        article_data: payload
                    }, function(response) {
                        if(response.success) {
                            finishSuccess(response.data);
                            
                            // === FIX QUI SOTTO ===
                            // Inviamo il segnale di successo alla finestra di n8n che sta aspettando
                            if (sourceWindow) {
                                sourceWindow.postMessage({
                                    action: 'sgaia_save_success',
                                    edit_link: response.data.edit_link
                                }, '*');
                            }
                            // =====================

                        } else {
                            statusText.innerText = 'Errore Salvataggio: ' + response.data;
                            launchBtn.disabled = false;
                            launchBtn.innerText = 'Riprova Salvataggio';
                        }
                    });
                }
            }

            function finishSuccess(data) {
                btnEdit.href = data.edit_link;
                btnView.href = data.view_link;
                updateUI('success');
            }

            // Listener Messaggi Reali
            window.addEventListener('message', function(event) {
                if (!SIMULATION_MODE && event.data.action === 'sgaia_save_article') {
                    // MODIFICA: Passiamo event.source (la finestra n8n) alla funzione di salvataggio
                    handleSaveArticle(event.data.payload, event.source);
                }
            });

            btnReset.addEventListener('click', function() {
                location.reload();
            });
        });
        </script>
    </div>
    <?php
}

// 4. AJAX Handler (RESTO INVARIATO)
add_action('wp_ajax_sgaia_save_ajax', 'sgaia_handle_save');
function sgaia_handle_save() {
    check_ajax_referer('sgaia_save_nonce', 'security');
    if(!current_user_can('edit_posts')) wp_send_json_error('Permessi insufficienti');

    $data = $_POST['article_data'];

    $post_id = wp_insert_post(array(
        'post_title'   => sanitize_text_field($data['title']),
        'post_content' => wp_kses_post($data['content']), 
        'post_excerpt' => isset($data['riassunto']) ? sanitize_textarea_field($data['riassunto']) : '',
        'post_name'    => isset($data['slug']) ? sanitize_title($data['slug']) : '',
        'post_status'  => 'draft',
        'post_author'  => get_current_user_id(),
        'post_type'    => 'post'
    ));

    if(is_wp_error($post_id)) wp_send_json_error($post_id->get_error_message());
    clean_post_cache($post_id);

    if(isset($data['tag']) && is_array($data['tag'])) wp_set_post_tags($post_id, array_map('sanitize_text_field', $data['tag']));

    if(isset($data['yoast']) && is_array($data['yoast'])) {
        $yoast = $data['yoast'];
        if(!empty($yoast['focus_keyword'])) update_post_meta($post_id, '_yoast_wpseo_focuskw', sanitize_text_field($yoast['focus_keyword']));
        if(!empty($yoast['meta_description'])) update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_text_field($yoast['meta_description']));
        if(!empty($yoast['social_title'])) update_post_meta($post_id, '_yoast_wpseo_opengraph-title', sanitize_text_field($yoast['social_title']));
        if(!empty($yoast['x_title'])) update_post_meta($post_id, '_yoast_wpseo_twitter-title', sanitize_text_field($yoast['x_title']));
        
        $real_permalink = get_permalink($post_id);
        $n8n_slug = isset($data['slug']) ? sanitize_title($data['slug']) : '';
        $wrong_url = home_url() . '/' . $n8n_slug;
        
        if(!empty($yoast['social_description'])) {
            $desc = str_replace($wrong_url, $real_permalink, $yoast['social_description']);
            update_post_meta($post_id, '_yoast_wpseo_opengraph-description', sanitize_text_field($desc));
        }
        if(!empty($yoast['x_description'])) {
            $desc = str_replace($wrong_url, $real_permalink, $yoast['x_description']);
            update_post_meta($post_id, '_yoast_wpseo_twitter-description', sanitize_text_field($desc));
        }
    }

    wp_send_json_success(array(
        'post_id' => $post_id,
        'edit_link' => get_edit_post_link($post_id, ''),
        'view_link' => get_permalink($post_id)
    ));
}