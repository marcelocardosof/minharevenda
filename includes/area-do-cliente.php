<?php
function shortcode_area_do_cliente() {
    ob_start();
?>

<!-- Incluir o Bootstrap e FontAwesome -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

<!-- Estilos personalizados -->
<style>
    body {
        font-family: Arial, sans-serif;
    }
    .sidebar {
        background-color: #f7f7f7;
        padding-top: 20px;
    }
    .nav-item {
        margin-bottom: 5px;
    }
    .nav-link {
        border-radius: 4px;
        color: #333;
        padding: 10px 15px;
        transition: all 0.3s;
    }
    .nav-link:hover, .nav-link.active {
        background-color: #007BFF;
        color: #FFF;
    }
    .fa {
        margin-right: 8px;
    }
    .dashboard-block {
        background-color: #007BFF;
        color: #FFF;
        padding: 20px;
        border-radius: 5px;
        transition: all 0.3s;
        cursor: pointer;
    }
    .dashboard-block:hover {
        transform: translateY(-5px);
    }
</style>

<?php
    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . "mr_sites";
$urls = $wpdb->get_results($wpdb->prepare("SELECT id, url FROM $table_name WHERE user_id = %d", $user_id));
?>

<div class="container-fluid mt-5">
    <div class="row">
        <!-- Menu lateral -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="#"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="?pg=meus-sites"><i class="fa fa-globe"></i> Meus Sites</a></li>
                <li class="nav-item"><a class="nav-link" href="?pg=faturas"><i class="fa fa-file-invoice-dollar"></i> Faturas</a></li>
                <li class="nav-item"><a class="nav-link" href="?pg=minha-conta"><i class="fa fa-user-circle"></i> Minha Conta</a></li>
                <li class="nav-item"><a class="nav-link" href="?pg=suporte"><i class="fa fa-life-ring"></i> Suporte</a></li>
            </ul>
        </nav>

        <!-- Conteúdo principal -->
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <?php if (isset($_GET['pg'])): 
                switch ($_GET['pg']):
                    case 'faturas':
            ?>
                <h2>Faturas</h2>
                <!-- Aqui você pode adicionar o conteúdo de Faturas -->

            <?php
                    break;
                    case 'minha-conta':
            ?>
                <h2>Minha Conta</h2>
                <!-- Aqui você pode adicionar o conteúdo de Minha Conta -->

            <?php
                    break;
                    case 'suporte':
            ?>
                <h2>Suporte</h2>
                <!-- Aqui você pode adicionar o conteúdo de Suporte -->

            <?php 
            break;
            case 'administrar-site':
            ?>
               <?php
               
            
    $siteId = intval($_GET['id']);  // Converter para número inteiro por segurança
             
               
               
    // Obtendo a informação do site com base no user_id
$siteInfo = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "mr_sites WHERE id = " . $siteId);


    // Verificando o tamanho da pasta
    function getFolderSize($path) {
        $bytestotal = 0;
        $path = realpath($path);
        if($path !== false && $path != '' && file_exists($path)){
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
                $bytestotal += $object->getSize();
            }
        }
        return round($bytestotal / 1048576, 2); // Convertendo bytes para MB
    }
    
    $folderSize = getFolderSize(ABSPATH . $siteInfo->pasta);
?>

