<?php
require_once( '../../../../wp-load.php' );
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Lista de nomes proibidos
$prohibited_names = array(
    "wp-admin",
    "wp-content",
    "wp-includes",
    "index.php",
    // ... adicione outros nomes conforme necessário
);

// Verificando se o nome é proibido
if (in_array($novaPasta, $prohibited_names)) {
    echo "Por favor, escolha outro nome. O nome escolhido é reservado.";
    exit;
}

// Verificando se a pasta já existe
if (is_dir('../../../../' . $novaPasta)) {
    echo "Esta pasta já existe. Por favor, escolha outro nome.";
    exit;
}

// Verificando se o nome contém apenas letras
if (!preg_match("/^[a-zA-Z]*$/", $novaPasta)) {
    echo "O nome deve conter apenas letras, sem espaços, números ou caracteres especiais.";
    exit;
}



    $modelo = $_POST['modelo'];
    $novaPasta = $_POST['novaPasta'];

function recurse_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function exportDatabase($db_host, $db_user, $db_pass, $db_name, $prefix) {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    $tables = [];
    $result = $conn->query("SHOW TABLES LIKE '{$prefix}%'");  // Filtra as tabelas pelo prefixo
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    $sql = "";
    foreach ($tables as $table) {
        $result = $conn->query("SELECT * FROM $table");
        $numFields = $result->field_count;

        $sql .= "DROP TABLE IF EXISTS $table;";
        $row2 = $conn->query("SHOW CREATE TABLE $table")->fetch_row();
        $sql .= "\n\n" . $row2[1] . ";\n\n";

        for ($i = 0; $i < $numFields; $i++) {
            while ($row = $result->fetch_row()) {
                $sql .= "INSERT INTO $table VALUES(";
                for ($j = 0; $j < $numFields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = preg_replace("/\n/", "\\n", $row[$j]);
                    if (isset($row[$j])) {
                        $sql .= '"' . $row[$j] . '"';
                    } else {
                        $sql .= 'NULL';
                    }
                    if ($j < ($numFields - 1)) {
                        $sql .= ',';
                    }
                }
                $sql .= ");\n";
            }
        }
        $sql .= "\n\n\n";
    }

    $conn->close();

    return $sql;
}



    // 2. Clonar arquivos
recurse_copy('../../../../modelos/' . $modelo, '../../../../' . $novaPasta);



    // 3. Ler wp-config.php
$wp_config = file_get_contents("../../../../modelos/" . $modelo . "/wp-config.php");
preg_match("/define\(\s*'DB_NAME'\s*,\s*'(.+?)'\s*\);/", $wp_config, $matches);
$db_name = $matches[1] ?? null;

preg_match("/define\(\s*'DB_USER'\s*,\s*'(.+?)'\s*\);/", $wp_config, $matches);
$db_user = $matches[1] ?? null;

preg_match("/define\(\s*'DB_PASSWORD'\s*,\s*'(.+?)'\s*\);/", $wp_config, $matches);
$db_pass = $matches[1] ?? null;

// Adicione este se você também precisar do host. Se não estiver no seu exemplo, você pode omiti-lo.
preg_match("/define\(\s*'DB_HOST'\s*,\s*'(.+?)'\s*\);/", $wp_config, $matches);
$db_host = $matches[1] ?? 'localhost';  // Usando 'localhost' como padrão se não for encontrado.

preg_match("/table_prefix\s*=\s*'(.+?)';/", $wp_config, $matches);
$prefix = $matches[1] ?? 'wp_';  // Usando 'wp_' como padrão, que é o padrão do WordPress.
$new_prefix = $prefix . "_clone_";


    // 4. Exportar DB usando PHP puro
    $sql = exportDatabase($db_host, $db_user, $db_pass, $db_name, $prefix);
file_put_contents('../../../../' . $novaPasta . '/temp.sql', $sql);

function generateRandomString($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$new_prefix = generateRandomString() . "_";


$new_wp_config = str_replace($prefix, $new_prefix, $wp_config);
file_put_contents('../../../../' . $novaPasta . '/wp-config.php', $new_wp_config);


$sqlContent = file_get_contents('../../../../' . $novaPasta . '/temp.sql');
$newSqlContent = str_replace($prefix, $new_prefix, $sqlContent);
file_put_contents('../../../../' . $novaPasta . '/temp.sql', $newSqlContent);

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
$sqlContent = file_get_contents('../../../../' . $novaPasta . '/temp.sql');
$conn->multi_query($sqlContent);

// Use o loop para buscar todos os conjuntos de resultados
do {
    if ($result = $conn->store_result()) {
        $result->free();
    }
} while ($conn->more_results() && $conn->next_result());

// Agora, execute a próxima consulta
    $new_url = "http://$_SERVER[HTTP_HOST]/$novaPasta";
if (!$conn->query("UPDATE {$new_prefix}options SET option_value = '$new_url' WHERE option_name = 'siteurl' OR option_name = 'home'")) {
    echo "Error updating record: " . $conn->error;
}
 // Detectar e atualizar o prefixo na tabela _options para _user_roles
$query_detect_prefix = "SELECT option_name FROM {$new_prefix}options WHERE option_name LIKE '%_user_roles'";
$result = $conn->query($query_detect_prefix);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $old_prefix = explode("_", $row['option_name'])[0] . "_";
    $update_user_roles_query = "UPDATE {$new_prefix}options SET option_name = REPLACE(option_name, '$old_prefix', '{$new_prefix}') WHERE option_name LIKE '%_user_roles'";
    if ($conn->query($update_user_roles_query)) {
        echo "Opção user_roles atualizada com sucesso.<br>";
    } else {
        echo "Erro ao atualizar a opção user_roles: " . $conn->error . "<br>";
    }
}

// Detectar e atualizar o prefixo na tabela _usermeta para _capabilities
$query_detect_prefix_meta = "SELECT meta_key FROM {$new_prefix}usermeta WHERE meta_key LIKE '%_capabilities' LIMIT 1";
$result_meta = $conn->query($query_detect_prefix_meta);
if ($result_meta && $result_meta->num_rows > 0) {
    $row_meta = $result_meta->fetch_assoc();
    $old_prefix_meta = explode("_", $row_meta['meta_key'])[0] . "_";
    $update_capabilities_query = "UPDATE {$new_prefix}usermeta SET meta_key = REPLACE(meta_key, '$old_prefix_meta', '{$new_prefix}') WHERE meta_key LIKE '%_capabilities'";
    if ($conn->query($update_capabilities_query)) {
        echo "Meta key capabilities atualizada com sucesso.<br>";
    } else {
        echo "Erro ao atualizar a meta key capabilities: " . $conn->error . "<br>";
    }
}

global $wpdb;

// Obtenha o ID e o nome do usuário logado
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$user_name = $current_user->user_login;

// Prepare os dados para inserção
$data = array(
    'user_id' => $user_id,
    'user_name' => $user_name,
    'url' => $new_url,
    'active' => 1,
    'date_time_created' => date('Y-m-d H:i:s'),  // Formato atual: 1984-10-20 23:20:20
    'pasta' => $novaPasta,
    'prefixo' => $new_prefix  // Adicione esta linha
);

// Insira os dados na tabela
$table_name = $wpdb->prefix . "mr_sites";
$wpdb->insert($table_name, $data);




?>