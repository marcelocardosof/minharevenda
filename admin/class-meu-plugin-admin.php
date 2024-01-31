<?
class MinhaRevendaAdmin {

    public function __construct() {
        // Adiciona a página do menu e submenus
        add_action('admin_menu', array($this, 'minha_revenda_menu_page'));
        add_action('admin_menu', array($this, 'minha_revenda_submenus'));
    }

    // Função para adicionar a página do menu principal
    public function minha_revenda_menu_page() {
        add_menu_page(
            'Minha Revenda',
            'Minha Revenda',
            'manage_options',
            'minha-revenda',
            array($this, 'minha_revenda_page_html'),
            '',
            100
        );
    }

    public function minha_revenda_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>
            <p>Conteúdo da página principal do plugin "Minha Revenda".</p>
        </div>
        <?php
    }

    public function minha_revenda_submenus() {
        add_submenu_page(
            'minha-revenda',
            'Configurações',
            'Configurações',
            'manage_options',
            'minha-revenda-config',
            array($this, 'minha_revenda_config_html')
        );

        add_submenu_page(
            'minha-revenda',
            'Pedidos',
            'Pedidos',
            'manage_options',
            'minha-revenda-pedidos',
            array($this, 'minha_revenda_pedidos_html')
        );

        add_submenu_page(
            'minha-revenda',
            'Planos',
            'Planos',
            'manage_options',
            'minha-revenda-planos',
            'minha_revenda_planos_html'
        );


        add_submenu_page(
            'minha-revenda',
            'Clientes',
            'Clientes',
            'manage_options',
            'minha-revenda-clientes',
            array($this, 'minha_revenda_clientes_html')
        );
        
        
                add_submenu_page(
            'minha-revenda',
            'Modelos',
            'Modelos',
            'manage_options',
            'minha-revenda-modelos',
            array($this, 'minha_revenda_modelos_html')
        );
        
        
    }
    
    

public function minha_revenda_modelos_html() {
    
    echo do_shortcode( '[minharevendamodelos]' );

    ?>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <h2 class="text-center">Envie um Novo Modelo</h2>
    <form id="uploadForm" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="file">Escolha um arquivo ZIP:</label>
            <input type="file" name="file" id="file" required>
        </div>
        <div class="form-group">
            <label for="screenshot">Escolha uma imagem (PNG ou JPG):</label>
            <input type="file" name="screenshot" id="screenshot" accept=".png, .jpg, .jpeg" required>
        </div>
        <div class="text-center">
            <input type="hidden" name="action" value="clone_upload">
            <input type="submit" value="Enviar Modelo" class="btn btn-primary">
        </div>
        <!-- Barra de Progresso -->
        <div class="progress mt-4" style="width: 60%; margin: 0 auto;">
            <div id="uploadProgressBar" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                0%
            </div>
        </div>
    </form>

    <script>
        jQuery(document).ready(function($) {
            $('#uploadForm').submit(function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                $.ajax({
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percent = Math.round((e.loaded / e.total) * 100);
                                $('#uploadProgressBar').css('width', percent + '%').text(percent + '%');
                            }
                        });
                        return xhr;
                    },
                    type: 'POST',
                    url: '<?php echo plugins_url('../includes/uploadModel.php', __FILE__); ?>',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        alert(data);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Erro AJAX: " + textStatus, errorThrown);
                    }
                });
            });

            function updateProgressBar(percentage) {
                $('.progress-bar').css('width', percentage + '%').attr('aria-valuenow', percentage).text(percentage + '%');
            }
        });
    </script>
    <?php

}

    public function minha_revenda_config_html() {
        ?>
        <div class="wrap">
            <h1>Configurações</h1>
            <p>Conteúdo da página de configurações.</p>
        </div>
        <?php
    }


    public function minha_revenda_pedidos_html() {
        global $wpdb; // Importar a classe global $wpdb
        $table_name = $wpdb->prefix . "pedidos"; // Nome da tabela

        // Buscar todos os registros da tabela pedidos
        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        ?>
        <div class="wrap">
            <h1>Pedidos</h1>
            
            <!-- Tabela para exibir os pedidos -->
            <table class="widefat">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome do Usuário</th>
                        <th>URL Criada</th>
                        <th>Data e Hora</th>
                        <th>Editar</th>
                        <th>Excluir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($results as $row) {
                        $user_info = get_userdata($row['user_id']);
                        $edit_link = "#"; 
                        $delete_link = "#"; 
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>{$user_info->display_name}</td>";
                        echo "<td>{$row['url_created']}</td>";
                        echo "<td>{$row['date_time_created']}</td>";
                        echo "<td><a href='{$edit_link}'>Editar</a></td>";
                        echo "<td><a href='{$delete_link}'>Excluir</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Insira o código das funções 'minha_revenda_planos_html', 'minha_revenda_modelos_html' etc. aqui.

    public function minha_revenda_clientes_html() {
        ?>
        <div class="wrap">
            <h1>Clientes</h1>
            <p>Conteúdo da página de clientes.</p>
        </div>
        <?php
    }

    // Adicione todas as outras funções de subpáginas aqui...

}

// Instancia a classe para ativar o plugin
$minhaRevendaAdmin = new MinhaRevendaAdmin();




