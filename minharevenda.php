<?php
/*
Plugin Name: Minha Revenda
Description: Um plugin para instalar modelos de sites WordPress conforme o cliente escolhe.
Version: 1.0
Author: Marcelo Falcão
*/

// Incluir arquivos de administração

require plugin_dir_path(__FILE__) . 'admin/class-meu-plugin-admin.php';


function create_orders_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "mr_sites";

    // Verificar se a tabela já existe
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            user_name varchar(255) NOT NULL,
            url varchar(255) NOT NULL,
            active varchar(255) NOT NULL,
            date_time_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            pasta varchar(255) NOT NULL,
            prefixo varchar(255) NOT NULL,
            plano varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    
    // Tabela mr_planos
$table_name_planos = $wpdb->prefix . "mr_planos";

// Verificar se a tabela mr_planos já existe
if($wpdb->get_var("SHOW TABLES LIKE '$table_name_planos'") != $table_name_planos) {

    $sql_planos = "CREATE TABLE $table_name_planos (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nome varchar(255) NOT NULL,
        preco decimal(10,2) NOT NULL,
        duracao varchar(255) NOT NULL,
        espaco varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta($sql_planos);
}

    // Criar página "modelos"
    $modelos_page = get_page_by_path('modelos');
    if (!$modelos_page) {
        $modelos_page_id = wp_insert_post(array(
            'post_title'    => 'Modelos',
            'post_name'     => 'modelos',
            'post_content'  => '[modelosfront]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        ));
    }

    // Criar página "formulario"
    $formulario_page = get_page_by_path('formulario');
    if (!$formulario_page) {
        $formulario_page_id = wp_insert_post(array(
            'post_title'    => 'Formulário',
            'post_name'     => 'formulario',
            'post_content'  => '[minharevenda]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        ));
    }

    // Criar diretório "modelos"
    $modelos_dir = ABSPATH . 'modelos';
    if (!is_dir($modelos_dir)) {
        mkdir($modelos_dir, 0755);
    }
}


register_activation_hook(__FILE__, 'create_orders_table');



function mr_listar_modelos() {
    ob_start();

    // Incluir as dependências
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">';

    // Incluir os estilos
    echo '<style>
.meu-estilo-isolado * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: Quicksand, sans-serif;
}

.meu-estilo-isolado {
  display: flex;
  flex-wrap: wrap;
  gap: 25px;
  justify-content: space-between;
}

.card {
  flex: 1 0 calc(25% - 20px); 
  max-width: calc(25% - 20px); 
  height: 300px;
  background: #eee;
  border-radius: 20px;
  box-shadow: 0 2rem 6rem rgba(0, 0, 0, 0.2);
  position: relative;
}

.card-top {
  width: 100%;
  height: 63%;
  background: #f1ae04;
  border-radius: 20px 20px 0 0;
  overflow: hidden;
}

.card-top img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-bottom {
  padding: 15px;
  font-size: 1.2rem;
  font-weight: 500;
  margin-left: 83px;
}

.price, .price2 {
  width: 130px;
  height: 31px;
  background: #1c477a;
  color: #fff;
  border-radius: 5px;
  position: absolute;
  bottom: 16px;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 1rem 2rem rgba(28, 71, 122, 0.5);
  transition: background 0.5s;
}

.price a, .price2 a {
  color: white;
  text-decoration: none !important;  /* remove o sublinhado */
  font-weight: 400;
}

.price2 {
  left: -1rem;
}

.price {
  right: -1rem;
}

.price a, .price2 a {
  color: white;
  text-decoration: none;
  font-weight: 400;
}

.price a span:last-child, .price2 a span:last-child {
  font-weight: 700;
}

</style>';

    $modelos_directory = ABSPATH . 'modelos';

    if (is_dir($modelos_directory)) {
        $modelos_folders = scandir($modelos_directory);

        echo '<div class="meu-estilo-isolado">';

        foreach ($modelos_folders as $modelo) {
            if ($modelo !== '.' && $modelo !== '..' && is_dir($modelos_directory . '/' . $modelo)) {
                $screenshot_path = site_url("/modelos/$modelo/screenshot.png");
                $demonstracao = site_url("/modelos/$modelo/");
                $model_label = ucwords(str_replace('_', ' ', $modelo));
                $criacao_link = site_url("/formulario/?criacao=$modelo");

                // Início do Card
                echo '<div class="card">';

                // Top do Card (Imagem)
                echo '<div class="card-top">';
                echo '<img src="' . $screenshot_path . '" alt="' . $model_label . '">';
                echo '</div>'; // Fim do card-top

                // Bottom do Card (Detalhes)
                echo '<div class="card-bottom">';
                echo $model_label;
                echo '</div>'; // Fim do card-bottom

                // Botões
                echo '<div class="price2">';
                echo '<a href="' . $demonstracao . '" target="_blank"><span>VER</span><span>MODELO</span></a>';
                echo '</div>';

                echo '<div class="price">';
                echo '<a href="' . $criacao_link . '"><span>QUERO</span><span>ESSE!</span></a>';
                echo '</div>';

                echo '</div>'; // Fim do card
            }
        }

        echo '</div>'; // Fim do modelos-container

    } else {
        echo 'O diretório de modelos não foi encontrado.';
    }

    return ob_get_clean();
}

add_shortcode('modelosfront', 'mr_listar_modelos');


// Função para renderizar o formulário via shortcode
function mr_criacao() {
    

    
?>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos adicionais */

        #etapas .active {
            font-weight: bold;
            color: blue;
        }
            /* Estilos adicionais */
    .screen {
        display: block !important;
    }
    .hide {
        display: none !important;
    }
    </style>
<!-- INICIO SHORTCODE -->    
<div class="container mt-5">
    <div id="etapas" class="text-center mb-5">
        <span id="etapa1" class="active">1 - Escolha seu nome</span> | 
        <span id="etapa2">2 - Criação do seu site</span> | 
        <span id="etapa3">3 - Seus dados de acesso</span>
    </div>
    
<div id="formScreen" >

    <form method="post" class="mt-4">

        <div class="row">

<?php
$modelo = isset($_GET['criacao']) ? sanitize_text_field($_GET['criacao']) : ''; // Pega o modelo da URL

echo '<input class="form-check-input" type="radio" name="modelo" id="' . $modelo . '" value="' . $modelo . '" required checked hidden>';

?>

       
        </div>

        <div class="form-group mt-4">
            <label for="novaPasta" class="d-block">Escolha um nome:</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">http://<?php echo $_SERVER['HTTP_HOST']; ?>/</span>
                </div>
                <input type="text" class="form-control" name="novaPasta" id="novaPasta" placeholder="sitedamaria" required>
                <input type="hidden" name="action" value="clone_site">
            </div>
        </div>
<?
    ob_start();
    
    // Verificar se o usuário está logado
    if (is_user_logged_in()):

?>
        <div class="text-center">
            <input type="submit" value="Criar meu site!" class="btn btn-primary">
        </div>
          <?php
    else: // Se o usuário não estiver logado
    ?>
            <div class="text-center">
                Você precisa estar registrado! <br>
            <a href="<?php echo wp_registration_url(); ?>" class="btn btn-primary">Registre-se</a>
        </div>

<?php
    endif; // Fim da condição
    ?>
    </form>
</div>


<br>
<br>


<!-- Tela de Carregamento -->
<div id="loadingScreen" class="screen d-flex hide">
    
        <div class="text-center">
        <h2>Criando seu site</h2>
        <div class="progress mt-4" style="width: 60%; margin: 0 auto;">
            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                0%
            </div>
        </div>
    </div>
</div>

<!-- Tela de Sucesso -->
<div id="successScreen" class="screen d-flex hide">
        <div class="text-center">
        <h2>Parabéns! Seu site está pronto!</h2>
        <p>Seus dados de acesso:</p>
<p><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/PASTA_NOVA">http://<?php echo $_SERVER['HTTP_HOST']; ?>/<span id="pastaVisivel"></span></a></p>
<p>Painel de controle:</p>
<p><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/PASTA_NOVA/wp-admin">http://<?php echo $_SERVER['HTTP_HOST']; ?>/<span id="pastaVisivel2"></span>/wp-admin</a></p>
<p>login: admin</p>
        <p>senha: </p>
    </div>
</div>
<!-- FIM SHORTCODE --> 

<?
    return ob_get_clean();
}
add_shortcode('minharevenda', 'mr_criacao');
//----


// Função para renderizar o formulário via shortcode
function mr_modelos1() {
    
    ob_start();
    
?>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>
    /* Estilos adicionais */
    #etapas .active {
        font-weight: bold;
        color: blue;
    }

    .screen {
        display: block !important;
    }

    .hide {
        display: none !important;
    }

    /* Estilos para simular o grid de temas do WordPress */
    .theme-container {
        cursor: pointer;
        float: left;
        margin: 0 2% 4% 0;
        position: relative;
        width: 23%; /* Ajustado para 4 colunas */
        border: 1px solid #dcdcde;
        box-shadow: 0 1px 2px rgba(0,0,0,.1);
        box-sizing: border-box;
        overflow: hidden;
    }

    .theme-screenshot {
        width: 100%;
        height: auto;
    }

    .theme-details {
        background-color: #e5e5e5; /* Barra cinza clara */
        padding: 10px 15px;
        height: 48px;
        position: relative;
    }

    .theme-name {
        font-size: 15px;
        font-weight: 600;
        height: 18px;
        margin: 0;
        padding: 0;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        color: #333;
        float: left;
    }

    .theme-actions {
        position: absolute;
        right: 15px;
        top: 10px;
    }

    .theme-actions a {
        color: #2271b1;
        border-color: #2271b1;
        background: #f6f7f7;
        vertical-align: top;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 4px;
        border: 1px solid;
        display: inline-block;
    }

    .theme-actions a:hover {
        text-decoration: underline;
    }
</style>

<!-- INICIO SHORTCODE -->    
<div class="container mt-5">
    <div id="formScreen">
        <div class="row">

<?php
$modelos_directory = ABSPATH . 'modelos';
$site_url = "http://" . $_SERVER['HTTP_HOST'];

if (is_dir($modelos_directory)) {
    $modelos_folders = scandir($modelos_directory);

    foreach ($modelos_folders as $modelo) {
        if ($modelo !== '.' && $modelo !== '..' && is_dir($modelos_directory . '/' . $modelo)) {
            $screenshot_path = site_url("/modelos/$modelo/screenshot.png");
            $demonstracao = site_url("/modelos/$modelo/");
            $model_label = ucwords($modelo);

            echo '<div class="theme-container">';
            echo '<img src="' . $screenshot_path . '" alt="' . $model_label . '" class="theme-screenshot img-thumbnail">';
            echo '<div class="theme-details">';
            echo '<h3 class="theme-name">' . $model_label . '</h3>';
            echo '<div class="theme-actions">';
            echo '<a href="' . $demonstracao . '" target="_blank">Demonstração</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }
} else {
    echo 'O diretório de modelos não foi encontrado.';
}
?>

        </div>
    </div>
</div>





<!-- FIM SHORTCODE --> 

<?
    return ob_get_clean();
}
add_shortcode('minharevendamodelos', 'mr_modelos1');


//---
function mr_envio() {
?>
<!-- Novo Formulário de Upload de Modelo -->
 <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>

    </style>

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
<?
    return ob_get_clean();
}

add_shortcode('minharevenda-envio', 'mr_envio');

function process_clone_form() {
    include(plugin_dir_path(__FILE__) . 'includes/processClone.php');
}


function process_upload_form() {
    include(plugin_dir_path(__FILE__) . 'includes/uploadModel.php');
}


function mpc_enqueue_scripts() {
    // Enfileirar jQuery
    wp_enqueue_script('jquery');

    // Enfileirar Popper.js
    wp_enqueue_script('popper', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js', array('jquery'), null, true);

    // Enfileirar Bootstrap JS
    wp_enqueue_script('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery', 'popper'), null, true);

    // Enfileirar seu próprio script
    wp_enqueue_script('mpc-script', plugins_url('includes/script.js', __FILE__), array('jquery', 'popper', 'bootstrap'), '1.0.0', true);

    // Localizar variáveis para seu script
    wp_localize_script('mpc-script', 'mpc_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'uploadModelPath' => plugins_url('includes/uploadModel.php', __FILE__),
        'cloneModelPath' => plugins_url('includes/processClone.php', __FILE__)  
    ));

    // Enfileirar Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
}
add_action('wp_enqueue_scripts', 'mpc_enqueue_scripts');
add_action('wp_ajax_process_clone_form', 'process_clone_form');
add_action('wp_ajax_process_upload_form', 'process_upload_form');



// Adicione isso ao seu arquivo de plugin

function process_mr_criacao_form() {
    // Verifique se o formulário foi enviado
    if (isset($_POST['action']) && $_POST['action'] == 'clone_site') {
        // Pegue os dados do formulário
        $novaPasta = sanitize_text_field($_POST['novaPasta']);
        $modelo = sanitize_text_field($_POST['modelo']);


    }
}

add_action('init', 'process_mr_criacao_form');

// Inclua o arquivo da área do cliente
include_once('includes/area-do-cliente.php');