<?php

if ($_FILES['file']['error'] == 0 && $_FILES['screenshot']['error'] == 0) {
    $zip = new ZipArchive;
    $tmp_name = $_FILES['file']['tmp_name'];
    $zip_path = '../../../../modelos/' . $_FILES['file']['name'];

    $i = 1;
    do {
        $extractPath = '../../../../modelos/modelo' . $i;
        $i++;
    } while (file_exists($extractPath));

    if (!@mkdir($extractPath, 0777, true)) {
        $error = error_get_last();
        die('Falha ao criar o diretório. Erro: ' . $error['message']);
    }

    if (move_uploaded_file($tmp_name, $zip_path)) {
        if ($zip->open($zip_path) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
            move_uploaded_file($_FILES['screenshot']['tmp_name'], $extractPath . '/screenshot.png');
            echo "Arquivo descompactado e imagem salva com sucesso em $extractPath.";
        } else {
            echo 'Erro ao descompactar o arquivo.';
        }
        unlink($zip_path);
    } else {
        echo 'Falha ao mover os arquivos carregados.';
    }



// Etapa 1: Ler o wp-config.php da raiz para identificar as credenciais do banco de dados.
$wp_config = file_get_contents('../../../../wp-config.php');

preg_match("/define\(\s*'DB_NAME'\s*,\s*'(.+?)'\s*\);/", $wp_config, $matches);
$db_name = $matches[1] ?? null;

preg_match("/define\(\s*'DB_USER'\s*,\s*'(.+?)'\s*\);/", $wp_config, $matches);
$db_user = $matches[1] ?? null;

preg_match("/define\(\s*'DB_PASSWORD'\s*,\s*'(.+?)'\s*\);/", $wp_config, $matches);
$db_pass = $matches[1] ?? null;

preg_match("/define\(\s*'DB_HOST'\s*,\s*'(.+?)'\s*\);/", $wp_config, $matches);
$db_host = $matches[1] ?? 'localhost';

preg_match("/define\(\s*'DB_CHARSET'\s*,\s*'(.+?)'\s*\);/", $wp_config, $matches);
$db_charset = $matches[1] ?? 'utf8';

preg_match("/define\(\s*'DB_COLLATE'\s*,\s*'(.+?)'\s*\);/", $wp_config, $matches);
$db_collate = $matches[1] ?? '';

// Etapa 2: Ler o wp-config.php do arquivo que foi extraído para identificar o prefixo das tabelas.
$modelo_wp_config = file_get_contents("$extractPath/wp-config.php");
preg_match("/table_prefix\s*=\s*'(.+?)';/", $modelo_wp_config, $matches);
$original_prefix = $matches[1] ?? 'wp_';

// Etapa 3: Verificar no banco de dados se tabelas com esse prefixo já existem.
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
$result = $conn->query("SHOW TABLES LIKE '{$original_prefix}%'");
$counter = 1;
$new_prefix = $original_prefix;

while ($result && $result->num_rows > 0) {
    $new_prefix = $counter . $original_prefix;
    $result = $conn->query("SHOW TABLES LIKE '{$new_prefix}%'");
    $counter++;
}

// Se o novo prefixo é diferente do prefixo original, atualize o wp-config.php do modelo extraído.
if ($new_prefix != $original_prefix) {
    $modelo_wp_config = str_replace($original_prefix, $new_prefix, $modelo_wp_config);
    file_put_contents("$extractPath/wp-config.php", $modelo_wp_config);
}

// Substituir as credenciais de banco de dados no wp-config.php do modelo extraído
$replacements = [
    "/define\(\s*'DB_NAME'\s*,\s*'(.+?)'\s*\);/" => "define('DB_NAME', '$db_name');",
    "/define\(\s*'DB_USER'\s*,\s*'(.+?)'\s*\);/" => "define('DB_USER', '$db_user');",
    "/define\(\s*'DB_PASSWORD'\s*,\s*'(.+?)'\s*\);/" => "define('DB_PASSWORD', '$db_pass');",
    "/define\(\s*'DB_HOST'\s*,\s*'(.+?)'\s*\);/" => "define('DB_HOST', '$db_host');",
    "/define\(\s*'DB_CHARSET'\s*,\s*'(.+?)'\s*\);/" => "define('DB_CHARSET', '$db_charset');",
    "/define\(\s*'DB_COLLATE'\s*,\s*'(.+?)'\s*\);/" => "define('DB_COLLATE', '$db_collate');"
];

foreach ($replacements as $pattern => $replacement) {
    $modelo_wp_config = preg_replace($pattern, $replacement, $modelo_wp_config);
}

file_put_contents("$extractPath/wp-config.php", $modelo_wp_config);




// Etapa 4: Detectar o arquivo .sql na pasta modelo e importar.
$files = scandir($extractPath);
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
        $sql_file_path = $extractPath . '/' . $file;
        break;
    }
}