<h2>Administração do seu site</h2>
<style>
    .progress-circle {
        position: relative;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: conic-gradient(
            #6c757d 0% <?= $folderSize ?>%, 
            #e9ecef <?= $folderSize ?>% 100%
        );
    }
    .progress-circle i {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
</style>
<div class="container mt-4">

    <div class="row">

        <!-- Status Ativo/Desativado -->
        <div class="col-md-4 text-center">
            <?php if($siteInfo->active == 1): ?>
                <i class="fas fa-check-circle fa-3x text-success"></i>
                <p class="mt-2">Ativo</p>
            <?php else: ?>
                <i class="fas fa-times-circle fa-3x text-danger"></i>
                <p class="mt-2">Desativado</p>
            <?php endif; ?>
        </div>

        <!-- Tamanho da Pasta -->
        <div class="col-md-4 text-center">
            <div class="progress-circle" data-percentage="<?= ($folderSize / 5000) * 100 ?>">
                <!-- Aqui, você deve substituir MAX_FOLDER_SIZE pela capacidade máxima permitida para a pasta. -->
                <i class="fas fa-folder fa-3x"></i>
            </div>
            <p class="mt-2"><?= $folderSize ?> MB</p>
        </div>

        <!-- Botões de Ação -->
        <div class="col-md-4 text-center">
            <a href="<?= $siteInfo->url ?>/wp-admin" class="btn btn-primary mb-2">
                <i class="fas fa-sign-in-alt"></i> Fazer Login
            </a>
            <br>
            <a href="<?= $siteInfo->url ?>" class="btn btn-secondary">
                <i class="fas fa-globe"></i> Ver Site
            </a>
        </div>
    </div>
</div>



            <?php
                    
                    break;
                    case 'meus-sites':
            ?>
                <h2>Meus Sites</h2>
                <!-- Listagem das URLs -->
                <?php if ($urls): ?>
                <div class="mt-5">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>URL</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($urls as $row): ?>
                            <tr>
                                <td><?php echo $row->url; ?></td>
                                <td>
                                    <a href="?pg=administrar-site&id=<?php echo $row->id; ?>" class="btn btn-primary btn-sm mr-2">Administrar</a>
                                    <button class="btn btn-danger btn-sm btn-delete-site" data-id="<?php echo $row->id; ?>">Excluir</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p>Nenhuma URL encontrada para o usuário atual.</p>
                <?php endif; ?>

            <?php
                    break;
                    default:
            ?>
                <h2>Dashboard</h2>
                <div class="row mt-4">
                    <div class="col-md-4">
                        <a href="?pg=meus-sites">
                        <div class="dashboard-block">
                            <i class="fa fa-globe fa-3x mb-3"></i>
                            <h4>Meus Sites</h4>
                            <p>Veja todos os seus sites em um só lugar.</p>
                        </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-block">
                            <i class="fa fa-file-invoice-dollar fa-3x mb-3"></i>
                            <h4>Faturas</h4>
                            <p>Gerencie suas faturas e pagamentos.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-block">
                            <i class="fa fa-user-circle fa-3x mb-3"></i>
                            <h4>Minha Conta</h4>
                            <p>Edite seus detalhes e preferências.</p>
                        </div>
                    </div>
                </div>

            <?php
                endswitch;
            else:
            ?>
                              <h2>Dashboard</h2>
                <div class="row mt-4">
                    <div class="col-md-4">
                        <a href="?pg=meus-sites">
                        <div class="dashboard-block">
                            <i class="fa fa-globe fa-3x mb-3"></i>
                            <h4>Meus Sites</h4>
                            <p>Veja todos os seus sites em um só lugar.</p>
                        </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-block">
                            <i class="fa fa-file-invoice-dollar fa-3x mb-3"></i>
                            <h4>Faturas</h4>
                            <p>Gerencie suas faturas e pagamentos.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-block">
                            <i class="fa fa-user-circle fa-3x mb-3"></i>
                            <h4>Minha Conta</h4>
                            <p>Edite seus detalhes e preferências.</p>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </main>
    </div>
</div>

<?php
    return ob_get_clean();
}
add_shortcode('area_do_cliente', 'shortcode_area_do_cliente');

function enqueue_my_scripts() {
    // Enfileirar jQuery, se ainda não estiver enfileirado
    wp_enqueue_script('jquery');

    // Enfileirar seu script personalizado
    wp_enqueue_script('delete-site-script', plugins_url('delete-site.js', __FILE__), array('jquery'), null, true);

    // Localizar o script com os dados do diretório do plugin
    $plugin_directory_name = dirname(plugin_basename(__FILE__));
    $plugin_url = plugins_url($plugin_directory_name);
    wp_localize_script('delete-site-script', 'pluginInfo', array(
        'url' => $plugin_url
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_my_scripts');



?>
