<?php
/*
Plugin Name: Assistente Blog
Description: Assistente redazionale by SGAIA connesso al motore noon.
Version: 2.1
Author: SGAIA
Ultima modifica: UX ottimizzata con auto-salvataggio delle preferenze (Flusso/Istanza)
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Menu
add_action('admin_menu', 'sgaia_iframe_menu');
function sgaia_iframe_menu() {
    add_menu_page('Assistente Blog', 'Assistente Blog', 'edit_posts', 'sgaia-generator', 'sgaia_render_ui', 'dashicons-superhero', 81);
}

// 2. Settings
add_action('admin_init', 'sgaia_register_settings');
function sgaia_register_settings() {
    register_setting('sgaia_settings_group', 'sgaia_n8n_api_token');
    register_setting('sgaia_settings_group', 'sgaia_noon_base_url');
    // Registriamo le nuove opzioni per memorizzare le scelte dell'utente
    register_setting('sgaia_settings_group', 'sgaia_saved_flow_id');
    register_setting('sgaia_settings_group', 'sgaia_saved_instance_id');
}

// 3. UI Frontend
function sgaia_render_ui() {
    $sheet_url = 'https://docs.google.com/spreadsheets/d/1V2LkVpTc4KjmRwrVT2xPxOXMAes3krc7XoiP-2lhFg4/edit?usp=sharing';
    $api_token = get_option('sgaia_n8n_api_token');
    $base_url = rtrim(get_option('sgaia_noon_base_url'), '/');
    
    // Recupero preferenze salvate
    $saved_flow_id = get_option('sgaia_saved_flow_id', '');
    $saved_instance_id = get_option('sgaia_saved_instance_id', '');
    ?>
    
    <div class="wrap sgaia-wrapper">
        <h1 class="wp-heading-inline"></h1>

        <style>
            .sgaia-wrapper { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 900px; margin: 20px auto; }
            .sgaia-card { background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 50px 40px; max-width: 500px; margin: 40px auto; text-align: center; border: 1px solid #e2e4e7; }
            .sgaia-title { font-size: 26px; font-weight: 700; color: #1d2327; margin: 0 0 5px 0; }
            .sgaia-subtitle { font-size: 16px; color: #646970; margin: 0 0 35px 0; font-weight: 400; }

            .sgaia-btn-hero { background: linear-gradient(135deg, #2271b1 0%, #135e96 100%); color: #fff; border: none; padding: 16px 24px; font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(34, 113, 177, 0.25); transition: all 0.2s ease; width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 10px; }
            .sgaia-btn-hero:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(34, 113, 177, 0.35); }
            .sgaia-btn-hero:disabled { background: #f0f0f1; color: #a7aaad; cursor: not-allowed; transform: none; box-shadow: none; }
            
            .sgaia-token-box { background: #f6f7f7; padding: 20px; border-radius: 8px; border: 1px dashed #c3c4c7; text-align: left; margin-bottom: 20px; }
            .sgaia-label { display: block; font-weight: 600; margin-bottom: 8px; color: #1d2327; font-size: 13px; }
            .sgaia-input, .sgaia-select { width: 100%; padding: 10px; font-size: 14px; border: 1px solid #8c8f94; border-radius: 4px; box-sizing: border-box; margin-bottom: 15px; }
            .sgaia-input-desc { font-size: 12px; color: #646970; margin-top: -10px; margin-bottom: 15px; display: block; }

            /* Configurazione UI */
            .sgaia-config-area { text-align: left; background: #f0f6fc; padding: 20px; border-radius: 8px; border: 1px solid #c8d8e8; margin-bottom: 25px; }
            .sgaia-flex-row { display: flex; gap: 10px; margin-bottom: 5px; }
            .sgaia-flex-row input { margin-bottom: 0; flex: 1; }
            
            .sgaia-flow-display { font-size: 13px; color: #2c3338; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #c8d8e8; padding-bottom: 10px; }
            .sgaia-flow-badge { font-family: monospace; background: #fff; border: 1px solid #c8d8e8; padding: 2px 6px; border-radius: 4px; color: #2271b1; }
            .sgaia-btn-link { background: none; border: none; color: #2271b1; text-decoration: underline; cursor: pointer; font-size: 12px; padding: 0; }

            .sgaia-success-placeholder { display: none; margin: 20px 0; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
            .sgaia-check-circle { width: 80px; height: 80px; background: #dff0d8; border-radius: 50%; color: #46b450; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; }
            .sgaia-check-icon { font-size: 45px; width: 45px; height: 45px; }

            .sgaia-status { margin-top: 15px; font-size: 14px; color: #646970; min-height: 20px; font-style: italic; }
            .sgaia-results { display: none; margin-top: 30px; animation: fadeInUp 0.5s ease; border-top: 1px solid #eee; padding-top: 20px; }
            .sgaia-action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; }
            .sgaia-btn-action { text-decoration: none; padding: 12px; border-radius: 5px; text-align: center; font-weight: 500; display: block; }
            .sgaia-btn-primary { background: #2271b1; color: #fff; }
            .sgaia-btn-primary:hover { background: #135e96; color: #fff; }
            .sgaia-btn-secondary { background: #f0f0f1; color: #2271b1; border: 1px solid #2271b1; }
            .sgaia-btn-secondary:hover { background: #fff; }
            .sgaia-btn-reset { grid-column: 1 / -1; background: transparent; color: #646970; border: none; margin-top: 10px; cursor: pointer; text-decoration: underline; }

            .sgaia-token-toggle { margin-top: 40px; font-size: 12px; color: #a7aaad; border-top: 1px solid #f0f0f1; padding-top: 15px; text-align: left; }
            
            @keyframes popIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
            @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
            .spin { animation: spin 1s infinite linear; }
            @keyframes spin { 100% { transform: rotate(360deg); } }
        </style>

        <a href="<?php echo esc_url($sheet_url); ?>" target="_blank" style="float: right; text-decoration: none; color: #2271b1; display: flex; align-items: center; gap: 5px; margin-bottom: 20px;">
            <span class="dashicons dashicons-media-spreadsheet"></span> Piano Editoriale
        </a>

        <div class="sgaia-card">
            <h2 class="sgaia-title">Assistente Blog</h2>
            <p class="sgaia-subtitle">Connesso al motore noon</p>

            <?php if (empty($api_token) || empty($base_url)): ?>
                
                <form method="post" action="options.php" class="sgaia-token-box" style="background: #fff8e5; border-color: #f0c33c; border-style: solid;">
                    <?php settings_fields('sgaia_settings_group'); ?>
                    <h3 style="margin: 0 0 15px 0; color: #b76e00;">⚙️ Configurazione Connessione</h3>
                    
                    <label class="sgaia-label">URL Base App noon</label>
                    <input type="url" name="sgaia_noon_base_url" class="sgaia-input" placeholder="es. https://noon.miodominio.it" value="<?php echo esc_attr($base_url); ?>" required>
                    
                    <label class="sgaia-label">Secret Token (API Key)</label>
                    <input type="text" name="sgaia_n8n_api_token" class="sgaia-input" placeholder="incolla qui la stringa..." value="<?php echo esc_attr($api_token); ?>" required>
                    
                    <button type="submit" class="button button-primary" style="margin-top: 5px; width: 100%;">Salva e Procedi</button>
                </form>

            <?php else: ?>

                <div id="app-container">
                    
                    <div class="sgaia-config-area">
                        
                        <!-- Header Configurazione (Sempre visibile) -->
                        <div class="sgaia-flow-display">
                            <div>Flusso: <span id="display-flow-id" class="sgaia-flow-badge"><?php echo $saved_flow_id ? esc_html($saved_flow_id) : 'Nessuno'; ?></span></div>
                            <button type="button" id="btn-toggle-edit" class="sgaia-btn-link">✏️ Modifica Flusso</button>
                        </div>

                        <!-- Ricerca Flusso (Nascosta se c'è già un flusso salvato) -->
                        <div id="flow-search-box" style="<?php echo $saved_flow_id ? 'display:none;' : 'display:block;'; ?> margin-bottom: 15px;">
                            <label class="sgaia-label">ID del Flusso da collegare</label>
                            <div class="sgaia-flex-row">
                                <input type="text" id="flow-id-input" class="sgaia-input" placeholder="es. flusso_olimpico_fase_5" value="<?php echo esc_attr($saved_flow_id); ?>">
                                <button type="button" id="btn-fetch-instances" class="button button-secondary" style="height: 40px;">Cerca</button>
                            </div>
                        </div>
                        
                        <!-- Selettore Istanza (Sempre visibile se ci sono istanze) -->
                        <div id="instances-wrapper" style="display:none;">
                            <label class="sgaia-label" style="margin-bottom: 4px;">Istanza di esecuzione:</label>
                            <select id="instance-select" class="sgaia-select" style="margin-bottom: 0; font-weight: 500;"></select>
                            
                            <!-- Fallback Manuale -->
                            <input type="text" id="manual-url-input" class="sgaia-input" placeholder="https://..." style="display:none; margin-top: 10px;">
                            <a href="#" id="toggle-manual-url" style="font-size: 11px; text-decoration: none; display: block; margin-top: 8px;">Usa URL Webhook manuale</a>
                        </div>
                    </div>

                    <button type="button" id="launch-btn" class="sgaia-btn-hero" disabled>
                        <span class="dashicons dashicons-controls-play" style="font-size:20px;width:20px;height:20px;"></span> 
                        <span>AVVIA FLUSSO</span>
                    </button>

                    <div id="status-text" class="sgaia-status">In attesa di connessione...</div>
                </div>

                <div id="success-visual" class="sgaia-success-placeholder">
                    <div class="sgaia-check-circle"><span class="dashicons dashicons-yes sgaia-check-icon"></span></div>
                    <div style="font-size: 18px; font-weight: bold; color: #2c3338;">Salvato in Bozza!</div>
                </div>

                <div id="result-actions" class="sgaia-results">
                    <div class="sgaia-action-grid">
                        <a href="#" id="btn-edit" target="_blank" class="sgaia-btn-action sgaia-btn-primary">✏️ Modifica</a>
                        <a href="#" id="btn-view" target="_blank" class="sgaia-btn-action sgaia-btn-secondary">👁️ Vedi</a>
                        <button type="button" id="btn-reset" class="sgaia-btn-reset">↺ Crea nuovo</button>
                    </div>
                </div>

                <details class="sgaia-token-toggle">
                    <summary>🔧 Server & Token</summary>
                    <div style="margin-top: 15px;">
                        <form method="post" action="options.php">
                            <?php settings_fields('sgaia_settings_group'); ?>
                            <label class="sgaia-label">URL Base:</label>
                            <input type="text" name="sgaia_noon_base_url" value="<?php echo esc_attr($base_url); ?>" class="sgaia-input" style="background: #fff;">
                            <label class="sgaia-label">API Token:</label>
                            <input type="text" name="sgaia_n8n_api_token" value="<?php echo esc_attr($api_token); ?>" class="sgaia-input" style="background: #fff;">
                            <button type="submit" class="button button-secondary button-small">Salva</button>
                        </form>
                    </div>
                </details>

            <?php endif; ?>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const SIMULATION_MODE = false; 
            const BASE_URL = '<?php echo esc_js($base_url); ?>';
            const TOKEN = '<?php echo esc_js($api_token); ?>';
            const SAVED_FLOW = '<?php echo esc_js($saved_flow_id); ?>';
            const SAVED_INSTANCE = '<?php echo esc_js($saved_instance_id); ?>';

            const btnFetch = document.getElementById('btn-fetch-instances');
            const btnToggleEdit = document.getElementById('btn-toggle-edit');
            const flowSearchBox = document.getElementById('flow-search-box');
            const displayFlowId = document.getElementById('display-flow-id');
            const flowIdInput = document.getElementById('flow-id-input');
            const instancesWrapper = document.getElementById('instances-wrapper');
            const instanceSelect = document.getElementById('instance-select');
            const manualUrlInput = document.getElementById('manual-url-input');
            const toggleManualBtn = document.getElementById('toggle-manual-url');
            
            const launchBtn = document.getElementById('launch-btn');
            const statusText = document.getElementById('status-text');
            const appContainer = document.getElementById('app-container');
            
            let isManualMode = false;
            let currentInstancesMap = {};

            // Auto-load se abbiamo già un flusso salvato
            if (SAVED_FLOW && btnFetch) {
                statusText.innerText = 'Sincronizzazione istanze...';
                fetchInstances(SAVED_FLOW);
            }

            // Toggle Edit Box
            if (btnToggleEdit) {
                btnToggleEdit.addEventListener('click', () => {
                    flowSearchBox.style.display = flowSearchBox.style.display === 'none' ? 'block' : 'none';
                });
            }

            // Funzione di Salvataggio Silenzioso Preferenze via AJAX
            function savePreferencesSilent(flowId, instanceId) {
                jQuery.post(ajaxurl, {
                    action: 'sgaia_save_prefs_ajax',
                    security: '<?php echo wp_create_nonce("sgaia_save_nonce"); ?>',
                    flow_id: flowId,
                    instance_id: instanceId
                });
            }

            // Gestione Cambio Istanza dal Dropdown
            if (instanceSelect) {
                instanceSelect.addEventListener('change', (e) => {
                    const selectedId = e.target.value;
                    const flowId = flowIdInput.value.trim();
                    savePreferencesSilent(flowId, selectedId);
                    
                    // Ridisegna le option per spostare la dicitura "Salvata"
                    renderOptions(flowId, selectedId);
                });
            }

            // Fetch Core Function
            function fetchInstances(flowId) {
                btnFetch.disabled = true;
                btnFetch.innerText = 'Ricerca...';

                fetch(`${BASE_URL}/api/flows/${flowId}/instances`, {
                    method: 'GET',
                    headers: { 'Authorization': `Bearer ${TOKEN}`, 'ngrok-skip-browser-warning': '69420' }
                })
                .then(res => res.json())
                .then(data => {
                    btnFetch.disabled = false;
                    btnFetch.innerText = 'Cerca';

                    if(data.error) throw new Error(data.error);
                    
                    currentInstancesMap = {};
                    data.instances.forEach(inst => currentInstancesMap[inst.id] = inst);
                    
                    // Se non c'è un'istanza salvata (o è stata eliminata da db), usa la prima
                    let targetInstanceId = SAVED_INSTANCE;
                    if (!currentInstancesMap[targetInstanceId] && data.instances.length > 0) {
                        targetInstanceId = data.instances[0].id;
                        savePreferencesSilent(flowId, targetInstanceId);
                    }

                    renderOptions(flowId, targetInstanceId);

                    instancesWrapper.style.display = 'block';
                    flowSearchBox.style.display = 'none'; // Nascondi la ricerca dopo il successo
                    displayFlowId.innerText = flowId; // Aggiorna la label in alto
                    launchBtn.disabled = false;
                    statusText.innerText = 'Pronto all\'uso. Clicca Avvia Flusso.';
                })
                .catch(err => {
                    btnFetch.disabled = false;
                    btnFetch.innerText = 'Cerca';
                    statusText.innerHTML = '<span style="color:red">Errore sincronizzazione: ' + err.message + '</span>';
                });
            }

            function renderOptions(flowId, activeInstanceId) {
                instanceSelect.innerHTML = '';
                Object.values(currentInstancesMap).forEach(inst => {
                    const option = document.createElement('option');
                    option.value = inst.id;
                    
                    let text = inst.isMaster ? '[MASTER] ' : '';
                    text += inst.name;
                    if (inst.isSimulationMode) text += ' (SIM)';
                    
                    if (inst.id === activeInstanceId) {
                        option.selected = true;
                        text += ' ★ (Salvata)';
                    }
                    
                    option.textContent = text;
                    instanceSelect.appendChild(option);
                });
            }

            if (btnFetch) {
                btnFetch.addEventListener('click', function() {
                    const flowId = flowIdInput.value.trim();
                    if(!flowId) return alert('Inserisci un ID Flusso');
                    fetchInstances(flowId);
                });
            }

            // Toggle Manuale
            if (toggleManualBtn) {
                toggleManualBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    isManualMode = !isManualMode;
                    if(isManualMode) {
                        instanceSelect.style.display = 'none';
                        manualUrlInput.style.display = 'block';
                        toggleManualBtn.innerText = 'Torna alla selezione guidata';
                    } else {
                        instanceSelect.style.display = 'block';
                        manualUrlInput.style.display = 'none';
                        toggleManualBtn.innerText = 'Usa URL Webhook manuale';
                    }
                });
            }

            function updateUI(state, msg = '') {
                switch(state) {
                    case 'loading':
                        launchBtn.disabled = true;
                        launchBtn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Connessione...';
                        statusText.innerText = 'Apertura ponte in corso...';
                        break;
                    case 'working':
                        launchBtn.innerHTML = '⚠️ Finestra Aperta';
                        statusText.innerHTML = '<strong>Non chiudere questa scheda.</strong><br>Lavora nella nuova finestra aperta...';
                        statusText.style.color = '#d63638';
                        break;
                    case 'saving':
                        launchBtn.innerHTML = '<span class="dashicons dashicons-cloud-upload"></span> Salvataggio...';
                        statusText.innerText = msg || 'Ricezione dati...';
                        statusText.style.color = '#646970';
                        break;
                    case 'success':
                        appContainer.style.display = 'none';
                        document.getElementById('success-visual').style.display = 'block';
                        document.getElementById('result-actions').style.display = 'block';
                        break;
                }
            }

            // Esecuzione
            if (launchBtn) {
                launchBtn.addEventListener('click', function() {
                    let targetUrl = '';
                    if (isManualMode) {
                        targetUrl = manualUrlInput.value.trim();
                        if(!targetUrl) return alert('Inserisci l\'URL');
                    } else {
                        const selectedId = instanceSelect.value;
                        if(!selectedId) return alert('Seleziona un\'istanza');
                        const inst = currentInstancesMap[selectedId];
                        targetUrl = `${BASE_URL}/api/webhook/${inst.entryPoint}`;
                    }

                    updateUI('loading');

                    if (SIMULATION_MODE) {
                        setTimeout(() => { updateUI('working'); }, 1000);
                    } else {
                        fetch(targetUrl, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${TOKEN}`,
                                'Content-Type': 'application/json',
                                'ngrok-skip-browser-warning': '69420'
                            },
                            body: JSON.stringify({ source: 'wordpress_plugin' })
                        })
                        .then(async response => {
                            if (response.headers.get("content-type")?.includes("text/html")) {
                                const html = await response.text();
                                const blob = new Blob([html], { type: 'text/html' });
                                const blobUrl = URL.createObjectURL(blob);
                                const n8nWin = window.open(blobUrl, 'sgaia_generator');
                                
                                if(n8nWin) updateUI('working');
                                else throw new Error('Il browser ha bloccato il popup. Abilita i popup.');
                            } else if (response.ok) {
                                const json = await response.json();
                                statusText.innerHTML = `<span style="color:green">Azione inviata con successo. (Log ID: ${json.logId})</span>`;
                                launchBtn.innerText = 'Azione Avviata';
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
            }

            window.addEventListener('message', function(event) {
                if (event.data && event.data.action === 'sgaia_save_article') {
                    updateUI('saving', 'Dati ricevuti da noon. Salvataggio in corso...');
                    jQuery.post(ajaxurl, {
                        action: 'sgaia_save_ajax',
                        security: '<?php echo wp_create_nonce("sgaia_save_nonce"); ?>',
                        article_data: event.data.payload
                    }, function(response) {
                        if(response.success) {
                            document.getElementById('btn-edit').href = response.data.edit_link;
                            document.getElementById('btn-view').href = response.data.view_link;
                            updateUI('success');
                            if (event.source) {
                                event.source.postMessage({ action: 'sgaia_save_success', edit_link: response.data.edit_link }, '*');
                            }
                        } else {
                            statusText.innerText = 'Errore Salvataggio WP: ' + response.data;
                            launchBtn.disabled = false;
                            launchBtn.innerText = 'Riprova Salvataggio';
                        }
                    });
                }
            });

            if(document.getElementById('btn-reset')) {
                document.getElementById('btn-reset').addEventListener('click', function() { location.reload(); });
            }
        });
        </script>
    </div>
    <?php
}

// 4. AJAX Handlers
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
    if (function_exists('clean_post_cache')) clean_post_cache($post_id);

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
        'edit_link' => get_edit_post_link($post_id, '&'),
        'view_link' => get_permalink($post_id)
    ));
}

// NUOVO: AJAX per salvare le preferenze utente
add_action('wp_ajax_sgaia_save_prefs_ajax', 'sgaia_save_prefs');
function sgaia_save_prefs() {
    check_ajax_referer('sgaia_save_nonce', 'security');
    if(!current_user_can('edit_posts')) wp_send_json_error();

    update_option('sgaia_saved_flow_id', sanitize_text_field($_POST['flow_id']));
    update_option('sgaia_saved_instance_id', sanitize_text_field($_POST['instance_id']));

    wp_send_json_success();
}