if (isset($sql_file_path)) {
    $sql_content = file_get_contents($sql_file_path);
    
    // Substituir o prefixo original pelo novo prefixo no conteúdo SQL
    $sql_content_modified = str_replace("`$original_prefix", "`$new_prefix", $sql_content);
    
    if ($conn->multi_query($sql_content_modified)) {
        while ($conn->more_results() && $conn->next_result());
        echo "Arquivo .sql importado com sucesso com o prefixo $new_prefix.<br>";
    } else {
        echo "Erro ao importar o arquivo .sql: " . $conn->error . "<br>";
    }
} else {
    echo "Arquivo .sql não encontrado.<br>";
}




// Atualizar URLs no banco de dados
$new_url = "http://$_SERVER[HTTP_HOST]/modelos/modelo" . ($i - 1);
$update_url_query = "UPDATE {$new_prefix}options SET option_value = '$new_url' WHERE option_name = 'siteurl' OR option_name = 'home'";
if ($conn->query($update_url_query)) {
    echo "URL atualizada com sucesso.<br>";
} else {
    echo "Erro ao atualizar a URL: " . $conn->error . "<br>";
}

// Detectar e atualizar o prefixo na tabela _options para _user_roles
$query_detect_prefix = "SELECT option_name FROM {$new_prefix}options WHERE option_name LIKE '%user_roles'";
$result = $conn->query($query_detect_prefix);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $old_prefix = explode("_", $row['option_name'])[0] . "_";
    $update_user_roles_query = "UPDATE {$new_prefix}options SET option_name = REPLACE(option_name, '$old_prefix', '{$new_prefix}') WHERE option_name LIKE '%user_roles'";
    if ($conn->query($update_user_roles_query)) {
        echo "Opção user_roles atualizada com sucesso.<br>";
    } else {
        echo "Erro ao atualizar a opção user_roles: " . $conn->error . "<br>";
    }
}

// Detectar e atualizar o prefixo na tabela _usermeta para _capabilities
$query_detect_prefix_meta = "SELECT meta_key FROM {$new_prefix}usermeta WHERE meta_key LIKE '%capabilities' LIMIT 1";
$result_meta = $conn->query($query_detect_prefix_meta);
if ($result_meta && $result_meta->num_rows > 0) {
    $row_meta = $result_meta->fetch_assoc();
    $old_prefix_meta = explode("_", $row_meta['meta_key'])[0] . "_";
    $update_capabilities_query = "UPDATE {$new_prefix}usermeta SET meta_key = REPLACE(meta_key, '$old_prefix_meta', '{$new_prefix}') WHERE meta_key LIKE '%capabilities'";
    if ($conn->query($update_capabilities_query)) {
        echo "Meta key capabilities atualizada com sucesso.<br>";
    } else {
        echo "Erro ao atualizar a meta key capabilities: " . $conn->error . "<br>";
    }
}








    

} else {
    echo 'Erro no upload dos arquivos.';
}
?>
