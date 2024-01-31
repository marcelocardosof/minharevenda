<?php
// Carregar o WordPress
require_once("../../../../wp-load.php");

if ($_POST['action'] == 'delete_site') {
    global $wpdb;

    $siteId = $_POST['site_id'];

    try {
        // Buscar informações do site
        $siteInfo = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "mr_sites WHERE id = " . $siteId);

        if (!$siteInfo) {
            throw new Exception("Site não encontrado.");
        }

        // Encontrar e deletar tabelas com o prefixo
        $tables = $wpdb->get_results("SHOW TABLES LIKE '" . $siteInfo->prefixo . "%'", ARRAY_N);
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE " . $table[0]);
        }

        // Montar o caminho completo para a pasta
        $path_to_folder = ABSPATH . $siteInfo->pasta;

        // Deletar pasta (cuidado com esta operação, pois é irreversível)
        rrmdir($path_to_folder);

        // Deletar registro no banco de dados
        $rows_deleted = $wpdb->delete($wpdb->prefix . "mr_sites", array("id" => $siteId));
        if (!$rows_deleted) {
            throw new Exception("Falha ao deletar o registro do site no banco de dados.");
        }

        // Se tudo ocorreu bem, retornar sucesso
        echo json_encode(array("success" => true));

    } catch (Exception $e) {
        // Se algum erro ocorreu, retornar falha
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    exit;
}

// Função para deletar uma pasta e seu conteúdo
function rrmdir($src) {
    $dir = opendir($src);
    while (false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if (is_dir($full)) {
                rrmdir($full);
            } else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}
?>
